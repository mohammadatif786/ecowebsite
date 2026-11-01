<template>
  <button type="button" @click="$emit('open', post)" title="Post options" aria-label="Post options"
          class="text-slate-400 shrink-0 h-8 w-8 grid place-items-center rounded-full hover:bg-slate-100">
    <MoreHorizontal class="w-5 h-5" />
  </button>
</template>

<script setup>
import { MoreHorizontal } from 'lucide-vue-next';

defineProps({ post: { type: Object, required: true } });
defineEmits(['open']);
</script>
