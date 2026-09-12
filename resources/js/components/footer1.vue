<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from './ApplicationLogo.vue';
import Facebook from './icons/facebook.vue';
import Instagram from './icons/instagram.vue';
import Twitter from './icons/twitter.vue';
</script>
<style scoped>
li:hover {
    cursor: pointer;
    color: white;
}
</style>

<template>
    <div>
        <footer class="bg-gray-800 p-3 py-20 text-white">
            <div class="container mx-auto flex flex-wrap justify-between gap-5">
                <div>
                    <ApplicationLogo />
                </div>
                <div class="flex gap-6 text-gray-300 duration-15 md:text-[18px]">
                    <div>
                        <ul class="list-unstyled">
                            <li v-if="$page.props.legalPagesStatus?.['terms-of-service'] !== false">
                                <Link :href="route('legal.terms')">Terms of Service</Link>
                            </li>
                            <li v-if="$page.props.legalPagesStatus?.['privacy-policy'] !== false">
                                <Link :href="route('legal.privacy')">Privacy Policy</Link>
                            </li>
                            <li v-if="$page.props.legalPagesStatus?.['acceptable-use-policy'] !== false">
                                <Link :href="route('legal.acceptable-use')">Acceptable Use Policy</Link>
                            </li>
                            <li v-if="$page.props.legalPagesStatus?.['vibes-acceptable-use-policy'] !== false">
                                <Link :href="route('legal.vibes-policy')">Vibes AUP</Link>
                            </li>
                            <li v-if="$page.props.legalPagesStatus?.['prohibited-activities'] !== false">
                                <Link :href="route('legal.prohibited')">Prohibited Activities</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <ul>
                            <li v-if="$page.props.legalPagesStatus?.['refund-policy'] !== false">
                                <Link :href="route('legal.refund')">Refund Policy</Link>
                            </li>
                            <li v-if="$page.props.legalPagesStatus?.['law-enforcement-guidelines'] !== false">
                                <Link :href="route('legal.law-enforcement')">Law Enforcement</Link>
                            </li>
                            <li v-if="$page.props.legalPagesStatus?.['pricing-and-fees'] !== false">
                                <Link :href="route('legal.pricing')">Pricing & Fees</Link>
                            </li>
                            <li v-if="$page.props.legalPagesStatus?.['contact-and-customer-support'] !== false">
                                <Link :href="route('legal.support')">Contact & Support</Link>
                            </li>
                        </ul>
                    </div>
                </div>
                <div>
                    <ul class="flex">
                        <li class="">
                            <div class="">
                                <Instagram />
                            </div>
                        </li>
                        <li>
                            <Facebook />
                        </li>
                        <li>
                            <Twitter />
                        </li>
                    </ul>
                </div>
            </div>
        </footer>
    </div>
</template>
