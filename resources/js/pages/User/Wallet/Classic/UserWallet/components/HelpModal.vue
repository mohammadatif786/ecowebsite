<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
    <div class="p-8 h-full flex flex-col">
      <div class="flex justify-between mb-6 flex-shrink-0">
        <h3 class="text-2xl font-bold text-slate-900">How to use Link Up</h3>
        <button @click="$emit('close')"><X class="w-5 h-5" /></button>
      </div>

      <div class="overflow-y-auto pr-2 space-y-6 flex-grow">
        <div class="space-y-2">
           <h4 class="text-sm font-bold text-linkup-blue uppercase tracking-wider">💸 Sending & Receiving</h4>
           <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
              <p class="text-sm text-slate-600 mb-2"><strong>To Send Money:</strong> Click 'Send', enter a valid ~LinkTag, Amount, and Reason. Verify the user, then confirm on the Blue Shield screen.</p>
              <p class="text-sm text-slate-600"><strong>To Receive:</strong> Use the 'POS' button on your dashboard. You can show your QR code or type a specific amount for the payer to scan.</p>
           </div>
        </div>

        <div class="space-y-2">
           <h4 class="text-sm font-bold text-linkup-teal uppercase tracking-wider">🤝 Digital Asue</h4>
           <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
              <p class="text-sm text-slate-600 mb-2"><strong>1. Create:</strong> Set the hand amount and frequency. Invite trusted contacts.</p>
              <p class="text-sm text-slate-600 mb-2"><strong>2. Escrow:</strong> Your hand is deducted automatically. Funds are held safely until payout day.</p>
              <p class="text-sm text-slate-600"><strong>3. Payout:</strong> The full pot is sent to the member whose turn it is (Draw 1, Draw 2, etc.).</p>
           </div>
        </div>

        <div class="space-y-2">
           <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider">🧾 Paying Bills</h4>
           <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
              <p class="text-sm text-slate-600">Select your provider (e.g., BPL, BTC), enter your account number, and pay instantly from your wallet balance.</p>
           </div>
        </div>

        <div class="space-y-2">
           <h4 class="text-sm font-bold text-orange-500 uppercase tracking-wider">₿ Crypto Assets</h4>
           <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
              <p class="text-sm text-slate-600">Buy Bitcoin, Ethereum, or Solana. Enter the USD amount you want to invest, and see the live conversion before confirming.</p>
           </div>
        </div>
      </div>

      <div class="mt-6 text-center border-t border-slate-100 pt-4 flex-shrink-0">
        <p class="text-xs text-slate-400 mb-2">Still need help?</p>
        <button
          class="px-6 py-3 bg-slate-900 text-white rounded-xl text-sm font-bold hover:brightness-105 transition-colors"
          @click="$emit('close')"
        >
          Chat Support
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { X, Send, Users, Receipt, Bitcoin } from 'lucide-vue-next';

defineEmits<{
  (e: 'close'): void;
}>();
</script>

