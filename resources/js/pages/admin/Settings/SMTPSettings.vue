<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

type SmtpSettingsValue = {
    host: string;
    port: string;
    encryption: 'TLS' | 'SSL' | 'None';
    from_name: string;
    username: string;
    has_password?: boolean;
};

const props = defineProps<{
    initialSmtpSettings?: Partial<SmtpSettingsValue>;
}>();

const hasStoredPassword = ref(!!props.initialSmtpSettings?.has_password);

const form = useForm({
    host: props.initialSmtpSettings?.host ?? '',
    port: props.initialSmtpSettings?.port ?? '587',
    encryption: props.initialSmtpSettings?.encryption ?? 'TLS',
    from_name: props.initialSmtpSettings?.from_name ?? 'LinkUp',
    username: props.initialSmtpSettings?.username ?? '',
    password: '',
});

const updateSmtpSetting = () => {
    form.put(route('admin.settings.smtp.update'), {
        preserveScroll: true,
        onSuccess: () => {
            if (form.password) hasStoredPassword.value = true;
            form.password = '';
        },
    });
};

const testing = ref(false);
const testResult = ref<{ success: boolean; message: string } | null>(null);

const sendTestEmail = async () => {
    testing.value = true;
    testResult.value = null;
    try {
        const { data } = await axios.post(route('admin.settings.smtp.test'), form.data());
        testResult.value = { success: true, message: data.message };
    } catch (error: any) {
        testResult.value = {
            success: false,
            message: error?.response?.data?.message ?? 'Failed to send test email.',
        };
    } finally {
        testing.value = false;
    }
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">SMTP Settings</h3>
            <p class="text-slate-500">Configure outbound email delivery for notifications, confirmations, receipts, and admin campaigns.</p>
        </div>
        <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
            <div class="2xl:col-span-2 card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">SMTP Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="font-bold text-slate-600">Host
                        <input v-model="form.host" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        <span v-if="form.errors.host" class="mt-1 block text-sm text-rose-600">{{ form.errors.host }}</span>
                    </label>
                    <label class="font-bold text-slate-600">Port
                        <input v-model="form.port" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        <span v-if="form.errors.port" class="mt-1 block text-sm text-rose-600">{{ form.errors.port }}</span>
                    </label>
                    <label class="font-bold text-slate-600">Encryption
                        <select v-model="form.encryption" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                            <option>TLS</option>
                            <option>SSL</option>
                            <option>None</option>
                        </select>
                    </label>
                    <label class="font-bold text-slate-600">From Name
                        <input v-model="form.from_name" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="font-bold text-slate-600">Username
                        <input v-model="form.username" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        <span v-if="form.errors.username" class="mt-1 block text-sm text-rose-600">{{ form.errors.username }}</span>
                    </label>
                    <label class="font-bold text-slate-600">Password
                        <input v-model="form.password" type="password" :placeholder="hasStoredPassword ? 'Leave blank to keep current password' : ''" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                </div>
                <div class="mt-5 flex gap-3">
                    <button @click="updateSmtpSetting" :disabled="form.processing" class="rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-5 py-3 font-black disabled:opacity-60">Update SMTP Setting</button>
                    <button @click="sendTestEmail" :disabled="testing" class="rounded-2xl bg-white border border-slate-200 px-5 py-3 font-black disabled:opacity-60">{{ testing ? 'Sending…' : 'Send Test Email' }}</button>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">Connection Test</h3>
                <div class="space-y-3">
                    <div v-if="!testResult" class="rounded-2xl bg-slate-50 p-4">
                        <b>Status</b>
                        <p class="text-slate-500 font-bold">Not tested yet</p>
                    </div>
                    <div v-else :class="testResult.success ? 'bg-green-50' : 'bg-rose-50'" class="rounded-2xl p-4">
                        <b>Status</b>
                        <p :class="testResult.success ? 'text-green-700' : 'text-rose-700'" class="font-bold">{{ testResult.success ? 'Connected' : 'Failed' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ testResult.message }}</p>
                    </div>
                    <p class="text-sm text-slate-500">Sending a test email uses the credentials above (or the saved password if left blank) without changing your app's live mail configuration.</p>
                </div>
            </div>
        </div>
    </div>
</template>
