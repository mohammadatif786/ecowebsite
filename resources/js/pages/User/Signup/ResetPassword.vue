<script setup lang="ts">
// Reset password: the page the emailed link opens. Same password rules as sign-up.
import AuthLayout from '@/components/User/Signup/AuthLayout.vue';
import PasswordFields from '@/components/User/Signup/PasswordFields.vue';
import { passwordScore } from '@/components/User/Signup/passwordRules';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    email: string;
    token: string;
    status?: string | null;
    urls: { save: string; login: string; forgot: string };
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const canSubmit = computed(
    () => !form.processing && passwordScore(form.password) === 4 && !!form.password_confirmation && form.password === form.password_confirmation,
);

function submit() {
    if (!canSubmit.value) return;
    form.post(props.urls.save, {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Choose a new password" />

    <AuthLayout :step="1">
        <form class="flex flex-col gap-[18px]" novalidate @submit.prevent="submit">
            <div class="flex flex-col gap-1.5">
                <h1 class="m-0 text-[36px] font-extrabold leading-[1.1] tracking-[-0.03em] max-[900px]:text-[28px]">Choose a new password</h1>
                <p v-if="email" class="m-0 break-all text-[16px] text-[#475569]">
                    For <strong class="text-[#0B1530]">{{ email }}</strong>
                </p>
                <p v-else class="m-0 text-[16px] text-[#475569]">Pick something you haven't used anywhere else.</p>
            </div>

            <p v-if="status" role="status" class="m-0 rounded-[12px] bg-[#DCFCE7] px-4 py-3 text-[14px] font-semibold text-[#166534]">{{ status }}</p>

            <div v-if="form.errors.email || form.errors.token" role="alert" class="m-0 flex flex-col gap-1 rounded-[12px] bg-[#FEF2F2] px-4 py-3 text-[14px] font-semibold text-[#B91C1C]">
                <span>{{ form.errors.token || form.errors.email }}</span>
                <Link :href="urls.forgot" class="font-extrabold">Send me a new link</Link>
            </div>

            <PasswordFields
                v-model:password="form.password"
                v-model:confirmation="form.password_confirmation"
                :password-error="form.errors.password"
                :confirmation-error="form.errors.password_confirmation"
            />

            <button
                type="submit"
                :disabled="!canSubmit"
                class="flex h-[58px] w-full items-center justify-center rounded-[14px] border-0 text-[17px] font-bold"
                :class="canSubmit ? 'cursor-pointer bg-[#1D4ED8] text-white shadow-[0_10px_24px_rgba(29,78,216,0.28)] hover:bg-[#1E3A8A]' : 'cursor-not-allowed bg-[#DCE3F0] text-[#64748B]'"
            >
                {{ form.processing ? 'Saving…' : 'Reset password' }}
            </button>

            <p class="m-0 text-center text-[14px] text-[#475569]">
                <Link :href="urls.login" class="font-extrabold no-underline">Back to sign in</Link>
            </p>
        </form>
    </AuthLayout>
</template>
