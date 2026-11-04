<template>
  <div
    class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center p-4 z-50"
    v-if="open"
    role="dialog"
    aria-modal="true"
    @click.self="$emit('close')"
  >
    <div class="bg-white w-full max-w-lg rounded-2xl p-4 shadow-glass">
      <!-- Header -->
      <div class="flex items-center gap-2 mb-2">
        <i data-lucide="qr-code" class="w-5 h-5"></i>
        <div class="font-semibold">Pay by QR / Code</div>
        <button
          class="ml-auto p-1 rounded-lg hover:bg-slate-100"
          @click="$emit('close')"
        >
          Close
        </button>
      </div>

      <!-- Content -->
      <div class="grid sm:grid-cols-2 gap-4">
        <!-- My PayCode -->
        <div class="border rounded-xl p-3">
          <div class="text-sm font-semibold mb-2">My PayCode</div>
          <div id="qrcode" class="mx-auto w-[160px] h-[160px]"></div>
          <div class="text-xs text-slate-500 mt-2 break-all">
            {{ payCode }}
          </div>
        </div>

        <!-- Pay someone -->
        <div class="border rounded-xl p-3 space-y-2">
          <div class="text-sm font-semibold">Pay someone</div>
          <input
            class="w-full px-3 py-2 border rounded-xl"
            placeholder="Paste PayCode"
          />
          <input
            type="number"
            step="0.01"
            min="0.01"
            class="w-full px-3 py-2 border rounded-xl"
            :placeholder="`Amount (${currency})`"
          />
          <input
            class="w-full px-3 py-2 border rounded-xl"
            placeholder="Note (optional)"
          />
          <button
            class="w-full px-4 py-2 rounded-xl bg-linkup-blue text-white"
            @click="payQR"
          >
            Pay
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, onMounted } from 'vue'

const emit = defineEmits(["close"])
const props = defineProps({
  open: { type: Boolean, default: false },
  currency: { type: String, default: "USD" }
})

// inject global helpers
const current = inject('current')
const showToast = inject('showToast')

// generate PayCode from walletId
const payCode = computed(() => {
  if (!current || !current().walletId) return "LUP-XXXXXXXXXXXX"
  return "LUP-" + btoa(current().walletId).replace(/=/g, "").slice(0, 16)
})


const payQR = () => {
  showToast("Paid via QR (demo)")
  emit("close")
}
</script>
