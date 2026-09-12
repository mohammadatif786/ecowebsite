<script setup lang="ts">
import { ref, computed } from "vue"
import AppLayout from "@/layouts/organizer/AppLayout.vue"
import { Head, useForm } from "@inertiajs/vue3"
import { toast } from "vue-sonner"
import { Loader2 } from 'lucide-vue-next'

const props = defineProps<{
    paypal: any;
    stripe: any;
    bankAccounts: any[];
}>()

// -------------------- State / Toggles --------------------
const paypalOpen = ref(false)
const stripeOpen = ref(false)
const bankingOpen = ref(false)

function toggleSection(section: 'paypal' | 'stripe' | 'banking') {
    if (section === 'paypal') {
        paypalOpen.value = !paypalOpen.value
        stripeOpen.value = false
        bankingOpen.value = false
    } else if (section === 'stripe') {
        stripeOpen.value = !stripeOpen.value
        paypalOpen.value = false
        bankingOpen.value = false
    } else if (section === 'banking') {
        bankingOpen.value = !bankingOpen.value
        paypalOpen.value = false
        stripeOpen.value = false
    }
}

// -------------------- PayPal --------------------
const paypalData = typeof props.paypal?.value === "string"
    ? JSON.parse(props.paypal.value)
    : props.paypal?.value || {}

const paypalForm = useForm({
    clientId: paypalData.clientId || '',
    clientSecret: paypalData.clientSecret || ''
})

const isPaypalProvided = computed(() => !!paypalForm.clientId && !!paypalForm.clientSecret)

const handlePayPalForm = () => {
    paypalForm.post(route('organizer.payout.store.paypal'), {
        onSuccess: () => {
            toast.success("✅ PayPal details saved")
            paypalOpen.value = false
        },
        onError: () => toast.error("Error updating PayPal info")
    })
}

// -------------------- Stripe --------------------
const stripeData = typeof props.stripe?.value === "string"
    ? JSON.parse(props.stripe.value)
    : props.stripe?.value || {}

const stripeForm = useForm({
    clientId: stripeData.clientId || '',
    clientSecret: stripeData.clientSecret || ''
})

const isStripeProvided = computed(() => !!stripeForm.clientId && !!stripeForm.clientSecret)

const handleStripeForm = () => {
    stripeForm.post(route('organizer.payout.store.stripe'), {
        onSuccess: () => {
            toast.success("✅ Stripe details saved")
            stripeOpen.value = false
        },
        onError: () => toast.error("Error updating Stripe info")
    })
}

// -------------------- Banking --------------------
const banks = ref(props.bankAccounts ? props.bankAccounts.map((a: any) => ({
    ...a,
    isEditing: false,
    bank_name: a.bank_name,
    account_number: a.account_number,
    routing_number: a.routing_number
})) : [])

const isBankingProvided = computed(() => banks.value.some(b => b.bank_name))

const bankForm = useForm({
    banks: banks.value
})

const handleBankForm = () => {
    bankForm.banks = banks.value
    bankForm.post(route('organizer.payout.store.bank'), {
        onSuccess: () => {
            toast.success("✅ Banking details saved")
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors)
            toast.error("Error updating bank accounts")
        }
    })
}

function editBank(account: any) {
    if (account.isEditing) {
        if (!account.bank_name || !account.account_number || !account.routing_number) {
            toast.error("Please fill all required fields");
            return;
        }
        handleBankForm();
        account.isEditing = false;
    } else {
        account.isEditing = true;
    }
}

function removeBank(id: number) {
    if (banks.value.length <= 1) {
        toast.error("Keep at least one bank on file");
        return;
    }

    const account = banks.value.find(acc => acc.id === id);
    toast.warning(`Bank account "${account.bank_name || 'Unknown'}" will be removed`, {
        action: {
            label: 'Remove',
            onClick: () => {
                banks.value = banks.value.filter(acc => acc.id !== id)
                bankForm.banks = banks.value
                handleBankForm()
            }
        },
        duration: 5000
    });
}

function addNewBank() {
    banks.value.push({
        id: Date.now(),
        bank_name: '',
        account_number: '',
        routing_number: '',
        isEditing: true
    })
}
</script>

