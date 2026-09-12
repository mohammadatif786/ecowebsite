<script setup lang="ts">
const saveAISettings = () => {
    alert('AI settings saved for demo.');
};

const openGlobalAI = (q: string) => {
    alert('Ask AI: ' + q);
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div><h3 class="text-3xl font-black">AI Settings</h3><p class="text-slate-500">Configure Ask AI so it can search and assist across every LinkUp module.</p></div>
            <button @click="saveAISettings" class="rounded-2xl bg-slate-950 text-white px-5 py-3 font-black">Save AI Settings</button>
        </div>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="card rounded-3xl p-6 xl:col-span-2 space-y-5">
                <h3 class="text-xl font-black">Ask AI Coverage</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="rounded-2xl border border-slate-200 p-4 flex items-center justify-between"><span><b>Event Management</b><br><small class="text-slate-500">Tickets, sponsors, coupons, fees</small></span><input type="checkbox" checked></label>
                    <label class="rounded-2xl border border-slate-200 p-4 flex items-center justify-between"><span><b>Event Organizer</b><br><small class="text-slate-500">Organizers, scanners, payouts</small></span><input type="checkbox" checked></label>
                    <label class="rounded-2xl border border-slate-200 p-4 flex items-center justify-between"><span><b>E-Wallet</b><br><small class="text-slate-500">Transactions, ASUE, bills, compliance</small></span><input type="checkbox" checked></label>
                    <label class="rounded-2xl border border-slate-200 p-4 flex items-center justify-between"><span><b>Marketplace</b><br><small class="text-slate-500">Orders, escrow, sellers, products</small></span><input type="checkbox" checked></label>
                    <label class="rounded-2xl border border-slate-200 p-4 flex items-center justify-between"><span><b>Caribbean 360 News</b><br><small class="text-slate-500">News, categories, authors, regions</small></span><input type="checkbox" checked></label>
                    <label class="rounded-2xl border border-slate-200 p-4 flex items-center justify-between"><span><b>Admins & Settings</b><br><small class="text-slate-500">Roles, permissions, system logs</small></span><input type="checkbox" checked></label>
                </div>
            </div>
            <div class="card rounded-3xl p-6 space-y-4">
                <h3 class="text-xl font-black">AI Controls</h3>
                <label class="block"><span class="text-sm font-bold text-slate-500">Default AI Action</span><select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"><option>Search system first</option><option>Summarize current page</option><option>Generate recommendation</option></select></label>
                <label class="block"><span class="text-sm font-bold text-slate-500">Data Access Level</span><select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"><option>Respect admin role permissions</option><option>Super admin only</option><option>Read-only demo mode</option></select></label>
                <label class="block"><span class="text-sm font-bold text-slate-500">AI Safety</span><select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"><option>Do not expose private credentials</option><option>Mask banking and KYC data</option><option>Full internal demo access</option></select></label>
            </div>
        </div>
        <div class="card rounded-3xl p-6">
            <h3 class="text-xl font-black mb-4">AI Usage Examples</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button @click="openGlobalAI('Find pending payouts and explain what action is needed')" class="rounded-2xl bg-slate-50 p-4 text-left font-bold">Find pending payouts</button>
                <button @click="openGlobalAI('Show me where ASUE Drawer Data is located')" class="rounded-2xl bg-slate-50 p-4 text-left font-bold">Locate ASUE data</button>
                <button @click="openGlobalAI('Which modules should a News Manager access?')" class="rounded-2xl bg-slate-50 p-4 text-left font-bold">Suggest role access</button>
            </div>
        </div>
    </div>
</template>
