<script setup lang="ts">
const saveSystemControls = () => {
    alert('System controls saved');
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">System Settings</h3>
            <p class="text-slate-500">Operational settings, maintenance mode, logs, backups and environment status.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Environment</p><h3 class="text-3xl font-black">Production</h3></div>
            <div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Database</p><h3 class="text-3xl font-black text-green-600">Online</h3></div>
            <div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">API Latency</p><h3 class="text-3xl font-black">112ms</h3></div>
            <div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Last Backup</p><h3 class="text-3xl font-black">2 hrs</h3></div>
        </div>
        <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
            <div class="card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">Maintenance Controls</h3>
                <div class="space-y-3">
                    <label class="flex justify-between rounded-2xl bg-slate-50 p-4 font-bold">Maintenance Mode<input type="checkbox" /></label>
                    <label class="flex justify-between rounded-2xl bg-slate-50 p-4 font-bold">Freeze Payouts<input type="checkbox" /></label>
                    <label class="flex justify-between rounded-2xl bg-slate-50 p-4 font-bold">Disable New Signups<input type="checkbox" /></label>
                    <button @click="saveSystemControls" class="rounded-2xl bg-slate-950 text-white px-5 py-3 font-black">Save System Controls</button>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">Recent System Logs</h3>
                <div class="space-y-3 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3">10:42 AM — Backup completed successfully</div>
                    <div class="rounded-xl bg-slate-50 p-3">10:38 AM — API health check passed</div>
                    <div class="rounded-xl bg-slate-50 p-3">10:31 AM — Admin updated SMTP setting</div>
                    <div class="rounded-xl bg-slate-50 p-3">10:18 AM — Feature flag changed: Cryptocurrency Future</div>
                </div>
            </div>
        </div>
    </div>
</template>
