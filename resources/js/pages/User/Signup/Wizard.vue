<script setup lang="ts">
// The 16-step profile wizard ("LinkUp Onboarding" design, steps 2–17).
//
// Every "Continue" saves that step on the server (wizard/steps/{step}), which then opens the
// next one, so a member can leave and pick up later on any device. The phone step sends and
// checks a texted code first; the last step uploads the photos and finishes. The server checks
// every answer again; this page only helps the member get them right.
import AuthLayout from '@/components/User/Signup/AuthLayout.vue';
import AgeRangeStep from '@/components/User/Signup/Wizard/AgeRangeStep.vue';
import BioStep from '@/components/User/Signup/Wizard/BioStep.vue';
import BirthdayStep from '@/components/User/Signup/Wizard/BirthdayStep.vue';
import DistanceStep from '@/components/User/Signup/Wizard/DistanceStep.vue';
import GenderStep from '@/components/User/Signup/Wizard/GenderStep.vue';
import HomeStep from '@/components/User/Signup/Wizard/HomeStep.vue';
import IntentStep from '@/components/User/Signup/Wizard/IntentStep.vue';
import InterestsStep from '@/components/User/Signup/Wizard/InterestsStep.vue';
import LanguagesStep from '@/components/User/Signup/Wizard/LanguagesStep.vue';
import LinkWithStep from '@/components/User/Signup/Wizard/LinkWithStep.vue';
import PhoneStep from '@/components/User/Signup/Wizard/PhoneStep.vue';
import PhotosStep from '@/components/User/Signup/Wizard/PhotosStep.vue';
import ProfileCard from '@/components/User/Signup/Wizard/ProfileCard.vue';
import RootsStep from '@/components/User/Signup/Wizard/RootsStep.vue';
import ShowMeStep from '@/components/User/Signup/Wizard/ShowMeStep.vue';
import UniversityStep from '@/components/User/Signup/Wizard/UniversityStep.vue';
import WizardAside from '@/components/User/Signup/Wizard/WizardAside.vue';
import WorkStep from '@/components/User/Signup/Wizard/WorkStep.vue';
import { phoneCodes, stepCopy } from '@/components/User/Signup/Wizard/copy';
import { firstError, postJson } from '@/components/User/Signup/Wizard/http';
import { wizardIcons } from '@/components/User/Signup/Wizard/icons';
import { primaryButton } from '@/components/User/Signup/Wizard/styles';
import type { Answers, PickedPhoto, SavedAnswers, StepKey, WizardMember, WizardOptions, WizardUrls } from '@/components/User/Signup/Wizard/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue';

const props = defineProps<{
    step: StepKey;
    furthest: StepKey;
    steps: Array<{ key: StepKey; optional: boolean }>;
    answers: SavedAnswers;
    member: WizardMember;
    options: WizardOptions;
    minimumAge: number;
    phoneResendSeconds: number;
    urls: WizardUrls;
}>();

const page = usePage();
const errors = computed(() => (page.props.errors ?? {}) as Record<string, string>);
const photoErrors = computed(() => Object.fromEntries(Object.entries(errors.value).filter(([key]) => key.startsWith('more_photos'))));

/* ------------------------------------------------------------------ answers */

function saved<K extends StepKey>(key: K, fallback: Answers[K]): Answers[K] {
    const value = props.answers[key] as Answers[K] | { skipped: true } | undefined;
    if (!value || (value as { skipped?: boolean }).skipped) return fallback;
    return { ...fallback, ...(value as Answers[K]) };
}

const drafts = reactive<Answers>({
    home: saved('home', { country: '', country_name: '', state: '', state_name: '', city: '', latitude: null, longitude: null }),
    languages: saved('languages', { languages: [] }),
    phone: {},
    birthday: saved('birthday', { birthday: '', show_age: true }),
    interests: saved('interests', { interests: [] }),
    gender: saved('gender', { gender: '', show_gender: true }),
    distance: saved('distance', { km: 40, unit: 'km' }),
    age_range: saved('age_range', { min: 18, max: 35 }),
    show_me: saved('show_me', { show_me: '' }),
    roots: saved('roots', { roots: '' }),
    link_with: saved('link_with', { countries: [] }),
    bio: saved('bio', { bio: '' }),
    work: saved('work', { work: '' }),
    university: saved('university', { no_university: false, university: '', location: '', country: null, studying: false }),
    intent: saved('intent', { intents: [] }),
    photos: {},
});
const photos = ref<Array<PickedPhoto | null>>(Array.from({ length: props.options.maxPhotos }, () => null));

