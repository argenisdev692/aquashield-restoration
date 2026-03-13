<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\ExternalServices\Phone;

use Modules\Users\Domain\Ports\UserPhoneNormalizerPort;
use Shared\Infrastructure\Utils\PhoneHelper;

final class UserPhoneNormalizerAdapter implements UserPhoneNormalizerPort
{
    public function normalize(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        return PhoneHelper::normalizeUs($phone);
    }
}
