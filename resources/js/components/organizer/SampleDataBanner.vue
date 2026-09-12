<template>
  <div v-if="show" :class="['rounded-2xl border p-3 mb-3 text-[12px] font-bold', variantClasses]">
    <slot>{{ message }}</slot>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  show?: boolean
  message?: string
  variant?: 'warning' | 'info' | 'success' | 'error'
}

const props = withDefaults(defineProps<Props>(), {
  show: true,
  message: 'Showing sample data — publish your own event to see your own numbers here.',
  variant: 'warning'
})

const variantClasses = computed(() => {
  const variants = {
    warning: 'bg-amber-50 border-amber-200 text-amber-700',
    info: 'bg-blue-50 border-blue-200 text-blue-700',
    success: 'bg-green-50 border-green-200 text-green-700',
    error: 'bg-red-50 border-red-200 text-red-700'
  }
  return variants[props.variant]
})
</script>
