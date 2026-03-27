<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Http\Controllers\Web;

use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class CampaignPageController
{
    public function index(): InertiaResponse
    {
        return Inertia::render('ai-campaigns/CampaignsIndexPage');
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('ai-campaigns/CampaignCreatePage');
    }

    public function show(string $uuid): InertiaResponse
    {
        return Inertia::render('ai-campaigns/CampaignShowPage', ['uuid' => $uuid]);
    }
}
