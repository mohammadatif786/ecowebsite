<template>
  <!-- The buttons down the right of a reel: like, comments, Big Up, share, and more. -->
  <div class="flex flex-col items-center gap-4">
    <button type="button" :aria-pressed="reel.is_liked" aria-label="Like" class="flex flex-col items-center gap-0.5 text-white" @click.stop="$emit('like')">
      <span class="h-11 w-11 rounded-full bg-white/15 backdrop-blur grid place-items-center"><Heart :class="['w-6 h-6', reel.is_liked ? 'fill-rose-500 text-rose-500' : '']" /></span>
      <span class="text-[10px] font-black">{{ compact(reel.likes_count) }}</span>
    </button>
    <button type="button" aria-label="Comments" class="flex flex-col items-center gap-0.5 text-white" @click.stop="$emit('comments')">
      <span class="h-11 w-11 rounded-full bg-white/15 backdrop-blur grid place-items-center"><MessageCircle class="w-6 h-6" /></span>
      <span class="text-[10px] font-black">{{ compact(reel.comments_count) }}</span>
    </button>
    <button v-if="reel.allow_coin_gifts !== false && !reel.is_mine" type="button" aria-label="Big Up" class="flex flex-col items-center gap-0.5 text-white" @click.stop="$emit('bigup')">
      <span class="h-11 w-11 rounded-full grid place-items-center" style="background:linear-gradient(135deg,#f59e0b,#ec4899)"><Zap class="w-6 h-6 fill-white" /></span>
      <span class="text-[10px] font-black">{{ compact(reel.bigups_count) }}</span>
    </button>
    <button type="button" aria-label="Share" class="flex flex-col items-center gap-0.5 text-white" @click.stop="$emit('share')">
      <span class="h-11 w-11 rounded-full bg-white/15 backdrop-blur grid place-items-center"><Send class="w-6 h-6" /></span>
      <span class="text-[10px] font-black">{{ compact(reel.shares_count) }}</span>
    </button>
    <button type="button" :aria-pressed="reel.is_saved" aria-label="Save" class="flex flex-col items-center gap-0.5 text-white" @click.stop="$emit('save')">
      <span class="h-11 w-11 rounded-full bg-white/15 backdrop-blur grid place-items-center"><Bookmark :class="['w-6 h-6', reel.is_saved ? 'fill-white' : '']" /></span>
    </button>
    <button v-if="reel.is_mine" type="button" aria-label="Delete reel" class="flex flex-col items-center gap-0.5 text-white" @click.stop="$emit('delete')">
      <span class="h-11 w-11 rounded-full bg-white/15 backdrop-blur grid place-items-center"><Trash2 class="w-5 h-5" /></span>
    </button>
  </div>
</template>

<script setup>
import { Bookmark, Heart, MessageCircle, Send, Trash2, Zap } from 'lucide-vue-next';
import { compact } from '../format';

defineProps({ reel: { type: Object, required: true } });
defineEmits(['like', 'comments', 'bigup', 'share', 'save', 'delete']);
</script>
