<template>
  <div v-if="open" class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center p-4 z-50"
    role="dialog" aria-modal="true" @click.self="$emit('close')">
    <div class="bg-white w-full max-w-lg rounded-2xl p-4 shadow-glass">
      <!-- Header -->
      <div class="flex items-center gap-2 mb-2">
        <i data-lucide="user-plus" class="w-5 h-5"></i>
        <div class="font-semibold">Add Contact</div>
        <button class="ml-auto p-1 rounded-lg hover:bg-slate-100" @click="$emit('close')">
          Close
        </button>
      </div>

      <!-- Form -->
      <div class="space-y-3">
        <select v-model="selectedUser" class="w-full px-3 py-2 border rounded-xl">
          <option value="">Select a user</option>
          <option v-for="user in users" :key="user.id" :value="user">
            {{ user.name }}
          </option>
        </select>

        <button class="w-full px-4 py-2 rounded-xl bg-blue-500 text-white" @click="confirmAdd">
          Add Contact
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue"
import { useForm } from "@inertiajs/vue3"

const emit = defineEmits(["close"])
const props = defineProps({
  open: { type: Boolean, default: false },
  users: { type: Array, default: () => [] }
})

const selectedUser = ref("")

const form = useForm({
  contact_user_id: ""
})

const confirmAdd = () => {
  if (!selectedUser.value) {
    return alert("Please select a user")
  }

  form.contact_user_id = selectedUser.value.id

  form.post(route('frontend.user.wallet.contact'), {
    preserveScroll: true,
    onSuccess: () => {
      emit("close")
    },
    onError: (errors) => {
      alert(errors.contact_user_id || "Failed to add contact")
    }
  })
}

</script>
