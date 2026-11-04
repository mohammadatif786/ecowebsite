<template>
  <!-- What's happening: the words of the post (or of the text card), with an emoji picker. -->
  <div class="space-y-2">
    <div class="relative">
      <textarea ref="box" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" rows="3"
                :maxlength="maxlength" placeholder="What's happening on campus?"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 pr-11 text-sm outline-none"></textarea>
      <button type="button" @click="showEmoji = !showEmoji" title="Add emoji" aria-label="Add emoji"
              class="absolute right-2 top-2 h-7 w-7 rounded-full bg-slate-100 hover:bg-slate-200 grid place-items-center text-base">😊</button>
    </div>
    <PostEmojiPicker v-if="showEmoji" @pick="insert" />
  </div>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import PostEmojiPicker from './PostEmojiPicker.vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  maxlength: { type: Number, default: 2200 },
});
const emit = defineEmits(['update:modelValue']);

const box = ref(null);
const showEmoji = ref(false);

// Put the emoji where the cursor is.
const insert = (emoji) => {
  const el = box.value;
  const text = props.modelValue || '';
  const start = el?.selectionStart ?? text.length;
  const end = el?.selectionEnd ?? text.length;
  emit('update:modelValue', (text.slice(0, start) + emoji + text.slice(end)).slice(0, props.maxlength));
  nextTick(() => {
    el?.focus();
    el?.setSelectionRange(start + emoji.length, start + emoji.length);
  });
};

const focus = () => box.value?.focus();
defineExpose({ focus });
</script>
