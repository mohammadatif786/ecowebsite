<script setup lang="ts">
// Forgot password: ask for the account email and send a reset link.
import AuthLayout from '@/components/User/Signup/AuthLayout.vue';
import FieldInput from '@/components/User/Signup/FieldInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{ status?: string | null }>();

// Resolved here (not in the template) so it works whether or not Ziggy is a global template property.
const loginUrl = route('login');

const form = useForm({ email: '' });
const canSubmit = computed(() => !form.processing && !!form.email.trim());

function submit() {
    if (!canSubmit.value) return;
    form.transform((data) => ({ ...data, email: data.email.trim() })).post(route('password.email'));
}
</script>

<template>
    <Head title="Reset your password" />

    <AuthLayout :step="1">
        <form class="flex flex-col gap-[18px]" novalidate @submit.prevent="submit">
            <div class="flex flex-col gap-1.5">
                <h1 class="m-0 text-[36px] font-extrabold leading-[1.1] tracking-[-0.03em] max-[900px]:text-[28px]">Forgot your password?</h1>
                <p class="m-0 text-[16px] text-[#475569]">Enter your account email and we'll send you a link to choose a new one.</p>
            </div>

            <p v-if="status" role="status" class="m-0 rounded-[12px] bg-[#DCFCE7] px-4 py-3 text-[14px] font-semibold text-[#166534]">{{ status }}</p>

            <FieldInput
                id="fp-email"
                v-model="form.email"
                label="Email"
                type="email"
                inputmode="email"
                autocomplete="email"
                placeholder="you@example.com"
                :error="form.errors.email"
            />

            <button
                type="submit"
                :disabled="!canSubmit"
                class="flex h-[58px] w-full items-center justify-center rounded-[14px] border-0 text-[17px] font-bold"
                :class="canSubmit ? 'cursor-pointer bg-[#1D4ED8] text-white shadow-[0_10px_24px_rgba(29,78,216,0.28)] hover:bg-[#1E3A8A]' : 'cursor-not-allowed bg-[#DCE3F0] text-[#64748B]'"
            >
                {{ form.processing ? 'Sending…' : 'Send reset link' }}
            </button>

            <p class="m-0 text-center text-[14px] text-[#475569]">
                Remembered it?
                <Link :href="loginUrl" class="font-extrabold no-underline">Back to sign in</Link>
            </p>
        </form>
    </AuthLayout>
</template>
