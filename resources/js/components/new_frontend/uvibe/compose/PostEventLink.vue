<template>
  <div class="space-y-1.5">
    <select :value="modelValue ?? ''" @change="$emit('update:modelValue', $event.target.value ? Number($event.target.value) : null)"
            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none bg-white">
      <option value="">Link to your event in Events (optional)</option>
      <option v-for="ev in events" :key="ev.id" :value="ev.id">🎟️ {{ ev.title }}{{ ev.price ? ' · ' + money(ev.price) : '' }}</option>
    </select>
    <p class="text-[11px] text-slate-500 px-1">
      Tickets are sold through Events. Add a price and people click it on your post to go to Events and buy.
    </p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: Number, default: null }, // the linked event's id
  events: { type: Array, default: () => [] }, // the member's events
});
defineEmits(['update:modelValue']);

const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>