/* ------------------------------------------------------------------ phone */

const digits = (v: string | null | undefined) => (v ?? '').replace(/\D/g, '');

function splitPhone(full: string | null): { code: string; number: string } {
    const fallbackCode = phoneCodes.some((c) => c[0] === drafts.home.country) ? drafts.home.country : 'BS';
    if (!full) return { code: fallbackCode, number: '' };
    const d = digits(full);
    const byDial = [...phoneCodes].sort((a, b) => b[1].length - a[1].length).find(([, dial]) => d.startsWith(digits(dial)));
    if (!byDial) return { code: fallbackCode, number: d };
    const dial = digits(byDial[1]);
    const code = dial === '1' && phoneCodes.some((c) => c[0] === fallbackCode && c[1] === '+1') ? fallbackCode : byDial[0];
    return { code, number: d.slice(dial.length) };
}

const initialPhone = splitPhone(props.member.phone);
const phoneCode = ref(initialPhone.code);
const phoneNumber = ref(initialPhone.number);
const phoneSent = ref(false);
const phoneOtp = ref('');
const phoneVerified = ref(props.member.phoneVerified);
const verifiedPhone = ref(props.member.phoneVerified ? props.member.phone : null);
const phoneError = ref('');
const phoneNotice = ref('');
const resendWait = ref(0);
let resendTimer: number | undefined;

const dial = computed(() => (phoneCodes.find((c) => c[0] === phoneCode.value) ?? phoneCodes[0])[1]);
const fullPhone = computed(() => dial.value + digits(phoneNumber.value));
const phoneAlreadyVerified = computed(() => phoneVerified.value && !!verifiedPhone.value && digits(verifiedPhone.value) === digits(fullPhone.value));

watch([phoneCode, phoneNumber], () => {
    phoneError.value = '';
});

// Until a number is typed, the country code follows the home country picked on step 1.
watch(
    () => drafts.home.country,
    (country) => {
        if (!phoneNumber.value && !phoneSent.value && phoneCodes.some((c) => c[0] === country)) phoneCode.value = country;
    },
);

function startResendTimer(seconds: number) {
    resendWait.value = seconds;
    window.clearInterval(resendTimer);
    resendTimer = window.setInterval(() => {
        if (resendWait.value > 0) resendWait.value -= 1;
        else window.clearInterval(resendTimer);
    }, 1000);
}
onBeforeUnmount(() => window.clearInterval(resendTimer));

async function sendPhoneCode(): Promise<boolean> {
    phoneError.value = '';
    phoneNotice.value = '';
    const result = await postJson<{ sent: boolean; verified: boolean; resend_available_in: number; message: string }>(props.urls.phoneSend, {
        phone_number: fullPhone.value,
    });
    if (!result.ok) {
        phoneError.value = firstError(result, "We couldn't text that number. Check it and try again.");
        return false;
    }
    if (result.data.verified) {
        phoneVerified.value = true;
        verifiedPhone.value = fullPhone.value;
        return true;
    }
    phoneVerified.value = false;
    phoneSent.value = true;
    phoneOtp.value = '';
    phoneNotice.value = result.data.message;
    startResendTimer(result.data.resend_available_in || props.phoneResendSeconds);
    return false;
}

async function verifyPhoneCode(): Promise<boolean> {
    phoneError.value = '';
    const result = await postJson(props.urls.phoneVerify, { otp: phoneOtp.value });
    if (!result.ok) {
        phoneError.value = firstError(result, "That code isn't right.");
        return false;
    }
    phoneVerified.value = true;
    verifiedPhone.value = fullPhone.value;
    phoneSent.value = false;
    phoneNotice.value = '';
    return true;
}

function changePhone() {
    phoneSent.value = false;
    phoneOtp.value = '';
    phoneError.value = '';
    phoneNotice.value = '';
}

/* ------------------------------------------------------------------ steps */

const index = computed(() => Math.max(0, props.steps.findIndex((s) => s.key === props.step)));
const current = computed(() => props.steps[index.value]);
const copy = computed(() => stepCopy[props.step]);

