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

/**
 * @OA\Tag(
 *     name="AI Campaigns",
 *     description="AI-powered social media campaign management endpoints"
 * )
 */
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

    /**
     * List campaigns (paginated).
     *
     * @OA\Get(
     *     path="/ai-campaigns/data/admin",
     *     tags={"AI Campaigns"},
     *     summary="List campaigns",
     *     description="Get paginated list of AI campaigns with optional filtering",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="platform", in="query", required=false, @OA\Schema(type="string", enum={"tiktok","instagram","facebook"})),
     *     @OA\Parameter(name="sort_field", in="query", required=false, @OA\Schema(type="string", default="created_at")),
     *     @OA\Parameter(name="sort_direction", in="query", required=false, @OA\Schema(type="string", enum={"asc","desc"}, default="desc")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=10)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(CampaignFilterRequest $request): JsonResponse
    {
        $result = $this->listHandler->handle(
            new ListCampaignsQuery(CampaignFilterDTO::from($request->validated()))
        );

        return response()->json($result);
    }

    /**
     * Create a new campaign (manual).
     *
     * @OA\Post(
     *     path="/ai-campaigns/data/admin",
     *     tags={"AI Campaigns"},
     *     summary="Create campaign",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","niche","platform"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="niche", type="string"),
     *             @OA\Property(property="platform", type="string", enum={"tiktok","instagram","facebook"}),
     *             @OA\Property(property="caption", type="string", nullable=true),
     *             @OA\Property(property="hashtags", type="string", nullable=true),
     *             @OA\Property(property="call_to_action", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(CreateCampaignRequest $request): JsonResponse
    {
        $campaign = $this->createHandler->handle(
            new CreateCampaignCommand(CreateCampaignDTO::from($request->validated()))
        );

        return response()->json(['data' => new CampaignResource($campaign)], 201);
    }

    /**
     * Get a single campaign by UUID.
     *
     * @OA\Get(
     *     path="/ai-campaigns/data/admin/{uuid}",
     *     tags={"AI Campaigns"},
     *     summary="Get campaign by UUID",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="niche", type="string"),
     *                 @OA\Property(property="platform", type="string"),
     *                 @OA\Property(property="caption", type="string", nullable=true),
     *                 @OA\Property(property="hashtags", type="string", nullable=true),
     *                 @OA\Property(property="call_to_action", type="string", nullable=true),
     *                 @OA\Property(property="image_url", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(string $uuid): JsonResponse
    {
        $readModel = $this->getHandler->handle(new GetCampaignQuery($uuid));

        return response()->json(['data' => $readModel->toArray()]);
    }

    /**
     * Update a campaign.
     *
     * @OA\Put(
     *     path="/ai-campaigns/data/admin/{uuid}",
     *     tags={"AI Campaigns"},
     *     summary="Update campaign",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="niche", type="string"),
     *             @OA\Property(property="platform", type="string", enum={"tiktok","instagram","facebook"}),
     *             @OA\Property(property="caption", type="string", nullable=true),
     *             @OA\Property(property="hashtags", type="string", nullable=true),
     *             @OA\Property(property="call_to_action", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateCampaignRequest $request, string $uuid): JsonResponse
    {
        $campaign = $this->updateHandler->handle(
            new UpdateCampaignCommand($uuid, UpdateCampaignDTO::from($request->validated()))
        );

        return response()->json(['data' => new CampaignResource($campaign)]);
    }

    /**
     * Soft delete a campaign.
     *
     * @OA\Delete(
     *     path="/ai-campaigns/data/admin/{uuid}",
     *     tags={"AI Campaigns"},
     *     summary="Delete campaign",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteCampaignCommand($uuid));

        return response()->json(null, 204);
    }

    /**
     * Restore a soft-deleted campaign.
     *
     * @OA\Patch(
     *     path="/ai-campaigns/data/admin/{uuid}/restore",
     *     tags={"AI Campaigns"},
     *     summary="Restore deleted campaign",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle(new RestoreCampaignCommand($uuid));

        return response()->json(['message' => 'Campaign restored successfully.']);
    }

    /**
     * Bulk soft-delete campaigns.
     *
     * @OA\Post(
     *     path="/ai-campaigns/data/admin/bulk-delete",
     *     tags={"AI Campaigns"},
     *     summary="Bulk delete campaigns",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"uuids"},
     *             @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *         )
     *     ),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * AI-generate a complete campaign (content + image).
     *
     * @OA\Post(
     *     path="/ai-campaigns/data/admin/generate",
     *     tags={"AI Campaigns"},
     *     summary="Generate campaign via AI",
     *     description="Uses Anthropic + Tavily + Replicate to generate caption, hashtags, CTA and image for the campaign",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","niche","platform"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="niche", type="string"),
     *             @OA\Property(property="platform", type="string", enum={"tiktok","instagram","facebook"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Campaign generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="caption", type="string"),
     *                 @OA\Property(property="hashtags", type="string"),
     *                 @OA\Property(property="call_to_action", type="string"),
     *                 @OA\Property(property="image_url", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function generate(GenerateCampaignRequest $request): JsonResponse
    {
        $campaign = $this->generateHandler->handle(
            new GenerateCampaignCommand(GenerateCampaignDTO::from($request->validated()))
        );

        return response()->json(['data' => new CampaignResource($campaign)], 201);
    }
}
