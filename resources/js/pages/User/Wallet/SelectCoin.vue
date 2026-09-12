<template>
    <AuthenticatedLayout>

        <Head title="Wallet" />
        <div class="flex items-center justify-center mb-12 mt-12">
            <img src="/images/wallet.jpg" class="rounded-4xl" />
        </div>
        <main
            class="flex flex-col items-center mx-auto max-w-[1000px] rounded-lg bg-stone-50 py-10 px-4 max-md:w-full max-md:px-2">
            <div class="flex text-center items-center gap-2 mb-2">
                <Coins color="black" class="w-10 h-10" />
                <h1 class="text-3xl font-bold text-black">{{ props.currentCoin }}</h1>
            </div>
            <div>
                <p class="text-black mt-2">Your Current Coins</p>
            </div>
            <hr class="text-secondary w-full max-md:w-full max-w-none mx-auto mt-4" />
            <h1 class="text-3xl font-bold text-black mb-4 mt-2">Select Coins</h1>

            <!-- Grid of predefined coins -->
            <div class="grid grid-cols-4 gap-4 mb-6 w-full max-w-md">
                <button v-for="coin in predefinedCoins" :key="coin" @click="selectCoin(coin)" :class="[
                    'border border-blue-400 py-2 rounded-md font-semibold',
                    selectedCoinValue === coin
                        ? 'bg-blue-200 text-blue-800'
                        : 'bg-white text-blue-600',
                ]">
                    {{ coin }}
                </button>
            </div>

            <!-- Custom coin input -->
            <div class="mb-6 w-full max-w-md">
                <input type="number" v-model.number="customCoin" placeholder="Or enter custom coin"
                    class="w-full border text-black border-gray-300 rounded-md px-4 py-2" @input="selectCustomCoin" />
            </div>

            <!-- Add Amount Button -->
            <Button class="bg-primary-front w-full max-w-md text-black font-semibold" @click="submitCoin">
                <CirclePlus color="black" />Add Coin
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
import { Coins } from "lucide-vue-next";

const props = defineProps<{
    currentCoin: string;
}>();

const form = useForm({
    selectedCoin: "",
});

const predefinedCoins = [50, 60, 70, 80, 90, 100, 110, 120];
const selectedCoinValue = ref<number | null>(null);
const customCoin = ref<number | null>(null);

function selectCoin(coin: number) {
    selectedCoinValue.value = coin;
    customCoin.value = null;
}

function selectCustomCoin() {
    selectedCoinValue.value = customCoin.value;
}

function submitCoin() {
    if (selectedCoinValue.value) {
        form.selectedCoin = selectedCoinValue.value;
        form.post(route("frontend.wallet.coin.checkout"), {
            forceFormData: true,
        });
    }
}
</script>


<style scoped></style>
