import JSZip from 'jszip'
import config from 'src/config'
import { toChainId } from 'src/helpers'
import { ChainNetwork } from 'src/types'
import { Injectable } from '@nestjs/common'
import { AdminService } from 'src/admin/admin.service'
import {
  Code,
  Address,
  U64Value,
  TypedValue,
  BytesValue,
  AbiRegistry,
  Transaction,
  Interaction,
  CodeMetadata,
  AddressValue,
  SmartContract,
  TransactionHash,
  NativeSerializer,
  ContractFunction,
} from '@multiversx/sdk-core'

@Injectable()
export class ContractService {
  constructor(private readonly adminService: AdminService) {}

  async deployContract(network: ChainNetwork, artifactUrl: string, address: string | null, initArgs: any[]): Promise<TransactionHash> {
    const data = await this.fetchArtifact(artifactUrl)
    const zip = await this.unpackZip(data)
    const code = await this.extractCodeFromZippedArtifact(zip)
    const abi = await this.extractAbiFromZippedArtifact(zip)
    const args = this.serializeInitArgs(abi, initArgs)

    if (address) {
      const txHash = await this.upgrade(network, code, new Address(address), args)
      console.log('Upgraded contract with address:', address)
      return txHash
    } else {
      const txHash = await this.deploy(network, code, args)
      console.log('Deployed contract with tx:', txHash.toString())
      return txHash
    }
  }

  private async fetchArtifact(url: string): Promise<ArrayBuffer> {
    const response = await fetch(url)

    if (!response.ok) {
      throw new Error(`Failed to fetch artifact from URL: ${url}`)
    }

    return response.arrayBuffer()
  }

  private async unpackZip(data: ArrayBuffer): Promise<JSZip> {
    const newZip = new JSZip()

    return newZip.loadAsync(data)
  }

  private async extractCodeFromZippedArtifact(zip: JSZip): Promise<Code> {
    const wasmFiles = Object.keys(zip.files).filter((filename) => filename.startsWith('raw/') && filename.endsWith('.wasm'))
    const wasmFileName = wasmFiles[0]
    const wasmFile = await zip.file(wasmFileName)?.async('nodebuffer')

    if (!wasmFile) {
      throw new Error('Failed to extract WASM file from the archive')
    }

    return Code.fromBuffer(wasmFile)
  }

  private async extractAbiFromZippedArtifact(zip: JSZip): Promise<AbiRegistry> {
    const abiFiles = Object.keys(zip.files).filter((filename) => filename.startsWith('raw/') && filename.endsWith('.abi.json'))
    const abiFileName = abiFiles[0]
    const abiFileContent = await zip.file(abiFileName)?.async('string')

    if (!abiFileContent) {
      throw new Error('Failed to extract WASM file from the archive')
    }

    return AbiRegistry.create(abiFileContent as any)
  }

  private serializeInitArgs(abi: AbiRegistry, initArgs: any[]): TypedValue[] {
    return NativeSerializer.nativeToTypedValues(initArgs, abi.constructorDefinition)
  }

  private async deploy(network: ChainNetwork, code: Code, initArgs: TypedValue[]): Promise<TransactionHash> {
    const sc = new SmartContract({ address: Address.fromBech32(config().chain.contracts.spawner(network)) })

    const codeArg = new BytesValue(code.valueOf())
    const codeMetadataArg = new BytesValue(new CodeMetadata(true, true, true, true).toBuffer())
    const gasLimitArg = new U64Value(100_000_000)

    const tx = new Interaction(sc, new ContractFunction('spawnContract'), [codeArg, codeMetadataArg, gasLimitArg, ...initArgs])
      .withChainID(toChainId(network))
      .withSender(this.adminService.getAccount(network).address)
      .withGasLimit(50_000_000)
      .buildTransaction()

    await this.adminService.signAndSend(tx, network)

    return tx.getHash()
  }

  private async upgrade(network: ChainNetwork, code: Code, address: Address, initArgs: TypedValue[]): Promise<TransactionHash> {
    const sc = new SmartContract({ address: Address.fromBech32(config().chain.contracts.spawner(network)) })

    const addressArg = new AddressValue(address)
    const codeArg = new BytesValue(code.valueOf())
    const codeMetadataArg = new BytesValue(new CodeMetadata(true, true, true, true).toBuffer())
    const gasLimitArg = new U64Value(100_000_000)

    const tx = new Interaction(sc, new ContractFunction('respawnContract'), [
      addressArg,
      codeArg,
      codeMetadataArg,
      gasLimitArg,
      ...initArgs,
    ])
      .withChainID(toChainId(network))
      .withSender(this.adminService.getAccount(network).address)
      .withGasLimit(50_000_000)
      .buildTransaction()

    await this.adminService.signAndSend(tx, network)

    return tx.getHash()
  }
}
