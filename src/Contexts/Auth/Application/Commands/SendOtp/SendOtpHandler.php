<?php

declare(strict_types=1);

namespace Src\Contexts\Auth\Application\Commands\SendOtp;

use Src\Contexts\Auth\Domain\Exceptions\UserNotFoundException;
use Src\Contexts\Auth\Domain\Ports\OtpServicePort;
use Src\Contexts\Auth\Domain\Ports\UserRepositoryPort;

/**
 * SendOtpHandler — Validates user exists, generates OTP, sends notification.
 */
final readonly class SendOtpHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private OtpServicePort $otpService,
    ) {
    }

    public function handle(SendOtpCommand $command): void
    {
        $user = $this->userRepository->findByEmailOrPhone($command->identifier);

        if ($user === null) {
            throw UserNotFoundException::withIdentifier($command->identifier);
        }

        $this->otpService->send($command->identifier);
    }
}
