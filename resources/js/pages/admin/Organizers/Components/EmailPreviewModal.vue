<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-4xl bg-white rounded-lg shadow-xl max-h-screen overflow-y-auto">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Payout Email Preview</h3>
                    <button @click="close" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Email Content -->
                <div class="p-4">
                    <div class="email-container"
                        style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 6px 16px rgba(12,54,124,.06); overflow: hidden;">
                        <!-- Email Header -->
                        <div
                            style="background: linear-gradient(135deg, #2f7de1, #2563c9); color: white; padding: 32px 24px; text-align: center;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700;">LinkUp Payout Confirmation</h1>
                            <p style="margin: 8px 0 0; opacity: 0.9;">Your funds have been transferred successfully</p>
                        </div>

                        <!-- Email Body -->
                        <div style="padding: 32px 24px;">
                            <p style="font-size: 18px; font-weight: 600; margin-bottom: 24px;">Dear {{
                                payout?.organizer?.name || 'Valued Organizer' }},</p>

                            <p>We hope this email finds you well. We're pleased to confirm that your requested payout
                                from LinkUp has been successfully processed and sent via wire transfer.</p>

                            <h2 style="color: #2f7de1; margin: 32px 0 16px;">Payout Details</h2>
                            <table
                                style="width: 100%; border-collapse: collapse; margin: 24px 0; background: #f8fbff; border-radius: 8px; overflow: hidden;">
                                <tr>
                                    <th
                                        style="padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f8; background: #f8fbff; font-weight: 600; color: #425a7b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Field</th>
                                    <th
                                        style="padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f8; background: #f8fbff; font-weight: 600; color: #425a7b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Details</th>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Reference Number</strong></td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        {{ payout?.reference }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Event Name</strong></td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        {{ payout?.event?.title || 'Unknown Event' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Payout Date</strong></td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        {{ formatDate(payout?.updated_at) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Amount Requested</strong></td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        {{ formatCurrency(payout?.amount || 0) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Processing Fee</strong></td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        {{ formatCurrency(payout?.fee_amount || 0) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Net Amount Transferred</strong></td>
                                    <td
                                        style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500; color: #10b981; font-weight: 600;">
                                        {{ formatCurrency(payout?.net_amount || (payout?.amount || 0)) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Transfer Method</strong></td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        {{ payout?.method ? payout.method.charAt(0).toUpperCase() +
                                        payout.method.slice(1) : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Destination Account</strong></td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        {{ payout?.method ? payout.method.charAt(0).toUpperCase() +
                                        payout.method.slice(1) : 'N/A' }} Account</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        <strong>Expected Arrival</strong></td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">
                                        1-3 Business Days ({{ formatDate(payout?.updated_at, true) }})</td>
                                </tr>
                                <!-- <tr><td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;"><strong>Transaction ID</strong></td><td style="padding: 12px 16px; border-bottom: 1px solid #eef2f8; font-weight: 500;">{{ payout?.firebase_id || 'PENDING' }}</td></tr> -->
                            </table>

                            <p>This payout reflects the available balance from ticket sales and other revenues for your
                                event after deducting platform fees and any applicable taxes.</p>

                            <div
                                style="background: #f0f6ff; border: 1px solid #c7dcff; border-radius: 8px; padding: 16px; margin: 24px 0;">
                                <h3 style="margin: 0 0 12px; color: #29569b;">Proof of Transfer</h3>
                                <p>Attached to this email is a PDF copy of the official wire transfer receipt from our
                                    payment processor (e.g., Stripe or ACH Network). <strong>Digital Signature
                                        Verified</strong> – This document includes:</p>
                                <ul style="margin: 0; padding-left: 20px;">
                                    <li>Timestamped confirmation of the transfer initiation.</li>
                                    <li>Full transaction details matching the above.</li>
                                    <li>Bank routing and account verification stamps.</li>
                                </ul>
                                <p>For added security, this email includes a transaction hash for verification: <code
                                        style="font-family: monospace; background: #edf2fc; padding: 4px 8px; border-radius: 4px; font-size: 12px;">09CA6E355E1B5263</code>.
                                    Verify it on our secure dashboard.</p>
                            </div>

                            <div
                                style="background: #fff6e8; border: 1px solid #f59e0b; border-radius: 8px; padding: 12px; margin: 16px 0; text-align: center;">
                                <p style="margin: 0; font-size: 13px; color: #92400e;"><strong>Secure
                                        Verification</strong>: Click below to confirm receipt with 2FA (one-time link
                                    expires in 24 hours).</p>
                                <a href="https://linkup.com/verify/d8b281a16e4de33c78d05f1d6dbcc285"
                                    style="display: inline-block; background: #2f7de1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 16px 0;">Verify
                                    & Acknowledge</a>
                            </div>

                            <div
                                style="background: #e7f7ef; border: 1px solid #cfeede; border-radius: 8px; padding: 16px; margin: 24px 0;">
                                <h3 style="margin: 0 0 12px; color: #085e43;">Next Steps</h3>
                                <ul style="margin: 0; padding-left: 20px;">
                                    <li><strong>Monitor Your Account</strong>: Funds should appear within the estimated
                                        timeframe. If not received by {{ formatDate(payout?.updated_at, false, true) }},
                                        contact us.</li>
                                    <li><strong>Questions?</strong> Reply to this email or reach support@linkup.com / +1
                                        (555) 123-4567.</li>
                                    <li><strong>View in Dashboard</strong>: <a href="#"
                                            style="display: inline-block; background: #2f7de1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 16px 0;">Access
                                            Payout Report</a></li>
                                </ul>
                            </div>

                            <div
                                style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 12px; margin: 16px 0; font-size: 13px; color: #92400e;">
                                <strong>Tax Notice</strong>: This payout may be reportable on IRS Form 1099-K if annual
                                totals exceed $600 USD. For details, see our <a href="https://linkup.com/tax-resources"
                                    style="color: #92400e;">Tax Resources</a>. By using LinkUp, you agree to our <a
                                    href="https://linkup.com/tos" style="color: #92400e;">Terms of Service</a> and <a
                                    href="https://linkup.com/privacy" style="color: #92400e;">Privacy Policy</a>.
                            </div>

                            <p>Thank you for partnering with LinkUp to create unforgettable experiences. We look forward
                                to your next event!</p>

                            <p style="text-align: center; margin: 24px 0;">
                                <a href="https://linkup.com/feedback?ref={{ payout?.reference }}"
                                    style="display: inline-block; background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600;">How
                                    was your experience? Rate Us</a>
                            </p>
                        </div>

                        <!-- Email Footer -->
                        <div
                            style="background: #f8fbff; padding: 24px; text-align: center; font-size: 14px; color: #5b6b81; border-top: 1px solid #eef2f8;">
                            <p>The LinkUp Team<br>LinkUp Events Inc.<br>123 Event Street, Suite 100<br>New York, NY
                                10001</p>
                            <p>Email: hello@linkup.com | Website: <a href="https://www.linkupvibes.com"
                                    style="color: #2f7de1;">www.linkupvibes.com</a></p>
                            <p style="font-size: 12px; opacity: 0.8; margin-top: 16px;">This is an automated
                                confirmation. For security, never share credentials via email. If you didn't request
                                this, contact support immediately.</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 p-4 border-t border-gray-200">
                    <button @click="close"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface Props {
    show: boolean;
    payout: any;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

const close = () => {
    emit('close');
};

const formatCurrency = (n: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(n || 0);
};

const formatDate = (d: string | null, addDays: boolean = false, formatForDisplay: boolean = false) => {
    if (!d) return 'N/A';

    const date = new Date(d);

    if (addDays) {
        date.setDate(date.getDate() + 3);
    }

    if (formatForDisplay) {
        return date.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<style scoped>
.email-container {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #0b2239;
}
</style>
