<template>
  <div class="bg-white rounded-2xl p-5 shadow-glass border">
    <!-- Header -->
    <div class="flex items-center gap-2 mb-2">
      <i data-lucide="credit-card" class="w-4 h-4 text-slate-500"></i>
      <div class="font-semibold">Virtual Card</div>
      <span
        class="ml-auto text-xs px-2 py-1 rounded-full"
        :class="isFrozen ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
      >
        {{ isFrozen ? "Frozen" : "Active" }}
      </span>
    </div>

    <!-- Card -->
    <div
      class="rounded-xl p-4 bg-gradient-to-br from-linkup-blue to-linkup-lime text-linkup-dark relative overflow-hidden"
    >
      <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>

      <div class="text-sm">Link Up</div>
      <div class="mt-2 text-xl font-semibold tracking-wider">{{ displayCardNumber }}</div>
      <div class="mt-1 text-xs opacity-80">
        EXP {{ card.exp }} • CVV {{ displayCvv }}
      </div>
      <div class="mt-6 text-xs opacity-80">CARDHOLDER</div>
      <div class="text-sm">{{ cardholder }}</div>
    </div>

    <!-- Actions -->
    <div class="mt-3 flex flex-wrap gap-2">
      <button class="px-3 py-2 border rounded-xl text-sm" @click="toggleMaskCard">
        {{ cardMasked ? "Show" : "Hide" }}
      </button>
      <button
        class="px-3 py-2 border rounded-xl text-sm"
        :class="isFrozen ? 'border-red-300 text-red-700' : 'border-green-300 text-green-700'"
        @click="freezeCard"
      >
        {{ isFrozen ? "Unfreeze" : "Freeze" }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue"

// Dummy data
const card = reactive({
  number: "1234567812345678",
  exp: "12/27",
  cvv: "123",
  frozen: false
})

const cardholder = "John Doe"

// State
const cardMasked = ref(true)

const isFrozen = computed(() => card.frozen)

const displayCardNumber = computed(() => {
  if (cardMasked.value) {
    return "•••• •••• •••• " + card.number.slice(-4)
  }
  return card.number.replace(/(.{4})/g, "$1 ").trim()
})

const displayCvv = computed(() => (cardMasked.value ? "•••" : card.cvv))

// Actions
const toggleMaskCard = () => {
  cardMasked.value = !cardMasked.value
}

const freezeCard = () => {
  card.frozen = !card.frozen
  alert(card.frozen ? "Card frozen" : "Card active") // simple dummy feedback
}
</script>
