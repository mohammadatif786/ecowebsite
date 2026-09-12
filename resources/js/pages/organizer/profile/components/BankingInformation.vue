<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { toast } from "vue-sonner";
import { Plus, Minus, Loader2, Trash2 } from 'lucide-vue-next';

const page = usePage()

const props = defineProps<{
    organizer_bank_accounts: Array<{
        id: number;
        organizer_id: number;
        bank_name: string;
        account_number: string;
        routing_number: string;
        paypal_id?: string;
    }> | null;
}>();

const isOpen = ref(!!props.organizer_bank_accounts);

// Fill banks from props if available
const banks = ref(
    props.organizer_bank_accounts && props.organizer_bank_accounts.length
        ? props.organizer_bank_accounts.map(b => ({
            id: b.id,
            bank_name: b.bank_name,
            account_number: b.account_number,
            routing_number: b.routing_number,
            isEditing: false
        }))
        : [{ id: Date.now(), bank_name: "", account_number: "", routing_number: "", isEditing: true }]
);

const form = useForm({
    paypal_id: props.organizer_bank_accounts && props.organizer_bank_accounts[0]?.paypal_id
        ? props.organizer_bank_accounts[0].paypal_id
        : "",
    banks: banks.value
});

const addBank = () => {
    banks.value.push({ id: Date.now(), bank_name: "", account_number: "", routing_number: "", isEditing: true });
};

const removeBank = (index: number) => {
    if (banks.value.length <= 1) {
        toast.error("Keep at least one bank on file");
        return;
    }
    banks.value.splice(index, 1);
};

const handleSubmit = () => {
    form.banks = banks.value;

    form.post(route('organizer.profile.update', { organizer_profile_type: 'profileBankAccounts' }), {
        onSuccess: () => {
            toast.success("Banking information updated successfully!");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
            toast.error("Failed to update banking information.");
        },
    });
};
</script>

<template>
    <div class="card p-6 bg-white border border-slate-100 rounded-[20px] shadow-sm mb-4">
        <!-- Header matching orgCardHeader reference line 5007 -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-black text-slate-800">Banking Information</h3>
                <div class="h-[3px] rounded-full mt-1.5 max-w-full bg-gradient-to-r from-indigo-500 to-amber-400"></div>
            </div>
            <button @click="isOpen = !isOpen"
                class="h-9 w-9 rounded-full text-white grid place-items-center shrink-0 ml-3 bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                <Plus v-if="!isOpen" class="w-4 h-4" />
                <Minus v-else class="w-4 h-4" />
            </button>
        </div>

        <p class="text-slate-400 text-sm font-bold mb-4 uppercase tracking-tight" v-if="!isOpen">
            Provide your banking details for payouts.
        </p>

        <div v-show="isOpen" class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">

            <div class="space-y-4">
                <p class="font-black text-indigo-600 mb-2 flex items-center gap-2">
                    <span class="text-xl">○</span> Bank Accounts
                </p>

                <div v-for="(bank, index) in banks" :key="bank.id"
                    class="rounded-2xl border border-slate-100 p-5 bg-slate-50/30 relative group">

                    <div class="flex items-center justify-between mb-4">
                        <p class="font-black text-slate-800 text-sm uppercase">Bank Account {{ index + 1 }}</p>
                        <button @click="removeBank(index)"
                            class="px-4 py-1.5 rounded-lg text-[10px] font-black text-white bg-rose-500 hover:bg-rose-600 uppercase transition shadow-sm">
                            Remove
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-black text-slate-400 block mb-1 ml-1 uppercase">Bank Name <span class="text-rose-500">*</span></label>
                            <input v-model="bank.bank_name" type="text" placeholder="Bank name"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                            <p v-if="form.errors[`banks.${index}.bank_name`]" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors[`banks.${index}.bank_name`] }} </p>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 block mb-1 ml-1 uppercase">Account Number <span class="text-rose-500">*</span></label>
                            <input v-model="bank.account_number" type="text" placeholder="Account number"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                            <p v-if="form.errors[`banks.${index}.account_number`]" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors[`banks.${index}.account_number`] }} </p>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 block mb-1 ml-1 uppercase">Routing Number <span class="text-rose-500">*</span></label>
                            <input v-model="bank.routing_number" type="text" placeholder="Routing number"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                            <p v-if="form.errors[`banks.${index}.routing_number`]" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors[`banks.${index}.routing_number`] }} </p>
                        </div>
                    </div>
                </div>

                <button @click="addBank"
                    class="w-full py-3 rounded-xl border-2 border-dashed border-slate-200 text-slate-400 hover:text-indigo-500 hover:border-indigo-200 text-sm font-black transition-all flex items-center justify-center gap-2">
                    <Plus class="w-4 h-4" />
                    Add Additional Bank
                </button>
            </div>

            <div class="rounded-2xl border border-slate-100 p-5 bg-slate-50/30">
                <label class="text-xs font-black text-slate-500 uppercase ml-1">PayPal ID (Email) <span class="text-rose-500">*</span></label>
                <input v-model="form.paypal_id" placeholder="Enter PayPal email"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                <p v-if="form.errors.paypal_id" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.paypal_id }} </p>
            </div>

            <div class="flex justify-end pt-2">
                <button @click="handleSubmit" :disabled="form.processing"
                    class="px-10 py-2.5 rounded-full font-black text-slate-900 transition hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 bg-amber-400">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    Update
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}
</style>
