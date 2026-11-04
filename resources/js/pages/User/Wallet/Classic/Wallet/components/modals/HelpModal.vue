<template>
  <div
    v-if="open"
    class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center p-4 z-50"
    role="dialog"
    aria-modal="true"
    @click.self="emit('close')"
  >
    <div class="bg-white w-full max-w-lg rounded-2xl p-4 shadow-glass">
      <!-- Header -->
      <div class="flex items-center gap-2 mb-2">
        <i data-lucide="help-circle" class="w-5 h-5"></i>
        <div class="font-semibold">Help & Guides</div>
        <button class="ml-auto p-1 rounded-lg hover:bg-slate-100" @click="emit('close')">
          Close
        </button>
      </div>

      <!-- Content -->
      <div class="space-y-3 text-sm text-slate-700">
        <details class="border rounded-xl p-3" open>
          <summary class="font-semibold cursor-pointer">Coins & Payouts Policy</summary>
          <div class="pt-2">
            • Coins (WCO) are non-cash credits used for gifting.<br />
            • Gifts monetize at 50% of coin USD value to creator earnings; 50% to platform.<br />
            • Earnings are withdrawable instantly in demo mode.<br />
            • Payouts have a small fee.<br />
            • Coin purchases are final in demo mode.
          </div>
        </details>

        <details class="border rounded-xl p-3">
          <summary class="font-semibold cursor-pointer">Multi-Currency & Conversion</summary>
          <div class="pt-2">
            • Supported currencies: USD, PKR, EUR.<br />
            • Change your display currency in <b>Settings</b>.<br />
            • All history is stored in USD and displayed in your currency.<br />
            • Rates are approximate for demo.
          </div>
        </details>

        <details class="border rounded-xl p-3">
          <summary class="font-semibold cursor-pointer">Top-Ups & Transfers</summary>
          <div class="pt-2">
            • Card top-ups simulate 2.9%+0.30 USD fees.<br />
            • Transfers are instant in demo.
          </div>
        </details>
      </div>
    </div>
  </div>
</template>

<script setup>
const emit = defineEmits(["close"])
defineProps({
  open: { type: Boolean, default: false }
})
</script>
