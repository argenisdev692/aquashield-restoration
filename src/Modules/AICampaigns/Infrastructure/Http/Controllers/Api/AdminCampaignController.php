<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\AICampaigns\Application\Commands\CreateCampaign\CreateCampaignCommand;
use Modules\AICampaigns\Application\Commands\CreateCampaign\CreateCampaignHandler;
use Modules\AICampaigns\Application\Commands\DeleteCampaign\DeleteCampaignCommand;
use Modules\AICampaigns\Application\Commands\DeleteCampaign\DeleteCampaignHandler;
use Modules\AICampaigns\Application\Commands\GenerateCampaign\GenerateCampaignCommand;
use Modules\AICampaigns\Application\Commands\GenerateCampaign\GenerateCampaignHandler;
use Modules\AICampaigns\Application\Commands\RestoreCampaign\RestoreCampaignCommand;
use Modules\AICampaigns\Application\Commands\RestoreCampaign\RestoreCampaignHandler;
use Modules\AICampaigns\Application\Commands\UpdateCampaign\UpdateCampaignCommand;
use Modules\AICampaigns\Application\Commands\UpdateCampaign\UpdateCampaignHandler;
use Modules\AICampaigns\Application\DTOs\CampaignFilterDTO;
use Modules\AICampaigns\Application\DTOs\CreateCampaignDTO;
use Modules\AICampaigns\Application\DTOs\GenerateCampaignDTO;
use Modules\AICampaigns\Application\DTOs\UpdateCampaignDTO;
use Modules\AICampaigns\Application\Queries\GetCampaign\GetCampaignHandler;
use Modules\AICampaigns\Application\Queries\GetCampaign\GetCampaignQuery;
use Modules\AICampaigns\Application\Queries\ListCampaigns\ListCampaignsHandler;
use Modules\AICampaigns\Application\Queries\ListCampaigns\ListCampaignsQuery;
use Modules\AICampaigns\Infrastructure\Http\Requests\CampaignFilterRequest;
use Modules\AICampaigns\Infrastructure\Http\Requests\CreateCampaignRequest;
use Modules\AICampaigns\Infrastructure\Http\Requests\GenerateCampaignRequest;
use Modules\AICampaigns\Infrastructure\Http\Requests\UpdateCampaignRequest;
use Modules\AICampaigns\Infrastructure\Http\Resources\CampaignResource;

final class AdminCampaignController
{
    public function __construct(
        private readonly CreateCampaignHandler   $createHandler,
        private readonly UpdateCampaignHandler   $updateHandler,
        private readonly DeleteCampaignHandler   $deleteHandler,
        private readonly RestoreCampaignHandler  $restoreHandler,
        private readonly GenerateCampaignHandler $generateHandler,
        private readonly GetCampaignHandler      $getHandler,
        private readonly ListCampaignsHandler    $listHandler,
    ) {
    }

    public function index(CampaignFilterRequest $request): JsonResponse
    {
        $result = $this->listHandler->handle(
            new ListCampaignsQuery(CampaignFilterDTO::from($request->validated()))
        );

        return response()->json($result);
    }

    public function store(CreateCampaignRequest $request): JsonResponse
    {
        $campaign = $this->createHandler->handle(
            new CreateCampaignCommand(CreateCampaignDTO::from($request->validated()))
        );

        return response()->json(['data' => new CampaignResource($campaign)], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $readModel = $this->getHandler->handle(new GetCampaignQuery($uuid));

        return response()->json(['data' => $readModel->toArray()]);
    }

    public function update(UpdateCampaignRequest $request, string $uuid): JsonResponse
    {
        $campaign = $this->updateHandler->handle(
            new UpdateCampaignCommand($uuid, UpdateCampaignDTO::from($request->validated()))
        );

        return response()->json(['data' => new CampaignResource($campaign)]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteCampaignCommand($uuid));

        return response()->json(null, 204);
    }

    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle(new RestoreCampaignCommand($uuid));

        return response()->json(['message' => 'Campaign restored successfully.']);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuids'   => ['required', 'array'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        foreach ($validated['uuids'] as $uuid) {
            $this->deleteHandler->handle(new DeleteCampaignCommand((string) $uuid));
        }

        return response()->json(null, 204);
    }

    public function generate(GenerateCampaignRequest $request): JsonResponse
    {
        $campaign = $this->generateHandler->handle(
            new GenerateCampaignCommand(GenerateCampaignDTO::from($request->validated()))
        );

        return response()->json(['data' => new CampaignResource($campaign)], 201);
    }
}
