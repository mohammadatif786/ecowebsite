<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border p-6">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Change phone number</h3>
        <button @click="$emit('close')" class="text-slate-500 hover:text-slate-700">✕</button>
      </div>

      <p class="mt-2 text-sm text-slate-600">Enter your new phone number. We will send a new OTP to this number.</p>

      <div class="mt-4">
        <label class="block text-sm font-medium text-slate-700">Phone</label>
        <input
          v-model="localPhone"
          type="tel"
          class="mt-1 w-full h-11 px-3 border rounded-lg focus:ring-2 focus:ring-brand-focus/30 focus:border-brand-focus outline-none"
          placeholder="e.g. +1234567890"
        />
        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
      </div>

      <div class="mt-6 flex gap-3">
        <button @click="$emit('close')" class="flex-1 h-11 rounded-lg border bg-slate-50 font-semibold">Cancel</button>
        <button :disabled="submitting" @click="submit" class="flex-1 h-11 rounded-lg bg-brand-focus font-semibold disabled:opacity-60">
          {{ submitting ? 'Saving...' : 'Save & Send OTP' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{ open: boolean; phone?: string }>()
const emit = defineEmits<{ (e: 'close'): void; (e: 'submit', phone: string): void }>()

const localPhone = ref(props.phone || '')
const submitting = ref(false)
const error = ref('')

watch(
  () => props.phone,
  (v) => {
    if (typeof v === 'string') localPhone.value = v
  }
)

function submit() {
  error.value = ''
  const p = (localPhone.value || '').trim()
  if (!p) {
    error.value = 'Phone is required'
    return
  }
  if (p.length < 8) {
    error.value = 'Enter a valid phone number'
    return
  }
  submitting.value = true
  emit('submit', p)
  // Parent will handle request and then close modal
  submitting.value = false
}
</script>
