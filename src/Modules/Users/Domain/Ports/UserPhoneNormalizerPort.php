<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Ports;

interface UserPhoneNormalizerPort
{
    public function normalize(?string $phone): ?string;
}
