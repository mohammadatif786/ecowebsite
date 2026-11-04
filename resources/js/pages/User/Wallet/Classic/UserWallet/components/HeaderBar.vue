<template>
  <header class="h-20 flex items-center justify-between px-4 sm:px-8 flex-shrink-0 z-10 bg-white/50 backdrop-blur border-b border-slate-200">
    <div class="flex items-center gap-4">
      <button class="md:hidden p-2 hover:bg-slate-100 rounded-lg" @click="$emit('toggle-menu')">
        <Menu class="w-6 h-6" />
      </button>
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Overview</h1>
        <p class="text-xs text-slate-400 hidden sm:block">Welcome to Link Up Wallet</p>
      </div>
    </div>

    <!-- <div class="flex items-center gap-3">
      <div class="relative group">
        <button class="flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold hover:bg-slate-50 transition-colors">
          <span>{{ flags[currency] }} {{ currency }}</span>
          <ChevronDown class="w-4 h-4 text-slate-400" />
        </button>
        <div class="absolute right-0 top-full mt-2 w-32 bg-white border border-slate-100 rounded-xl shadow-xl p-1 hidden group-hover:block z-50">
          <button
            v-for="(flag, code) in flags"
            :key="code"
            class="w-full text-left px-3 py-2 text-xs font-bold hover:bg-slate-50 rounded-lg"
            @click="$emit('set-currency', code)"
          >
            {{ flag }} {{ code }}
          </button>
        </div>
      </div>
    </div> -->
  </header>
</template>

<script setup lang="ts">
import { Menu, ChevronDown } from 'lucide-vue-next';

defineProps<{
  currency: string;
  flags: Record<string, string>;
}>();

defineEmits<{
  (e: 'set-currency', code: string): void;
  (e: 'toggle-menu'): void;
}>();
</script>

