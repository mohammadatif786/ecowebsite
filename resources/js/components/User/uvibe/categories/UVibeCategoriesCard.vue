<template>
  <div class="card p-4">
    <h3 class="font-black mb-3">Categories</h3>
    <div class="flex flex-wrap gap-2">
      <span v-for="c in list" :key="c.id" role="button" tabindex="0" @click="$emit('pick', c.id)" @keydown.enter="$emit('pick', c.id)" class="chip">{{ c.label }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { postCategories } from '../../../../services/uvibeCategories';

// pick: the category id the member tapped — the page applies it as the feed filter.
// options: the server's composer options (its `categories` list) — optional.
const props = defineProps({ options: { type: Object, default: null } });
defineEmits(['pick']);

const list = computed(() => postCategories(props.options));
</script>
