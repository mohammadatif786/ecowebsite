<script setup lang="ts">
import { SelectOption, type BreadcrumbItem, type User } from '@/types';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Header from '@/components/ProfileTabComponents/Header.vue';
import AboutTab from '@/components/ProfileTabComponents/AboutTab.vue';
import EventAndTicketTab from '@/components/ProfileTabComponents/EventAndTicketTab.vue';
import MatchesTab from '@/components/ProfileTabComponents/MatchesTab.vue';
import MarketPlaceTab from '@/components/ProfileTabComponents/MarketPlaceTab.vue';
import NewsTab from '@/components/ProfileTabComponents/NewsTab.vue';
import GiftsTab from '@/components/ProfileTabComponents/GiftsTab.vue';
import LinkUpLive from '@/components/ProfileTabComponents/LinkUpLive.vue';
import SubscriptionTab from '@/components/ProfileTabComponents/SubscriptionTab.vue';
import LinkUpCoinTab from '@/components/ProfileTabComponents/LinkUpCoinTab.vue';
import BillPaymentTab from '@/components/ProfileTabComponents/BillPaymentTab.vue';
import OrganizerTab from '@/components/ProfileTabComponents/OrganizerTab.vue';
import WalletTab from '@/components/ProfileTabComponents/WalletTab.vue';
import { ref, watch } from 'vue';
import axios from 'axios';

interface Props {
    user: User;
    purchased_tickets: number;
    cancelled_tickets: number;
    market_place: number;
    matches: number;
    like_matches: number;
    nationalities: Array<SelectOption>;
    caribbean_island: Array<SelectOption>;
    phone_codes: Array<SelectOption>;
    gifts_purchased: number;
    gifts_collected_amount: number;
    gifts_sent: Array<any>;
    gifts_received: Array<any>;
    all_gifts: Array<any>;
    news: Array<any>;
    user_wallet_balance?: number;
}

const props = defineProps<Props>();
const eventTickectTabData = ref([]);
const marketPlaceTabData = ref([]);
const matchesTabTabData = ref([]);
const subscriptionTabData = ref([]);
const linkUpCoinTabData = ref([]);
const organizerTabData = ref([]);
const walletTabData = ref([]);
const linkupLiveTabData = ref([]);
const newsTabData = ref([]);
const active_tab = ref('about');
const active_css = 'bg-sky-100/12 border-sky-300/25 text-sky-900 shadow-[0_10px_22px_rgba(14,165,233,.14)] -translate-y-[1px]'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: route('admin.users.index'),
    },
    {
        title: 'View',
        href: route('admin.users.show', props.user.id),
    },
];

watch(
    () => active_tab.value,
    async (value) => {
        if (value === 'tickets') {
            const response = await axios.get(`/api/ticket-tab/${props.user.id}`)
            if (response && response.data) {
                eventTickectTabData.value = response.data.data
            }
        } else if (value === 'marketplace') {
            const response = await axios.get(`/api/marke-tplace-tab/${props.user.id}`)
            if (response && response.data) {
                marketPlaceTabData.value = response.data.data
            }
        } else if (value === 'matches') {
            const response = await axios.get(`/api/matches-tab/${props.user.id}`)
            if (response && response.data) {
                matchesTabTabData.value = response.data.data
            }
        } else if (value === 'subscription') {
            const response = await axios.get(`/api/subscription-tab/${props.user.id}`)
            if (response && response.data) {
                subscriptionTabData.value = response.data
            }
        } else if (value === 'coins') {
            const response = await axios.get(`/api/linkup-coin-tab/${props.user.id}`)
            if (response && response.data) {
                linkUpCoinTabData.value = response.data
            }
        } else if (value === 'organizer') {
            const response = await axios.get(`/api/organizer-tab/${props.user.id}`)
            if (response && response.data) {
                organizerTabData.value = response.data
            }
        } else if (value === 'wallet') {
            const response = await axios.get(`/api/wallet-tab/${props.user.id}`)
            if (response && response.data) {
                walletTabData.value = response.data
            }
        } else if (value === 'linkup-live') {
            const response = await axios.get(`/api/linkup-live-tab/${props.user.id}`)
            if (response && response.data) {
                linkupLiveTabData.value = response.data.data
            }
        } else if (value === 'news') {
            const response = await axios.get(`/api/news-tab/${props.user.id}`)
            if (response && response.data) {
                newsTabData.value = response.data.data
            }
        }
    },
    { immediate: true }
)

