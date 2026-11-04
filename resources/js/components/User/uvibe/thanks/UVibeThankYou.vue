<template>
  <!-- The 🙏 in the U Vibe header: everyone who sent you a gift or a Boost; pick one and send a thank-you. -->
  <button type="button" @click="open" class="h-11 w-11 rounded-xl bg-white/20 grid place-items-center text-lg" title="Say Thank You" aria-label="Say Thank You">🙏</button>

  <!-- These windows are opened from the purple header (white text); they must not inherit it.
       `contents` = no box of its own, so it doesn't change the header's layout. -->
  <div class="contents text-slate-900">
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div class="p-4 flex items-center justify-between border-b border-slate-100">
      <h3 class="text-lg font-black flex items-center gap-2">🙏 Say Thank You</h3>
      <button type="button" @click="close" aria-label="Close"><X class="w-5 h-5" /></button>
    </div>

    <!-- Who is waiting for a thank-you -->
    <div v-if="!picked" class="p-2">
      <p v-if="loading" class="text-center text-slate-400 font-bold py-10 text-sm">Loading…</p>
      <p v-else-if="failed" class="text-center text-rose-600 font-bold py-10 text-sm px-4">Couldn’t load this — close it and try again.</p>
      <p v-else-if="!senders.length" class="text-center text-slate-400 font-bold py-10 text-sm px-4">
        No Vibes gifts received yet — once someone sends you one, they’ll show up here so you can thank them.
      </p>
      <button v-for="s in senders" :key="s.user_id" type="button" @click="pick(s)"
              class="w-full flex items-center justify-between gap-2 rounded-2xl p-3 text-left hover:bg-slate-50">
        <div class="flex items-center gap-3 min-w-0">
          <img v-if="s.avatar && !broken[s.user_id]" :src="s.avatar" alt="" @error="broken[s.user_id] = true"
               class="h-10 w-10 rounded-full object-cover bg-slate-100 shrink-0" />
          <div v-else class="h-10 w-10 rounded-full bg-slate-700 text-white grid place-items-center font-black text-xs shrink-0">{{ initials(s.name) }}</div>
          <div class="min-w-0">
            <p class="font-black text-sm truncate">{{ s.name }}</p>
            <p class="text-[11px] text-slate-400 truncate">Sent you {{ s.emoji }} {{ s.gift }} · 🪙 {{ num(s.coins) }}<template v-if="s.count > 1"> · +{{ s.count - 1 }} more</template></p>
          </div>
        </div>
        <span class="text-[11px] font-black shrink-0 text-lkblue">Thank →</span>
      </button>
    </div>

    <!-- The message -->
    <div v-else class="p-4 space-y-3">
      <p class="text-sm text-slate-500">Sending a thank-you message to <b class="text-slate-900">{{ picked.name }}</b> for their support.</p>
      <textarea v-model="message" rows="5" :maxlength="max" aria-label="Your thank-you message"
                class="w-full rounded-2xl border border-slate-200 p-3 text-sm outline-none focus:border-violet-300"></textarea>
      <p class="text-[11px] text-slate-400">Feel free to edit this into your own words before sending.</p>
      <p v-if="error" class="text-sm font-bold text-rose-600">{{ error }}</p>
      <button type="button" @click="send" :disabled="sending || !message.trim()"
              class="w-full btn btn-primary py-3 font-black flex items-center justify-center gap-1.5 disabled:opacity-60">
        <Send class="w-4 h-4" />{{ sending ? 'Sending…' : 'Send Thank You' }}
      </button>
    </div>
  </Modal>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Send, X } from 'lucide-vue-next';
import Modal from '../../ui/Modal.vue';
import { firstError, notify } from '../../../../services/uvibe';
import { fetchThankYous, sendThankYou } from '../../../../services/uvibeThanks';
import { num } from '../../../../services/uvibeFormat';

const modalRef = ref(null);
const senders = ref([]);
const template = ref('');
const max = ref(600);
const picked = ref(null);
const message = ref('');
const loading = ref(false);
const failed = ref(false);
const sending = ref(false);
const error = ref('');
const broken = reactive({});

const open = async () => {
  picked.value = null;
  error.value = '';
  failed.value = false;
  loading.value = true;
  modalRef.value?.open();
  try {
    const res = await fetchThankYous();
    senders.value = res.senders || [];
    template.value = res.template || '';
    max.value = res.max || 600;
  } catch (e) {
    senders.value = [];
    failed.value = true;
  } finally {
    loading.value = false;
  }
};
const close = () => modalRef.value?.close();

const initials = (name) => String(name || '?').slice(0, 2).toUpperCase();

const pick = (s) => {
  picked.value = s;
  message.value = template.value;
  error.value = '';
};

const send = async () => {
  if (!picked.value || sending.value) return;
  sending.value = true;
  error.value = '';
  try {
    const res = await sendThankYou(picked.value.user_id, message.value);
    senders.value = res.senders || [];
    notify(res.message);
    close();
  } catch (e) {
    error.value = firstError(e, 'Could not send your thank-you');
  } finally {
    sending.value = false;
  }
};

defineExpose({ open, close });
</script>
