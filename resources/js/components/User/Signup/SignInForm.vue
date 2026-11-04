<script setup lang="ts">
// "Sign in" tab: LinkUp ID or email plus password. The server decides where the member goes next
// (verify email, phone code, or the app).
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import FieldInput from './FieldInput.vue';
import SocialButtons from './SocialButtons.vue';
import type { AccountUrls } from './types';

const props = defineProps<{ urls: AccountUrls }>();
defineEmits<{ (e: 'switch'): void }>();

// The design has no "remember me" box, so signing in keeps the member signed in on this device.
const form = useForm({ email: '', password: '', remember: true });

const canSubmit = computed(() => !form.processing && !!form.email.trim() && !!form.password);

function submit() {
    if (!canSubmit.value) return;
    form
        .transform((data) => ({ ...data, email: data.email.trim() }))
        .post(props.urls.login, {
            preserveScroll: true,
            onFinish: () => form.reset('password'),
        });
}
</script>

<template>
    <form class="flex flex-col gap-[18px]" novalidate @submit.prevent="submit">
        <div class="flex flex-col gap-1.5">
            <h1 class="m-0 text-[36px] font-extrabold leading-[1.1] tracking-[-0.03em] max-[900px]:text-[28px]">Welcome back</h1>
            <p class="m-0 text-[16px] text-[#475569]">Sign in with your LinkUp ID or email.</p>
        </div>

        <FieldInput
            id="s-id"
            v-model="form.email"
            label="LinkUp ID or email"
            autocomplete="username"
            placeholder="@yourname or you@example.com"
            :error="form.errors.email"
        />

        <FieldInput
            id="s-pw"
            v-model="form.password"
            label="Password"
            reveal
            autocomplete="current-password"
            placeholder="Your password"
            :error="form.errors.password"
        >
            <template #label-aside>
                <Link :href="urls.forgotPassword" class="text-[14px] font-bold no-underline">Forgot password?</Link>
            </template>
        </FieldInput>

        <button
            type="submit"
            :disabled="!canSubmit"
            class="flex h-[58px] w-full items-center justify-center gap-2.5 rounded-[14px] border-0 text-[17px] font-bold"
            :class="canSubmit ? 'cursor-pointer bg-[#1D4ED8] text-white shadow-[0_10px_24px_rgba(29,78,216,0.28)] hover:bg-[#1E3A8A]' : 'cursor-not-allowed bg-[#DCE3F0] text-[#64748B]'"
        >
            {{ form.processing ? 'Signing in…' : 'Sign in' }}
        </button>

        <SocialButtons />

        <div class="flex items-center justify-between text-[14px] text-[#475569]">
            <span>New to LinkUp?</span>
            <button type="button" class="min-h-11 cursor-pointer text-[14px] font-extrabold text-[#1D4ED8] hover:text-[#1E3A8A]" @click="$emit('switch')">Create account</button>
        </div>
    </form>
</template>
