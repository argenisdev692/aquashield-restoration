<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AI\Domain\Ports\ResearchPort;
use Modules\AI\Domain\Ports\TextGenerationPort;
use Modules\AICampaigns\Application\Commands\CreateCampaign\CreateCampaignHandler;
use Modules\AICampaigns\Application\Commands\DeleteCampaign\DeleteCampaignHandler;
use Modules\AICampaigns\Application\Commands\GenerateCampaign\GenerateCampaignHandler;
use Modules\AICampaigns\Application\Commands\RestoreCampaign\RestoreCampaignHandler;
use Modules\AICampaigns\Application\Commands\UpdateCampaign\UpdateCampaignHandler;
use Modules\AICampaigns\Application\Queries\GetCampaign\GetCampaignHandler;
use Modules\AICampaigns\Application\Queries\ListCampaigns\ListCampaignsHandler;
use Modules\AICampaigns\Domain\Ports\CampaignGenerationPort;
use Modules\AICampaigns\Domain\Ports\CampaignImagePort;
use Modules\AICampaigns\Domain\Ports\CampaignRepositoryPort;
use Modules\AICampaigns\Infrastructure\ExternalServices\Ai\CampaignGenerationAdapter;
use Modules\AICampaigns\Infrastructure\ExternalServices\Ai\CampaignReplicateAdapter;
use Modules\AICampaigns\Infrastructure\Http\Controllers\Api\AdminCampaignController;
use Modules\AICampaigns\Infrastructure\Persistence\Repositories\EloquentCampaignRepository;
use Shared\Infrastructure\Audit\AuditInterface;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;

final class AICampaignsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CampaignRepositoryPort::class, EloquentCampaignRepository::class);

        $this->app->bind(
            CampaignImagePort::class,
            fn (): CampaignReplicateAdapter => new CampaignReplicateAdapter(
                apiToken:        (string) config('services.replicate.api_token'),
                baseUrl:         (string) config('services.replicate.base_url'),
                imageModel:      (string) config('services.replicate.image_model'),
                outputFormat:    (string) config('services.replicate.image_output_format'),
                outputQuality:   (int)    config('services.replicate.image_output_quality'),
                safetyTolerance: (int)    config('services.replicate.image_safety_tolerance'),
                promptUpsampling:(bool)   config('services.replicate.image_prompt_upsampling'),
                waitSeconds:     (int)    config('services.replicate.wait_seconds'),
                storageDisk:     (string) config('services.replicate.storage_disk'),
                storageDirectory:(string) config('services.replicate.storage_directory'),
                circuitBreaker:  $this->app->make(CircuitBreakerInterface::class),
            ),
        );

        $this->app->bind(
            CampaignGenerationPort::class,
            fn (): CampaignGenerationAdapter => new CampaignGenerationAdapter(
                textGen:  $this->app->make(TextGenerationPort::class),
                research: $this->app->make(ResearchPort::class),
                imageGen: $this->app->make(CampaignImagePort::class),
            ),
        );

        $this->app->bind(
            GenerateCampaignHandler::class,
            fn (): GenerateCampaignHandler => new GenerateCampaignHandler(
                generation: $this->app->make(CampaignGenerationPort::class),
                repository: $this->app->make(CampaignRepositoryPort::class),
                audit:      $this->app->make(AuditInterface::class),
            ),
        );

        $this->app->bind(
            AdminCampaignController::class,
            fn (): AdminCampaignController => new AdminCampaignController(
                createHandler:   $this->app->make(CreateCampaignHandler::class),
                updateHandler:   $this->app->make(UpdateCampaignHandler::class),
                deleteHandler:   $this->app->make(DeleteCampaignHandler::class),
                restoreHandler:  $this->app->make(RestoreCampaignHandler::class),
                generateHandler: $this->app->make(GenerateCampaignHandler::class),
                getHandler:      $this->app->make(GetCampaignHandler::class),
                listHandler:     $this->app->make(ListCampaignsHandler::class),
            ),
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('ai-campaigns')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
