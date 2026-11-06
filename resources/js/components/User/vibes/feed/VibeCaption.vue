<template>
  <!-- "@handle caption" with the #tags bold and tappable (tapping one filters the feed). -->
  <p class="text-[15px] text-slate-800 leading-relaxed break-words whitespace-pre-line">
    <button type="button" class="font-black text-slate-900 hover:underline" @click="actions.openProfile(post.created_by)">{{ post.handle }}</button>
    {{ ' ' }}
    <template v-for="(part, i) in parts" :key="i">
      <button v-if="part.tag" type="button" class="font-bold text-lkblue hover:underline" @click="actions.filterTag(part.tag)">{{ part.text }}</button>
      <template v-else>{{ part.text }}</template>
    </template>
  </p>
</template>

<script setup>
import { computed } from 'vue';
import { captionParts } from '../format';
import { useVibeActions } from '../useVibeActions';

const props = defineProps({ post: { type: Object, required: true } });
const actions = useVibeActions();
const parts = computed(() => captionParts(props.post.caption));
</script>
