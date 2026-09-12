<template>
  <aside class="hidden md:flex w-72 bg-white border-r border-slate-200 flex-col flex-shrink-0 z-20">
    <div class="p-8 pb-4">
      <!-- User Details -->
      <div class="mt-6 flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
          <img 
            v-if="userProfile.avatar" 
            :src="userProfile.avatar" 
            :alt="userProfile.name"
            class="w-full h-full object-cover"
          />
          <div 
            v-else 
            class="w-full h-full bg-linkup-dark text-white font-bold flex items-center justify-center"
          >
            {{ userProfile.name?.charAt(0).toUpperCase() || 'U' }}
          </div>
        </div>
        <div class="flex-1 overflow-hidden">
          <div class="text-sm font-bold truncate">{{ userProfile.name }}</div>
          <div class="text-xs text-slate-500 truncate">~{{ userProfile.linkup_id }}</div>
        </div>
      </div>
    </div>

    <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
      <div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold px-4 mb-2 mt-4">Main</div>
        <Link :href="route('frontend.user.wallet')" class="w-full flex items-center gap-3 px-4 py-3 bg-linkup-blue/5 text-linkup-blue rounded-xl font-bold border border-linkup-blue/10">
            <LayoutGrid class="w-5 h-5" /> Dashboard
        </Link>

      <div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold px-4 mb-2 mt-8">Support</div>
      <button
        @click="goHome"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition-colors font-medium"
      >
        <Home class="w-5 h-5" /> Back to Home
      </button>
      <button
        @click="$emit('open', 'help')"
        class="w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition-colors font-medium"
      >
        <HelpCircle class="w-5 h-5" /> Help &amp; Guide
      </button>
    </nav>
  </aside>
</template>

<script setup lang="ts">
import {
  LayoutGrid,
  HelpCircle,
  Home,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { Link } from '@inertiajs/vue3';


defineProps<{
  userProfile: { 
    name: string; 
    tag: string; 
    linkup_id: string;
    avatar?: string;
  };
}>();

defineEmits<{
  (e: 'open', modal: string): void;
}>();

const goHome = () => {
  window.location.href = '/findmatch';
};
</script>

