<script setup lang="ts">
// "Create account" tab. Fills in name, LinkUp ID, email and password, then hands off to the
// Verify email page. Nothing here proves the email is real: the server holds the member on the
// Verify email page until the 6-digit code is entered.
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import FieldInput from './FieldInput.vue';
import { icons } from './icons';
import LinkupIdField from './LinkupIdField.vue';
import PasswordFields from './PasswordFields.vue';
import { passwordScore } from './passwordRules';
import SocialButtons from './SocialButtons.vue';
import type { AccountUrls, LinkupIdState } from './types';

const props = defineProps<{ urls: AccountUrls }>();
defineEmits<{ (e: 'switch'): void }>();

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    linkup_id: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const idState = ref<LinkupIdState>('waiting');

const emailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim()));
const emailHint = computed(() => (form.email.trim() && !emailValid.value ? 'Enter a valid email address.' : null));
const passwordsMatch = computed(() => !!form.password_confirmation && form.password_confirmation === form.password);

const canSubmit = computed(
    () =>
        !form.processing &&
        !!form.first_name.trim() &&
        !!form.last_name.trim() &&
        // "unknown" = the live check could not run; the server still decides on submit.
        (idState.value === 'ok' || idState.value === 'unknown') &&
        emailValid.value &&
        passwordScore(form.password) === 4 &&
        passwordsMatch.value &&
        form.terms,
);

function submit() {
    if (!canSubmit.value) return;
    form
        .transform((data) => ({
            ...data,
            first_name: data.first_name.trim(),
            last_name: data.last_name.trim(),
            email: data.email.trim().toLowerCase(),
        }))
        .post(props.urls.register, { preserveScroll: true });
}
</script>

<template>
    <form class="flex flex-col gap-[18px]" novalidate @submit.prevent="submit">
        <div class="flex flex-col gap-1.5">
            <h1 class="m-0 text-[36px] font-extrabold leading-[1.1] tracking-[-0.03em] max-[900px]:text-[28px]">Create your account</h1>
            <p class="m-0 text-[16px] text-[#475569]">Reserve your LinkUp ID. It takes less than a minute.</p>
        </div>

        <div class="grid grid-cols-2 gap-[14px] max-[480px]:grid-cols-1">
            <FieldInput id="a-first" v-model="form.first_name" label="First name" autocomplete="given-name" placeholder="Aaliyah" :error="form.errors.first_name" />
            <FieldInput id="a-last" v-model="form.last_name" label="Last name" autocomplete="family-name" placeholder="Brown" :error="form.errors.last_name" />
        </div>

        <LinkupIdField
            v-model="form.linkup_id"
            :first-name="form.first_name"
            :last-name="form.last_name"
            :check-url="urls.checkLinkupId"
            :error="form.errors.linkup_id"
            @update:state="idState = $event"
        />

        <FieldInput
            id="a-email"
            v-model="form.email"
            label="Email"
            type="email"
            inputmode="email"
            autocomplete="email"
            placeholder="you@example.com"
            :hint="emailHint"
            :error="form.errors.email"
        />

        <PasswordFields
            v-model:password="form.password"
            v-model:confirmation="form.password_confirmation"
            :password-error="form.errors.password"
            :confirmation-error="form.errors.password_confirmation"
        />

        <div class="flex flex-col gap-1.5">
            <div class="flex items-start gap-3">
                <button
                    type="button"
                    role="checkbox"
                    :aria-checked="form.terms"
                    aria-labelledby="a-agree"
                    class="mt-px flex h-6 w-6 flex-none cursor-pointer items-center justify-center rounded-[7px] border-2 p-0"
                    :class="form.terms ? 'border-[#1D4ED8] bg-[#1D4ED8]' : 'border-[#CBD5E1] bg-white'"
                    @click="form.terms = !form.terms"
                >
                    <svg v-if="form.terms" aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="icons.check" />
                    </svg>
                </button>
                <span id="a-agree" class="text-[14px] leading-[1.5] text-[#1E293B]">
                    I agree to LinkUp's
                    <a :href="urls.terms" target="_blank" rel="noopener">Terms of Service</a>,
                    <a :href="urls.privacy" target="_blank" rel="noopener">Privacy Policy</a> and
                    <a :href="urls.acceptableUse" target="_blank" rel="noopener">Acceptable Use Policy</a>.
                </span>
            </div>
            <p v-if="form.errors.terms" role="alert" class="m-0 text-[13px] font-semibold text-[#B91C1C]">{{ form.errors.terms }}</p>
        </div>

        <button
            type="submit"
            :disabled="!canSubmit"
            class="flex h-[58px] w-full items-center justify-center gap-2.5 rounded-[14px] border-0 text-[17px] font-bold"
            :class="canSubmit ? 'cursor-pointer bg-[#1D4ED8] text-white shadow-[0_10px_24px_rgba(29,78,216,0.28)] hover:bg-[#1E3A8A]' : 'cursor-not-allowed bg-[#DCE3F0] text-[#64748B]'"
        >
            {{ form.processing ? 'Creating account…' : 'Create account' }}
            <svg v-if="!form.processing" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path :d="icons.arrow" />
            </svg>
        </button>

        <SocialButtons />

        <div class="flex items-center justify-between text-[14px] text-[#475569]">
            <span>Already have an account?</span>
            <button type="button" class="min-h-11 cursor-pointer text-[14px] font-extrabold text-[#1D4ED8] hover:text-[#1E3A8A]" @click="$emit('switch')">Sign in</button>
        </div>
    </form>
</template>
