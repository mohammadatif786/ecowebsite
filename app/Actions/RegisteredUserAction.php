<?php

namespace App\Actions;

use App\Services\RegisteredUserService;
use App\Models\User;


class RegisteredUserAction
{

    protected $registerService;

    public function __construct(RegisteredUserService $registerService)
    {
        $this->registerService = $registerService;
    }

    public function execute(array $validated)
    {
        $this->registerService->store($validated);
    }

    public function validateLinkUpId(object $request)
    {
        $linkupId = '@' . strtolower(trim($request->linkup_id));

        $reservedWords = $this->reservedWords();

        $error = $this->validationIdWords($linkupId, $reservedWords);

        if ($error) {
            return $error;
        }

        $exists = User::where('linkup_id', $linkupId)->exists();

        return [
            'available' => !$exists,
            'reason' => $exists ? 'That LinkUp ID is taken.' : null
        ];
    }

    private function reservedWords()
    {
        $reservedWords = [
            'linkup',
            'support',
            'admin',
            'moderator',
            'security',
            'billing',
            'payments',
            'cashapp',
            'bank',
            'wallet',
            'kyc',
            'aml',
            'help',
            'official',
            'verified',
            'ceo',
            'staff',
            'team',
            'fraud',
            'chargeback'
        ];

        return $reservedWords;
    }

    private function validationIdWords(string $linkupId, array $reservedWords)
    {
        if (in_array($linkupId, $reservedWords)) {
            return [
                'available' => false,
                'reason' => 'That ID is reserved for security reasons.'
            ];
        }

        if (preg_match('/_{2,}/', $linkupId)) {
            return [
                'available' => false,
                'reason' => 'Too many underscores. Use one underscore max.'
            ];
        }

        if (preg_match('/(.)\1\1\1/', $linkupId)) {
            return [
                'available' => false,
                'reason' => 'Too many repeated characters.'
            ];
        }

        return null;
    }
}
