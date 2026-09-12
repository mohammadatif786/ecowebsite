<template>
  <div class="rounded-xl border border-violet-200 bg-violet-50/70 p-4 text-sm">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="font-black text-violet-700">{{ packageData?.name || ticket?.name }}</p>
        <p class="mt-1 text-xs font-bold text-slate-500">
          Table for {{ ticket?.table_capacity || 0 }}
        </p>
      </div>
      <p v-if="showPrice" class="font-black text-slate-800">{{ formatPrice(packagePrice) }}</p>
    </div>

    <div class="mt-3 space-y-3 border-t border-violet-200 pt-3">
      <div>
        <div class="flex justify-between gap-3 text-xs">
          <span class="font-black text-violet-700">Table</span>
          <span v-if="showPrice" class="font-bold text-slate-700">{{ formatPrice(packagePrice) }}</span>
        </div>
      </div>

      <div v-for="group in groups" :key="group.key">
        <p class="text-xs font-black text-violet-700">{{ group.label }}:</p>
        <p class="mt-1 text-xs leading-relaxed text-slate-700">
          <span v-for="(item, index) in group.items" :key="`${group.key}-${item?.id || item?.name || index}`">
            {{ item?.qty || 1 }}x {{ item?.name || item?.title || item?.label || item }}<span v-if="index < group.items.length - 1">, </span>
          </span>
        </p>
      </div>

      <div v-if="sections.length > 0">
        <p class="text-xs font-black text-violet-700">Table Seating:</p>
        <p class="mt-1 text-xs leading-relaxed text-slate-700">
          <span v-for="(section, index) in sections" :key="section?.id || section?.name || index">
            Seat: {{ section?.name || section }}<span v-if="index < sections.length - 1">, </span>
          </span>
        </p>
      </div>

      <div v-if="packageData?.notes">
        <p class="text-xs font-black text-violet-700">Notes:</p>
        <p class="mt-1 text-xs leading-relaxed text-slate-700">{{ packageData.notes }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  getPackageGroups,
  getPackagePrice,
  getTicketPackage,
  getTicketSections,
} from './ticketPackages';

const props = defineProps({
  ticket: { type: Object, required: true },
  formatPrice: { type: Function, required: true },
  showPrice: { type: Boolean, default: true },
});

const packageData = computed(() => getTicketPackage(props.ticket));
const groups = computed(() => getPackageGroups(props.ticket));
const sections = computed(() => getTicketSections(props.ticket));
const packagePrice = computed(() => getPackagePrice(props.ticket));
</script>
