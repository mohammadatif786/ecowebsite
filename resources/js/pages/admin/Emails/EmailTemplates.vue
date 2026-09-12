<script setup lang="ts">
import { computed, ref } from 'vue';
import { Bot, FileText, Sparkles } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

const emit = defineEmits<{
    (event: 'view-changed', id: string): void;
}>();

type EmailTemplate = {
    name: string;
    type: string;
    subject: string;
    used: string;
    open: string;
    status: 'Active' | 'Draft';
};

const templates = ref<EmailTemplate[]>([
    { name: 'Welcome to LinkUp', type: 'Onboarding', subject: 'Welcome to LinkUp - your Caribbean connection starts here', used: '2026-06-01', open: '48.2%', status: 'Active' },
    { name: 'Event Promotion Blast', type: 'Events', subject: 'This weekend on LinkUp: tickets are moving fast', used: '2026-05-30', open: '44.6%', status: 'Active' },
    { name: 'Wallet Feature Notice', type: 'E-Wallet', subject: 'Your LinkUp Wallet is ready for transfers and payments', used: '2026-05-28', open: '39.9%', status: 'Active' },
    { name: 'Marketplace Sale', type: 'Marketplace', subject: 'New Caribbean products just landed in Marketplace', used: '2026-05-26', open: '36.4%', status: 'Draft' },
    { name: 'Security Notice', type: 'Compliance', subject: 'Important security update for your LinkUp account', used: '2026-05-21', open: '57.1%', status: 'Active' },
]);

const search = ref('');
const aiName = ref('AI Caribbean Campaign Template');
const aiGoal = ref('event promotion');
const aiAudience = ref('all LinkUp customers');
const aiTone = ref('professional and warm');
const aiCta = ref('Open LinkUp');
const aiSubject = ref('');
const aiBody = ref('');
const aiNote = ref('');

const filteredTemplates = computed(() => {
    const q = search.value.toLowerCase();
    return templates.value.filter((template) => JSON.stringify(template).toLowerCase().includes(q));
});

const generateTemplate = () => {
    aiSubject.value = `LinkUp Update: ${aiGoal.value.charAt(0).toUpperCase()}${aiGoal.value.slice(1)}`;
    aiBody.value = `Dear LinkUp Member,

We are reaching out to ${aiAudience.value} with an important LinkUp update about ${aiGoal.value}.

This message is written in a ${aiTone.value} tone and is designed to be clear, helpful, and action-focused. LinkUp is continuing to connect customers across events, marketplace, wallet services, community updates, and Caribbean experiences.

${aiCta.value} to view the latest details inside the LinkUp app.

Thank you for being part of the LinkUp community.

The LinkUp Team`;
    aiNote.value = 'AI template generated and copied into the email composer fields.';
};

const saveTemplate = () => {
    templates.value.unshift({
        name: aiName.value || 'AI Generated Template',
        type: 'AI Generated',
        subject: aiSubject.value || 'LinkUp Update',
        used: 'Draft',
        open: '-',
        status: 'Active',
    });
    aiNote.value = 'Template saved in simulation and added to the template list.';
    toast.success('Email template saved.');
};

const useTemplate = () => {
    emit('view-changed', 'sendEmailCommand');
};
</script>

