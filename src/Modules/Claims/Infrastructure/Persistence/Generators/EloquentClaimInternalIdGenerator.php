<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Generators;

use Src\Modules\Claims\Domain\Ports\ClaimInternalIdGeneratorPort;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

final class EloquentClaimInternalIdGenerator implements ClaimInternalIdGeneratorPort
{
    private const PREFIX = 'AQ-';
    private const PAD_LENGTH = 6;

    #[\NoDiscard('Generated AQ- ID must be captured and persisted')]
    public function nextId(): string
    {
        $max = ClaimEloquentModel::withTrashed()
            ->where('claim_internal_id', 'like', self::PREFIX . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(claim_internal_id, 4) AS UNSIGNED)) as max_seq')
            ->value('max_seq');

        $next = (int) ($max ?? 0) + 1;

        return self::PREFIX . str_pad((string) $next, self::PAD_LENGTH, '0', STR_PAD_LEFT);
    }
}
