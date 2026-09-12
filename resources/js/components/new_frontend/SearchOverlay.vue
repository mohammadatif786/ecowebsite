<template>
  <div v-if="isOpen" class="fixed inset-0 z-[120] flex flex-col" style="background:#f8fafc">
    <!-- Header bar -->
    <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-200 bg-white/80 backdrop-blur-md">
      <button @click="close" class="w-10 h-10 rounded-xl hover:bg-slate-100 grid place-items-center transition shrink-0">
        <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
      </button>

      <!-- Search input -->
      <div class="flex-1 relative">
        <i data-lucide="sparkles" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-lkblue pointer-events-none"></i>
        <input
          ref="inputRef"
          v-model="query"
          @keyup.enter="doSearch"
          @input="onInput"
          placeholder="e.g. free fêtes this weekend near me"
          class="w-full bg-slate-100 focus:bg-white focus:border-lkblue/40 border border-transparent rounded-full pl-12 pr-4 py-3 font-semibold text-sm outline-none transition"
        />
        <button v-if="query" @click="query = ''; results = []" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
    </div>

    <!-- Body -->
    <div class="flex-1 overflow-y-auto p-4 max-w-2xl mx-auto w-full">
      <!-- Intro / empty state -->
      <template v-if="!query && !results.length">
        <div class="card p-6 text-center mb-4">
          <div class="w-14 h-14 brandgrad rounded-2xl grid place-items-center mx-auto mb-3">
            <i data-lucide="sparkles" class="w-7 h-7 text-white"></i>
          </div>
          <h2 class="text-xl font-black text-lkink">Search / Ask AI</h2>
          <p class="text-slate-500 text-sm mt-1">Find tickets, food, products, people — just ask.</p>
        </div>

        <!-- Quick suggestions -->
        <p class="text-xs font-black text-slate-400 uppercase mb-2 px-1">Quick searches</p>
        <div class="flex flex-wrap gap-2 mb-6">
          <span v-for="s in suggestions" :key="s" @click="selectSuggestion(s)" class="chip cursor-pointer hover:bg-slate-200 transition">
            {{ s }}
          </span>
        </div>

        <!-- Category shortcut grid -->
        <p class="text-xs font-black text-slate-400 uppercase mb-2 px-1">Browse</p>
        <div class="grid grid-cols-3 gap-3">
          <Link v-for="q in quickLinks" :key="q.dest" :href="linkHref(q.dest)" @click="close" class="card p-3 flex flex-col items-center gap-2 hover:-translate-y-0.5 transition">
            <span class="w-10 h-10 rounded-xl grid place-items-center text-white" :style="{ background: q.color }">
              <i :data-lucide="q.icon" class="w-5 h-5"></i>
            </span>
            <span class="text-xs font-black text-slate-600 text-center leading-tight">{{ q.label }}</span>
          </Link>
        </div>
      </template>

      <!-- Typing state (AI thinking) -->
      <template v-else-if="isTyping">
        <div class="card p-6 text-center">
          <div class="flex items-center justify-center gap-2 text-lkblue font-black">
            <span class="w-2 h-2 rounded-full bg-lkblue animate-bounce" style="animation-delay:0ms"></span>
            <span class="w-2 h-2 rounded-full bg-lkblue animate-bounce" style="animation-delay:150ms"></span>
            <span class="w-2 h-2 rounded-full bg-lkblue animate-bounce" style="animation-delay:300ms"></span>
          </div>
          <p class="text-slate-400 text-sm mt-2 font-semibold">Searching "{{ query }}"…</p>
        </div>
      </template>

      <!-- Results -->
      <template v-else-if="results.length">
        <p class="text-xs font-black text-slate-400 uppercase mb-3 px-1">{{ results.length }} results for "{{ lastQuery }}"</p>

        <div class="space-y-3">
          <div v-for="r in results" :key="r.id" class="card p-3 flex items-center gap-3">
            <img :src="r.image" class="h-14 w-14 rounded-xl object-cover shrink-0" />
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-black px-1.5 py-0.5 rounded-full text-white" :style="{ background: r.color }">{{ r.kind }}</span>
              </div>
              <p class="font-black text-sm truncate mt-0.5">{{ r.title }}</p>
              <p class="text-[11px] text-slate-500 truncate">{{ r.sub }}</p>
            </div>
            <Link v-if="r.dest" :href="linkHref(r.dest)" @click="close" class="btn btn-primary px-3 py-2 text-xs shrink-0">{{ r.cta }}</Link>
          </div>
        </div>
      </template>

      <!-- No results -->
      <template v-else-if="lastQuery">
        <div class="card p-10 text-center">
          <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-300"></i>
          <p class="font-black mt-3">Nothing found for "{{ lastQuery }}"</p>
          <p class="text-slate-400 text-sm mt-1">Try a different search or browse a section below.</p>
          <div class="flex flex-wrap gap-2 justify-center mt-4">
            <span v-for="s in suggestions" :key="s" @click="selectSuggestion(s)" class="chip cursor-pointer">{{ s }}</span>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { getEvents, getProducts, getRestaurants, SEED, PIC } from './MockDataStore';

