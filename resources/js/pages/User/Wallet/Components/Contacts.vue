<template>
  <div class="bg-white rounded-2xl p-5 shadow-glass border">
    <!-- Header -->
    <div class="flex items-center gap-2 mb-2">
      <i data-lucide="users" class="w-4 h-4 text-slate-500"></i>
      <div class="font-semibold">Contacts</div>
      <button class="ml-auto px-3 py-1.5 text-xs rounded-xl border" @click="showSend = true">
        Add
      </button>
    </div>

    <!-- Contact list -->
    <div class="space-y-2 max-h-60 overflow-y-auto">
      <div v-for="contact in contacts" :key="contact.id" class="flex items-center gap-3 p-2 border rounded-xl">
        <!-- Avatar -->
        <div class="w-8 h-8 rounded-full bg-linkup-blue/10 flex items-center justify-center text-xs font-semibold">
          {{ contactInitial(contact.contact_user) }}
        </div>

        <!-- Info -->
        <div class="min-w-0">
          <div class="text-sm font-semibold truncate">{{ contact.contact_user.name || '—' }}</div>
          <div class="text-xs text-slate-500 truncate">
            {{ contact.contact_user.email }}
          </div>
        </div>

        <!-- Actions -->
        <div class="ml-auto flex gap-2">
          <button class="px-3 py-1.5 text-xs rounded-xl border" @click="giftCoins(contact.contact_user)">
            Gift
          </button>
        </div>
      </div>
    </div>
  </div>
  <SendMoneyModal :open="showSend" @close="showSend = false" :users="users" />
  <!-- Gift Coins Modal -->
  <GiftCionModal :open="showGiftModal" :user="selectedUser" @close="showGiftModal = false" @sent="onGiftSent" />
</template>

<script setup>
import { ref } from "vue"
import SendMoneyModal from "./modals/SendMoneyModal.vue"
import GiftCionModal from "./modals/GiftCionModal.vue"
const showSend = ref(false)
const showGiftModal = ref(false)
const selectedUser = ref(null)
defineProps({
  contacts: {
    type: Array,
    default: []
  },
  users: {
    type: Array,
    default: []
  }
})

// Helpers
const contactInitial = (contact) => {
  return (contact.name || contact.email)?.[0]?.toUpperCase() || "U"
}

const sendMoney = (contact) => {
  alert(`Send money to ${contact.name || contact.email}`)
}

// When Gift button clicked
const giftCoins = (user) => {
  selectedUser.value = user
  showGiftModal.value = true
}

// After gift sent
const onGiftSent = () => {
  showGiftModal.value = false
}
</script>
