<template>
  <div class="card p-4">
    <h3 class="font-black mb-3">Quick Links</h3>
    <button v-for="(l, i) in links" :key="l.key" type="button" @click="l.go"
            :class="['w-full flex items-center gap-3 rounded-2xl border border-slate-100 p-2.5 text-left hover:border-violet-200', i ? 'mt-2' : '']">
      <span class="h-9 w-9 rounded-xl grid place-items-center shrink-0" style="background:#7c3aed14;color:#7c3aed">
        <component :is="l.icon" class="w-4 h-4" />
      </span>
      <span class="font-bold text-sm">{{ l.label }}</span>
    </button>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { Briefcase, Store } from 'lucide-vue-next';

// browseGigs: show the Campus Gigs on this page (the page applies the "Gigs" filter).
const emit = defineEmits(['browseGigs']);

const links = [
  { key: 'marketplace', label: 'Sell on the Marketplace', icon: Store, go: () => router.visit(route('new_frontend.marketplace')) },
  { key: 'gigs', label: 'Browse Campus Gigs', icon: Briefcase, go: () => emit('browseGigs') },
];
</script>
