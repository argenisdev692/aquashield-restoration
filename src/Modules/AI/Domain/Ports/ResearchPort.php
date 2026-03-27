<?php

declare(strict_types=1);

namespace Modules\AI\Domain\Ports;

use Modules\AI\Domain\ValueObjects\ResearchResult;

interface ResearchPort
{
    #[\NoDiscard('Research result must be captured and used')]
    public function research(string $query): ResearchResult;
}
