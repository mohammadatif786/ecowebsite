<template>
    <div class="grid md:grid-cols-3 gap-4">
        <!-- Balances Card -->
        <div class="md:col-span-2 bg-white rounded-2xl p-5 shadow-glass border">
            <div class="flex items-center gap-3">
                <i data-lucide="wallet" class="w-5 h-5 text-slate-500"></i>
                <div class="font-semibold">Balances</div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 mt-2">
                <!-- Coins -->
                <div class="rounded-xl border p-3">
                    <div class="text-xs text-slate-500">Wallet Coins</div>
                    <div class="text-3xl font-bold">
                        ${{ currentBalance }}
                        <span class="text-base font-normal opacity-60">WCO</span>
                    </div>
                    <div class="text-xs text-slate-500">
                        <button class="underline" @click="showHelp = true">Coins & Payout Policy</button> •
                        <button class="underline" @click="showHelp = true">Multi-Currency Help</button>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-4 flex flex-wrap gap-2">
                <button class="px-4 py-2 rounded-xl border text-sm bg-blue-400 text-white hover:bg-blue-500"
                    @click="addMoney">
                    Add Money
                </button>
                <button class="px-4 py-2 rounded-xl text-white bg-blue-400 hover:bg-blue-500 border text-sm" @click="showSend = true">
                    Add Contact
                </button>
                <button class="px-4 py-2 rounded-xl text-white bg-blue-400 hover:bg-blue-500 border text-sm"
                    @click="showRequest = true">
                    Request
                </button>
                <button class="px-4 py-2 rounded-xl text-white bg-blue-400 hover:bg-blue-500 border text-sm"
                    @click="showPayCode = true">
                    My PayCode
                </button>
                <button class="px-4 py-2 rounded-xl text-white bg-blue-400 hover:bg-blue-500 border text-sm"
                    @click="buyCoins">
                    Buy Coins
                </button>
                <button class="px-4 py-2 rounded-xl text-white bg-blue-400 hover:bg-blue-500 border text-sm" @click="showHelp = true">
                    Help
                </button>
            </div>
        </div>

        <!-- This Month Stats -->
        <div class="bg-white rounded-2xl p-5 shadow-glass border">
            <div class="font-semibold mb-2">This Month</div>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div>
                    <div class="text-xs text-slate-500">Top-ups</div>
                    <div class="font-bold">{{ statTopups }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Sent</div>
                    <div class="font-bold">{{ statSent }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Gifts</div>
                    <div class="font-bold">{{ statGifts }}</div>
                </div>
            </div>
            <button class="mt-4 w-full px-3 py-2 rounded-xl border text-sm">
                Export CSV
            </button>
        </div>
    </div>
    <!-- Modal -->
    <HelpModal :open="showHelp" @close="showHelp = false" />
    <RequestMoneyModal :open="showRequest" :currency="'USD'" @close="showRequest = false" :users="users"/>
    <QRModal :open="showPayCode" @close="showPayCode = false" />
    <SendMoneyModal :open="showSend" @close="showSend = false" :users="users"/>

</template>

<script setup lang="ts">
import { ref } from "vue"
import HelpModal from "./modals/HelpModal.vue"
import RequestMoneyModal from "./modals/RequestMoneyModal.vue"
import QRModal from "./modals/QRModal.vue"
import SendMoneyModal from "./modals/SendMoneyModal.vue"
import { router } from '@inertiajs/vue3'

const showHelp = ref(false);
const showRequest = ref(false)
const showPayCode = ref(false)
const showSend = ref(false)

defineProps({
    currentBalance: {
        type: Number,
        default: 0
    },
    users: {
        type: Array,
        default: () => []
    }
})


// Dummy stats
const statTopups = ref("$500")
const statSent = ref("$220")
const statGifts = ref("50 WCO")

const addMoney = () => router.visit(route('frontend.user.wallet.add'))
const buyCoins = () => router.visit(route('frontend.user.wallet.coin'))
</script>
