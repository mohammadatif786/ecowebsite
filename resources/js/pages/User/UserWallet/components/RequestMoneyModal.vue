<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <div v-if="!showConfirmModal" class="p-8">
      <div class="flex justify-between mb-8">
        <h3 class="text-2xl font-bold">Request Money</h3>
        <button @click="$emit('close')"><X class="w-5 h-5" /></button>
      </div>

      <div class="space-y-4 relative">
        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">From</label>
        <input
          ref="recipientInput"
          v-model="recipientQuery"
          type="text"
          placeholder="~LinkTag"
          class="w-full p-4 bg-slate-50 rounded-xl mb-4 font-medium text-lg border border-transparent focus:bg-white focus:border-linkup-lime transition-colors outline-none"
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
          class="w-full p-4 bg-slate-50 rounded-xl mb-4 text-3xl font-bold border border-transparent focus:bg-white focus:border-linkup-lime transition-colors outline-none"
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
          class="w-full py-4 bg-linkup-lime text-linkup-dark rounded-xl font-bold text-lg hover:brightness-105 transition-colors"
          @click="validateAndConfirm()"
        >
          Send Request
        </button>
      </div>
    </div>

    <!-- Confirmation Modal (embedded style for consistency) -->
    <div v-else class="p-8 bg-slate-50">
      <div class="text-center mb-6">
        <h3 class="text-2xl font-bold text-slate-900 mb-2">Confirm Request</h3>
        <p class="text-slate-500">Requesting from {{ selectedUser?.name }}</p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-6">
        <div class="flex justify-between items-center mb-2">
           <span class="text-sm font-bold text-slate-500">To</span>
           <span class="font-bold text-slate-900">{{ selectedUser?.linkup_id }}</span>
        </div>
        <div class="flex justify-between items-center mb-2">
          <span class="text-sm font-bold text-slate-500">Amount</span>
          <span class="text-xl font-bold text-slate-900">${{ parseFloat(amount).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm font-bold text-slate-500">For</span>
          <span class="text-sm font-bold text-slate-800">"{{ note }}"</span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <button
          @click="showConfirmModal = false"
          class="py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50"
        >
          Back
        </button>
        <button
          @click="confirmRequest()"
          class="py-3 bg-linkup-lime text-linkup-dark rounded-xl font-bold shadow-lg hover:brightness-105 disabled:opacity-50"
          :disabled="form.processing"
        >
            {{ form.processing ? 'Sending...' : 'Request Money' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { X } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps<{
    users: Array<Record<string, any>>;
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
        errors.value.note = 'Reason is required. Please enter a reason for this request.';
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

const confirmRequest = () => {
    form.post(route('frontend.user.money.request'), {
        onSuccess: () => {
            showConfirmModal.value = false;
            alert('Money Request sent successfully!');
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
                alert('Failed to send money request. Please try again.');
            }
        },
    });
};

const emit = defineEmits<{
    (e: 'close'): void;
}>();
</script>