const age = computed(() => {
    const b = drafts.birthday.birthday;
    if (!b) return null;
    const [y, m, d] = b.split('-').map(Number);
    const now = new Date();
    let years = now.getFullYear() - y;
    if (now.getMonth() + 1 < m || (now.getMonth() + 1 === m && now.getDate() < d)) years--;
    return years;
});
const photoCount = computed(() => photos.value.filter(Boolean).length);

/** Whether each step has what it needs to continue (the design's rules). */
const valid = computed<Record<StepKey, boolean>>(() => ({
    home: !!drafts.home.city.trim() && !!drafts.home.state && !!drafts.home.country,
    languages: drafts.languages.languages.length > 0,
    phone: phoneAlreadyVerified.value || (phoneSent.value ? digits(phoneOtp.value).length === 6 : digits(phoneNumber.value).length >= 7),
    birthday: age.value !== null && age.value >= props.minimumAge,
    interests: drafts.interests.interests.length > 0,
    gender: !!drafts.gender.gender,
    distance: true,
    age_range: true,
    show_me: !!drafts.show_me.show_me,
    roots: !!drafts.roots.roots,
    link_with: drafts.link_with.countries.length > 0,
    bio: drafts.bio.bio.trim().length > 0,
    work: drafts.work.work.trim().length > 0,
    university: drafts.university.no_university || !!drafts.university.university.trim(),
    intent: drafts.intent.intents.length > 0,
    photos: photoCount.value > 0,
}));
const ready = computed(() => valid.value[props.step]);
const answered = computed(() => Object.values(valid.value).filter(Boolean).length);

const title = computed(() => (props.step === 'phone' && phoneSent.value ? 'Enter your code' : copy.value.title));
const sub = computed(() =>
    props.step === 'phone' && phoneSent.value ? `We sent a 6-digit code to ${dial.value} ${phoneNumber.value}.` : copy.value.sub,
);
const nextLabel = computed(() => {
    if (props.step === 'phone') return phoneAlreadyVerified.value ? 'Continue' : phoneSent.value ? 'Verify and continue' : 'Send code';
    if (props.step === 'photos') return 'Finish and start linking up';
    return 'Continue';
});
const hint = computed(() => {
    if (props.step === 'photos' && !photoCount.value) return 'Add at least one photo to finish.';
    if (props.step === 'home' && !drafts.home.city) return 'Pick your city, or tap a popular one above.';
    return '';
});

/* ------------------------------------------------------------------ actions */

const busy = ref(false);

function stepUrl(key: StepKey) {
    return props.urls.saveStep.replace('__STEP__', key);
}

function save(key: StepKey, payload: Record<string, unknown>) {
    router.post(stepUrl(key), payload as never, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => (busy.value = true),
        onFinish: () => (busy.value = false),
    });
}

async function next() {
    if (!ready.value || busy.value) return;

    if (props.step === 'phone') {
        if (!phoneAlreadyVerified.value) {
            busy.value = true;
            const done = phoneSent.value ? await verifyPhoneCode() : await sendPhoneCode();
            busy.value = false;
            if (!done) return;
        }
        save('phone', {});
        return;
    }

    if (props.step === 'photos') {
        const files = photos.value.filter((p): p is PickedPhoto => !!p).map((p) => p.file);
        router.post(props.urls.complete, { more_photos: files }, {
            forceFormData: true,
            preserveState: true,
            preserveScroll: true,
            onStart: () => (busy.value = true),
            onFinish: () => (busy.value = false),
        });
        return;
    }

    save(props.step, { ...drafts[props.step] });
}

function skip() {
    if (busy.value) return;
    save(props.step, { skip: true });
}

function back() {
    const prev = props.steps[index.value - 1];
    if (!prev) return;
    router.get(props.urls.wizard, { step: prev.key }, { preserveState: true });
}

watch(
    () => props.step,
    async () => {
        await nextTick();
        window.scrollTo({ top: 0 });
    },
);
</script>

