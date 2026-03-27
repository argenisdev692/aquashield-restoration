<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Domain\Exceptions;

use Shared\Domain\Exceptions\DomainException;

final class CampaignNotFoundException extends DomainException
{
    public function __construct(string $uuid)
    {
        parent::__construct("Campaign with UUID [{$uuid}] not found.");
    }
}
