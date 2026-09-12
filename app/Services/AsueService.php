<?php

namespace App\Services;

use App\DTOs\SimulateAsueCycleDTO;
use App\DTOs\SimulateAsueAcceptDTO;
use App\Models\Asue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use O21\LaravelWallet\Models\Custodian;

class AsueService
{
    /**
     * Platform fee percentage (3%)
     */
    const PLATFORM_FEE_PERCENTAGE = 0.03;

    protected function normalizedStatus(?string $status): string
    {
        return strtolower((string) $status);
    }

    protected function ensureCircleActive(Asue $asue): void
    {
        if ($this->normalizedStatus($asue->status) !== 'active') {
            throw new \RuntimeException('This Asue circle is not active yet. Waiting for all participants to accept the invitation.');
        }

        $hasPendingParticipation = $asue->invitedUsers()
            ->wherePivot('participation_status', '!=', 'accepted')
            ->exists();

        if ($hasPendingParticipation) {
            throw new \RuntimeException('This Asue circle cannot start until all participants accept the invitation.');
        }
    }

    /**
     * Validate if a user has sufficient balance for the hand amount.
     *
     * @param User $user
     * @param float $handAmount
     * @return bool
     */
    public function hasUserSufficientBalance(User $user, float $handAmount): bool
    {
        $balance = $user->balance('USD')->value->get();
        return $balance >= $handAmount;
    }

    /**
     * Validate if the current user can create an Asue.
     *
     * @param User $creator
     * @param float $handAmount
     * @throws \RuntimeException
     */
    public function validateCreatorBalance(User $creator, float $handAmount): void
    {
        if (!$this->hasUserSufficientBalance($creator, $handAmount)) {
            $balance = $creator->balance('USD')->value->get();
            throw new \RuntimeException("You do not have sufficient balance in your wallet. Required: {$handAmount} USD, Available: {$balance} USD");
        }
    }

    /**
     * Validate if invited users have sufficient balance.
     *
     * @param array $userIds
     * @param float $handAmount
     * @throws \RuntimeException
     */
    public function validateInvitedUsersBalance(array $userIds, float $handAmount): void
    {
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) {
                throw new \RuntimeException("User with ID {$userId} not found.");
            }

