<template>
  <component :is="iconComponent" :class="className" v-bind="$attrs" />
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  name: string;
  class?: string;
}>();

const className = computed(() => props.class || 'w-5 h-5');

// Simple icon mapping - you can extend this based on your icon library
const iconComponent = computed(() => {
  const icons: Record<string, string> = {
    'plus': '➕',
    'search': '🔍', 
    'edit': '✏️',
    'trash': '🗑️',
    'eye': '👁️',
    'eye-off': '🙈',
    'calendar': '📅',
    'clock': '🕐',
    'ticket': '🎫',
    'upload': '📤',
    'x': '✖️',
    'loader': '⏳',
    'arrow-left': '←',
    'building': '🏢',
    'image': '🖼️',
    'chart': '📊',
    'dollar-sign': '💰',
    'file-text': '📄',
    'users': '👥',
    'star': '⭐',
    'printer': '🖨️',
    'credit-card': '💳',
    'mail': '✉️',
    'refresh': '🔄',
    'check': '✓',
    'xx': '❌'
  };
  
  return () => icons[props.name] || '❓';
});
</script>