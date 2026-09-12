<?php

namespace App\Actions;

use App\DTOs\MessageAttendeesBroadcastDTO;
use App\Services\MessageAttendeesBroadcastService;

class MessageAttendeesBroadcastAction
{
    public function __construct(
        protected MessageAttendeesBroadcastService $messageAttendeesBroadcastService
    ) {}

    public function execute(MessageAttendeesBroadcastDTO $dto): MessageAttendeesBroadcastDTO
    {
        return $this->messageAttendeesBroadcastService->messageAttendeesBroadcast($dto);
    }
}
