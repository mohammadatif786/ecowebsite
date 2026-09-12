<template>
  <div v-if="isOpen" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="close">
    <div :class="['bg-white rounded-t-3xl sm:rounded-3xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]', (reviewRecipient || showPinModal) ? 'max-w-lg' : 'max-w-md']">
      <div v-if="showPinModal" class="p-5 sm:p-7 text-white shrink-0" style="background:linear-gradient(135deg,#0b2942,#1e3a5f)">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-black">Transaction PIN</h3>
          <button @click="showPinModal = false" class="hover:bg-white/20 p-1 rounded transition" aria-label="Close transaction PIN">
            <i data-lucide="x" class="w-7 h-7"></i>
          </button>
        </div>
      </div>
      <div v-else-if="!reviewRecipient" class="p-5 text-white shrink-0" style="background:linear-gradient(135deg,#0b2942,#1e3a5f)">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-black">Send Money</h3>
          <button @click="close" class="hover:bg-white/20 p-1 rounded transition">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
        <p class="text-white/70 text-xs mt-1 font-bold">Wallet: {{ money(balance) }}</p>
      </div>

      <div :class="[reviewRecipient ? 'px-6 py-7 sm:px-8 sm:py-7' : 'p-4 space-y-4', 'overflow-y-auto flex-1']">
        <div v-if="showPinModal" class="text-center py-8 sm:py-12 fade">
          <div class="h-24 w-24 rounded-3xl bg-sky-500 text-white grid place-items-center mx-auto shadow-lg shadow-sky-200">
            <i data-lucide="shield-check" class="w-12 h-12"></i>
          </div>
          <h4 class="text-3xl font-black text-slate-900 mt-8">Create a 4-digit PIN</h4>
          <p class="text-slate-500 text-lg font-bold mt-3">Used every time you send money</p>
          <div class="flex justify-center gap-3 mt-10" aria-label="Transaction PIN">
            <input v-for="(_, index) in pinDigits" :key="index" :ref="(element) => pinInputs[index] = element"
              v-model="pinDigits[index]" type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="1"
              class="pin-box" :aria-label="`PIN digit ${index + 1}`" @input="handlePinInput(index, $event)"
              @keydown.backspace="handlePinBackspace(index, $event)" />
          </div>
          <button @click="submitSend" :disabled="form.processing" class="w-full mt-10 py-4 rounded-2xl text-white text-xl font-black shadow-lg disabled:opacity-60 bg-gradient-to-r from-sky-400 to-blue-600">
            {{ form.processing ? 'Sending…' : 'Confirm' }}
          </button>
          <p class="text-xs text-slate-400 font-bold mt-3">Transaction PIN setup is a static preview for now.</p>
        </div>

        <div v-else-if="!reviewRecipient">
          <p class="text-[11px] font-black text-slate-500 uppercase mb-2">Quick contacts</p>
          <div class="flex gap-2 overflow-x-auto hide-scroll pb-2 mb-3">
            <button v-for="contact in contacts" :key="contact.id || contact.tag" @click="recipientQuery = contact.tag" class="shrink-0 text-center hover:opacity-80 transition">
              <img :src="contact.img" class="h-11 w-11 rounded-full object-cover mx-auto" />
              <p class="text-[10px] font-bold mt-1 w-11 truncate">{{ contact.name }}</p>
            </button>
          </div>

          <div>
            <label class="text-xs font-black text-slate-600 ml-1 block mb-1">To (@tag or name)</label>
            <input v-model="recipientQuery" placeholder="@username" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue transition-colors" />
          </div>

          <div class="mt-3">
            <label class="text-xs font-black text-slate-600 ml-1 block mb-1">Amount (USD)</label>
            <input v-model="form.amount" type="number" min="1" max="1000" placeholder="0.00" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-black text-lg outline-none focus:border-lkblue transition-colors" />
          </div>

          <div v-if="transferPreview" class="mt-3 rounded-2xl p-3 text-[12px] bg-blue-50 border border-blue-200">
            <div class="flex items-center justify-between gap-3">
              <b class="text-blue-700">{{ transferPreview.isInternational ? 'International transfer' : 'Domestic transfer' }}</b>
              <span :class="['text-[10px] font-black uppercase px-2 py-0.5 rounded-full', transferPreview.mode === 'pegged' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700']">
                {{ transferPreview.mode === 'pegged' ? 'Fixed peg' : 'Market rate' }}
              </span>
            </div>
            <p class="text-slate-600 mt-1 font-bold">
              {{ transferPreview.recipient.name }}{{ transferPreview.recipient.country ? ' (' + transferPreview.recipient.country + ')' : '' }}
              receives <b class="text-slate-900">{{ fmtCcy(transferPreview.converted, transferPreview.to) }}</b>
            </p>
            <p class="text-slate-400 mt-0.5 font-bold">
              1 {{ MY_CCY }} ~= {{ Number(transferPreview.one).toLocaleString(undefined, { maximumFractionDigits: 4 }) }} {{ transferPreview.to }} - LinkUp FX spread included
            </p>
          </div>

          <div v-else-if="selectedRecipient" class="mt-3 rounded-2xl p-3 text-[12px] bg-emerald-50 border border-emerald-200">
            <b class="text-emerald-700">{{ selectedRecipient.name }}</b>
            <p class="text-slate-500 mt-0.5 font-bold">{{ selectedRecipient.tag }}</p>
          </div>

          <p v-if="lookupError" class="text-[12px] font-bold text-rose-600 mt-3 bg-rose-50 p-2 rounded-xl">{{ lookupError }}</p>
          <p v-if="form.errors.recipientId || form.errors.amount || form.errors.note || form.errors.transaction" class="text-[12px] font-bold text-rose-600 mt-3 bg-rose-50 p-2 rounded-xl">
            {{ form.errors.recipientId || form.errors.amount || form.errors.note || form.errors.transaction }}
          </p>

          <div class="mt-3">
            <input v-model="form.note" placeholder="Note (optional)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-lkblue transition-colors" />
          </div>

          <button @click="reviewSend" :disabled="form.processing" class="btn btn-primary w-full py-3.5 mt-4 shadow hover:shadow-md transition disabled:opacity-60">
            Review &amp; Send
          </button>
          <p class="text-center text-xs text-slate-400 font-bold mt-2">Set a transaction PIN</p>
        </div>

        <div v-else class="text-center fade">
          <p class="text-[11px] font-black text-slate-400 uppercase tracking-normal">You're sending to</p>
          <img :src="reviewRecipient.img" class="h-24 w-24 rounded-full object-cover mx-auto mt-3 border-4 shadow-md border-emerald-200" />
          <p class="font-black text-2xl mt-3 leading-tight text-slate-950">{{ reviewRecipient.name }}</p>
          <p class="text-sm text-slate-500 font-bold mt-1">{{ reviewRecipient.tag }}{{ reviewRecipient.country ? ' · ' + reviewRecipient.country : '' }}</p>
          <p class="text-[40px] leading-none font-black mt-4 text-slate-950">{{ money(form.amount) }}</p>
          <p v-if="transferPreview?.isInternational" class="text-[13px] font-black text-blue-600 mt-2">
            International - {{ reviewRecipient.name }} receives {{ fmtCcy(transferPreview.converted, transferPreview.to) }}
          </p>
          <p class="text-[12px] text-slate-500 mt-4 font-bold">Sent money can't be undone.</p>

          <button @click="openTransactionPin" :disabled="form.processing" class="w-full mt-5 py-3.5 rounded-xl text-white font-black shadow disabled:opacity-60 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-sky-400 to-blue-600">
            <i data-lucide="lock" class="w-4 h-4"></i>
            Yes — send {{ money(form.amount) }}
          </button>
          <button @click="reviewRecipient = null" class="w-full mt-2 py-3.5 rounded-xl border border-slate-200 bg-white text-slate-900 font-black hover:bg-slate-50 transition">Back</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  balance: { type: Number, default: 0 },
  contacts: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
});