            if (!$this->hasUserSufficientBalance($user, $handAmount)) {
                $balance = $user->balance('USD')->value->get();
                throw new \RuntimeException("User {$user->name} does not have sufficient amount in their account. Required: {$handAmount} USD, Available: {$balance} USD");
            }
        }
    }

    /**
     * Advance the Asue cycle to the next turn.
     *
     * @param SimulateAsueCycleDTO $dto
     * @return array
     * @throws \RuntimeException
     */
    public function advanceCycle(SimulateAsueCycleDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $asue = $dto->asue;
            $userId = $dto->userId;

            // Security: Only creator can advance
            if ($userId !== $asue->user_id) {
                throw new \RuntimeException('Only the creator can advance the cycle.');
            }

            $memberCount = $asue->invitedUsers()->count();

            if ($asue->status === 'COMPLETED') {
                throw new \RuntimeException('This Asue circle is already completed.');
            }

            $this->ensureCircleActive($asue);

            if (!$asue->payout_accepted && $asue->current_turn > 1) {
                throw new \RuntimeException('Cannot advance cycle until the current payout is accepted.');
            }

            if ($asue->payout_accepted) {
                if ($asue->current_turn >= $memberCount) {
                    $asue->update(['status' => 'COMPLETED']);
                    return [
                        'success' => true,
                        'message' => 'Asue circle completed!'
                    ];
                }
                $asue->current_turn++;
                $asue->payout_accepted = false;
            }

            $asue->save();

            return [
                'success' => true,
                'message' => 'Cycle advanced to turn ' . $asue->current_turn . '.'
            ];
        });
    }

    /**
     * Accept the payout for the current turn.
     *
     * @param SimulateAsueAcceptDTO $dto
     * @return array
     * @throws \RuntimeException
     */
    public function acceptPayout(SimulateAsueAcceptDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $asue = $dto->asue;
            $userId = $dto->userId;

            if ($asue->payout_accepted) {
                throw new \RuntimeException('Payout already accepted for this turn.');
            }

            if ($asue->status === 'COMPLETED') {
                throw new \RuntimeException('This Asue circle is already completed.');
            }

            $this->ensureCircleActive($asue);

            $currentTurnMember = $asue->invitedUsers()
                ->wherePivot('position', $asue->current_turn)
                ->first();

            if (!$currentTurnMember) {
                throw new \RuntimeException('No member found for the current turn (' . $asue->current_turn . ').');
            }

            // Security: Allow the current turn member OR the Asue creator to accept
            if ($userId !== $currentTurnMember->id && $userId !== $asue->user_id) {
                throw new \RuntimeException('Only ' . $currentTurnMember->name . ' or the creator can accept this payout.');
            }

            $members = $asue->invitedUsers;
            $handAmount = $asue->hand_amount;
            $memberCount = $members->count();
            $totalPot = $handAmount * $memberCount;

            // Calculate Platform Fee
            $fee = $totalPot * self::PLATFORM_FEE_PERCENTAGE;
            $payoutAmount = $totalPot - $fee;

            // Validate all members have sufficient balance BEFORE deducting
            foreach ($members as $member) {
                if (!$this->hasUserSufficientBalance($member, $handAmount)) {
                    $balance = $member->balance('USD')->value->get();
                    throw new \RuntimeException("Member {$member->name} does not have sufficient amount in their account. Required: {$handAmount} USD, Available: {$balance} USD");
                }
            }

            // 1. Deduct from ALL members (including the receiver)
            foreach ($members as $member) {
                transfer($handAmount, 'USD')
                    ->from($member)
                    ->to(Custodian::of('e_money'))
                    ->meta([
                        'asue_id' => $asue->id,
                        'turn' => $asue->current_turn,
                        'type' => 'asue_contribution'
                    ])
                    ->commit();
            }

            // 2. Deposit the payout amount (minus fee) to the current turn member
            deposit($payoutAmount, 'USD')
                ->from(Custodian::of('e_money'))
                ->to($currentTurnMember)
                ->meta([
                    'asue_id' => $asue->id,
                    'turn' => $asue->current_turn,
                    'type' => 'asue_payout',
                    'total_pot' => $totalPot,
                    'platform_fee' => $fee
                ])
                ->overcharge()
                ->commit();

            // 3. Track fee revenue
            // Update the user’s platformRevenue field (stored in cents USD)
            $currentTurnMember->increment('platform_revenue', (int) ($fee * 100));

            $asue->payout_accepted = true;

            // If it was the last turn, mark completed
            if ($asue->current_turn >= $memberCount) {
                $asue->status = 'COMPLETED';
            }

            $asue->save();

            return [
                'success' => true,
                'message' => 'Pot of ' . $totalPot . ' USD collected. Fee of ' . $fee . ' USD deducted. Payout of ' . $payoutAmount . ' USD paid to ' . $currentTurnMember->name
            ];
        });
    }

    public function acceptParticipation(Asue $asue, int $userId): array
    {
        return DB::transaction(function () use ($asue, $userId) {
            if ($this->normalizedStatus($asue->status) === 'completed') {
                throw new \RuntimeException('This Asue circle is already completed.');
            }

            $invite = DB::table('asue_invites')
                ->where('asue_id', $asue->id)
                ->where('user_id', $userId)
                ->first();

            if (!$invite) {
                throw new \RuntimeException('You are not invited to this Asue circle.');
            }

            if (($invite->participation_status ?? null) !== 'accepted') {
                DB::table('asue_invites')
                    ->where('asue_id', $asue->id)
                    ->where('user_id', $userId)
                    ->update(['participation_status' => 'accepted', 'updated_at' => now()]);
            }

            $hasPendingParticipation = DB::table('asue_invites')
                ->where('asue_id', $asue->id)
                ->where('participation_status', '!=', 'accepted')
                ->exists();

            if (!$hasPendingParticipation && $this->normalizedStatus($asue->status) !== 'active') {
                $asue->status = 'active';
                $asue->current_turn = $asue->current_turn ?: 1;
                $asue->payout_accepted = false;
                $asue->save();

                return [
                    'success' => true,
                    'message' => 'Participation accepted. All participants have accepted, so the Asue circle is now active.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Participation accepted.'
            ];
        });
    }
}
