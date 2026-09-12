<template>
  <div v-if="isOpen" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="close">
    <div :class="['bg-white rounded-t-3xl sm:rounded-3xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]', reviewRecipient ? 'max-w-lg' : 'max-w-md']">
      <div v-if="!reviewRecipient" class="p-5 text-white shrink-0" style="background:linear-gradient(135deg,#0b2942,#1e3a5f)">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-black">Request Money</h3>
          <button @click="close" class="hover:bg-white/20 p-1 rounded transition">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
        <p class="text-white/70 text-xs mt-1 font-bold">Ask a contact to pay you.</p>
      </div>

      <div :class="[reviewRecipient ? 'px-6 py-7 sm:px-8 sm:py-7' : 'p-4 space-y-4', 'overflow-y-auto flex-1']">
        <div v-if="!reviewRecipient">
          <p class="text-[11px] font-black text-slate-500 uppercase mb-2">Quick contacts</p>
          <div class="flex gap-2 overflow-x-auto hide-scroll pb-2 mb-3">
            <button v-for="contact in contacts" :key="contact.id || contact.tag" @click="recipientQuery = contact.tag" class="shrink-0 text-center hover:opacity-80 transition">
              <img :src="contact.img" class="h-11 w-11 rounded-full object-cover mx-auto" />
              <p class="text-[10px] font-bold mt-1 w-11 truncate">{{ contact.name }}</p>
            </button>
          </div>

          <div>
            <label class="text-xs font-black text-slate-600 ml-1 block mb-1">From (@tag or name)</label>
            <input v-model="recipientQuery" placeholder="@username" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue transition-colors" />
          </div>

          <div v-if="selectedRecipient" class="rounded-2xl p-3 text-[12px] bg-emerald-50 border border-emerald-200">
            <b class="text-emerald-700">{{ selectedRecipient.name }}</b>
            <p class="text-slate-500 mt-0.5 font-bold">{{ selectedRecipient.tag }}{{ selectedRecipient.country ? ' - ' + selectedRecipient.country : '' }}</p>
          </div>

          <div>
            <label class="text-xs font-black text-slate-600 ml-1 block mb-1">Amount (USD)</label>
            <input v-model="form.amount" type="number" min="1" max="1000" step="1" placeholder="0.00" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-black text-lg outline-none focus:border-lkblue transition-colors" />
          </div>

          <div>
            <label class="text-xs font-black text-slate-600 ml-1 block mb-1">Reason</label>
            <input v-model="form.note" placeholder="Reason (optional)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-lkblue transition-colors" />
          </div>

          <p v-if="lookupError" class="text-[12px] font-bold text-rose-600 bg-rose-50 p-2 rounded-xl">{{ lookupError }}</p>
          <p v-if="form.errors.recipientId || form.errors.amount || form.errors.note || form.errors.transaction" class="text-[12px] font-bold text-rose-600 bg-rose-50 p-2 rounded-xl">
            {{ form.errors.recipientId || form.errors.amount || form.errors.note || form.errors.transaction }}
          </p>

          <button @click="reviewRequest" :disabled="form.processing" class="btn w-full py-3.5 text-white shadow hover:shadow-md transition disabled:opacity-60" style="background:linear-gradient(135deg,#3b82f6,#10b981)">
            Review Request
          </button>
        </div>

        <div v-else class="text-center fade">
          <p class="text-[11px] font-black text-slate-400 uppercase tracking-normal">You're requesting from</p>
          <img :src="reviewRecipient.img" class="h-24 w-24 rounded-full object-cover mx-auto mt-3 border-4 shadow-md border-emerald-200" />
          <p class="font-black text-2xl mt-3 leading-tight text-slate-950">{{ reviewRecipient.name }}</p>
          <p class="text-sm text-slate-500 font-bold mt-1">{{ reviewRecipient.tag }}{{ reviewRecipient.country ? ' - ' + reviewRecipient.country : '' }}</p>
          <p class="text-[40px] leading-none font-black mt-4 text-slate-950">{{ money(form.amount) }}</p>
          <p v-if="form.note" class="text-[13px] font-black text-blue-600 mt-2">{{ form.note }}</p>
          <p class="text-[12px] text-slate-500 mt-4 font-bold">They will receive a money request notification.</p>

          <button @click="submitRequest" :disabled="form.processing" class="w-full mt-5 py-3.5 rounded-xl text-white font-black shadow disabled:opacity-60 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-sky-400 to-emerald-500">
            <i data-lucide="hand-coins" class="w-4 h-4"></i>
            {{ form.processing ? 'Sending...' : 'Yes - request ' + money(form.amount) }}
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
  contacts: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
});

const emit = defineEmits(['money-requested']);

const isOpen = ref(false);
const recipientQuery = ref('');
const reviewRecipient = ref(null);
const lookupError = ref('');

const form = useForm({
  recipientId: '',
  amount: '',
  note: '',
});

const money = n => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

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

const open = (tag = '') => {
  recipientQuery.value = tag || '';
  reviewRecipient.value = null;
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
  lookupError.value = '';
  form.reset();
  form.clearErrors();
};

const reviewRequest = () => {
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

  if (amount > 1000) {
    lookupError.value = 'Maximum request amount is $1,000.';
    return;
  }

  form.recipientId = recipient.id;
  reviewRecipient.value = recipient;
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};

const submitRequest = () => {
  if (!reviewRecipient.value || form.processing) {
    return;
  }

  form.post(route('new_frontend.wallet.request_money'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('money-requested', {
        recipient: reviewRecipient.value,
        amount: Number(form.amount || 0),
      });
      close();
    },
    onError: () => {
      reviewRecipient.value = null;
    },
  });
};

defineExpose({ open, close });
</script>
