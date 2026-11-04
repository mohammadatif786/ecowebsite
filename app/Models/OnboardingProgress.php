<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A member's saved wizard answers. One row per member, removed once the wizard is finished.
 *
 * @property int $current_step 0-based index of the furthest step the member may open
 * @property array<string, mixed>|null $answers keyed by step (see WizardStep)
 */
class OnboardingProgress extends Model
{
    protected $table = 'onboarding_progress';

    protected $fillable = ['user_id', 'current_step', 'answers'];

    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'answers' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