const emit = defineEmits(['money-sent']);

const isOpen = ref(false);
const recipientQuery = ref('');
const reviewRecipient = ref(null);
const lookupError = ref('');
const showPinModal = ref(false);
const pinDigits = ref(['', '', '', '']);
const pinInputs = ref([]);

const form = useForm({
  recipientId: '',
  amount: '',
  note: '',
});

const MY_CCY = 'USD';
const CCY = [
  { code: 'USD', sym: '$', mode: 'pegged', rate: 1, bps: 0 },
  { code: 'BSD', sym: 'B$', mode: 'pegged', rate: 1, bps: 0 },
  { code: 'XCD', sym: 'EC$', mode: 'pegged', rate: 2.70, bps: 0 },
  { code: 'BBD', sym: 'Bds$', mode: 'pegged', rate: 2.00, bps: 0 },
  { code: 'TTD', sym: 'TT$', mode: 'floating', rate: 6.78, bps: 150 },
  { code: 'JMD', sym: 'J$', mode: 'floating', rate: 157, bps: 150 },
  { code: 'COP', sym: 'COL$', mode: 'floating', rate: 4000, bps: 200 },
];

const money = n => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const ccyOf = (code) => CCY.find(c => c.code === code) || CCY[0];
const appFx = (amount, from, to) => {
  if (from === to) {
    return Math.round(amount * 100) / 100;
  }

  const fromCurrency = ccyOf(from);
  const toCurrency = ccyOf(to);

  return Math.round((amount / fromCurrency.rate) * toCurrency.rate * (1 - toCurrency.bps / 10000) * 100) / 100;
};
const fmtCcy = (amount, code) => {
  const currency = ccyOf(code);

  return currency.sym + Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const recipients = computed(() => {
  const byId = new Map();

  [...props.contacts, ...props.users].forEach((user) => {
    if (!user?.id || byId.has(Number(user.id))) {
      return;
    }

    byId.set(Number(user.id), user);
  });

  return [...byId.values()];
});

const resolveRecipient = (query) => {
  const needle = String(query || '').trim().toLowerCase().replace(/^[@~]/, '');

  if (!needle) {
    return null;
  }

  return recipients.value.find((user) => {
    const tag = String(user.tag || '').toLowerCase().replace(/^[@~]/, '');
    const name = String(user.name || '').toLowerCase();
    const email = String(user.email || '').toLowerCase();

    return tag === needle || name === needle || email === needle;
  }) || null;
};

const selectedRecipient = computed(() => resolveRecipient(recipientQuery.value));
const activeRecipient = computed(() => reviewRecipient.value || selectedRecipient.value);
const transferAmount = computed(() => Number(form.amount || 0));
const transferPreview = computed(() => {
  const recipient = activeRecipient.value;
  const amount = transferAmount.value;

  if (!recipient || !amount || amount <= 0) {
    return null;
  }

  const to = recipient.ccy || MY_CCY;
  const currency = ccyOf(to);

  return {
    recipient,
    to,
    mode: currency.mode,
    isInternational: to !== MY_CCY,
    converted: appFx(amount, MY_CCY, to),
    one: appFx(1, MY_CCY, to),
  };
});

const open = (tag = '') => {
  recipientQuery.value = tag || '';
  reviewRecipient.value = null;
  showPinModal.value = false;
  pinDigits.value = ['', '', '', ''];
  lookupError.value = '';
  form.reset();
  form.clearErrors();
  isOpen.value = true;
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};

const close = () => {
  isOpen.value = false;
  recipientQuery.value = '';
  reviewRecipient.value = null;
  showPinModal.value = false;
  pinDigits.value = ['', '', '', ''];
  lookupError.value = '';
  form.reset();
};

const reviewSend = () => {
  lookupError.value = '';
  form.clearErrors();

  const recipient = selectedRecipient.value;
  const amount = Number(form.amount || 0);

  if (!recipient) {
    lookupError.value = 'Choose a valid LinkUp user.';
    return;
  }

  if (!amount || amount <= 0) {
    lookupError.value = 'Enter an amount.';
    return;
  }

  if (amount > props.balance) {
    lookupError.value = 'Insufficient balance. Top up first.';
    return;
  }

  form.recipientId = recipient.id;
  reviewRecipient.value = recipient;
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};

const openTransactionPin = () => {
  if (!reviewRecipient.value || form.processing) return;
  pinDigits.value = ['', '', '', ''];
  showPinModal.value = true;
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
    pinInputs.value[0]?.focus();
  });
};

const handlePinInput = (index, event) => {
  pinDigits.value[index] = String(event.target.value || '').replace(/\D/g, '').slice(-1);
  if (pinDigits.value[index] && index < pinInputs.value.length - 1) pinInputs.value[index + 1]?.focus();
};

const handlePinBackspace = (index, event) => {
  if (!pinDigits.value[index] && index > 0) {
    event.preventDefault();
    pinInputs.value[index - 1]?.focus();
  }
};

const submitSend = () => {
  if (!reviewRecipient.value || form.processing) {
    return;
  }

  form.post(route('new_frontend.wallet.send_money'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('money-sent', {
        recipient: reviewRecipient.value,
        amount: Number(form.amount || 0),
      });
      close();
    },
  });
};

defineExpose({ open, close });
</script>

<style scoped>
.pin-box { background:#f8fafc; border:4px solid #eef2f7; border-radius:1.5rem; color:#0f172a; font-size:2.5rem; font-weight:800; height:7rem; outline:none; text-align:center; width:7rem; }
.pin-box:focus { border-color:#0f172a; }
</style>
