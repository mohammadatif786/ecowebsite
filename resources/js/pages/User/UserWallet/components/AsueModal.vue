<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <div class="p-8">
      <div class="flex justify-between mb-6">
        <h3 class="text-2xl font-bold text-linkup-teal">Start an Asue</h3>
        <button @click="$emit('close')">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="space-y-4">
        <label class="block text-xs font-bold uppercase text-slate-400">Circle Name</label>
        <input v-model="form.name"
          class="w-full p-4 bg-slate-50 rounded-xl font-medium outline-none border border-transparent focus:bg-white focus:border-linkup-teal"
          placeholder="e.g. Christmas Pot" />
        <p class="text-red-500 text-xs">{{ form.errors.name }}</p>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase text-slate-400">Hand Amount ($)</label>
            <input v-model.number="form.hand_amount" type="number"
              class="w-full p-4 bg-slate-50 rounded-xl font-bold outline-none border border-transparent focus:bg-white focus:border-linkup-teal"
              placeholder="100" />
            <p class="text-red-500 text-xs">{{ form.errors.hand_amount }}</p>

          </div>
          <div>
            <label class="block text-xs font-bold uppercase text-slate-400">Frequency</label>
            <select v-model="form.frequency"
              class="w-full p-4 bg-slate-50 rounded-xl font-bold outline-none border border-transparent focus:bg-white focus:border-linkup-teal">
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>

            <p class="text-red-500 text-xs">{{ form.errors.frequency }}</p>

          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase text-slate-400">Max Members</label>
            <input v-model.number="form.max_members" type="number"
              class="w-full p-4 bg-slate-50 rounded-xl font-bold outline-none border border-transparent focus:bg-white focus:border-linkup-teal"
              placeholder="10" />
            <p class="text-red-500 text-xs">{{ form.errors.max_members }}</p>

          </div>
          <div>
            <label class="block text-xs font-bold uppercase text-slate-400">Start Date</label>
            <input v-model="form.start_date" type="date"
              class="w-full p-4 bg-slate-50 rounded-xl font-bold outline-none border border-transparent focus:bg-white focus:border-linkup-teal" />
            <p class="text-red-500 text-xs">{{ form.errors.start_date }}</p>

          </div>
        </div>

        <div class="flex justify-between items-center">
          <label class="block text-xs font-bold uppercase text-slate-400">Invite Friends</label>
          <span class="text-xs font-bold text-linkup-teal">Slots: {{ selectedUsers.length }} / {{ form.max_members || 10
            }}</span>
        </div>

        <!-- Implementing user selection similar to existing but better styled -->
        <div class="relative">
          <input ref="recipientInput" v-model="recipientQuery" type="text" placeholder="Search @tags to invite"
            class="w-full p-4 bg-slate-50 rounded-xl font-medium outline-none border border-transparent focus:bg-white focus:border-linkup-teal"
            @input="handleRecipientInput" :disabled="selectedUsers.length >= (parseInt(form.max_members) || 10)" />
          <div v-if="showSuggestions && filteredUsers.length > 0"
            class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-lg z-10 max-h-48 overflow-y-auto mt-1">
            <div v-for="user in filteredUsers" :key="user.id"
              class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-b-0"
              @click="selectUser(user)">
              <div class="font-semibold text-slate-700">{{ user.name }}</div>
              <div class="text-sm text-slate-500">{{ user.linkup_id }}</div>
            </div>
          </div>
          <p class="text-red-500 text-xs">{{ form.errors.user_ids }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <div v-for="user in selectedUsers" :key="user.id"
            class="flex items-center bg-linkup-teal text-white text-xs font-bold px-3 py-2 rounded-xl">
            {{ user.name }}
            <button type="button" class="ml-2 hover:text-red-200" @click="removeUser(user)">
              &times;
            </button>
          </div>
          <span v-if="selectedUsers.length === 0" class="text-xs text-slate-400">Add contacts first.</span>
        </div>

        <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 flex gap-3 mt-4">
          <input type="checkbox" v-model="form.disclaimerAgreed"
            class="w-5 h-5 text-indigo-600 mt-0.5 border-slate-300 rounded" />
          <div class="text-xs text-indigo-700 leading-relaxed">
            <p>
              <strong>Disclaimer:</strong> I acknowledge that Link Up is a facilitator and is not responsible for the
              solvency of my group members. I affirm that I have invited only trusted contacts and understand that funds
              are locked in escrow until payout.
            </p>
            <p class="mt-2">
              <strong>Platform Fee:</strong> A <strong>3%</strong> Link Up service fee is deducted from each <strong>pot
                payout</strong>. The payout recipient receives the pot amount <strong>minus</strong> this fee.
            </p>
          </div>
        </div>
        <p class="text-red-500 text-xs">{{ form.errors.disclaimerAgreed }}</p>

        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 mt-2 mb-4 text-center">
            <p class="text-xs font-bold text-slate-500">
                Fee: 3% deducted from each pot payout (winner receives net payout).
            </p>
        </div>


        <button
          class="w-full py-4 bg-linkup-teal text-white rounded-xl font-bold text-lg shadow-lg hover:opacity-90 mt-2 transition-opacity"
          @click="handleSubmit()">
          Create & Pledge Hand
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
import { toast } from 'vue-sonner';

const props = defineProps<{
  users: Array<Record<string, any>>;
}>();

const recipientInput = ref<HTMLInputElement>();
const recipientQuery = ref('');
const showSuggestions = ref(false);
const form = useForm({
  name: '',
  frequency: '',
  hand_amount: '' as any,
  start_date: '',
  max_members: '' as any,
  user_ids: [] as number[],
  disclaimerAgreed: false,
});

const filteredUsers = computed(() => {

  if (!recipientQuery.value.startsWith('@')) return [];
  const query = recipientQuery.value.slice(1).toLowerCase();
  return props.users
    .filter(user =>
      !selectedUsers.value.find(u => u.id === user.id) &&
      (user.name.toLowerCase().includes(query) || user.linkup_id.toLowerCase().includes(query))
    );
});

const handleRecipientInput = () => {
  showSuggestions.value = recipientQuery.value.startsWith('@') && recipientQuery.value.length > 1;
};

const selectedUsers = ref<Array<Record<string, any>>>([]);

const selectUser = (user: Record<string, any>) => {

  if (!selectedUsers.value.find(u => u.id === user.id)) {
    selectedUsers.value.push(user);
    form.user_ids.push(user.id);
  }

  recipientQuery.value = '';
  showSuggestions.value = false;
  recipientInput.value?.focus();
};

const removeUser = (user: Record<string, any>) => {
  selectedUsers.value = selectedUsers.value.filter(u => u.id !== user.id);
  form.user_ids = form.user_ids.filter(id => id !== user.id);
};
const handleSubmit = () => {
  form.post(route('frontend.user.asues.store'), {
    onSuccess: () => {
      form.reset();
      selectedUsers.value = [];
      toast.success('Asue created successfully!');
      emit('close');
    },
    onError: (errors) => {
      const errorMessage = errors.asue_creation || 'Failed to create Asue!';
      toast.error(errorMessage);
    }
  });
};

const emit = defineEmits<{
  (e: 'close'): void;
}>();
</script>

<style scoped>
.bg-linkup-teal {
  background-color: #008080;
}

.text-linkup-teal {
  color: #008080;
}

.focus\:border-linkup-teal:focus {
  border-color: #008080;
}
</style>
