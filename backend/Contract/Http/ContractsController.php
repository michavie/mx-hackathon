<?php

namespace App\Http\Contract\Controllers;

use App\Domain\Bridge\Job\FetchSpawnedContractAddressJob;
use App\Domain\Chain;
use App\Domain\ChainNetwork;
use App\Domain\Contract\Actions\CreateContractAction;
use App\Domain\Contract\Actions\ExecuteContractProxyCallAction;
use App\Domain\Contract\Models\Contract;
use App\Http\Contract\Requests\ContractAbiStoreRequest;
use App\Http\Contract\Requests\ContractInitArgsRequest;
use App\Http\Contract\Requests\ContractStoreRequest;
use App\Http\Contract\Resources\ContractResource;
use App\Http\Controller;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContractsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $projectSlug = $request->input('project') ?? throw new InvalidArgumentException('project is required');

        $project = $request->user()->projects()
            ->withSlug($projectSlug)
            ->firstOrFail();

        $contracts = $project->contracts()
            ->get();

        return $this->ok(ContractResource::collection($contracts));
    }

    public function show(Contract $contract)
    {
        return $this->ok(new ContractResource($contract));
    }

    public function store(ContractStoreRequest $request, CreateContractAction $createContractAction)
    {
        $projectSlug = $request->input('project') ?? throw new InvalidArgumentException('project is required');

        $project = $request->user()->projects()
            ->withSlug($projectSlug)
            ->firstOrFail();

        $contract = $createContractAction->execute(
            $request->user(),
            $project,
            $request->input('name'),
        );

        return $this->ok(new ContractResource($contract));
    }

    public function abi(Contract $contract, ContractAbiStoreRequest $request)
    {
        $contract
            ->syncFromMediaLibraryRequest($request->get('abi'))
            ->toMediaCollection(Contract::MediaLibraryCollectionAbi);

        return $this->ok();
    }

    public function initArgs(Contract $contract, ContractInitArgsRequest $request)
    {
        $networkId = $request->input('network') ?? throw new InvalidArgumentException('network is required');
        $network = ChainNetwork::from($networkId);

        $contract->setInitArgs(Chain::Multiversx, $network, $request->input('args') ?? []);

        return $this->ok();
    }

    public function mainnetDeployment(Contract $contract, Request $request)
    {
        $txHash = $request->input('tx') ?? throw new InvalidArgumentException('tx is required');

        dispatch(new FetchSpawnedContractAddressJob(Chain::Multiversx, ChainNetwork::Mainnet, $contract, $txHash))
            ->delay(config('multiversx.block_time') * 2);

        return $this->ok();
    }

    public function proxyCall(Contract $contract, Request $request, ExecuteContractProxyCallAction $executeContractProxyCallAction)
    {
        $network = $request->input('network') ?? throw new InvalidArgumentException('network is required');
        $txObject = $request->input('tx') ?? throw new InvalidArgumentException('tx is required');

        $result = $executeContractProxyCallAction->execute($contract, ChainNetwork::from($network), $txObject);

        return $this->ok([
            'tx' => $result->txHash,
        ]);
    }
}
