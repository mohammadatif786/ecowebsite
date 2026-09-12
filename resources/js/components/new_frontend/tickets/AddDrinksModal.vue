<template>
  <div v-if="open && ticket" class="fixed inset-0 z-[190] flex items-center justify-center bg-slate-900/40 px-4 py-6">
    <div class="w-full max-w-3xl max-h-[88vh] overflow-hidden rounded-2xl bg-white shadow-2xl border border-blue-100">
      <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
        <div>
          <p class="text-lg font-black text-slate-900">Add Drinks</p>
          <p class="text-sm text-slate-500">{{ ticket.name }}</p>
        </div>
        <button
          type="button"
          class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-bold text-slate-500 hover:bg-slate-50"
          @click="$emit('close')"
        >
          Close
        </button>
      </div>

      <div class="max-h-[70vh] overflow-y-auto p-5">
        <div v-if="groups.length === 0" class="rounded-xl border border-slate-100 bg-slate-50 py-10 text-center">
          <p class="text-sm font-bold text-slate-500">No drinks available for this ticket.</p>
        </div>

        <div v-else class="space-y-5">
          <section v-for="group in groups" :key="group.key" class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="font-black text-slate-900">{{ group.label }}</h3>
              <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-black text-lkblue2">
                {{ group.drinks.length }} option{{ group.drinks.length === 1 ? '' : 's' }}
              </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <div
                v-for="drink in group.drinks"
                :key="drinkKey(group, drink)"
                class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="font-black text-slate-800">{{ drink.name }}</p>
                    <p class="mt-1 text-sm font-bold text-slate-500">
                      {{ formatPrice(getDrinkPrice(drink)) }}
                    </p>
                  </div>
                  <span
                    class="rounded-full px-2.5 py-1 text-[11px] font-black"
                    :class="getDrinkAvailable(drink) > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'"
                  >
                    {{ getDrinkAvailable(drink) > 0 ? `Available: ${getDrinkAvailable(drink)}` : 'Out of Stock' }}
                  </span>
                </div>

                <div class="mt-4 flex items-center justify-between">
                  <span class="text-xs font-bold text-slate-400">
                    Selected: {{ quantities[skuFor(group, drink)] || 0 }}
                  </span>
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      class="h-8 w-8 rounded-lg border border-slate-200 font-black text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                      :disabled="(quantities[skuFor(group, drink)] || 0) <= 0"
                      @click="$emit('dec', skuFor(group, drink), drink, group)"
                    >
                      -
                    </button>
                    <span class="w-6 text-center font-black text-slate-900">
                      {{ quantities[skuFor(group, drink)] || 0 }}
                    </span>
                    <button
                      type="button"
                      class="h-8 w-8 rounded-lg border border-slate-200 font-black text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                      :disabled="getDrinkAvailable(drink) <= 0 || (quantities[skuFor(group, drink)] || 0) >= getDrinkAvailable(drink)"
                      @click="$emit('inc', skuFor(group, drink), drink, group)"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  buildDrinkSku,
  getDrinkAddonGroups,
  getDrinkAvailable,
  getDrinkPrice,
} from './ticketDrinkAddons';

const props = defineProps({
  open: { type: Boolean, default: false },
  ticket: { type: Object, default: null },
  quantities: { type: Object, default: () => ({}) },
  formatPrice: { type: Function, required: true },
});

defineEmits(['close', 'inc', 'dec']);

const groups = computed(() => getDrinkAddonGroups(props.ticket));

const skuFor = (group, drink) => buildDrinkSku(group.prefix, drink?.name, props.ticket?.id);
const drinkKey = (group, drink) => `${group.key}-${drink?.id || drink?.name}`;
</script>
