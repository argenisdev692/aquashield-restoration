<?php

declare(strict_types=1);

namespace Modules\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AI\Domain\Ports\ImageGenerationPort;
use Modules\AI\Domain\Ports\ResearchPort;
use Modules\AI\Domain\Ports\TextGenerationPort;
use Modules\AI\Infrastructure\Anthropic\AnthropicAdapter;
use Modules\AI\Infrastructure\Replicate\ReplicateImageAdapter;
use Modules\AI\Infrastructure\Tavily\TavilyResearchAdapter;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;

/**
 * AIServiceProvider — Binds all AI domain ports to their infrastructure adapters.
 *
 * Add new providers here (OpenAI, Replicate video, etc.) as the module grows.
 * All adapters are singletons: stateless HTTP clients with no per-request state.
 */
final class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            TextGenerationPort::class,
            fn (): AnthropicAdapter => new AnthropicAdapter(
                apiKey:         (string) config('services.anthropic.api_key', ''),
                apiUrl:         (string) config('services.anthropic.api_url'),
                version:        (string) config('services.anthropic.version'),
                model:          (string) config('services.ai.default_model'),
                circuitBreaker: $this->app->make(CircuitBreakerInterface::class),
            ),
        );

        $this->app->singleton(
            ResearchPort::class,
            fn (): TavilyResearchAdapter => new TavilyResearchAdapter(
                apiKey:         (string) config('services.tavily.api_key', ''),
                searchUrl:      (string) config('services.tavily.search_url'),
                searchDepth:    (string) config('services.tavily.search_depth'),
                maxResults:     (int)    config('services.tavily.max_results'),
                circuitBreaker: $this->app->make(CircuitBreakerInterface::class),
            ),
        );

        $this->app->singleton(
            ImageGenerationPort::class,
            fn (): ReplicateImageAdapter => new ReplicateImageAdapter(
                apiToken:         (string) config('services.replicate.api_token', ''),
                baseUrl:          (string) config('services.replicate.base_url'),
                imageModel:       (string) config('services.replicate.image_model'),
                aspectRatio:      (string) config('services.replicate.image_aspect_ratio'),
                outputFormat:     (string) config('services.replicate.image_output_format'),
                outputQuality:    (int)    config('services.replicate.image_output_quality'),
                safetyTolerance:  (int)    config('services.replicate.image_safety_tolerance'),
                promptUpsampling: (bool)   config('services.replicate.image_prompt_upsampling'),
                waitSeconds:      (int)    config('services.replicate.wait_seconds'),
                storageDisk:      (string) config('services.replicate.storage_disk'),
                storageDirectory: (string) config('services.replicate.storage_directory_posts'),
                circuitBreaker:   $this->app->make(CircuitBreakerInterface::class),
            ),
        );
    }

    public function boot(): void
    {
    }
}
