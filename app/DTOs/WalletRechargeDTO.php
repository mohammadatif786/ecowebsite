<?php

namespace App\DTOs;

class WalletRechargeDTO
{
    public readonly int $userId;
    public readonly int $amount;
    public readonly ?string $redirectTo;
    public readonly int $recipientId;
    public readonly ?string $note;
    public readonly string $successRoute;

    public function __construct(
        int $userId,
        int $amount,
        ?string $redirectTo = null,
        int $recipientId = 0,
        ?string $note = null,
        string $successRoute = 'frontend.oneTimePay.success'
    )
    {
        $this->userId = $userId;
        $this->amount = $amount;
        $this->redirectTo = $redirectTo;
        $this->recipientId = $recipientId;
        $this->note = $note;
        $this->successRoute = $successRoute;
    }
}