<template>
    <Head :title="title" />

    <AuthLayout :width="660">
        <template #aside>
            <WizardAside :tag="copy.tag" :head="copy.head" :side="copy.side">
                <ProfileCard :member="member" :answers="drafts" :options="options" :answered="answered" :has-photo="!!photos[0]" />
            </WizardAside>
        </template>

        <template #header>
            <div class="flex flex-col gap-3.5">
                <div class="flex min-h-11 items-center justify-between">
                    <button
                        v-if="index > 0"
                        type="button"
                        class="inline-flex min-h-11 cursor-pointer items-center gap-2 border-0 bg-transparent px-1 text-[15px] font-semibold text-[#475569]"
                        @click="back"
                    >
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path :d="wizardIcons.back" />
                        </svg>
                        Back
                    </button>
                    <span v-else></span>
                    <span class="text-[14px] font-bold text-[#475569]">Step {{ index + 2 }} of 17</span>
                </div>
                <div aria-hidden="true" class="flex gap-1">
                    <div v-for="n in 17" :key="n" class="h-1.5 flex-1 rounded-[3px]" :class="n <= index + 2 ? 'bg-[#2563EB]' : 'bg-[#DCE3F0]'"></div>
                </div>
            </div>
        </template>

        <div class="flex flex-col gap-2">
            <h1 class="lu-title m-0 text-[38px] font-extrabold leading-[1.1] tracking-[-0.03em]">{{ title }}</h1>
            <p class="m-0 text-[16px] leading-[1.55] text-[#475569]">{{ sub }}</p>
        </div>

        <HomeStep v-if="step === 'home'" v-model="drafts.home" :locate-url="urls.locate" :errors="errors" @located="next" />
        <LanguagesStep v-else-if="step === 'languages'" v-model="drafts.languages" :options="options" :errors="errors" />
        <PhoneStep
            v-else-if="step === 'phone'"
            v-model:code="phoneCode"
            v-model:number="phoneNumber"
            v-model:otp="phoneOtp"
            :sent="phoneSent"
            :verified="phoneAlreadyVerified"
            :error="phoneError || errors.phone_number || ''"
            :notice="phoneNotice"
            :resend-wait="resendWait"
            :busy="busy"
            @resend="sendPhoneCode"
            @change="changePhone"
        />
        <BirthdayStep v-else-if="step === 'birthday'" v-model="drafts.birthday" :minimum-age="minimumAge" :errors="errors" />
        <InterestsStep v-else-if="step === 'interests'" v-model="drafts.interests" :options="options" :errors="errors" />
        <GenderStep v-else-if="step === 'gender'" v-model="drafts.gender" :options="options" :errors="errors" />
        <DistanceStep v-else-if="step === 'distance'" v-model="drafts.distance" />
        <AgeRangeStep v-else-if="step === 'age_range'" v-model="drafts.age_range" :errors="errors" />
        <ShowMeStep v-else-if="step === 'show_me'" v-model="drafts.show_me" :options="options" :errors="errors" />
        <RootsStep v-else-if="step === 'roots'" v-model="drafts.roots" :options="options" :errors="errors" />
        <LinkWithStep v-else-if="step === 'link_with'" v-model="drafts.link_with" :options="options" :errors="errors" />
        <BioStep v-else-if="step === 'bio'" v-model="drafts.bio" :errors="errors" />
        <WorkStep v-else-if="step === 'work'" v-model="drafts.work" :options="options" :errors="errors" />
        <UniversityStep v-else-if="step === 'university'" v-model="drafts.university" :errors="errors" />
        <IntentStep v-else-if="step === 'intent'" v-model="drafts.intent" :options="options" :errors="errors" />
        <PhotosStep v-else-if="step === 'photos'" v-model="photos" :errors="photoErrors" />

        <div class="flex flex-col gap-1 pt-1.5">
            <p v-if="errors.step || errors.email" role="alert" class="m-0 pb-2 text-center text-[13px] font-semibold text-[#B91C1C]">{{ errors.step || errors.email }}</p>
            <p v-if="step === 'photos' && (errors.birthday || errors.phone_number)" role="alert" class="m-0 pb-2 text-center text-[13px] font-semibold text-[#B91C1C]">
                {{ errors.birthday || errors.phone_number }}
            </p>
            <button type="button" :disabled="!ready || busy" :style="primaryButton(ready && !busy)" @click="next">
                {{ busy ? (step === 'photos' ? 'Uploading your photos…' : 'Saving…') : nextLabel }}
                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path :d="wizardIcons.arrow" />
                </svg>
            </button>
            <button
                v-if="current?.optional"
                type="button"
                :disabled="busy"
                class="min-h-11 cursor-pointer self-center border-0 bg-transparent text-[15px] font-bold text-[#475569]"
                @click="skip"
            >
                Skip for now
            </button>
            <span v-if="hint" class="pt-1.5 text-center text-[13px] text-[#64748B]">{{ hint }}</span>
        </div>
    </AuthLayout>
</template>
