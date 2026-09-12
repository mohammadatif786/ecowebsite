<template>
    <footer class="bg-white border-t border-slate-200 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 text-center md:text-left">
                <!-- Brand & Copyright -->
                <div class="md:col-span-1">
                    <Link :href="route('user.home')" class="flex items-center justify-center md:justify-start transition hover:opacity-80">
                        <BrandedLogo size="28" text-class="text-2xl" />
                    </Link>
                    <p class="mt-6 text-sm text-slate-500 leading-relaxed">
                        The Caribbean & LatAm Super-App. Linking people everywhere through vibes, culture, and community.
                    </p>
                    <p class="mt-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        &copy; {{ new Date().getFullYear() }} LinkUp Vibes Ltd.
                    </p>
                </div>

                <!-- Policies Column 1 -->
                <div>
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6">Core Policies</h4>
                    <ul class="space-y-4">
                        <li v-if="$page.props.legalPagesStatus?.['terms-of-service'] !== false"><Link :href="route('legal.terms')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Terms of Service</Link></li>
                        <li v-if="$page.props.legalPagesStatus?.['privacy-policy'] !== false"><Link :href="route('legal.privacy')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Privacy Policy</Link></li>
                        <li v-if="$page.props.legalPagesStatus?.['acceptable-use-policy'] !== false"><Link :href="route('legal.acceptable-use')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Acceptable Use Policy</Link></li>
                        <li v-if="$page.props.legalPagesStatus?.['vibes-acceptable-use-policy'] !== false"><Link :href="route('legal.vibes-policy')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Vibes AUP</Link></li>
                    </ul>
                </div>

                <!-- Policies Column 2 -->
                <div>
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6">Compliance & Safety</h4>
                    <ul class="space-y-4">
                        <li v-if="$page.props.legalPagesStatus?.['prohibited-activities'] !== false"><Link :href="route('legal.prohibited')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Prohibited Activities</Link></li>
                        <li v-if="$page.props.legalPagesStatus?.['refund-policy'] !== false"><Link :href="route('legal.refund')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Refund Policy</Link></li>
                        <li v-if="$page.props.legalPagesStatus?.['law-enforcement-guidelines'] !== false"><Link :href="route('legal.law-enforcement')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Law Enforcement</Link></li>
                        <li v-if="$page.props.legalPagesStatus?.['pricing-and-fees'] !== false"><Link :href="route('legal.pricing')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Pricing & Fees</Link></li>
                    </ul>
                </div>

                <!-- Contact & Social -->
                <div>
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6">Connect</h4>
                    <ul class="space-y-4 mb-8">
                        <li v-if="$page.props.legalPagesStatus?.['contact-and-customer-support'] !== false"><Link :href="route('legal.support')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Contact & Support</Link></li>
                        <li><Link :href="route('new_frontend.home')" class="text-sm font-bold text-slate-500 hover:text-lkink transition">Back to Home</Link></li>
                    </ul>
                    <!-- <div class="flex justify-center md:justify-start space-x-6">
                        <a href="#" class="text-slate-400 hover:text-lkink transition" aria-label="Facebook"><Facebook class="w-5 h-5" /></a>
                        <a href="#" class="text-slate-400 hover:text-lkink transition" aria-label="Twitter"><Twitter class="w-5 h-5" /></a>
                        <a href="#" class="text-slate-400 hover:text-lkink transition" aria-label="Instagram"><Instagram class="w-5 h-5" /></a>
                    </div> -->
                </div>
            </div>

            <!-- <div class="mt-16 pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                    Caribbean & LatAm Super-App · Legal & Compliance Office
                </div>
                <div class="flex gap-4 text-[10px] text-slate-400 font-bold uppercase">
                    <span>English</span>
                    <span class="opacity-30">|</span>
                    <span>Español</span>
                </div>
            </div> -->
        </div>
    </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import BrandedLogo from '../BrandedLogo.vue';
import Facebook from '../icons/facebook.vue';
import Twitter from '../icons/twitter.vue';
import Instagram from '../icons/instagram.vue';
</script>
