<template>
  <button type="button" @click="copyPostLink(post)" title="Copy link" aria-label="Copy link" class="text-slate-500">
    <Share2 class="w-4 h-4" />
  </button>
</template>

<script setup>
import { Share2 } from 'lucide-vue-next';
import { copyPostLink } from '../../../../services/uvibePosts';

defineProps({ post: { type: Object, required: true } });
</script>
