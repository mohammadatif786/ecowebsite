<template>
  <div class="rounded-2xl border border-slate-200 p-2 grid grid-cols-8 gap-1 max-h-40 overflow-y-auto">
    <button v-for="e in EMOJIS" :key="e" type="button" @click="$emit('pick', e)"
            class="text-xl rounded-lg hover:bg-slate-100 py-1">{{ e }}</button>
  </div>
</template>

<script setup>
defineEmits(['pick']);

// The same set the Vibes composer offers.
const EMOJIS = ['😀', '😂', '🥰', '😍', '😎', '🤩', '😭', '😅', '🙏', '👏', '🙌', '💪', '🔥', '✨', '💯', '🎉', '🎊', '🥳', '❤️', '💕', '💖', '💛', '💜', '🧡', '💙', '💚', '🖤', '😘', '😜', '🤔', '👀', '💃', '🕺', '🎶', '🎵', '🌴', '🌞', '🏝️', '🌊', '🍹', '🍗', '🥘', '🥭', '🇯🇲', '🇹🇹', '🇧🇸', '🇧🇧', '🇧🇷', '🇩🇴', '🥁', '⚡'];
</script>
