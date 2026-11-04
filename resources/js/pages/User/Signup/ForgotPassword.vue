<script setup lang="ts">
// Forgot password: ask for the account email and send a reset link. The page never says whether
// an account uses the email (the server answers the same either way).
import AuthLayout from '@/components/User/Signup/AuthLayout.vue';
import FieldInput from '@/components/User/Signup/FieldInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    status?: string | null;
    sentTo?: string | null;
    expireMinutes: number;
    urls: { send: string; login: string };
}>();

const form = useForm({ email: props.sentTo ?? '' });
const changing = ref(false);
const sent = computed(() => !!props.status && !!props.sentTo && !changing.value);
const canSubmit = computed(() => !form.processing && !!form.email.trim());

function submit() {
    if (!canSubmit.value) return;
    form.transform((data) => ({ ...data, email: data.email.trim() })).post(props.urls.send, {
        preserveScroll: true,
        onSuccess: () => (changing.value = false),
    });
}
</script>

<template>
    <Head title="Reset your password" />

    <AuthLayout :step="1">
        <form v-if="!sent" class="flex flex-col gap-[18px]" novalidate @submit.prevent="submit">
            <div class="flex flex-col gap-1.5">
                <h1 class="m-0 text-[36px] font-extrabold leading-[1.1] tracking-[-0.03em] max-[900px]:text-[28px]">Forgot your password?</h1>
                <p class="m-0 text-[16px] text-[#475569]">Enter the email you signed up with and we'll send you a link to choose a new one.</p>
            </div>

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
                <Link :href="urls.login" class="font-extrabold no-underline">Back to sign in</Link>
            </p>
        </form>

        <div v-else class="flex flex-col gap-[18px]">
            <div class="flex flex-col gap-1.5">
                <h1 class="m-0 text-[36px] font-extrabold leading-[1.1] tracking-[-0.03em] max-[900px]:text-[28px]">Check your email</h1>
                <p class="m-0 text-[16px] text-[#475569]">{{ status }}</p>
            </div>

            <div class="flex flex-col gap-2 rounded-[16px] border-[1.5px] border-[#BFD0F5] bg-[#EEF4FF] px-[18px] py-4">
                <span class="text-[14px] font-bold text-[#1E293B]">Sent to</span>
                <span class="break-all text-[16px] font-extrabold text-[#0B1530]">{{ sentTo }}</span>
                <span class="text-[13px] text-[#475569]">The link works once and expires in {{ expireMinutes }} minutes.</span>
            </div>

            <p v-if="form.errors.email" role="alert" class="m-0 text-[13px] font-semibold text-[#B91C1C]">{{ form.errors.email }}</p>

            <div class="flex flex-wrap items-center gap-[18px]">
                <button
                    type="button"
                    :disabled="form.processing"
                    class="min-h-10 cursor-pointer border-0 bg-transparent p-0 text-[14px] font-bold text-[#1D4ED8]"
                    @click="submit"
                >
                    {{ form.processing ? 'Sending…' : "Didn't get it? Send again" }}
                </button>
                <button type="button" class="min-h-10 cursor-pointer border-0 bg-transparent p-0 text-[14px] font-bold text-[#1D4ED8]" @click="changing = true">
                    Use a different email
                </button>
            </div>

            <Link
                :href="urls.login"
                class="flex h-[58px] w-full items-center justify-center rounded-[14px] bg-[#1D4ED8] text-[17px] font-bold !text-white no-underline shadow-[0_10px_24px_rgba(29,78,216,0.28)]"
            >
                Back to sign in
            </Link>
        </div>
    </AuthLayout>
</template>
