<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'code_hash',
        'attempts',
        'expires_at',
        'last_sent_at',
    ];

    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }

    /**
     * Keyed hash of a code for one address. Codes are never stored in clear, and a code only
     * matches the address it was sent to.
     */
    public static function hashFor(string $code, string $email): string
    {
        return hash_hmac('sha256', $code.'|'.strtolower($email), (string) config('app.key'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
