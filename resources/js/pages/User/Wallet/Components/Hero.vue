<template>
  <section class="max-w-6xl mx-auto px-4 mt-6">
    <div class="w-full rounded-2xl overflow-hidden relative bg-dark text-white">
      <!-- Background image -->
      <img
        src="https://images.unsplash.com/photo-1558494949-ef010cbdcc31?q=80&w=1600&auto=format&fit=crop"
        class="absolute inset-0 w-full h-full object-cover"
        alt="galaxy background"
      />

      <!-- Dark-to-light gradient (bottom → top) -->
      <div class="absolute inset-0 bg-gradient-to-t from-[#000000] via-[#000000cc] to-transparent"></div>

      <!-- Content -->
      <div class="relative p-6 flex items-center gap-4">
        <!-- Icon -->
        <div class="hidden sm:block">
          <i data-lucide="credit-card" class="w-12 h-12 text-white/80"></i>
        </div>

        <!-- Title -->
        <div>
          <div class="uppercase tracking-wide text-white/70 text-xs">Link Up</div>
          <h1 class="text-2xl font-bold">Wallet + Wantson Coins</h1>
          <p class="text-white/70 text-sm">
            Buy coins, gift creators (50/50), and withdraw earnings.
          </p>
        </div>

        <!-- Buttons -->
        <div class="ml-auto flex gap-2">
          <button
            class="px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-sm"
            @click="addMoney"
          >
            Add Money
          </button>
          <button
            class="px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-sm"
            @click="sendMoney"
          >
            Send
          </button>
          <button
            class="px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-sm"
            @click="buyCoins"
          >
            Buy Coins
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'

const addMoney = () => router.visit(route('frontend.user.wallet.add'))
const sendMoney = () => alert("Send clicked (demo)")
const buyCoins = () => router.visit(route('frontend.user.wallet.coin'))
</script>
