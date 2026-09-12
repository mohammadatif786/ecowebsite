<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    /**
     * Create a new class instance.
     */
    protected Client $client;
    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    public function sendSMS(string $to, string $message)
    {
        return $this->client->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);
    }

    /**
     * Send WhatsApp message
     *
     * @param string $to Recipient phone number (with country code, e.g., +923001234567)
     * @param string|null $contentSid Template content SID (for template messages)
     * @param array|null $contentVariables Optional content variables for template messages
     * @param string $fallbackMessage Fallback message for non-template messages
     * @return \Twilio\Rest\Api\V2010\Account\MessageInstance
     */
    // App\Services\TwilioService.php

    public function sendWhatsApp(string $to, ?string $contentSid = null, ?array $contentVariables = null, string $fallbackMessage = '')
    {
        $params = [
            'from' => config('services.twilio.whatsapp_from'),
        ];

        if ($contentSid) {
            // If we have a template, use the Content SID and variables
            $params['contentSid'] = $contentSid;
            if ($contentVariables !== null) {
                $params['contentVariables'] = json_encode($contentVariables);
            }
        } else {
            // Fallback for simple session messages (replies)
            $params['body'] = $fallbackMessage;
        }

        return $this->client->messages->create($to, $params);
    }
}
