<template>
  <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center p-4 z-50" role="dialog" aria-modal="true" @click.self="$emit('close')">
    <div class="bg-white w-full max-w-lg rounded-2xl p-4 shadow-glass">
      <div class="flex items-center gap-2 mb-2">
        <i data-lucide="settings" class="w-5 h-5"></i>
        <div class="font-semibold">Settings</div>
        <button class="ml-auto p-1 rounded-lg hover:bg-slate-100" @click="$emit('close')">
          <i data-lucide="x"></i>
        </button>
      </div>
      <div class="space-y-4">
        <div>
          <div class="text-sm font-semibold">Display Currency</div>
          <select v-model="selectedCurrency" class="w-full px-3 py-2 border rounded-xl">
            <option v-for="currency in Object.keys(FX)" :key="currency" :value="currency">{{ currency }}</option>
          </select>
          <div class="text-xs text-slate-500 mt-1">
            Amounts you type will be in this currency. Internally we store USD for consistency.
          </div>
        </div>
        <div>
          <div class="text-sm font-semibold">Security</div>
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="pinEnabled" />
            <span>Require PIN to send money</span>
          </label>
          <div class="grid grid-cols-[1fr_auto] gap-2 mt-2">
            <input
              v-model="pin"
              type="password"
              class="px-3 py-2 border rounded-xl"
              placeholder="Set/Update PIN"
            />
            <button class="px-3 py-2 border rounded-xl" @click="savePin">Save PIN</button>
          </div>
        </div>
        <div class="text-xs text-slate-500">Data is stored locally for this demo.</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, watch } from 'vue';

const current = inject('current');
const showToast = inject('showToast');
const refreshAll = inject('refreshAll');
const DB = inject('DB');
const hashPIN = (pin) => window.$CryptoJS.SHA256(pin).toString(window.$CryptoJS.enc.Hex);
const FX = inject('FX');
defineEmits(['close']);

const selectedCurrency = ref(current().currency);
const pinEnabled = ref(current().settings.pinEnabled);
const pin = ref('');

watch(selectedCurrency, (newCurrency) => {
  current().currency = newCurrency;
  saveDB();
  refreshAll();
  showToast('Currency updated');
});

watch(pinEnabled, (enabled) => {
  current().settings.pinEnabled = enabled;
  saveDB();
  showToast('Updated');
});

const savePin = () => {
  if (!pin.value || pin.value.length < 4) return showToast('PIN must be at least 4 characters');
  current().settings.pin = hashPIN(pin.value);
  current().settings.pinEnabled = true;
  saveDB();
  refreshAll();
  showToast('PIN saved');
};

const saveDB = () => {
  localStorage.setItem('linkupWalletDB_v9_multi_fx', JSON.stringify(DB.value));
};

onMounted(() => {
  window.$lucide.createIcons();
});
</script>