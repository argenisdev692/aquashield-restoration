<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Domain\Exceptions;

use Exception;

final class CompanyDataNotFoundException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf("CompanyData with ID '%s' not found.", $id));
    }
}
