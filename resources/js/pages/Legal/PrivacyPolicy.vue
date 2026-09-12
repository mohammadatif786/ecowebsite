<template>
    <Head :title="title" />
    <LegalLayout>
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-[#17365D]">LinkUp Vibes - {{ title }}</h1>
            <p class="text-center mt-2 text-sm text-slate-500">Effective 1 January 2026 | Updated 7 August 2026</p>
        </div>

        <div class="prose prose-blue dark:prose-invert lg:prose-lg max-w-none prose-headings:text-[#17365D] prose-headings:font-bold">
            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Introduction</h2>
            <p class="text-sm font-normal">This Privacy Policy explains how LinkUp collects, uses, stores, shares, and protects personal information when you use the LinkUp website, mobile application, wallet services, event platform, marketplace, messaging features, and related services.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Information We Collect</h2>
            <p class="text-sm font-normal">We may collect information you provide directly, including your name, email address, username, telephone number, date of birth, profile information, payment details, identity verification information, event registrations, merchant information, communications with customer support, and other information required to provide our services.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Automatically Collected Information</h2>
            <p class="text-sm font-normal">We may automatically collect device information, IP address, browser type, operating system, cookies, usage information, log files, location information where permitted, and analytics data to improve platform performance and security.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">How We Use Information</h2>
            <p class="text-sm font-normal">Information may be used to create and manage accounts, process payments, provide wallet services, facilitate events and marketplace transactions, improve products, personalize content, prevent fraud, comply with legal obligations, provide customer support, and communicate important service updates.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Sharing of Information</h2>
            <p class="text-sm font-normal">LinkUp does not sell personal information. Information may be shared with payment processors, financial institutions, identity verification providers, cloud service providers, technology vendors, business partners, and government authorities where required by law or to protect users and the platform.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Security</h2>
            <p class="text-sm font-normal">LinkUp uses administrative, technical, and physical safeguards designed to protect personal information from unauthorized access, disclosure, alteration, or destruction. No security system can guarantee absolute security.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Data Retention</h2>
            <p class="text-sm font-normal">Personal information is retained only for as long as necessary to provide services, comply with legal obligations, resolve disputes, enforce agreements, and meet regulatory requirements.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Your Rights</h2>
            <p class="text-sm font-normal">Depending on your location, you may have rights to access, correct, update, delete, or request a copy of your personal information, object to certain processing, or withdraw consent where applicable.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Cookies</h2>
            <p class="text-sm font-normal">LinkUp may use cookies and similar technologies to remember preferences, maintain security, analyze usage, and improve user experience. Users may manage cookie settings through their browser where available.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Children</h2>
            <p class="text-sm font-normal">LinkUp is not intended for children under the minimum legal age required to use the service. Accounts found to violate age requirements may be suspended or removed.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">International Transfers</h2>
            <p class="text-sm font-normal">Information may be processed or stored in countries where LinkUp or its service providers operate. Appropriate safeguards are used where required by applicable law.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Changes</h2>
            <p class="text-sm font-normal">LinkUp may update this Privacy Policy periodically. Updated versions become effective when published on the website.</p>

            <h2 class="text-[#17365D] font-bold mt-4 mb-2">Contact</h2>
            <p class="text-sm font-normal">Privacy questions may be directed to <a href="mailto:privacy@linkupvibes.com">privacy@linkupvibes.com</a>.</p>
            <p class="text-sm font-normal">
                Customer Support: <a href="mailto:support@linkupvibes.com">support@linkupvibes.com</a><br>
                Website: <a href="https://www.linkupvibes.com" target="_blank">www.linkupvibes.com</a>
            </p>
        </div>

        <!-- PREVIOUS TEMPLATE CONTENT
        <div class="text-center mb-12">
            <h1 class="logo text-5xl text-[#2f9bef] leading-none">Link<span style="color:#F5C518">üp</span></h1>
        </div>

        <div class="prose prose-blue dark:prose-invert lg:prose-lg max-w-none prose-headings:text-[#2f9bef]">
            <h1 class="text-4xl font-bold mb-8 text-[#2f9bef] dark:text-blue-400">{{ title }}</h1>
            <h2 class="text-[#2f9bef]">Introduction</h2>
            <p>This Privacy Policy explains how LinkUp collects, uses, stores, shares, and protects personal information when you use the LinkUp website, mobile application, wallet services, event platform, marketplace, messaging features, and related services.</p>
            <h2 class="text-[#2f9bef]">Information We Collect</h2>
            <p>We may collect information you provide directly, including your name, email address, username, telephone number, date of birth, profile information, payment details, identity verification information, event registrations, merchant information, communications with customer support, and other information required to provide our services.</p>
            <h2 class="text-[#2f9bef]">Automatically Collected Information</h2>
            <p>We may automatically collect device information, IP address, browser type, operating system, cookies, usage information, log files, location information where permitted, and analytics data to improve platform performance and security.</p>
            <h2 class="text-[#2f9bef]">How We Use Information</h2>
            <p>Information may be used to create and manage accounts, process payments, provide wallet services, facilitate events and marketplace transactions, improve products, personalize content, prevent fraud, comply with legal obligations, provide customer support, and communicate important service updates.</p>
            <h2 class="text-[#2f9bef]">Sharing of Information</h2>
            <p>LinkUp does not sell personal information. Information may be shared with payment processors, financial institutions, identity verification providers, cloud service providers, technology vendors, business partners, and government authorities where required by law or to protect users and the platform.</p>
            <h2 class="text-[#2f9bef]">Security</h2>
            <p>LinkUp uses administrative, technical, and physical safeguards designed to protect personal information from unauthorized access, disclosure, alteration, or destruction. No security system can guarantee absolute security.</p>
            <h2 class="text-[#2f9bef]">Data Retention</h2>
            <p>Personal information is retained only for as long as necessary to provide services, comply with legal obligations, resolve disputes, enforce agreements, and meet regulatory requirements.</p>
            <h2 class="text-[#2f9bef]">Your Rights</h2>
            <p>Depending on your location, you may have rights to access, correct, update, delete, or request a copy of your personal information, object to certain processing, or withdraw consent where applicable.</p>
            <h2 class="text-[#2f9bef]">Cookies</h2>
            <p>LinkUp may use cookies and similar technologies to remember preferences, maintain security, analyze usage, and improve user experience. Users may manage cookie settings through their browser where available.</p>
            <h2 class="text-[#2f9bef]">Children</h2>
            <p>LinkUp is not intended for children under the minimum legal age required to use the service. Accounts found to violate age requirements may be suspended or removed.</p>
            <h2 class="text-[#2f9bef]">International Transfers</h2>
            <p>Information may be processed or stored in countries where LinkUp or its service providers operate. Appropriate safeguards are used where required by applicable law.</p>
            <h2 class="text-[#2f9bef]">Changes</h2>
            <p>LinkUp may update this Privacy Policy periodically. Updated versions become effective when published on the website.</p>
            <h2 class="text-[#2f9bef]">Contact</h2>
            <p>Privacy questions may be directed to <a href="mailto:privacy@linkupvibes.com">privacy@linkupvibes.com</a>.</p>
            <p>
                Customer Support: <a href="mailto:support@linkupvibes.com">support@linkupvibes.com</a><br>
                Website: <a href="https://www.linkupvibes.com" target="_blank">www.linkupvibes.com</a>
            </p>
        </div>
        -->
    </LegalLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import LegalLayout from '@/layouts/LegalLayout.vue';

const title = "Privacy Policy";
</script>
