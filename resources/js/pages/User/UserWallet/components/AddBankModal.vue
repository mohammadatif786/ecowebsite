<template>
  <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
    <div class="p-8">
      <div class="flex justify-between items-center mb-8">
        <h3 class="text-2xl font-bold">Add Bank Details</h3>
        <button @click="$emit('close')" class="p-2 hover:bg-slate-100 rounded-full">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- PayPal ID Section -->
      <div class="mb-8">
        <label class="block text-sm font-bold text-slate-700 mb-2">PayPal ID</label>
        <p class="text-xs text-slate-500 mb-2">Enter the email address associated with your PayPal account for withdrawals.</p>
        <input
          v-model="paypalId"
          type="email"
          placeholder="Enter your PayPal email address"
          class="w-full p-4 bg-slate-50 rounded-xl border border-transparent focus:bg-white focus:border-linkup-blue outline-none"
        />
      </div>

      <!-- Bank Accounts Section -->
      <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
          <h4 class="text-lg font-bold text-slate-900">Bank Accounts</h4>
          <button
            @click="addBank"
            class="px-4 py-2 bg-linkup-blue text-white rounded-xl font-medium hover:bg-linkup-blue/90 transition-colors"
          >
            + Add Additional Bank
          </button>
        </div>

        <!-- Bank Account Forms -->
        <div v-for="(bank, index) in banks" :key="index" class="mb-6 p-6 bg-slate-50 rounded-xl">
          <div class="flex justify-between items-center mb-4">
            <h5 class="font-medium text-slate-700">Bank {{ index + 1 }}</h5>
            <button
              v-if="banks.length > 1"
              @click="removeBank(index)"
              class="text-red-500 hover:text-red-700 font-medium text-sm"
            >
              Remove Bank
            </button>
          </div>

          <!-- Default Bank Selection -->
          <div class="mb-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="radio"
                name="default-bank"
                :value="index"
                v-model="defaultBankIndex"
                class="w-4 h-4 text-linkup-blue border-slate-300 focus:ring-linkup-blue"
              />
              <span class="text-sm font-medium text-slate-700">Set as default bank</span>
            </label>
            <p class="text-xs text-slate-500 mt-1 ml-6">This bank will be used for all transactions by default.</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-600 mb-2">Bank Name</label>
              <p class="text-xs text-slate-500 mb-2">Enter the full name of your bank (e.g., RBC Royal Bank, Chase, Bank of America).</p>
              <input
                v-model="bank.name"
                type="text"
                placeholder="e.g., RBC Royal Bank"
                class="w-full p-3 bg-white rounded-lg border border-slate-200 focus:border-linkup-blue outline-none"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-600 mb-2">Account Number</label>
              <p class="text-xs text-slate-500 mb-2">Enter your bank account number (4-34 characters, numbers and letters only).</p>
              <input
                v-model="bank.accountNumber"
                type="text"
                placeholder="Enter account number"
                class="w-full p-3 bg-white rounded-lg border border-slate-200 focus:border-linkup-blue outline-none"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-600 mb-2">Routing Number</label>
              <p class="text-xs text-slate-500 mb-2">Enter your bank's routing number (5-20 digits, numbers only).</p>
              <input
                v-model="bank.routingNumber"
                type="text"
                placeholder="Enter routing number"
                class="w-full p-3 bg-white rounded-lg border border-slate-200 focus:border-linkup-blue outline-none"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-600 mb-2">Account Type</label>
              <p class="text-xs text-slate-500 mb-2">Select whether this is a checking or savings account.</p>
              <select
                v-model="bank.accountType"
                class="w-full p-3 bg-white rounded-lg border border-slate-200 focus:border-linkup-blue outline-none"
              >
                <option value="">Select account type</option>
                <option value="checking">Checking</option>
                <option value="savings">Savings</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4">
        <button
          @click="$emit('close')"
          class="flex-1 py-4 border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50 transition-colors"
        >
          Cancel
        </button>
        <button
          @click="handleSubmit"
          class="flex-1 py-4 bg-linkup-dark text-white rounded-xl font-bold hover:brightness-110 transition-colors"
        >
          Save Bank Details
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps<{
  existingBankDetails?: {
    paypal_id?: string | null;
    banks?: any[];
  };
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'submit', data: { paypalId: string; banks: any[] }): void;
}>();

const paypalId = ref('');
const defaultBankIndex = ref(0);
const banks = ref([
  {
    name: '',
    accountNumber: '',
    routingNumber: '',
    accountType: '',
    isDefault: false
  }
]);

// Pre-fill form with existing data when component mounts or props change
watch(() => props.existingBankDetails, (newData) => {
  paypalId.value = newData?.paypal_id || '';
  
  if (newData?.banks && newData.banks.length > 0) {
    banks.value = newData.banks.map((bank: any, index: number) => ({
      name: bank.bank_name || '',
      accountNumber: bank.account_number || '',
      routingNumber: bank.routing_number || '',
      accountType: bank.account_type || '',
      isDefault: bank.is_default || false
    }));
    
    // Set default bank index
    const defaultIndex = banks.value.findIndex((bank) => bank.isDefault);
    defaultBankIndex.value = defaultIndex !== -1 ? defaultIndex : 0;
  } else {
    // Reset to empty form if no banks
    banks.value = [
      {
        name: '',
        accountNumber: '',
        routingNumber: '',
        accountType: '',
        isDefault: false
      }
    ];
    defaultBankIndex.value = 0;
  }
}, { immediate: true, deep: true });

const addBank = () => {
  banks.value.push({
    name: '',
    accountNumber: '',
    routingNumber: '',
    accountType: '',
    isDefault: false
  });
};

const removeBank = (index: number) => {
  banks.value.splice(index, 1);
  
  // If we removed the default bank, reset default to 0
  if (defaultBankIndex.value === index) {
    defaultBankIndex.value = 0;
  } else if (defaultBankIndex.value > index) {
    defaultBankIndex.value--;
  }
};

const handleSubmit = () => {
  const data = {
    paypalId: paypalId.value,
    banks: banks.value
      .filter(bank => bank.name || bank.accountNumber || bank.routingNumber)
      .map((bank, index) => ({
        name: bank.name,
        accountNumber: bank.accountNumber,
        routingNumber: bank.routingNumber,
        accountType: bank.accountType,
        isDefault: index === defaultBankIndex.value
      }))
  };
  
  emit('submit', data);
};
</script>
