<script setup lang="ts">
// Verify email: the page a new member lands on after "Create account".
// Nothing else in LinkUp opens until the 6-digit code is entered. The server enforces that;
// this page only collects the code.
import AuthLayout from '@/components/User/Signup/AuthLayout.vue';
import CodeInput from '@/components/User/Signup/CodeInput.vue';
import FieldInput from '@/components/User/Signup/FieldInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    email: string;
    codeLength: number;
    ttlMinutes: number;
    resendAvailableIn: number;
    status?: string | null;
    urls: { verify: string; resend: string; change: string; logout: string };
}>();

const form = useForm({ code: '' });
const changeForm = useForm({ email: '' });

const showChange = ref(false);
const resendError = ref<string | null>(null);
const wait = ref(props.resendAvailableIn);
let timer: number | undefined;

watch(
    () => props.resendAvailableIn,
    (seconds) => {
        wait.value = seconds;
    },
);

onMounted(() => {
    timer = window.setInterval(() => {
        if (wait.value > 0) wait.value -= 1;
    }, 1000);
});
onBeforeUnmount(() => window.clearInterval(timer));

const canResend = computed(() => wait.value <= 0);
const ready = computed(() => form.code.length === props.codeLength);

function submit() {
    if (form.processing || !ready.value) return;
    form.post(props.urls.verify, {
        preserveScroll: true,
        onError: () => {
            form.code = '';
        },
    });
}

function resend() {
    if (!canResend.value) return;
    resendError.value = null;
    router.post(
        props.urls.resend,
        {},
        {
            preserveScroll: true,
            onError: (errors) => {
                resendError.value = errors.code ?? 'We could not send a new code. Try again in a moment.';
            },
        },
    );
}

function changeEmail() {
    changeForm.post(props.urls.change, {
        preserveScroll: true,
        onSuccess: () => {
            showChange.value = false;
            changeForm.reset();
        },
    });
}

function signOut() {
    router.post(props.urls.logout);
}
</script>

<template>
    <Head title="Verify your email" />

    <AuthLayout :step="1">
        <div class="flex flex-col gap-1.5">
            <h1 class="m-0 text-[36px] font-extrabold leading-[1.1] tracking-[-0.03em] max-[900px]:text-[28px]">Verify your email</h1>
            <p class="m-0 text-[16px] text-[#475569]">One more step before you set up your profile.</p>
        </div>

        <p v-if="status" role="status" class="m-0 rounded-[12px] bg-[#DCFCE7] px-4 py-3 text-[14px] font-semibold text-[#166534]">{{ status }}</p>

        <div class="flex flex-col gap-2.5 rounded-[16px] border-[1.5px] border-[#BFD0F5] bg-[#EEF4FF] px-[18px] py-4">
            <div class="flex items-center gap-2.5">
                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D4ED8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <path d="M3 7l9 6 9-6" />
                </svg>
                <span class="text-[14px] font-bold text-[#0B1530]">Check your inbox</span>
            </div>

            <span class="text-[13px] leading-[1.5] text-[#1E293B]">
                Enter the {{ codeLength }}-digit code we sent to <strong class="break-all">{{ email }}</strong
                >. It works for {{ ttlMinutes }} minutes.
            </span>

            <form class="flex items-start gap-2.5" novalidate @submit.prevent="submit">
                <CodeInput
                    v-model="form.code"
                    class="flex-1"
                    hide-label
                    :length="codeLength"
                    :error="form.errors.code"
                    :disabled="form.processing"
                    @complete="submit"
                />
                <button
                    type="submit"
                    :disabled="form.processing || !ready"
                    class="h-14 flex-none rounded-[14px] border-0 px-5 text-[15px] font-extrabold"
                    :class="ready && !form.processing ? 'cursor-pointer bg-[#1D4ED8] text-white hover:bg-[#1E3A8A]' : 'cursor-not-allowed bg-[#DCE3F0] text-[#64748B]'"
                >
                    {{ form.processing ? 'Verifying…' : 'Verify' }}
                </button>
            </form>

            <div class="flex flex-wrap gap-x-[18px] gap-y-1">
                <button
                    type="button"
                    :disabled="!canResend"
                    class="min-h-9 cursor-pointer border-0 bg-transparent p-0 text-[13px] font-bold text-[#1D4ED8] disabled:cursor-not-allowed disabled:text-[#94A3B8]"
                    @click="resend"
                >
                    {{ canResend ? "Didn't get it? Resend code" : `Resend code in ${wait}s` }}
                </button>
                <button
                    type="button"
                    class="min-h-9 cursor-pointer border-0 bg-transparent p-0 text-[13px] font-bold text-[#1D4ED8]"
                    :aria-expanded="showChange"
                    @click="showChange = !showChange"
                >
                    Change email
                </button>
            </div>
            <p v-if="resendError" role="alert" class="m-0 text-[13px] font-semibold text-[#B91C1C]">{{ resendError }}</p>

            <form v-if="showChange" class="flex flex-col gap-3 rounded-[14px] bg-white p-4" novalidate @submit.prevent="changeEmail">
                <FieldInput
                    id="signup-new-email"
                    v-model="changeForm.email"
                    label="New email address"
                    type="email"
                    inputmode="email"
                    autocomplete="email"
                    placeholder="you@example.com"
                    :error="changeForm.errors.email"
                />
                <div class="flex gap-3">
                    <button
                        type="submit"
                        :disabled="changeForm.processing || !changeForm.email"
                        class="h-12 flex-1 rounded-[14px] border-0 text-[15px] font-extrabold"
                        :class="changeForm.email && !changeForm.processing ? 'cursor-pointer bg-[#1D4ED8] text-white hover:bg-[#1E3A8A]' : 'cursor-not-allowed bg-[#DCE3F0] text-[#64748B]'"
                    >
                        Send code
                    </button>
                    <button
                        type="button"
                        class="h-12 cursor-pointer rounded-[14px] border-[1.5px] border-[#DCE3F0] bg-white px-5 text-[15px] font-extrabold text-[#475569] hover:bg-[#F8FAFC]"
                        @click="showChange = false"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <p class="m-0 text-center text-[14px] text-[#475569]">
            Wrong account?
            <button type="button" class="cursor-pointer border-0 bg-transparent p-0 text-[14px] font-extrabold text-[#1D4ED8] hover:text-[#1E3A8A]" @click="signOut">Sign out</button>
        </p>
    </AuthLayout>
</template>
