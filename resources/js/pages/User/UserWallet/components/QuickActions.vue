<template>
  <div class="grid grid-cols-2 md:grid-cols-5 gap-4 fade-in" style="animation-delay: 0.1s">
    <QuickAction label="Send Money" icon="send" @click="$emit('open', 'send')" />
    <QuickAction label="Requested Money" icon="download" @click="$emit('open', 'requested-money')" />
    <QuickAction label="Request" icon="hand-coins" @click="$emit('open', 'request')" />
    <QuickAction label="Pay Bills" icon="receipt" @click="$emit('open', 'bill')" />
    <QuickAction label="Merchant" icon="store" @click="$emit('open', 'merchant')" />
  </div>
</template>

<script setup lang="ts">
import QuickAction from './QuickAction.vue';

defineEmits<{
  (e: 'open', modal: string): void;
}>();
</script>