</script>

<template>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">

        <Head title="Users" />
        <div class="mx-auto flex w-full flex-col space-y-6 p-6">
            <Header :user="props.user" :purchased_tickets="props.purchased_tickets"
                :cancelled_tickets="props.cancelled_tickets" :market_place="props.market_place" :matches="props.matches"
                :gifts_purchased="props.gifts_purchased" :gifts_collected_amount="props.gifts_collected_amount"
                :like_matches="props.like_matches" />

            <!-- tabs section  -->
            <section class="mt-6">

                <div class="flex flex-wrap gap-2">

                    <button @click="active_tab = 'about'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'about' ? active_css : ''}`">
                        About
                    </button>

                    <button @click="active_tab = 'tickets'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'tickets' ? active_css : ''}`">
                        Tickets & Events
                    </button>

                    <button @click="active_tab = 'marketplace'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'marketplace' ? active_css : ''}`">
                        Marketplace
                    </button>

                    <button @click="active_tab = 'news'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'news' ? active_css : ''}`">
                        News
                    </button>

                    <button @click="active_tab = 'matches'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'matches' ? active_css : ''}`">
                        Matches
                    </button>

                    <button @click="active_tab = 'gifts'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'gifts' ? active_css : ''}`">
                        Gifts
                    </button>

                    <button @click="active_tab = 'linkup-live'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'linkup-live' ? active_css : ''}`">
                        LinkUp Live
                    </button>

                    <button @click="active_tab = 'subscription'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'subscription' ? active_css : ''}`">
                        <span class="inline-flex items-center gap-2">Subscription</span>
                    </button>

                    <button @click="active_tab = 'coins'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'coins' ? active_css : ''}`">
                        LinkUp Coins
                    </button>

                    <button @click="active_tab = 'bills'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'bills' ? active_css : ''}`">
                        Bill Payment
                    </button>

                    <button @click="active_tab = 'organizer'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'organizer' ? active_css : ''}`">
                        Event Organizer
                    </button>

                    <button @click="active_tab = 'wallet'"
                        :class="`px-4 py-2 rounded-xl border border-slate-200 bg-white font-extrabold text-sm transition-all duration-200 cursor-pointer ${active_tab === 'wallet' ? active_css : ''}`">
                        Wallet
                    </button>

                </div>


                <div class="mt-4">
                    <!-- tabs  -->
                    <AboutTab v-if="active_tab === 'about'" :user="props.user" />
                    <EventAndTicketTab v-if="active_tab === 'tickets'" :TabData="eventTickectTabData" />
                    <MarketPlaceTab v-if="active_tab === 'marketplace'" :TabData="marketPlaceTabData" />
                    <MatchesTab v-if="active_tab === 'matches'" :TabData="matchesTabTabData" />
                    <NewsTab v-if="active_tab === 'news'" :TabData="newsTabData" />
                    <GiftsTab v-if="active_tab === 'gifts'" :gifts_sent="props.gifts_sent"
                        :gifts_received="props.gifts_received" :all_gifts="props.all_gifts"
                        :user_wallet_balance="props.user_wallet_balance" />
                    <LinkUpLive v-if="active_tab === 'linkup-live'" :TabData="linkupLiveTabData" />
                    <SubscriptionTab v-if="active_tab === 'subscription'" :TabData="subscriptionTabData" />
                    <LinkUpCoinTab v-if="active_tab === 'coins'" :TabData="linkUpCoinTabData"
                        :coins="props.user.coins" />
                    <BillPaymentTab v-if="active_tab === 'bills'" />
                    <OrganizerTab v-if="active_tab === 'organizer'" :TabData="organizerTabData" />
                    <WalletTab v-if="active_tab === 'wallet'" :TabData="walletTabData" />
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