<template>
    <Head title="Payout Methods" />

    <AppLayout>
        <div class=" mx-auto">
            <!-- Sync Header Card (Line 4801 Reference) -->
            <div class="rounded-2xl overflow-hidden mb-6 shadow-sm">
                <div class="py-5 text-center text-white font-black text-xl tracking-wide uppercase"
                     style="background:linear-gradient(90deg,#2563eb,#22d3ee)">
                    Payout Methods
                </div>
            </div>

            <div class="space-y-4">
                <!-- PayPal Section (Line 4804 Reference) -->
                <div class="card overflow-hidden bg-white border border-slate-100 rounded-[20px] shadow-sm transition-all duration-300">
                    <div class="flex items-center justify-between p-5 flex-wrap gap-3">
                        <p class="font-black text-lg text-slate-800">PayPal</p>
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-1.5 rounded-full text-[11px] font-black text-white uppercase tracking-tighter"
                                  :style="{ background: isPaypalProvided ? 'linear-gradient(90deg,#2563eb,#eab308)' : 'linear-gradient(90deg,#94a3b8,#cbd5e1)' }">
                                {{ isPaypalProvided ? 'Information provided' : 'Information not provided' }}
                            </span>
                            <button @click="toggleSection('paypal')"
                                    class="px-5 py-1.5 rounded-full text-[11px] font-black text-white uppercase transition hover:brightness-105 active:scale-95"
                                    style="background:linear-gradient(90deg,#2563eb,#22d3ee)">
                                {{ paypalOpen ? 'CLOSE' : 'OPEN' }}
                            </button>
                        </div>
                    </div>

                    <div v-show="paypalOpen" class="px-5 pb-6 animate-in fade-in slide-in-from-top-2 duration-300">
                        <div class="rounded-xl text-center py-2.5 mb-5 font-black text-white text-[13px] shadow-sm"
                             style="background:linear-gradient(90deg,#059669,#34d399)">
                             ○ Make sure that the currency matches USD
                        </div>

                        <form @submit.prevent="handlePayPalForm" class="space-y-4 max-w-xl mx-auto">
                            <div>
                                <label class="text-sm font-black text-slate-600 block mb-1">PayPal Client ID <span class="text-rose-500">*</span></label>
                                <input v-model="paypalForm.clientId" type="text" placeholder="Enter PayPal Client ID"
                                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                            </div>
                            <div>
                                <label class="text-sm font-black text-slate-600 block mb-1">PayPal Client Secret <span class="text-rose-500">*</span></label>
                                <input v-model="paypalForm.clientSecret" type="password" placeholder="Enter PayPal Client Secret"
                                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button" @click="paypalOpen = false"
                                        class="px-8 py-2.5 rounded-xl font-black text-white bg-slate-400 hover:bg-slate-500 text-xs transition uppercase">
                                    BACK
                                </button>
                                <button type="submit" :disabled="paypalForm.processing"
                                        class="px-8 py-2.5 rounded-xl font-black text-white bg-emerald-500 hover:bg-emerald-600 text-xs transition uppercase flex items-center gap-2">
                                    <Loader2 v-if="paypalForm.processing" class="w-3.5 h-3.5 animate-spin" />
                                    SAVE
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Stripe Section (Line 4821 Reference) -->
                <div class="card overflow-hidden bg-white border border-slate-100 rounded-[20px] shadow-sm transition-all duration-300">
                    <div class="flex items-center justify-between p-5 flex-wrap gap-3">
                        <p class="font-black text-lg text-slate-800">Stripe</p>
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-1.5 rounded-full text-[11px] font-black text-white uppercase tracking-tighter"
                                  :style="{ background: isStripeProvided ? 'linear-gradient(90deg,#2563eb,#eab308)' : 'linear-gradient(90deg,#94a3b8,#cbd5e1)' }">
                                {{ isStripeProvided ? 'Information provided' : 'Information not provided' }}
                            </span>
                            <button @click="toggleSection('stripe')"
                                    class="px-5 py-1.5 rounded-full text-[11px] font-black text-white uppercase transition hover:brightness-105 active:scale-95"
                                    style="background:linear-gradient(90deg,#2563eb,#22d3ee)">
                                {{ stripeOpen ? 'CLOSE' : 'OPEN' }}
                            </button>
                        </div>
                    </div>

                    <div v-show="stripeOpen" class="px-5 pb-6 animate-in fade-in slide-in-from-top-2 duration-300">
                        <div class="rounded-xl text-center py-2.5 mb-5 font-black text-white text-[13px] shadow-sm"
                             style="background:linear-gradient(90deg,#059669,#34d399)">
                             ○ Make sure that the currency matches USD
                        </div>

                        <form @submit.prevent="handleStripeForm" class="space-y-4 max-w-xl mx-auto">
                            <div>
                                <label class="text-sm font-black text-slate-600 block mb-1">Stripe Publishable Key <span class="text-rose-500">*</span></label>
                                <input v-model="stripeForm.clientId" type="text" placeholder="Enter Stripe Publishable Key"
                                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                            </div>
                            <div>
                                <label class="text-sm font-black text-slate-600 block mb-1">Stripe Secret Key <span class="text-rose-500">*</span></label>
                                <input v-model="stripeForm.clientSecret" type="password" placeholder="Enter Stripe Secret Key"
                                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button" @click="stripeOpen = false"
                                        class="px-8 py-2.5 rounded-xl font-black text-white bg-slate-400 hover:bg-slate-500 text-xs transition uppercase">
                                    BACK
                                </button>
                                <button type="submit" :disabled="stripeForm.processing"
                                        class="px-8 py-2.5 rounded-xl font-black text-white bg-emerald-500 hover:bg-emerald-600 text-xs transition uppercase flex items-center gap-2">
                                    <Loader2 v-if="stripeForm.processing" class="w-3.5 h-3.5 animate-spin" />
                                    SAVE
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Banking Section (Line 4838 Reference) -->
                <div class="card overflow-hidden bg-white border border-slate-100 rounded-[20px] shadow-sm transition-all duration-300">
                    <div class="flex items-center justify-between p-5 flex-wrap gap-3">
                        <p class="font-black text-lg text-slate-800">Banking Information</p>
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-1.5 rounded-full text-[11px] font-black text-white uppercase tracking-tighter"
                                  :style="{ background: isBankingProvided ? 'linear-gradient(90deg,#2563eb,#eab308)' : 'linear-gradient(90deg,#94a3b8,#cbd5e1)' }">
                                {{ isBankingProvided ? 'Information provided' : 'Information not provided' }}
                            </span>
                            <button @click="toggleSection('banking')"
                                    class="px-5 py-1.5 rounded-full text-[11px] font-black text-white uppercase transition hover:brightness-105 active:scale-95"
                                    style="background:linear-gradient(90deg,#2563eb,#22d3ee)">
                                {{ bankingOpen ? 'CLOSE' : 'OPEN' }}
                            </button>
                        </div>
                    </div>

                    <div v-show="bankingOpen" class="px-5 pb-6 animate-in fade-in slide-in-from-top-2 duration-300">
                        <p class="font-black text-indigo-600 mb-4 flex items-center gap-2">
                            <span class="text-xl">○</span> Bank Accounts
                        </p>

                        <div class="space-y-4 max-w-xl mx-auto">
                            <div v-for="(account, index) in banks" :key="account.id"
                                 class="rounded-2xl border border-slate-100 p-5 bg-slate-50/30">
                                <div class="flex items-center justify-between mb-4">
                                    <p class="font-black text-slate-800 text-sm">Bank Account {{ index + 1 }}</p>
                                    <div class="flex gap-2">
                                        <button @click="editBank(account)"
                                                class="px-4 py-1.5 rounded-lg text-[10px] font-black text-white uppercase transition-all shadow-sm"
                                                :class="account.isEditing ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-blue-500 hover:bg-blue-600'">
                                            {{ account.isEditing ? 'Update' : 'Edit' }}
                                        </button>
                                        <button @click="removeBank(account.id)"
                                                class="px-4 py-1.5 rounded-lg text-[10px] font-black text-white bg-rose-500 hover:bg-rose-600 uppercase transition shadow-sm">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 block mb-1 ml-1 uppercase">Bank Name <span class="text-rose-500">*</span></label>
                                        <input v-model="account.bank_name" type="text" :readonly="!account.isEditing"
                                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold outline-none transition focus:border-indigo-400"
                                               :class="!account.isEditing ? 'bg-slate-100/50 text-slate-500' : 'bg-white'"
                                               placeholder="Enter Bank Name" />
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 block mb-1 ml-1 uppercase">Account Number <span class="text-rose-500">*</span></label>
                                        <input v-model="account.account_number" type="text" :readonly="!account.isEditing"
                                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold outline-none transition focus:border-indigo-400"
                                               :class="!account.isEditing ? 'bg-slate-100/50 text-slate-500' : 'bg-white'"
                                               placeholder="Enter Account Number" />
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 block mb-1 ml-1 uppercase">Routing Number <span class="text-rose-500">*</span></label>
                                        <input v-model="account.routing_number" type="text" :readonly="!account.isEditing"
                                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold outline-none transition focus:border-indigo-400"
                                               :class="!account.isEditing ? 'bg-slate-100/50 text-slate-500' : 'bg-white'"
                                               placeholder="Enter Routing Number" />
                                    </div>
                                </div>
                            </div>

                            <button @click="addNewBank"
                                    class="w-full py-3 rounded-xl border-2 border-dashed border-slate-200 text-slate-400 hover:text-indigo-500 hover:border-indigo-200 text-sm font-black transition-all flex items-center justify-center gap-2">
                                <Plus class="w-4 h-4" />
                                Add Additional Bank
                            </button>

                            <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                                <button @click="bankingOpen = false"
                                        class="px-8 py-2.5 rounded-xl font-black text-white bg-slate-400 hover:bg-slate-500 text-xs transition uppercase">
                                    BACK
                                </button>
                                <button @click="handleBankForm" :disabled="bankForm.processing"
                                        class="px-8 py-2.5 rounded-xl font-black text-white bg-emerald-500 hover:bg-emerald-600 text-xs transition uppercase flex items-center gap-2">
                                    <Loader2 v-if="bankForm.processing" class="w-3.5 h-3.5 animate-spin" />
                                    SAVE CHANGES
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
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
