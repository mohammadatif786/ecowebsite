<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="close"></div>
    
    <!-- Modal -->
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
      <!-- Icon -->
      <div class="text-center pt-6">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
          <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </div>
      </div>

      <!-- Content -->
      <div class="text-center px-6 py-4">
        <h3 class="text-xl font-bold text-gray-900 mb-2">
          {{ title }}
        </h3>
        <p class="text-gray-600">
          {{ message }}
        </p>
        <p v-if="itemName" class="text-sm font-medium text-gray-900 mt-2 break-all">
          "{{ itemName }}"
        </p>
      </div>

      <!-- Actions -->
      <div class="flex gap-3 px-6 pb-6">
        <button @click="close"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium">
          {{ cancelText }}
        </button>
        <button @click="confirmDelete"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Delete Item'
  },
  message: {
    type: String,
    default: 'Are you sure you want to delete this item? This action cannot be undone.'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  confirmText: {
    type: String,
    default: 'Delete'
  }
})

const emit = defineEmits(['confirm', 'close'])

const isOpen = ref(false)
const itemName = ref('')
const deleteData = ref(null)

const open = (name = '', data = null) => {
  itemName.value = name
  deleteData.value = data
  isOpen.value = true
}

const close = () => {
  isOpen.value = false
  itemName.value = ''
  deleteData.value = null
  emit('close')
}

const confirmDelete = () => {
  emit('confirm', deleteData.value)
  close()
}

defineExpose({ open, close })
</script>