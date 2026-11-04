<template>
  <div class="bg-white rounded-2xl p-5 shadow-glass border">
    <!-- Header -->
    <div class="flex items-center gap-2 mb-2">
      <i data-lucide="dollar-sign" class="w-4 h-4 text-slate-500"></i>
      <div class="font-semibold">Creator Earnings</div>
    </div>

    <!-- Earnings Stats -->
    <div class="grid gap-2 text-sm">
      <div class="flex justify-between">
        <span>Available</span>
        <span>{{ availableEarnings }}</span>
      </div>
      <div class="flex justify-between">
        <span>Lifetime</span>
        <span>{{ lifetimeEarnings }}</span>
      </div>
      <div class="flex justify-between">
        <span>Last gift</span>
        <span>{{ lastGift }}</span>
      </div>
    </div>

    <!-- Withdraw button -->
    <button
      class="mt-3 w-full px-3 py-2 rounded-xl bg-blue-400 text-white text-sm"
      @click="withdraw"
    >
      Withdraw
    </button>
  </div>
</template>

<script setup>
import { ref } from "vue"

// Dummy data
const availableEarnings = ref("$320")
const lifetimeEarnings = ref("$1,540")
const lastGift = ref(new Date("2025-09-20T14:35:00").toLocaleString())

// Actions
const withdraw = () => {
  alert("Withdraw earnings (demo)")
}
</script>
