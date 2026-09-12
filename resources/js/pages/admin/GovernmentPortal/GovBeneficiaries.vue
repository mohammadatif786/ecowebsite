<script setup lang="ts">
import { ref, computed } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps<{
    beneficiaries: any[];
    programs: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
}>();

const searchQuery = ref('');
const isModalOpen = ref(false);

const form = ref({
    name: '',
    natId: '',
    program: props.programs[0]?.name || '',
    monthly: 0,
    wallet: 'Auto-Created',
    kyc: 'Tier 1'
});

const filteredList = computed(() => {
    const q = searchQuery.value.toLowerCase();
    return props.beneficiaries.filter(b =>
        !q || [b.name, b.natId, b.program].some(v => v.toLowerCase().includes(q))
    );
});

const stats = computed(() => {
    return {
        enrolled: filteredList.value.length,
        autoCreated: filteredList.value.filter(b => b.wallet === 'Auto-Created').length,
        polDue: filteredList.value.filter(b => b.pol !== 'Verified').length,
        entitlement: filteredList.value.reduce((a, b) => a + b.monthly, 0)
    };
});

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Beneficiary Registry</h3>
                <p class="text-slate-500">
                    Citizens enrolled for government payouts, matched to a LinkUp wallet by national ID.
                    Unbanked beneficiaries get an auto-created KYC-lite wallet.
                </p>
            </div>
            <button
                @click="openModal"
                class="rounded-2xl bg-purple-600 px-5 py-3 font-black text-white hover:bg-purple-700 transition-colors"
            >
                + Enroll Beneficiary
            </button>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Enrolled (sample)</p>
                <h3 class="text-4xl font-black">{{ num(stats.enrolled) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Auto-Created Wallets</p>
                <h3 class="text-4xl font-black text-purple-600">{{ num(stats.autoCreated) }}</h3>
                <p class="text-xs text-slate-500">unbanked onboarded</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Proof-of-Life Due</p>
                <h3 class="text-4xl font-black text-amber-600">{{ num(stats.polDue) }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Monthly Entitlement</p>
                <h3 class="text-3xl font-black text-green-600">{{ fmt(stats.entitlement) }}</h3>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-xl font-black">Registry</h3>
                <input
                    v-model="searchQuery"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm sm:w-64"
                    placeholder="Search name, national ID..."
                />
            </div>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2">Beneficiary</th>
                            <th>National ID</th>
                            <th>Program</th>
                            <th>Monthly</th>
                            <th>Wallet</th>
                            <th>KYC</th>
                            <th>Proof of Life</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in filteredList" :key="b.id" class="border-t align-top">
                            <td class="py-2 font-black">{{ b.name }}</td>
                            <td class="text-slate-500">{{ b.natId }}</td>
                            <td>{{ b.program }}</td>
                            <td class="font-black">{{ fmt(b.monthly) }}</td>
                            <td>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-xs font-black"
                                    :class="{
                                        'bg-green-50 text-green-700': b.wallet === 'Linked',
                                        'bg-purple-50 text-purple-700': b.wallet === 'Auto-Created',
                                        'bg-amber-50 text-amber-700': b.wallet === 'Pending'
                                    }"
                                >
                                    {{ b.wallet }}
                                </span>
                            </td>
                            <td>{{ b.kyc }}</td>
                            <td>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-xs font-black"
                                    :class="{
                                        'bg-green-50 text-green-700': b.pol === 'Verified',
                                        'bg-amber-50 text-amber-700': b.pol === 'Due',
                                        'bg-rose-50 text-rose-700': b.pol === 'Overdue'
                                    }"
                                >
                                    {{ b.pol }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <button class="rounded-lg bg-sky-100 px-2 py-1 text-xs font-black text-sky-700">Edit</button>
                                    <button class="rounded-lg bg-rose-100 px-2 py-1 text-xs font-black text-rose-700">Remove</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Enrollment Modal -->
        <Teleport to="body">
            <div
                v-if="isModalOpen"
                class="fixed inset-0 z-[10000] flex items-center justify-center p-5 bg-slate-900/50 backdrop-blur-sm"
                @click.self="closeModal"
            >
                <div class="bg-white rounded-[2rem] max-w-2xl w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
                    <!-- Modal Header -->
                    <div class="p-8 bg-gradient-to-r from-red-600 to-rose-500 text-white relative">
                        <button
                            @click="closeModal"
                            class="absolute top-6 right-6 p-2 rounded-full hover:bg-white/10 transition-colors"
                        >
                            <X class="w-6 h-6" />
                        </button>
                        <h3 class="text-3xl font-black mb-1">Enroll Beneficiary</h3>
                        <p class="text-rose-100 text-sm max-w-md">
                            Matched to a LinkUp wallet by national ID. Unbanked citizens get an auto-created KYC-lite wallet.
                        </p>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-bold text-slate-600 block mb-2">Full Name *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Enter full name"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all"
                                />
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-600 block mb-2">National ID</label>
                                <input
                                    v-model="form.natId"
                                    type="text"
                                    placeholder="Enter ID number"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all"
                                />
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-600 block mb-2">Program</label>
                                <select
                                    v-model="form.program"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold appearance-none bg-no-repeat bg-[right_1rem_center]"
                                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-size: 1.25rem;"
                                >
                                    <option v-for="p in programs" :key="p.id" :value="p.name">{{ p.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-600 block mb-2">Monthly Entitlement ($)</label>
                                <input
                                    v-model.number="form.monthly"
                                    type="number"
                                    placeholder="0.00"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all"
                                />
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-600 block mb-2">Wallet Status</label>
                                <select
                                    v-model="form.wallet"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold appearance-none bg-no-repeat bg-[right_1rem_center]"
                                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-size: 1.25rem;"
                                >
                                    <option>Auto-Created</option>
                                    <option>Linked</option>
                                    <option>Pending</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-600 block mb-2">KYC Tier</label>
                                <select
                                    v-model="form.kyc"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold appearance-none bg-no-repeat bg-[right_1rem_center]"
                                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-size: 1.25rem;"
                                >
                                    <option>Tier 1</option>
                                    <option>Tier 2</option>
                                </select>
                            </div>
                        </div>

                        <!-- Modal Footer Actions -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button
                                @click="closeModal"
                                class="rounded-2xl bg-slate-100 px-8 py-4 font-black text-slate-600 hover:bg-slate-200 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                class="rounded-2xl bg-slate-950 px-8 py-4 font-black text-white hover:bg-slate-800 transition-colors shadow-lg shadow-slate-950/20"
                            >
                                Save Beneficiary
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
