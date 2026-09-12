<template>
    <Head :title="title" />
    <LegalLayout>
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-[#312E81]">LINKUP</h1>
        </div>
        <h1 class="text-2xl font-bold">Law Enforcement Request Guidelines</h1>
        <div class="prose prose-blue dark:prose-invert lg:prose-lg max-w-none prose-headings:text-[#312E81] prose-headings:font-bold">
            <h2 class="text-[#312E81] font-bold mt-4 mb-2">Purpose</h2>
            <p class="text-sm font-normal">LinkUp is committed to protecting user privacy while complying with applicable laws and valid legal requests from authorized law enforcement and government agencies. These guidelines explain how authorized agencies may request account information or other records from LinkUp.</p>

            <h2 class="text-[#312E81] font-bold mt-4 mb-2">Submitting a Request</h2>
            <p class="text-sm font-normal">Law enforcement requests should be submitted by an authorized government or law enforcement agency. Requests should include the requesting agency's name, officer or agent name, official email address, telephone number, legal authority supporting the request, and sufficient information to identify the relevant account or transaction.</p>

            <h2 class="text-[#312E81] font-bold mt-4 mb-2">Accepted Legal Process</h2>
            <p class="text-sm font-normal">LinkUp reviews requests supported by appropriate legal process, including subpoenas, court orders, search warrants, production orders, or other legally valid requests permitted by applicable law. Requests that are incomplete or not legally sufficient may be rejected or returned for clarification.</p>

            <h2 class="text-[#312E81] font-bold mt-4 mb-2">Emergency Requests</h2>
            <p class="text-sm font-normal">Where an agency believes there is an imminent threat of death, serious physical injury, child exploitation, terrorism, human trafficking, or another emergency recognized by law, the request should clearly identify the emergency and explain why expedited disclosure is necessary.</p>

            <h2 class="text-[#312E81] font-bold mt-4 mb-2">Information That May Be Available</h2>
            <p class="text-sm font-normal">Subject to applicable law, LinkUp may preserve or disclose basic subscriber information, account registration details, transaction records, wallet activity, event purchase records, merchant activity, IP logs, device information, or other records that LinkUp maintains and is legally permitted to disclose.</p>

            <h2 class="text-[#312E81] font-bold mt-4 mb-2">Data Preservation</h2>
            <p class="text-sm font-normal">Where permitted by law, LinkUp may preserve relevant account information for a reasonable period while valid legal process is obtained.</p>

            <h2 class="text-[#312E81] font-bold mt-4 mb-2">International Requests</h2>
            <p class="text-sm font-normal">Requests originating outside the jurisdiction in which LinkUp operates may require additional legal review or the use of applicable mutual legal assistance procedures before information can be disclosed.</p>

            <h2 class="text-[#312E81] font-bold mt-4 mb-2">Privacy & User Rights</h2>
            <p class="text-sm font-normal">LinkUp carefully reviews each request to ensure it complies with applicable law and our Privacy Policy. We seek to disclose only the information required by law.</p>

            <h2 class="text-[#312E81] font-bold mt-4 mb-2">Contact</h2>
            <div class="mt-12 pt-8 border-t border-slate-200">
                <div class="">
                    <div class="flex flex-row gap-2">
                        <p class="text-sm font-bold">Law Enforcement Email:</p>
                        <p class="text-sm font-normal"><a href="mailto:legal@linkupvibes.com">legal@linkupvibes.com</a></p>
                    </div>
                    <div class="flex flex-row gap-2">
                        <p class="text-sm font-bold">General Legal Inquiries:</p>
                        <p class="text-sm font-normal"><a href="mailto:legal@linkupvibes.com">legal@linkupvibes.com</a></p>
                    </div>
                    <div class="flex flex-row gap-2">
                        <p class="text-sm font-bold">Customer Support:</p>
                        <p class="text-sm font-normal"><a href="mailto:support@linkupvibes.com">support@linkupvibes.com</a></p>
                    </div>
                    <div class="flex flex-row gap-2">
                        <p class="text-sm font-bold">Website:</p>
                        <p class="text-sm font-normal"><a href="https://www.linkupvibes.com" target="_blank">www.linkupvibes.com</a></p>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h3 class="text-[#312E81] font-bold">Important Notice</h3>
                <p class="text-sm font-normal">
                    LinkUp does not accept legal requests through social media or customer support channels. All legal requests should be submitted through the designated legal contact using official government email addresses where possible.
                </p>
            </div>
        </div>
    </LegalLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import LegalLayout from '@/layouts/LegalLayout.vue';

const title = "Law Enforcement Request Guidelines";
</script>
