<script setup lang="ts">
import { Receipt } from 'lucide-vue-next';

const billsPaid = [
    { id: "BILL-9901", company: "BTC (Bahamas Telecommunications)", account: "ACC-2201933", amount: 89.50, status: "Paid", date: "Dec 12, 2025" },
    { id: "BILL-9902", company: "BEC (Bahamas Electric)", account: "ACC-1088331", amount: 143.20, status: "Paid", date: "Dec 08, 2025" },
    { id: "BILL-9903", company: "WSC (Water & Sewerage)", account: "ACC-3307721", amount: 52.00, status: "Paid", date: "Nov 28, 2025" },
];

</script>
<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <div
            class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5 trim">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Bill Payment</div>
                    <div class="text-sm text-slate-500 mt-1">Bills paid history</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-100 grid place-items-center">
                    <Receipt class="w-5 h-5 text-amber-700" />
                </div>
            </div>

            <div class="mt-4 relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                <div class="text-xs text-slate-500 font-black">Total paid (demo)</div>
                <div class="mt-1 text-4xl font-black">$324</div>
            </div>
        </div>

        <div
            class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5 lg:col-span-2 trim">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Bills Paid (demo)</div>
                    <div class="text-sm text-slate-500 mt-1">Company + account # + amount</div>
                </div>

                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-amber-50 border-amber-200 text-amber-800">9</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">ID</th>
                            <th class="text-left px-4 py-3 font-black">Company</th>
                            <th class="text-left px-4 py-3 font-black">Account #</th>
                            <th class="text-left px-4 py-3 font-black">Amount</th>
                            <th class="text-left px-4 py-3 font-black">Status</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in billsPaid">
                            <td class="px-4 py-3 align-top font-black">{{ item.id }}</td>
                            <td class="px-4 py-3 align-top font-black">{{ item.company }}</td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">{{ item.account }}</td>
                            <td class="px-4 py-3 align-top font-black">{{ item.amount }}</td>
                            <td class="px-4 py-3 align-top"><span
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-emerald-200 rounded-full px-[0.65rem] py-[0.35rem] bg-emerald-50 text-xs font-extrabold text-emerald-700">{{ item.status }}</span>
                            </td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">{{ item.date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>