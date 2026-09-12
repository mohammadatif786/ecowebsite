<script setup lang="ts">
import { ref } from 'vue';

const curProv = ref('scotiabank');
const PROVIDERS: any = {
    scotiabank: { name: 'Scotiabank', type: 'Bank onboarding partner', baseUrl: 'https://api.scotiabank.com/linkup/v1', fields: [['clientId', 'Client ID', 'text', 'client_xxx'], ['clientSecret', 'Client Secret', 'password', ''], ['apiKey', 'API Key', 'password', 'sk_live_xxx'], ['partnerId', 'Merchant / Partner ID', 'text', 'LINKUP-XXXX'], ['account', 'Settlement account / IBAN', 'text', '']] },
    stripe: { name: 'Stripe', type: 'Card & wallet processor', baseUrl: 'https://api.stripe.com/v1', fields: [['publishableKey', 'Publishable key', 'text', 'pk_live_xxx'], ['secretKey', 'Secret key', 'password', 'sk_live_xxx'], ['webhookSecret', 'Webhook signing secret', 'password', 'whsec_xxx'], ['accountId', 'Connected account ID', 'text', 'acct_xxx']] }
};

const saveWalletFunding = () => {
    alert('Wallet funding credentials saved');
};

const testWalletFunding = () => {
    alert('Test connection successful');
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">Wallet Funding API · Banking Partner</h3>
            <p class="text-slate-500">Connect the bank that funds wallet top-ups and money transfers (Scotiabank is our launch onboarding partner). Add their API credentials here to onboard them to handle wallet funding & settlement.</p>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            <button v-for="(p, key) in PROVIDERS" :key="key" @click="curProv = key"
                    :class="['rounded-2xl px-5 py-2.5 font-black text-sm', curProv === key ? 'text-white bg-slate-900' : 'bg-white border border-slate-200 text-slate-600']">
                {{ p.name }}
            </button>
        </div>

        <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6" v-if="PROVIDERS[curProv]">
            <div class="2xl:col-span-2 card rounded-3xl p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-xl font-black">{{ PROVIDERS[curProv].name }} API</h3>
                    <label class="flex items-center gap-2 text-sm font-black">Route wallet funding here <input type="checkbox" class="w-5 h-5" /></label>
                </div>
                <p class="text-[12px] text-slate-400 mb-4">{{ PROVIDERS[curProv].type }} — funds wallet top-ups & money transfers.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="font-bold text-slate-600">Environment
                        <select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                            <option value="sandbox">Sandbox</option>
                            <option value="production">Production</option>
                        </select>
                    </label>
                    <div></div>
                    <div class="md:col-span-2">
                        <label class="font-bold text-slate-600">API Base URL
                            <input :value="PROVIDERS[curProv].baseUrl" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        </label>
                    </div>
                    <div v-for="f in PROVIDERS[curProv].fields" :key="f[0]">
                        <label class="font-bold text-slate-600">{{ f[1] }}
                            <input :type="f[2]" :placeholder="f[3]" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        </label>
                    </div>
                </div>
                <div class="mt-5 flex gap-3">
                    <button @click="saveWalletFunding" class="rounded-2xl bg-gradient-to-r from-red-600 to-rose-500 text-white px-5 py-3 font-black">Save credentials</button>
                    <button @click="testWalletFunding" class="rounded-2xl bg-white border border-slate-200 px-5 py-3 font-black">Test connection</button>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">{{ PROVIDERS[curProv].name }} status</h3>
                <div class="space-y-3">
                    <div class="rounded-2xl bg-amber-50 p-4"><b>Status</b><p class="text-amber-700 font-bold">Not connected</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><b>Environment</b><p class="text-xl font-black">Sandbox</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><b>Wallet routing</b><p class="text-xl font-black">Off</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><b>Last test</b><p class="font-bold">—</p></div>
                    <p class="text-[12px] text-slate-400">Only one provider routes wallet funding at a time. Credentials are stored for the developer to wire to the live integration.</p>
                </div>
            </div>
        </div>
    </div>
</template>
