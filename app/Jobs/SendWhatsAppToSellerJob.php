<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Services\TwilioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWhatsAppToSellerJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 30;

    protected int $orderId;
    protected int $sellerUserId;

    public function __construct(int $orderId, int $sellerUserId)
    {
        $this->orderId = $orderId;
        $this->sellerUserId = $sellerUserId;
    }

    public function handle(TwilioService $twilio): void
    {
        $order = Order::with(['customer', 'orderItems.product'])->find($this->orderId);
        if (!$order) {
            Log::warning('Order not found for WhatsApp notification', ['order_id' => $this->orderId]);
            return;
        }

        $seller = User::find($this->sellerUserId);
        if (!$seller) {
            Log::warning('Seller not found for WhatsApp notification', ['seller_user_id' => $this->sellerUserId]);
            return;
        }

        // Get seller's phone number (from user profile or merchant profile)
        $sellerPhone = $this->getSellerPhoneNumber($seller);
        if (!$sellerPhone) {
            Log::info('Seller has no phone number for WhatsApp notification', ['seller_user_id' => $this->sellerUserId]);
            return;
        }

        // Format phone number for WhatsApp (ensure it has + prefix)
        $toPhone = $this->formatPhoneNumber($sellerPhone);
        if (!$toPhone) {
            Log::warning('Invalid phone number format for WhatsApp', ['phone' => $sellerPhone]);
            return;
        }

        // Build message
        $message = $this->buildMessage($order, $seller);

        $templateSid = (string) config('services.twilio.whatsapp_content_sid');
        $contentVariables = [
            // These keys must match EXACTLY what your Twilio Content Template defines
            'first_name'   => (string) ($seller->name ?? 'Seller'),
            'order_number' => (string) ($order->number ?? ('#' . $order->id)),
            'product_name' => (string) ($order->orderItems->first()?->product?->name ?? 'Multiple Items'),
            'order_id'     => (string) $order->id,
            'total'        => (string) number_format($order->total, 2),
        ];

        try {
            $usingTemplate = !empty($templateSid);

            if ($usingTemplate) {
                $twilioMessage = $twilio->sendWhatsApp($toPhone, $templateSid, $contentVariables, $message);
            } else {
                Log::warning('WhatsApp template SID not configured; sending plain WhatsApp message may fail outside messaging window', [
                    'order_id' => $this->orderId,
                    'seller_user_id' => $this->sellerUserId,
                ]);
                $twilioMessage = $twilio->sendWhatsApp($toPhone, null, null, $message);
            }

            Log::info('WhatsApp notification sent to seller', [
                'order_id' => $this->orderId,
                'seller_user_id' => $this->sellerUserId,
                'to_phone' => $this->maskPhoneNumber($toPhone),
                'using_template' => $usingTemplate,
                'template_sid' => $usingTemplate ? $templateSid : null,
                'content_variable_keys' => $usingTemplate ? array_keys($contentVariables) : null,
                'twilio_message_sid' => $twilioMessage?->sid ?? null,
                'twilio_status' => $twilioMessage?->status ?? null,
            ]);
        } catch (\Exception $e) {
            if ($this->isTransientNetworkError($e)) {
                Log::warning('Transient network error while sending Twilio message, will retry', [
                    'order_id' => $this->orderId,
                    'seller_user_id' => $this->sellerUserId,
                    'to_phone' => $this->maskPhoneNumber($toPhone),
                    'error' => $e->getMessage(),
                ]);

                if ($this->attempts() < $this->tries) {
                    $this->release($this->backoff);
                }

                return;
            }

            Log::error('Failed to send WhatsApp notification to seller', [
                'order_id' => $this->orderId,
                'seller_user_id' => $this->sellerUserId,
                'to_phone' => $this->maskPhoneNumber($toPhone),
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);

            $errorMessage = strtolower((string) $e->getMessage());
            $isTemplateVariableError = str_contains($errorMessage, 'content variables') || ((int) $e->getCode() === 21656);
            $isOutsideWindowError = str_contains($errorMessage, 'outside messaging window') || ((int) $e->getCode() === 63016);

            // If template failed due to variable/template issues, try a plain WhatsApp body once before SMS fallback.
            // If we are outside the messaging window, plain WhatsApp will also fail, so skip it.
            if (!empty($templateSid) && $isTemplateVariableError && !$isOutsideWindowError) {
                try {
                    $twilioMessage = $twilio->sendWhatsApp($toPhone, null, null, $message);

                    Log::info('WhatsApp notification sent to seller (plain fallback)', [
                        'order_id' => $this->orderId,
                        'seller_user_id' => $this->sellerUserId,
                        'to_phone' => $this->maskPhoneNumber($toPhone),
                        'twilio_message_sid' => $twilioMessage?->sid ?? null,
                        'twilio_status' => $twilioMessage?->status ?? null,
                    ]);
                    return;
                } catch (\Exception $plainError) {
                    Log::error('Failed to send plain WhatsApp fallback notification', [
                        'order_id' => $this->orderId,
                        'seller_user_id' => $this->sellerUserId,
                        'error' => $plainError->getMessage(),
                        'error_code' => $plainError->getCode(),
                    ]);
                }
            }

            // Fallback to SMS if WhatsApp fails (sandbox restriction)
            try {
                $smsPhone = str_replace('whatsapp:+', '+', $toPhone);
                $twilio->sendSMS($smsPhone, $message);

                Log::info('SMS notification sent as fallback to seller', [
                    'order_id' => $this->orderId,
                    'seller_user_id' => $this->sellerUserId,
                    'to_phone' => $this->maskPhoneNumber($smsPhone),
                ]);
            } catch (\Exception $smsError) {
                Log::error('Failed to send SMS fallback notification', [
                    'order_id' => $this->orderId,
                    'seller_user_id' => $this->sellerUserId,
                    'error' => $smsError->getMessage(),
                ]);

                // Mark as failed after max retries
                if ($this->attempts() >= $this->tries) {
                    Log::warning('Notification failed after max retries, giving up', [
                        'order_id' => $this->orderId,
                        'seller_user_id' => $this->sellerUserId,
                    ]);
                    return;
                }

                $this->release(60);
            }
        }
    }

    protected function isTransientNetworkError(\Throwable $e): bool
    {
        $message = strtolower((string) $e->getMessage());

        return str_contains($message, 'could not resolve host')
            || str_contains($message, 'curl error 6')
            || str_contains($message, 'name or service not known')
            || str_contains($message, 'temporary failure in name resolution')
            || str_contains($message, 'connection timed out')
            || str_contains($message, 'curl error 28');
    }

    /**
     * Get seller's phone number from user or merchant profile
     */
    protected function getSellerPhoneNumber(User $seller): ?string
    {
        // First try user's phone_number
        if (!empty($seller->phone_number)) {
            return $seller->phone_number;
        }

        // Try merchant profile if exists
        if ($seller->merchant && !empty($seller->merchant->phone)) {
            return $seller->merchant->phone;
        }

        return null;
    }

    /**
     * Format phone number for WhatsApp (ensure whatsapp:+ prefix and proper format)
     */
    protected function formatPhoneNumber(string $phone): ?string
    {
        // Remove all non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Ensure it has country code (at least 10 digits)
        if (strlen($clean) < 10) {
            return null;
        }

        // Add whatsapp:+ prefix for Twilio WhatsApp
        return 'whatsapp:+' . $clean;
    }

    /**
     * Build WhatsApp message content
     */
    protected function buildMessage(Order $order, User $seller): string
    {
        $customer = $order->customer;
        $customerName = $customer ? $customer->name : 'Unknown';
        $orderNumber = $order->number ?? '#' . $order->id;
        $totalAmount = number_format($order->total, 2);
        $itemCount = $order->orderItems->sum('qty');

        $message = "🔔 *New Order Received*\n\n";
        $message .= "Order #{$orderNumber}\n";
        $message .= "Customer: {$customerName}\n";
        $message .= "Items: {$itemCount}\n";
        $message .= "Total: \${$totalAmount}\n\n";
        $message .= "Please check your dashboard for details.";

        return $message;
    }

    /**
     * Mask phone number for logging (security)
     */
    protected function maskPhoneNumber(string $phone): string
    {
        if (strlen($phone) <= 4) {
            return '****';
        }

        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }
}
