<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewAdmin\SmtpSettingRequest;
use App\Mail\SmtpTestMail;
use App\Models\Settings;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SmtpSettingController extends Controller
{
    public function update(SmtpSettingRequest $request)
    {
        $data = $request->validated();
        if (empty($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = 'encrypted:' . Crypt::encryptString($data['password']);
        }

        $settings = Settings::firstOrCreate(['key' => 'smtpSettings'], ['value' => []]);
        $settings->update([
            'value' => array_merge($settings->value ?? [], $data),
        ]);

        return redirect()->back()->with('message', 'SMTP settings updated successfully.');
    }

    public function sendTestEmail(SmtpSettingRequest $request)
    {
        $data = $request->validated();
        $stored = Settings::where('key', 'smtpSettings')->first()?->value ?? [];
        $password = ($data['password'] ?? null) ?: $this->decryptStoredPassword($stored['password'] ?? null);

        if (! $password) {
            return response()->json(['success' => false, 'message' => 'Enter a password to test the connection.'], 422);
        }

        $encryption = $data['encryption'] ?? null;
        $encryption = ($encryption && $encryption !== 'None') ? strtolower($encryption) : null;

        config(['mail.mailers.dynamic_smtp' => [
            'transport' => 'smtp',
            'host' => $data['host'],
            'port' => (int) $data['port'],
            'encryption' => $encryption,
            'username' => $data['username'],
            'password' => $password,
        ]]);

        $fromName = $data['from_name'] ?? config('app.name');

        try {
            Mail::mailer('dynamic_smtp')
                ->to($request->user()->email)
                ->send(new SmtpTestMail($data['username'], $fromName));

            return response()->json([
                'success' => true,
                'message' => 'Test email sent to ' . $request->user()->email . '.',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Admin SMTP connection test failed.', [
                'admin_id' => $request->user()->getAuthIdentifier(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The SMTP connection test failed. Verify the server, port, encryption, and credentials.',
            ], 422);
        }
    }

    private function decryptStoredPassword(?string $password): ?string
    {
        if (! $password || ! str_starts_with($password, 'encrypted:')) {
            return $password;
        }

        try {
            return Crypt::decryptString(substr($password, strlen('encrypted:')));
        } catch (DecryptException) {
            return null;
        }
    }
}
