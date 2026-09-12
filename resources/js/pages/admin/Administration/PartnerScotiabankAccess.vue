<script setup lang="ts">
import { reactive, ref } from 'vue';

type ScotiaCreds = {
    user: string;
    pass: string;
};

const emit = defineEmits<{
    (event: 'change-view', viewId: string): void;
}>();

const loadCreds = (): ScotiaCreds => {
    if (typeof window === 'undefined') {
        return { user: 'partner@scotiabank.com', pass: 'scotia2026' };
    }

    try {
        const saved = JSON.parse(localStorage.getItem('linkupScotiaCreds') || 'null');
        if (saved?.user && saved?.pass) return saved;
    } catch {
        // Keep default credentials if local storage is malformed.
    }

    return { user: 'partner@scotiabank.com', pass: 'scotia2026' };
};

const creds = reactive<ScotiaCreds>(loadCreds());
const note = ref('');
const noteTone = ref<'default' | 'success' | 'error'>('default');

const saveScotiaAccess = () => {
    const user = creds.user.trim();
    const pass = creds.pass.trim();

    if (!user || !pass) {
        note.value = 'Enter both a username and password.';
        noteTone.value = 'error';
        return;
    }

    creds.user = user;
    creds.pass = pass;
    localStorage.setItem('linkupScotiaCreds', JSON.stringify({ user, pass }));
    note.value = 'Scotiabank portal credentials updated.';
    noteTone.value = 'success';
};

const noteClass = () => {
    if (noteTone.value === 'success') return 'text-green-600 font-bold';
    if (noteTone.value === 'error') return 'text-rose-600 font-bold';
    return 'text-slate-500';
};
</script>

<template>
    <section class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">Partner Access - Scotiabank Portal</h3>
            <p class="text-slate-500">Set the login username and password for the Scotiabank Partner Portal.</p>
        </div>

        <div class="card max-w-lg space-y-4 rounded-3xl p-6">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-2xl bg-red-600 font-black text-white">S</div>
                <h3 class="text-xl font-black">Scotiabank Login Credentials</h3>
            </div>

            <div>
                <label class="text-sm font-bold text-slate-600">Username / Email</label>
                <input
                    v-model="creds.user"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none"
                />
            </div>

            <div>
                <label class="text-sm font-bold text-slate-600">Password</label>
                <input
                    v-model="creds.pass"
                    type="password"
                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button @click="saveScotiaAccess" class="rounded-2xl bg-slate-950 px-5 py-2 font-black text-white">Save Credentials</button>
                <button @click="emit('change-view', 'scotiaPortal')" class="rounded-2xl border border-slate-200 px-5 py-2 font-black">Open Portal</button>
                <span v-if="note" class="text-sm" :class="noteClass()">{{ note }}</span>
            </div>

            <p class="text-xs text-slate-400">
                These credentials gate the Scotiabank Partner Portal. In production this would be backed by server-side authentication.
            </p>
        </div>
    </section>
</template>