const isOpen = ref(false);
const query = ref('');
const lastQuery = ref('');
const results = ref([]);
const isTyping = ref(false);
const inputRef = ref(null);

let searchTimeout = null;

const suggestions = ['Cheap eats tonight', 'Carnival costumes', 'Live soca now', 'Events under $30', 'Caribbean food delivery', 'Dating near me'];

const quickLinks = [
  { dest: 'events',      label: 'Events',     icon: 'party-popper', color: '#f97316' },
  { dest: 'eats',        label: 'Eats',        icon: 'utensils',     color: '#22c55e' },
  { dest: 'marketplace', label: 'Market',      icon: 'shopping-bag', color: '#8b5cf6' },
  { dest: 'live',        label: 'Live',        icon: 'radio',        color: '#e11d48' },
  { dest: 'vibes',       label: 'Vibes',       icon: 'sparkles',     color: '#a855f7' },
  { dest: 'dating',      label: 'LinkUp',      icon: 'heart',        color: '#ef4444' },
];

const money = (n) => '$' + Number(n || 0).toFixed(2);

const linkHref = (dest) => {
  try { return route('new_frontend.' + dest); } catch { return '/' + dest; }
};

// Build a mock search index from all data sources
const buildIndex = () => {
  const items = [];

  getEvents().forEach(e => {
    items.push({
      id: 'ev-' + e.id, kind: 'Event', color: '#f97316',
      title: e.title,
      sub: (e.date ? e.date + ' · ' : '') + (e.location || ''),
      image: e.image || PIC('ev' + e.id, 80, 80),
      dest: 'events', cta: 'View',
      search: (e.title + ' ' + (e.location || '') + ' ' + (e.category || '') + ' event fete party').toLowerCase()
    });
  });

  getProducts().forEach(p => {
    items.push({
      id: 'pr-' + p.id, kind: 'Product', color: '#8b5cf6',
      title: p.title,
      sub: p.seller + ' · ' + money(p.price),
      image: p.image || PIC('pr' + p.id, 80, 80),
      dest: 'marketplace', cta: 'Shop',
      search: (p.title + ' ' + p.seller + ' ' + (p.category || '') + ' product costume market').toLowerCase()
    });
  });

  getRestaurants().forEach(r => {
    items.push({
      id: 'rs-' + r.id, kind: 'Eats', color: '#22c55e',
      title: r.name,
      sub: r.cuisine + ' · ' + r.eta,
      image: r.image || PIC('rs' + r.id, 80, 80),
      dest: 'eats', cta: 'Order',
      search: (r.name + ' ' + r.cuisine + ' ' + (r.tags || []).join(' ') + ' food eat restaurant delivery').toLowerCase()
    });
  });

  (SEED.live_sessions || []).forEach(l => {
    if (l.status === 'live') {
      items.push({
        id: 'lv-' + l.id, kind: 'Live', color: '#e11d48',
        title: l.title,
        sub: l.host + ' · ' + (l.viewers || 0).toLocaleString() + ' viewers',
        image: l.thumb || PIC('live' + l.id, 80, 80),
        dest: 'live', cta: 'Watch',
        search: (l.title + ' ' + l.host + ' ' + (l.category || '') + ' live stream soca').toLowerCase()
      });
    }
  });

  return items;
};

const doSearch = () => {
  const q = query.value.trim();
  if (!q) return;
  clearTimeout(searchTimeout);
  isTyping.value = false;

  const index = buildIndex();
  const qLow = q.toLowerCase();
  const words = qLow.split(/\s+/);
  const found = index.filter(item => words.some(w => item.search.includes(w)));
  results.value = found;
  lastQuery.value = q;

  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};

let typeTimeout = null;
const onInput = () => {
  results.value = [];
  lastQuery.value = '';
  clearTimeout(typeTimeout);
  if (!query.value.trim()) { isTyping.value = false; return; }
  isTyping.value = true;
  typeTimeout = setTimeout(() => {
    isTyping.value = false;
    doSearch();
  }, 600);
};

const selectSuggestion = (s) => {
  query.value = s;
  doSearch();
};

const open = () => {
  isOpen.value = true;
  query.value = '';
  results.value = [];
  lastQuery.value = '';
  isTyping.value = false;
  nextTick(() => {
    if (inputRef.value) inputRef.value.focus();
    if (window.lucide) window.lucide.createIcons();
  });
};

const close = () => {
  isOpen.value = false;
};

defineExpose({ open, close });
</script>
