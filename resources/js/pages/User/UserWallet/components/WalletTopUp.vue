<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 fade-in pt-6">
        <div
            class="bg-linkup-dark p-8 rounded-[2rem] glow-border relative overflow-hidden h-64 flex flex-col justify-between group border border-slate-800">
            <div class="absolute inset-0 pattern-grid opacity-30"></div>
            <div
                class="absolute right-0 top-0 w-64 h-64 bg-linkup-blue/20 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-linkup-blue/30 transition-colors pointer-events-none">
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 rounded-xl bg-white/10 text-white border border-white/10">
                        <WalletIcon class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-bold uppercase tracking-wider text-white/60">Link Up Digital Wallet</span>
                </div>
                <div class="text-5xl font-extrabold text-white tracking-tight mt-4">
                    {{ balanceText }}
                </div>
            </div>
            <div class="relative z-10 flex gap-4 mt-auto">
                <button
                    class="btn-pulse flex items-center gap-2 px-6 py-3 bg-linkup-blue text-white rounded-xl text-sm font-bold shadow-lg transition-transform hover:-translate-y-0.5"
                    @click="$emit('open', 'wallet')">
                    <Plus class="w-4 h-4" /> Top Up
                </button>
                <button
                    class="flex items-center gap-2 px-6 py-3 bg-white/10 text-white border border-white/20 rounded-xl text-sm font-bold hover:bg-white/20 transition-colors"
                    @click="$emit('open', 'transfer')">
                    <ArrowRightLeft class="w-4 h-4" /> Transfer
                </button>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 p-8 rounded-[2rem] glow-border relative overflow-hidden group h-64 flex flex-col justify-between cursor-pointer border border-yellow-400"
            @click="$emit('open', 'wallet-coins')">
            <div class="absolute inset-0 pattern-ripple opacity-30"></div>
            <div
                class="absolute right-2 top-2 text-white opacity-90 transform group-hover:scale-110 transition-transform duration-500">
                <img src="/storage/avatars/coinlogo.png" alt="Coin Logo" class="w-20 h-20" />
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 rounded-xl bg-white/20 text-white border border-white/20">
                        <Gem class="w-5 h-5" />
                    </div>
                    <span class="text-sm font-bold uppercase tracking-wider text-white/90">Link Up Coins</span>
                </div>
                <div class="text-5xl font-extrabold text-white tracking-tight mt-4 drop-shadow-sm">{{
                    coinBalance.toLocaleString() }}</div>
            </div>
            <div class="relative z-10 mt-auto">
                <button
                    class="w-full flex items-center justify-center gap-2 py-3 bg-white/20 backdrop-blur border border-white/30 rounded-xl text-sm font-bold text-white hover:bg-white/30 transition-colors">
                    <ShoppingBag class="w-4 h-4" /> Buy More
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Wallet as WalletIcon, Plus, ArrowRightLeft, Coins, Gem, ShoppingBag } from 'lucide-vue-next';

defineProps<{
    balanceText: string;
    coinBalance: number;
}>();

defineEmits<{
    (e: 'open', modal: string): void;
}>();
</script>
