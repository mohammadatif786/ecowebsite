<script setup lang="ts">
import { ref } from 'vue';

const FF_FEATURES = [
    ['vibes', 'Vibes', 'Social feed & stories'],
    ['uvibe', 'U Vibe', 'University cross-campus thread'],
    ['eats', 'LinkUp Eats', 'Food delivery & dine-in'],
    ['live', 'LinkUp Live', 'Live streaming'],
    ['dating', 'LinkUp Dating', 'Swipes & matching'],
    ['shop', 'Marketplace', 'Buy & sell'],
    ['events', 'Events & Tickets', 'Discovery & ticketing'],
    ['news', '360 News', 'Caribbean 360 News'],
    ['nightlife', 'Nightlife', 'Clubs & fetes'],
    ['wallet', 'LinkUp Wallet', 'Wallet, send & bills'],
    ['asue', 'Asue / Susu', 'Savings circles'],
    ['save', 'LinkUp Save', 'High-yield savings'],
    ['coins', 'LinkUp Coins', 'Coin economy'],
    ['crypto', 'Cryptocurrency', 'Crypto wallet & trading'],
    ['wellness', 'Wellness & Spa', 'Massage & spa booking']
];

const FF_COUNTRIES = ['Bahamas', 'Jamaica', 'Trinidad & Tobago', 'Barbados', 'Saint Lucia', 'Guyana', 'Dominican Republic', 'Brazil', 'United States', 'Canada'];
const ffCountry = ref('Bahamas');

const ffAllCountries = (on: boolean) => {
    alert(on ? 'Activated ALL everywhere' : 'Disabled ALL everywhere');
};

const ffAll = (on: boolean) => {
    alert(on ? 'Enabled all for ' + ffCountry.value : 'Disabled all for ' + ffCountry.value);
};

const ffToggle = (key: string) => {
    alert('Toggled ' + key);
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">Feature Flags · Country Control</h3>
            <p class="text-slate-500">Turn LinkUp modules on/off per country. A disabled feature is hidden in the app for that market and shows a “coming soon” message — flip the toggle to launch it (e.g. roll out The Bahamas first, switch features on elsewhere when ready, or shut one off in a single country if there’s an issue).</p>
        </div>

        <div class="card rounded-3xl p-6 mb-5" style="background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black flex items-center gap-2">🌍 Global testing control</h3>
                    <p class="text-[13px] text-white/70">Turn every feature on (or off) across all {{ FF_COUNTRIES.length }} markets at once — test everything without toggling country by country.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="ffAllCountries(true)" class="rounded-2xl bg-green-500 text-white px-5 py-2.5 font-black text-sm">Activate ALL everywhere</button>
                    <button @click="ffAllCountries(false)" class="rounded-2xl bg-white/15 text-white px-5 py-2.5 font-black text-sm">Disable ALL everywhere</button>
                </div>
            </div>
            <div class="mt-3 text-[12px] font-bold text-green-300">✓ All features are active in every country (full-test mode).</div>
        </div>

        <div class="card rounded-3xl p-6 mb-5">
            <div class="flex flex-wrap items-center gap-3 justify-between">
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase mb-1">Country / market</label>
                    <select v-model="ffCountry" class="rounded-2xl border border-slate-200 px-4 py-2.5 font-black">
                        <option v-for="c in FF_COUNTRIES" :key="c" :value="c">{{ c }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-500">{{ FF_FEATURES.length }} / {{ FF_FEATURES.length }} enabled</span>
                    <button @click="ffAll(true)" class="rounded-2xl border border-slate-200 px-4 py-2.5 font-black text-sm">Enable all</button>
                    <button @click="ffAll(false)" class="rounded-2xl border border-slate-200 px-4 py-2.5 font-black text-sm text-rose-600">Disable all</button>
                </div>
            </div>
            <p class="text-[12px] text-slate-400 mt-3">Saved instantly to the shared bridge; takes effect next time a user in {{ ffCountry }} opens the app.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div v-for="ft in FF_FEATURES" :key="ft[0]" class="card rounded-2xl p-4 flex items-center justify-between gap-3">
                <div>
                    <p class="font-black">{{ ft[1] }}</p>
                    <p class="text-[12px] text-slate-500">{{ ft[2] }}</p>
                </div>
                <button @click="ffToggle(ft[0])" class="shrink-0 rounded-full px-4 py-2 font-black text-sm bg-green-500 text-white">On</button>
            </div>
        </div>
    </div>
</template>
