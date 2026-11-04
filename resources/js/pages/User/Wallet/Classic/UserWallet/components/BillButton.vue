<template>
  <button class="h-28 rounded-2xl border border-slate-200 hover:border-linkup-blue/60 hover:shadow-sm transition-all bg-white flex flex-col items-center justify-center gap-3">
    <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-500 flex items-center justify-center">
      <component :is="iconComponent" class="w-5 h-5" />
    </div>
    <span class="text-xs font-semibold text-slate-700">{{ label }}</span>
  </button>
</template>

<script setup lang="ts">
import {
  Zap,
  Droplet,
  Smartphone,
  Wifi,
  Signal,
  Waves,
} from 'lucide-vue-next';

const icons: Record<string, any> = {
  zap: Zap,
  droplet: Droplet,
  smartphone: Smartphone,
  wifi: Wifi,
  signal: Signal,
  waves: Waves,
};

const props = defineProps<{
  label: string;
  icon: string;
}>();

const iconComponent = icons[props.icon] ?? Smartphone;
</script>

