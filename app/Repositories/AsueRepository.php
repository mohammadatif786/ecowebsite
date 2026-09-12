<?php

namespace App\Repositories;

use App\DTOs\AsueData;
use App\Models\Asue;
use App\Models\Notification;
use App\Services\AsueService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AsueRepository
{
    public function __construct(
        protected AsueService $asueService
    ) {}

    /**
     * Create a new Asue record and send invitations.
     */
    public function createAsue(AsueData $data): Asue
    {
        return DB::transaction(function () use ($data) {
            $sender = Auth::user();
            $this->asueService->validateCreatorBalance($sender, $data->handAmount);

            $this->asueService->validateInvitedUsersBalance($data->userIds, $data->handAmount);

            $asue = Asue::create([
                'user_id' => $sender->id,
                'name' => $data->name,
                'frequency' => $data->frequency,
                'hand_amount' => $data->handAmount,
                'start_date' => $data->startDate,
                'max_members' => $data->maxMembers,
                'status' => 'inviting',
                'asue_unique_code' => Str::random(10),
            ]);

            $asue->invitedUsers()->attach($sender->id, [
                'position' => 1,
                'participation_status' => 'accepted'
            ]);
            
            if (!empty($data->userIds)) {
                $position = 2;
                foreach ($data->userIds as $userId) {
                    $asue->invitedUsers()->attach($userId, [
                        'position' => $position++,
                        'participation_status' => 'invited'
                    ]);
                }
            }

            foreach ($data->userIds as $userId) {
                Notification::create([
                    'title'    => 'You received an Asue invitation!',
                    'message'  => "{$sender->name} sent an asue invitation of {$data->handAmount} USD.",
                    'send_by'  => $sender->id,
                    'user_id'  => $userId,
                    'type'     => 'Asue Invitation',
                    'context'  => 'Asue Invitation',
                    'unread'   => true,
                    'avatar'   => $sender->avatar ?? null,
                    'metadata' => [
                        'amount'      => $data->handAmount,
                        'sender_id'   => $sender->id,
                        'sender_name' => $sender->name,
                        'asue_id'     => $asue->id,
                        'status'      => $asue->status,
                    ],
                ]);
            }

            return $asue;
        });
    }
}
