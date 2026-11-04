<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <!-- Send Form -->
    <div v-if="!showConfirmModal" class="p-8">
      <div class="flex justify-between mb-8">
        <h3 class="text-2xl font-bold">Send Money</h3>
        <button @click="$emit('close')"><X class="w-5 h-5" /></button>
      </div>

      <div class="space-y-4 relative">
        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Recipient</label>
        <input
          ref="recipientInput"
          v-model="recipientQuery"
          type="text"
          placeholder="~LinkTag"
          class="w-full p-4 bg-slate-50 rounded-xl mb-4 font-medium text-lg border border-transparent focus:bg-white focus:border-linkup-blue outline-none"
          :class="{ 'border-red-300': errors.recipient }"
          @input="handleRecipientInput"
        />
        <p v-if="errors.recipient" class="text-red-500 text-sm mt-1 mb-2">{{ errors.recipient }}</p>

        <!-- User suggestions dropdown -->
        <div
          v-if="showSuggestions && filteredUsers.length > 0"
          class="absolute top-[85px] left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-lg z-10 max-h-48 overflow-y-auto mt-1"
        >
          <div
            v-for="user in filteredUsers"
            :key="user.id"
            class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-b-0"
            @click="selectUser(user)"
          >
            <div class="font-semibold text-slate-700">{{ user.name }}</div>
            <div class="text-sm text-slate-500">{{ user.linkup_id }}</div>
          </div>
        </div>

        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Amount</label>
        <input
          v-model="amount"
          type="number"
          placeholder="0.00"
          class="w-full p-4 bg-slate-50 rounded-xl mb-4 text-3xl font-bold border border-transparent focus:bg-white focus:border-linkup-blue outline-none"
          :class="{ 'border-red-300': errors.amount }"
        />
        <p v-if="errors.amount" class="text-red-500 text-sm mt-1 mb-2">{{ errors.amount }}</p>

        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Reason</label>
        <input
          v-model="note"
          type="text"
          placeholder="Reason"
          class="w-full p-4 bg-slate-50 rounded-xl mb-8 text-sm outline-none border border-transparent focus:bg-white focus:border-linkup-blue"
          :class="{ 'border-red-300': errors.note }"
        />
        <p v-if="errors.note" class="text-red-500 text-sm mt-1 mb-2">{{ errors.note }}</p>

        <button
          class="w-full py-4 bg-linkup-blue text-white rounded-xl font-bold text-lg hover:brightness-105 transition-colors"
          @click="validateAndConfirm()"
        >
          Verify Recipient
        </button>
      </div>
    </div>

    <div v-else class="p-8 bg-slate-50">
      <div class="text-center mb-6">
        <div class="w-20 h-20 bg-linkup-dark rounded-full mx-auto flex items-center justify-center text-2xl font-bold text-white mb-3 shadow-lg border-4 border-white">
          <img :src="selectedUser?.avatar" alt="Avatar" class="w-full h-full object-cover rounded-full" v-if="selectedUser?.avatar" />
        </div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Sending to</div>
        <h3 class="text-2xl font-bold text-slate-900">{{ selectedUser?.name }}</h3>
        <p class="text-linkup-blue font-mono font-bold">{{ selectedUser?.linkup_id }}</p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-6">
        <div class="flex justify-between items-center mb-2">
          <span class="text-sm font-bold text-slate-500">Amount</span>
          <span class="text-xl font-bold text-slate-900">${{ parseFloat(amount).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between items-center mb-2">
          <span class="text-sm font-bold text-slate-500">For</span>
          <span class="text-sm font-bold text-slate-800">"{{ note }}"</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm font-bold text-slate-500">Fee</span>
          <span class="text-sm font-bold text-green-600">Free</span>
        </div>
      </div>

      <div class="flex items-start gap-3 p-4 bg-yellow-50 border border-yellow-100 rounded-xl mb-6">
        <ShieldAlert class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" />
        <p class="text-xs text-yellow-800 leading-relaxed">
          <strong>Safety Check:</strong> Is this the right person?
        </p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <button
          @click="showConfirmModal = false"
          class="py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50"
        >
          Back
        </button>
        <button
          @click="confirmSend()"
          class="py-3 bg-linkup-dark text-white rounded-xl font-bold shadow-lg hover:opacity-90 disabled:opacity-50"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Sending...' : 'Confirm Send' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { X, ShieldAlert } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps<{
  users: Array<Record<string, any>>;
  initialRecipient?: string | null;
}>();

const recipientInput = ref<HTMLInputElement>();
const recipientQuery = ref('');
const showSuggestions = ref(false);
const amount = ref('');
const note = ref('');
const showConfirmModal = ref(false);
const selectedUser = ref<Record<string, any> | null>(null);
const errors = ref<Record<string, string>>({});

const form = useForm({
  recipientId: '',
  amount: 0,
  note: '',
});
const filteredUsers = computed(() => {
  if (!recipientQuery.value.startsWith('@')) {
    return [];
  }

  const query = recipientQuery.value.slice(1).toLowerCase();
  return props.users.filter(user =>
    user.name.toLowerCase().includes(query) ||
    user.linkup_id.toLowerCase().includes(query)
  );
});

const handleRecipientInput = () => {
  showSuggestions.value = recipientQuery.value.startsWith('@') && recipientQuery.value.length > 1;
};

const selectUser = (user: Record<string, any>) => {
  recipientQuery.value = user.linkup_id;
  showSuggestions.value = false;
  recipientInput.value?.focus();
};

onMounted(() => {
  if (props.initialRecipient) {
    recipientQuery.value = props.initialRecipient;
  }
});

const validateAndConfirm = () => {
  // Reset errors
  errors.value = {};

  // Validate recipient
  const user = props.users.find(user => user.linkup_id === recipientQuery.value);
  if (!user) {
    errors.value.recipient = 'Please select a valid recipient from suggestions.';
    return;
  }

  // Validate amount
  const amountValue = parseFloat(amount.value);
  if (!amountValue || amountValue <= 0) {
    errors.value.amount = 'Please enter a valid amount greater than 0.';
    return;
  }

  // Validate reason (mandatory)
  if (!note.value || note.value.trim().length === 0) {
    errors.value.note = 'Reason is required. Please enter a reason for this transfer.';
    return;
  }

  if (note.value.trim().length < 3) {
    errors.value.note = 'Reason must be at least 3 characters long.';
    return;
  }

  // All validations passed, show confirmation
  selectedUser.value = user;
  form.recipientId = user.id;
  form.amount = amountValue;
  form.note = note.value.trim();
  showConfirmModal.value = true;
};

const confirmSend = () => {
  form.post(route('frontend.user.send.money'), {
    onSuccess: () => {
      showConfirmModal.value = false;
      alert('Money sent successfully!');
      emit('close');
    },
    onError: (errors) => {
      showConfirmModal.value = false;
      if (errors.amount) {
        alert('Please enter a valid amount.');
      } else if (errors.recipientId) {
        alert('Invalid recipient selected.');
      } else if (errors.note) {
        alert('Please provide a valid reason.');
      } else {
        alert('Failed to send money. Please try again.');
      }
    },
  });
};

const emit = defineEmits<{
  (e: 'close'): void;
}>();
</script>

