<template>
    <Head :title="title" />
    <LegalLayout>
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-[#312E82]">LINKUP</h1>
            <p class="text-center mt-2 text-sm text-slate-500">Complete Products, Services, Subscription & Fee Schedule</p>
        </div>

        <div class="prose prose-blue dark:prose-invert lg:prose-lg max-w-none prose-headings:text-[#312E82] prose-headings:font-bold">
            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Pricing Overview</h2>
            <p class="text-sm font-normal">LinkUp operates a multi-service digital platform. The following schedule consolidates the principal subscription prices, platform fees, transaction fees, commissions, digital-product pricing, and other charges documented for the LinkUp ecosystem. Where applicable, taxes, foreign-exchange charges, payment-processing costs, or third-party banking charges may be added or separately disclosed before a transaction is completed.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Dating & Social Subscriptions</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#312E82] text-white">
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold dark:text-slate-300">SERVICE / PRODUCT</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold dark:text-slate-300">FEE / PRICE</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold dark:text-slate-300">BASIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Monthly Dating & Social Subscription</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$23.99</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">1 month</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Three-Month Dating & Social Subscription</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$70.99</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">3 months</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Yearly Dating & Social Subscription</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$95.99</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">12 months</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm font-normal">Subscription pricing may vary by country, currency, taxes, or applicable app-store pricing. The price presented at checkout governs the user's purchase.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Events & Ticketing</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#312E82] text-white">
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold dark:text-slate-300">SERVICE / PRODUCT</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold dark:text-slate-300">FEE / PRICE</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold dark:text-slate-300">BASIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Standard Event Ticketing Platform Fee</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">3.5% + $1.00</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per ticket sold</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Payment Processing</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">2.9% + $0.30</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per transaction</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Drinks Add-On</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">5%</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Order value</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Bottle Service Add-On</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">6%</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Order value</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">VIP Tables Add-On</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">4%</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Order value</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Wellness & Spa</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">6% + $0.75</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Eligible transaction</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Cookouts</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">6% + $0.75</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Eligible transaction</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Organizer Cash-Out</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">3%</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Cash-out amount</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm font-normal">Event organizers establish the underlying ticket, product, table, drink, or service price. Applicable LinkUp fees and taxes are presented as part of the transaction process.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Wallet, Payments & Remittance</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left  border-collapse">
                    <thead>
                        <tr class="bg-[#312E82] text-white">
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">SERVICE / PRODUCT</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">FEE / PRICE</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">BASIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Wallet QR Merchant Payments</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">3.5%</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per transaction</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Remittance / Foreign Exchange</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">4% + FX spread</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per transaction</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Eligible Cash-Out</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">3%</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Cash-out amount</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm font-normal">Wallet and remittance services may be subject to country availability, regulatory requirements, banking arrangements, currency conversion, and third-party financial-service charges. Applicable charges should be displayed before authorization.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">ASUE Community Savings</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#312E82] text-white">
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">SERVICE / PRODUCT</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">FEE / PRICE</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">BASIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">ASUE Savings Pool</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">3%</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per payout cycle</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm font-normal">The ASUE fee applies to eligible community savings payout cycles made available through the LinkUp platform.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Marketplace</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#312E82] text-white">
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">SERVICE / PRODUCT</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">FEE / PRICE</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">BASIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Marketplace Commission</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">5%–8%</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Gross sale, depending on category/service</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm font-normal">Sellers determine their product or service prices. Other applicable processing, delivery, tax, or transaction charges may be shown at checkout.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">LinkUp Live, Creator Subscriptions & Digital Gifts</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50">
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold text-slate-700 dark:text-slate-300">SERVICE / PRODUCT</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold text-slate-700 dark:text-slate-300">FEE / PRICE</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold text-slate-700 dark:text-slate-300">BASIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">LinkUp Live Creator Subscriptions</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">50/50 revenue split</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">50% retained by LinkUp</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Virtual Gifts / LinkCoins</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">50/50 revenue split</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">50% retained by LinkUp</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">LinkCoin Accounting Value</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$0.01</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per coin</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm font-normal">For eligible creator activity, LinkUp retains 50% of applicable creator subscription or virtual-gift revenue and the creator receives the remaining 50%, subject to applicable program terms and cash-out requirements.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Additional Pricing & Commercial Services</h2>
            <h3 class="text-[#312E82] font-bold mt-6 text-sm font-normal">Advertising & Promotional Services</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#312E82] text-white">
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">SERVICE / PRODUCT</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">FEE / PRICE</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">BASIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Geo-Targeted Swipe Ads</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$25</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per week</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Explore Tab Boosted Listing</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$40</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per month</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Banner Advertising</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$4–$6 CPM</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per 1,000 impressions</td>
                        </tr>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Event Spotlight</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$25</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">3-day homepage placement</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm font-normal">Additional sponsorships, push notifications, email campaigns, multi-country campaigns, and enterprise advertising may be priced separately based on audience, geographic coverage, duration, placement, and campaign requirements.</p>

            <h3 class="text-[#312E82] font-bold mt-6 text-sm font-normal">Pro Analytics – Planned Service</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#312E82] text-white">
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">SERVICE / PRODUCT</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">FEE / PRICE</th>
                            <th class="p-4 border border-slate-200 dark:border-slate-800 font-bold  dark:text-slate-300">BASIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Pro Analytics Dashboard</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">$9.99</td>
                            <td class="p-4 border border-slate-200 dark:border-slate-800">Per month</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm font-normal">The Pro Analytics Dashboard is identified as a planned future subscription service for eligible event organizers and marketplace sellers seeking advanced sales and demographic analytics. It should not be represented as currently available until launched.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Taxes, Currency & Third-Party Charges</h2>
            <p class="text-sm font-normal">Applicable taxes may be added after LinkUp fees where required by law. Currency conversion and foreign-exchange charges may apply to cross-border transactions. Card networks, banks, payment processors, or other third-party providers may impose additional charges outside LinkUp's direct control. Where applicable, the customer will be shown the amount due before confirming payment.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Refunds & Cancellations</h2>
            <p class="text-sm font-normal">Refund eligibility depends on the product or service purchased and the applicable terms. Event ticket refunds may be governed by the event organizer's refund policy. Marketplace transactions may also be subject to seller terms and applicable consumer-protection requirements. Approved refunds may take additional time to appear depending on the payment method, processor, or financial institution.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Customized & Enterprise Pricing</h2>
            <p class="text-sm font-normal">Large event organizers, merchants, advertisers, universities, institutions, governments, financial partners, and enterprise customers may receive customized pricing based on service requirements, transaction volume, geographic coverage, integration requirements, and commercial agreements.</p>

            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Pricing Disclosure</h2>
            <p class="text-sm font-normal">LinkUp seeks to disclose applicable prices and fees before users authorize a purchase or transaction. Pricing may change from time to time as services, markets, regulatory requirements, and commercial arrangements evolve. The price or fee displayed to the customer at the time of purchase or transaction will apply to that transaction.</p>
            
            <h2 class="text-[#312E82] font-bold mt-4 mb-2">Contact LinkUp</h2>
            <div class="mt-4">
                <div class="">
                    <div class="flex flex-row gap-2">
                        <p class="text-sm font-bold">Customer Support:</p>
                        <p class="text-sm font-normal"><a href="mailto:support@linkupvibes.com">support@linkupvibes.com</a></p>
                    </div>
                    <div class="flex flex-row gap-2">
                        <p class="text-sm font-bold">Business Inquiries:</p>
                        <p class="text-sm font-normal"><a href="mailto:info@linkupvibes.com">info@linkupvibes.com</a></p>
                    </div>
                    <div class="flex flex-row gap-2">
                        <p class="text-sm font-bold">Website:</p>
                        <p class="text-sm font-normal"><a href="https://www.linkupvibes.com" target="_blank">www.linkupvibes.com</a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PREVIOUS TEMPLATE CONTENT
        <div class="text-center mb-12">
            <h1 class="logo text-5xl text-[#2f9bef] leading-none">Link<span style="color:#F5C518">üp</span></h1>
        </div>

        <div class="prose prose-blue dark:prose-invert lg:prose-lg max-w-none prose-headings:text-[#2f9bef]">
            <h1 class="text-4xl font-bold mb-8 text-[#2f9bef] dark:text-blue-400">{{ title }}</h1>
            <p>Last Updated: August 10, 2026</p>

            <h2 class="text-[#2f9bef]">Pricing Overview</h2>
            <p>LinkUp operates a multi-service digital platform. The following schedule consolidates the principal subscription prices, platform fees, transaction fees, commissions, digital-product pricing, and other charges documented for the LinkUp ecosystem. Where applicable, taxes, foreign-exchange charges, payment-processing costs, or third-party banking charges may be added or separately disclosed before a transaction is completed.</p>
            ...
        </div>
        -->
    </LegalLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import LegalLayout from '@/layouts/LegalLayout.vue';

const title = "Complete Products, Services, Subscription & Fee Schedule";
</script>
