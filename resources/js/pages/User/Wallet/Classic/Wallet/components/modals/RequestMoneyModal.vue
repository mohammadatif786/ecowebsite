<template>
  <div
    v-if="open || form.hasErrors" 
    class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center p-4 z-50"
    role="dialog"
    aria-modal="true"
    @click.self="emit('close')"
  >
    <div class="bg-white w-full max-w-lg rounded-2xl p-4 shadow-glass">
      <!-- Header -->
      <div class="flex items-center gap-2 mb-2">
        <i data-lucide="badge-dollar-sign" class="w-5 h-5"></i>
        <div class="font-semibold">Request Money</div>
        <button
          class="ml-auto p-1 rounded-lg hover:bg-slate-100"
          @click="closeModal"
        >
          Close
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="confirmRequest" class="space-y-3">
        <div>
          <select v-model="form.recipient" class="w-full px-3 py-2 border rounded-xl">
            <option value="">Select a user</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
          <div v-if="form.errors.recipient" class="text-red-500 text-xs mt-1">
            {{ form.errors.recipient }}
          </div>
        </div>

        <div>
          <input
            v-model="form.amount"
            type="number"
            step="0.01"
            min="0.01"
            class="w-full px-3 py-2 border rounded-xl"
            :placeholder="`Amount (${currency})`"
          />
          <div v-if="form.errors.amount" class="text-red-500 text-xs mt-1">
            {{ form.errors.amount }}
          </div>
        </div>

        <div>
          <input
            v-model="form.note"
            class="w-full px-3 py-2 border rounded-xl"
            placeholder="Note (optional)"
          />
          <div v-if="form.errors.note" class="text-red-500 text-xs mt-1">
            {{ form.errors.note }}
          </div>
        </div>

        <button
          type="submit"
          class="w-full px-4 py-2 rounded-xl bg-slate-900 text-white"
          :disabled="form.processing"
        >
          {{ form.processing ? "Sending..." : "Send request" }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3"

const emit = defineEmits(["close"])
const props = defineProps({
  open: { type: Boolean, default: false },
  currency: { type: String, default: "USD" },
  users: { type: Array, default: () => [] }
})

const form = useForm({
  recipient: "",
  amount: "",
  note: ""
})

const confirmRequest = () => {
  form.post(route("frontend.user.money.request"), {
    onSuccess: () => {
      form.reset()
      emit("close")
    }
  })
}

const closeModal = () => {
  form.clearErrors() // clear errors when manually closing
  emit("close")
}
</script>
