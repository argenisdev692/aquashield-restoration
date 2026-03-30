<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Domain\Ports;

interface ClaimInternalIdGeneratorPort
{
    public function nextId(): string;
}
