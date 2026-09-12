<template>
  <AuthenticatedLayout>
    <Head title="Wallet" />
    <div class="flex items-center justify-center mb-12 mt-12">
      <img src="/images/wallet.jpg" class="rounded-4xl" />
    </div>
    <main
      class="flex flex-col items-center mx-auto max-w-[1000px] rounded-lg bg-stone-50 py-10 px-4 max-md:w-full max-md:px-2"
    >
      <div class="flex text-center items-center gap-2 mb-2">
        <Wallet color="black" />
        <h1 class="text-3xl font-bold text-black">${{ props.currentBalance }}</h1>
      </div>
      <div>
        <p class="text-black mt-2">Your Current Balance</p>
      </div>
      <hr class="text-secondary w-full max-md:w-full max-w-none mx-auto mt-4" />
      <h1 class="text-3xl font-bold text-black mb-4 mt-2">Select Amount</h1>

      <!-- Grid of predefined amounts -->
      <div class="grid grid-cols-4 gap-4 mb-6 w-full max-w-md">
        <button
          v-for="amount in predefinedAmounts"
          :key="amount"
          @click="selectAmount(amount)"
          :class="[
            'border border-blue-400 py-2 rounded-md font-semibold',
            selectedAmount === amount
              ? 'bg-blue-200 text-blue-800'
              : 'bg-white text-blue-600',
          ]"
        >
          {{ amount }}
        </button>
      </div>

      <!-- Custom amount input -->
      <div class="mb-6 w-full max-w-md">
        <input
          type="number"
          v-model.number="customAmount"
          placeholder="Or enter custom amount"
          class="w-full border text-black border-gray-300 rounded-md px-4 py-2"
          @input="selectCustomAmount"
        />
      </div>

      <!-- Add Amount Button -->
      <Button
        class="bg-primary-front w-full max-w-md text-black font-semibold"
        @click="submitAmount"
      >
        <CirclePlus color="black" />Add Amount
      </Button>
    </main>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { Wallet, CirclePlus } from "lucide-vue-next";
import { Button } from "@/components/admin/ui/button";
import { ref } from "vue";

const props = defineProps<{
  currentBalance: string;
}>();

const form = useForm({
  selectedAmount: "",
});

const predefinedAmounts = [50, 60, 70, 80, 90, 100, 110, 120];
const selectedAmount = ref<number | null>(null);
const customAmount = ref<number | null>(null);

function selectAmount(amount: number) {
  selectedAmount.value = amount;
  customAmount.value = null;
}

function selectCustomAmount() {
  selectedAmount.value = customAmount.value;
}

function submitAmount() {
  if (selectedAmount.value) {
    form.selectedAmount = selectedAmount.value;
    form.post(route("frontend.wallet.checkout"), {
      forceFormData: true,
    });
  }
}
</script>

<style scoped></style>
