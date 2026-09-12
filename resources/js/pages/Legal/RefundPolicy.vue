<template>
    <Head :title="title" />
    <LegalLayout>
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-[#17365D]">LinkUp Vibes - {{ title }}</h1>
            <p class="text-center mt-2 text-sm text-slate-500">Effective 1 January 2026 | Updated 7 August 2026</p>
        </div>

        <div class="prose prose-blue dark:prose-invert lg:prose-lg max-w-none prose-headings:text-[#17365D] prose-headings:font-bold">
            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Overview</h2>
            <p class="text-sm font-normal">LinkUp is committed to providing a fair and transparent refund process. This Refund Policy explains when refunds may be available for purchases made through the LinkUp platform, including subscriptions, event tickets, marketplace purchases, wallet transactions, digital goods, and advertising services.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Dating & Social Subscriptions</h2>
            <p class="text-sm font-normal">Subscription purchases are generally non-refundable after the subscription has been activated or used unless otherwise required by applicable law or the terms of the Apple App Store, Google Play Store, or other authorized marketplace through which the subscription was purchased. Users may cancel future renewals at any time through the platform where the subscription was purchased.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Event Tickets</h2>
            <p class="text-sm font-normal">Refunds for event tickets are determined by the individual event organizer. Each organizer establishes their own cancellation and refund policy, which is displayed before purchase. If an event is cancelled by the organizer, customers may be eligible for a full or partial refund in accordance with the organizer's published policy and applicable law.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Marketplace Purchases</h2>
            <p class="text-sm font-normal">Marketplace purchases are subject to the individual seller's return and refund policy. LinkUp may assist in resolving disputes but is not the seller of marketplace products unless expressly stated.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Wallet Transactions</h2>
            <p class="text-sm font-normal">Completed wallet transfers, peer-to-peer payments, cash-outs, and remittance transactions generally cannot be reversed once successfully processed. Users should carefully verify recipient information before confirming a transaction.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Merchant Payments</h2>
            <p class="text-sm font-normal">Refunds for merchant purchases are processed by the merchant in accordance with their refund policy. Where a refund is approved, the funds will be returned through the original payment method whenever possible.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Digital Coins & Virtual Gifts</h2>
            <p class="text-sm font-normal">Purchases of LinkCoins, virtual gifts, and other digital items are generally non-refundable after delivery or use unless required by applicable law.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Advertising Services</h2>
            <p class="text-sm font-normal">Advertising, sponsored placements, and promotional campaign purchases are generally non-refundable once a campaign has started or advertising inventory has been reserved. Requests submitted before a campaign begins may be reviewed on a case-by-case basis.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Chargebacks</h2>
            <p class="text-sm font-normal">If a customer believes a transaction was unauthorized or processed incorrectly, they should contact LinkUp Customer Support before initiating a payment dispute with their financial institution. LinkUp will investigate all reported claims in good faith.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Processing Time</h2>
            <p class="text-sm font-normal">Approved refunds are typically processed within 5 to 10 business days. The time required for funds to appear in the customer's account depends on the payment processor, bank, or card issuer.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">How to Request a Refund</h2>
            <p class="text-sm font-normal">Refund requests should include the customer's name, LinkUp username, transaction reference number, date of purchase, and a description of the issue. Supporting documentation may be requested to verify the claim.</p>

            <div class="mt-12 pt-8 border-t border-slate-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-sm font-normal">Customer Support</p>
                        <p class="text-sm font-normal"><a href="mailto:support@linkupvibes.com">support@linkupvibes.com</a></p>
                    </div>
                    <div>
                        <p class="text-sm font-normal">Business Inquiries</p>
                        <p class="text-sm font-normal"><a href="mailto:info@linkupvibes.com">info@linkupvibes.com</a></p>
                    </div>
                    <div>
                        <p class="text-sm font-normal">Website</p>
                        <p class="text-sm font-normal"><a href="https://www.linkupvibes.com" target="_blank">www.linkupvibes.com</a></p>
                    </div>
                </div>
            </div>

            <p class="text-sm font-normal">LinkUp may update this Refund Policy from time to time. The latest version will always be published on the LinkUp website.</p>
        </div>

        <!-- PREVIOUS TEMPLATE CONTENT
        <div class="text-center mb-12">
            <h1 class="logo text-5xl text-[#2f9bef] leading-none">Link<span style="color:#F5C518">üp</span></h1>
        </div>

        <div class="prose prose-blue dark:prose-invert lg:prose-lg max-w-none prose-headings:text-[#2f9bef]">
            <h1 class="text-4xl font-bold mb-8 text-[#2f9bef] dark:text-blue-400">{{ title }}</h1>
            <p>Last Updated: August 10, 2026</p>

            <h2 class="text-[#2f9bef]">Overview</h2>
            <p>LinkUp is committed to providing a fair and transparent refund process. This Refund Policy explains when refunds may be available for purchases made through the LinkUp platform, including subscriptions, event tickets, marketplace purchases, wallet transactions, digital goods, and advertising services.</p>
            ...
        </div>
        -->
    </LegalLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import LegalLayout from '@/layouts/LegalLayout.vue';

const title = "Refund Policy";
</script>