<template>
    <div class="space-y-6">
        <Toaster rich-colors position="top-right" />

        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Email Templates</h3>
                <p class="text-slate-500">Create, store, and reuse email templates. AI can draft the subject and body for campaigns.</p>
            </div>
            <button class="rounded-2xl bg-purple-600 px-5 py-3 font-black text-white" @click="generateTemplate">
                <Sparkles class="mr-2 inline h-4 w-4" /> Create AI Template
            </button>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="card rounded-3xl p-5"><h3 class="text-4xl font-black">{{ templates.length }}</h3><p class="font-bold text-slate-500">Templates</p></div>
            <div class="card rounded-3xl p-5"><h3 class="text-4xl font-black">5</h3><p class="font-bold text-slate-500">Campaign Types</p></div>
            <div class="card rounded-3xl p-5"><h3 class="text-4xl font-black">38</h3><p class="font-bold text-slate-500">Sent Campaigns</p></div>
            <div class="card rounded-3xl p-5"><h3 class="text-4xl font-black">41.8%</h3><p class="font-bold text-slate-500">Average Open Rate</p></div>
        </div>

        <section class="card overflow-hidden rounded-3xl">
            <div class="flex items-center gap-3 bg-gradient-to-r from-slate-950 to-purple-700 p-5 text-white">
                <Bot class="h-6 w-6" />
                <h3 class="text-xl font-black">AI Template Builder</h3>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 xl:grid-cols-2">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="font-bold text-slate-600">Template Name
                            <input v-model="aiName" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                        </label>
                        <label class="font-bold text-slate-600">Campaign Goal
                            <select v-model="aiGoal" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option>event promotion</option><option>wallet update</option><option>marketplace sale</option><option>security notice</option><option>payout confirmation</option><option>new feature announcement</option><option>inactive customer reactivation</option>
                            </select>
                        </label>
                        <label class="font-bold text-slate-600">Audience
                            <select v-model="aiAudience" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option>all LinkUp customers</option><option>Bahamas customers</option><option>Jamaica customers</option><option>Caribbean island users</option><option>United States diaspora users</option><option>event organizers</option><option>wallet users</option><option>marketplace buyers</option>
                            </select>
                        </label>
                        <label class="font-bold text-slate-600">Tone
                            <select v-model="aiTone" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option>professional and warm</option><option>exciting and promotional</option><option>formal and compliance-focused</option><option>friendly Caribbean tone</option><option>short and urgent</option>
                            </select>
                        </label>
                    </div>
                    <label class="block font-bold text-slate-600">Call to Action
                        <input v-model="aiCta" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <button class="rounded-2xl bg-purple-600 px-5 py-3 font-black text-white" @click="generateTemplate">
                        <Sparkles class="mr-2 inline h-4 w-4" /> Generate Template
                    </button>
                </div>

                <div class="space-y-4">
                    <label class="block font-bold text-slate-600">AI Subject
                        <input v-model="aiSubject" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="AI generated subject appears here" />
                    </label>
                    <label class="block font-bold text-slate-600">AI Email Body
                        <textarea v-model="aiBody" class="mt-2 min-h-72 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="AI generated body appears here"></textarea>
                    </label>
                    <div class="flex flex-wrap gap-3">
                        <button class="rounded-2xl bg-slate-950 px-5 py-3 font-black text-white" @click="saveTemplate">Save Template</button>
                        <button class="rounded-2xl bg-lime-400 px-5 py-3 font-black text-slate-950" @click="useTemplate">Use In Composer</button>
                        <p class="self-center text-sm font-bold text-green-600">{{ aiNote }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="card rounded-3xl p-6">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center">
                <FileText class="h-6 w-6 text-purple-600" />
                <h3 class="flex-1 text-xl font-black">Saved Templates</h3>
                <input v-model="search" class="w-full rounded-2xl border border-slate-200 px-4 py-3 md:w-96" placeholder="Search templates..." />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-3">Template</th><th>Type</th><th>Subject</th><th>Last Used</th><th>Open Rate</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <tr v-for="template in filteredTemplates" :key="template.name + template.subject" class="border-t">
                            <td class="py-4 font-black">{{ template.name }}</td>
                            <td>{{ template.type }}</td>
                            <td>{{ template.subject }}</td>
                            <td>{{ template.used }}</td>
                            <td>{{ template.open }}</td>
                            <td><span class="rounded-full px-3 py-1 text-xs font-black" :class="template.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">{{ template.status }}</span></td>
                            <td><button class="font-black text-purple-600" @click="useTemplate">Use</button> <span class="text-slate-300">-</span> Edit <span class="text-slate-300">-</span> Delete</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}
</style>
