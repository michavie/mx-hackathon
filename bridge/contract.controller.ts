import { DeployRequest } from './types'
import { ContractService } from './contract.service'
import { Body, Controller, Post } from '@nestjs/common'

@Controller('contracts')
export class ContractController {
  constructor(private readonly contractService: ContractService) {}

  @Post('deploy')
  async deploy(@Body() request: DeployRequest) {
    const txHash = await this.contractService.deployContract(
      request.network,
      request.artifact.url,
      request.contract.address,
      request.contract.initArgs
    )

    return {
      data: {
        tx: txHash.toString(),
      },
    }
  }
}
