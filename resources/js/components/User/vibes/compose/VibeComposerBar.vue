<template>
  <!-- "Share a vibe…" — opens the composer. -->
  <div class="card p-4 flex items-center gap-3">
    <img :src="avatar" alt="" class="w-11 h-11 rounded-full object-cover bg-slate-200 shrink-0" />
    <button type="button" class="flex-1 text-left bg-slate-100 hover:bg-slate-200 rounded-full px-5 py-3 text-slate-500 font-semibold transition" @click="$emit('open')">{{ placeholder }}</button>
    <button type="button" aria-label="Add photos" class="w-11 h-11 rounded-full bg-gradient-to-tr from-lkblue to-purple-600 text-white grid place-items-center shrink-0" @click="$emit('open')">
      <ImagePlus class="w-5 h-5" />
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { ImagePlus } from 'lucide-vue-next';

defineProps({ placeholder: { type: String, default: 'Share a vibe…' } });
defineEmits(['open']);
const page = usePage();
const avatar = computed(() => page.props.auth?.user?.avatar || '');
</script>
