<?php

declare(strict_types=1);

namespace Src\Contexts\Auth\Application\Commands\VerifyOtp;

use Src\Contexts\Auth\Domain\Entities\User;
use Src\Contexts\Auth\Domain\Events\UserLoggedIn;
use Src\Contexts\Auth\Domain\Exceptions\InvalidOtpException;
use Src\Contexts\Auth\Domain\Exceptions\UserNotFoundException;
use Src\Contexts\Auth\Domain\Ports\OtpServicePort;
use Src\Contexts\Auth\Domain\Ports\UserRepositoryPort;

/**
 * VerifyOtpHandler — Validates OTP, authenticates user, emits domain event.
 */
final readonly class VerifyOtpHandler
{
    public function __construct(
        private UserRepositoryPort $userRepository,
        private OtpServicePort $otpService,
    ) {
    }

    /**
     * @return array{user: User, event: UserLoggedIn}
     */
    public function handle(VerifyOtpCommand $command): array
    {
        $user = $this->userRepository->findByEmailOrPhone($command->identifier);

        if ($user === null) {
            throw UserNotFoundException::withIdentifier($command->identifier);
        }

        $valid = $this->otpService->verify($command->identifier, $command->code);

        if (!$valid) {
            throw new InvalidOtpException();
        }

        $this->otpService->invalidate($command->identifier);

        $event = new UserLoggedIn(
            userId: $user->id,
            provider: 'otp',
            ipAddress: $command->ipAddress,
            userAgent: $command->userAgent,
            occurredAt: now()->toIso8601String(),
        );

        return ['user' => $user, 'event' => $event];
    }
}
