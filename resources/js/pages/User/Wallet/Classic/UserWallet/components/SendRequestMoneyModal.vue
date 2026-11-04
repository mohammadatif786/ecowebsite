<template>
    <div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden">
        <div class="flex items-start justify-between px-8 pt-8 pb-4">
            <h3 class="text-2xl font-bold text-slate-900">Send Requested Money</h3>
            <button class="p-2 rounded-full hover:bg-slate-100" @click="$emit('close')">
                <X class="w-5 h-5" />
            </button>
        </div>

        <div class="px-8 pb-8 space-y-4">
            <div class="space-y-2">
                <label class="text-[11px] font-bold uppercase text-slate-500">Requested Amount</label>
                <input type="number" readonly="true" :value="request.amount"
                    class="w-full h-16 rounded-2xl bg-slate-50 border border-slate-100 px-4 text-3xl font-bold text-slate-500 focus:outline-none focus:ring-2 focus:ring-linkup-blue" />
            </div>
            <div class="space-y-2">
                <label class="text-[11px] font-bold uppercase text-slate-500">Amount</label>
                <input v-model="amount" type="number" placeholder="0.00"
                    class="w-full h-16 rounded-2xl bg-slate-50 border border-slate-100 px-4 text-3xl font-bold text-slate-500 focus:outline-none focus:ring-2 focus:ring-linkup-blue" />
            </div>
            <button
                class="w-full h-14 rounded-2xl bg-linkup-blue text-white font-bold text-lg shadow-sm hover:brightness-105 transition-colors"
                @click="handleSubmit()">
                Accept & Send
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { X } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps<{
    request: Record<string, any>;
    currentBalanceUsd: number;
}>();
const amount = ref('');
const handleSubmit = () => {

    if (parseFloat(amount.value) > props.currentBalanceUsd) {
        alert('Insufficient balance to send the requested amount.')
        return
    }

    const form = useForm({
        amount: parseFloat(amount.value),
    });

    form.post(route('frontend.user.send.requested.money', props.request.id), {
        onSuccess: () => {
            alert('Money sent successfully!');
            emit('close');
        },
        onError: (errors) => {
            if (errors.amount) {
                alert('Please enter a valid amount.');
            } else if (errors.recipientId) {
                alert('Invalid recipient selected.');
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
