<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Utils;

use Illuminate\Support\Facades\Log;
use Modules\EmailData\Infrastructure\Persistence\Eloquent\Models\EmailDataEloquentModel;

class EmailHelper
{
    /**
     * Verify that the admin email exists and is valid.
     * 
     * @return array
     */
    public static function verifyAdminEmail(): array
    {
        // Get all email data for logging purposes
        $allEmails = EmailDataEloquentModel::query()->get(['id', 'type', 'email']);
        
        // Try exact match first
        $adminEmail = EmailDataEloquentModel::query()->where('type', 'Admin')->first();
        
        // If not found, try case-insensitive search
        if (!$adminEmail) {
            $adminEmail = EmailDataEloquentModel::query()->whereRaw('LOWER(type) = ?', [strtolower('Admin')])->first();
        }
        
        // Check if we found a valid email
        $isValid = $adminEmail && $adminEmail->email && filter_var($adminEmail->email, FILTER_VALIDATE_EMAIL);
        
        // Log information about admin email
        Log::info('Admin email verification', [
            'found' => (bool)$adminEmail,
            'email' => $adminEmail ? $adminEmail->email : 'Not found',
            'isValid' => $isValid,
            'allEmails' => $allEmails->map(fn($item) => ['id' => $item->id, 'type' => $item->type, 'email' => $item->email])->toArray()
        ]);
        
        return [
            'exists' => (bool)$adminEmail,
            'email' => $adminEmail ? $adminEmail->email : null,
            'isValid' => $isValid,
            'model' => $adminEmail,
        ];
    }
    
    /**
     * Verify that the info email exists and is valid.
     * 
     * @return array
     */
    public static function verifyInfoEmail(): array
    {
        // Get all email data for logging purposes
        $allEmails = EmailDataEloquentModel::query()->get(['id', 'type', 'email']);
        
        // Try exact match first
        $infoEmail = EmailDataEloquentModel::query()->where('type', 'Info')->first();
        
        // If not found, try case-insensitive search
        if (!$infoEmail) {
            $infoEmail = EmailDataEloquentModel::query()->whereRaw('LOWER(type) = ?', [strtolower('Info')])->first();
        }
        
        // Check if we found a valid email
        $isValid = $infoEmail && $infoEmail->email && filter_var($infoEmail->email, FILTER_VALIDATE_EMAIL);
        
        // Log information about info email
        Log::info('Info email verification', [
            'found' => (bool)$infoEmail,
            'email' => $infoEmail ? $infoEmail->email : 'Not found',
            'isValid' => $isValid,
            'allEmails' => $allEmails->map(fn($item) => ['id' => $item->id, 'type' => $item->type, 'email' => $item->email])->toArray()
        ]);
        
        return [
            'exists' => (bool)$infoEmail,
            'email' => $infoEmail ? $infoEmail->email : null,
            'isValid' => $isValid,
            'model' => $infoEmail,
        ];
    }
}
