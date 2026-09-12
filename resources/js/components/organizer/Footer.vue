<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer>
        <div class="footer-logo">Link Up</div>
        <div class="footer-links">
            <Link href="#">About Us</Link>
            <Link href="#">Frequently Asked Questions</Link>
            <Link v-if="$page.props.legalPagesStatus?.['terms-of-service'] !== false" :href="route('legal.terms')">Terms of Service</Link>
            <Link v-if="$page.props.legalPagesStatus?.['privacy-policy'] !== false" :href="route('legal.privacy')">Privacy Policy</Link>
            <Link v-if="$page.props.legalPagesStatus?.['acceptable-use-policy'] !== false" :href="route('legal.acceptable-use')">Acceptable Use Policy</Link>
            <Link v-if="$page.props.legalPagesStatus?.['vibes-acceptable-use-policy'] !== false" :href="route('legal.vibes-policy')">Vibes AUP</Link>
            <Link v-if="$page.props.legalPagesStatus?.['prohibited-activities'] !== false" :href="route('legal.prohibited')">Prohibited Activities</Link>
            <Link v-if="$page.props.legalPagesStatus?.['refund-policy'] !== false" :href="route('legal.refund')">Refund Policy</Link>
            <Link v-if="$page.props.legalPagesStatus?.['law-enforcement-guidelines'] !== false" :href="route('legal.law-enforcement')">Law Enforcement</Link>
            <Link v-if="$page.props.legalPagesStatus?.['pricing-and-fees'] !== false" :href="route('legal.pricing')">Pricing & Fees</Link>
            <Link v-if="$page.props.legalPagesStatus?.['contact-and-customer-support'] !== false" :href="route('legal.support')">Contact & Support</Link>
            <Link href="#">Blog</Link>
        </div>
        <div class="social-icons">
            <div class="social-icon">f</div>
            <div class="social-icon">x</div>
            <div class="social-icon">📷</div>
        </div>
    </footer>
</template>
