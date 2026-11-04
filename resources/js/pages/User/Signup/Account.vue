<script setup lang="ts">
// Create account and Sign in: one page, two tabs. /register opens the first, /login the second.
// Switching tabs keeps what was typed and updates the address bar without a round trip.
import AccountTabs from '@/components/User/Signup/AccountTabs.vue';
import AuthLayout from '@/components/User/Signup/AuthLayout.vue';
import CreateAccountForm from '@/components/User/Signup/CreateAccountForm.vue';
import SignInForm from '@/components/User/Signup/SignInForm.vue';
import type { AccountMode, AccountUrls } from '@/components/User/Signup/types';
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    mode: AccountMode;
    status?: string | null;
    urls: AccountUrls;
}>();

const tab = ref<AccountMode>(props.mode);

watch(
    () => props.mode,
    (mode) => {
        tab.value = mode;
    },
);

function select(next: AccountMode) {
    tab.value = next;
    // Keep the address honest (/register or /login) so refresh and sharing land on the same tab.
    // Path only: the app URL setting can differ from the host the member is on.
    const path = new URL(next === 'create' ? props.urls.register : props.urls.login, window.location.origin).pathname;
    window.history.replaceState(window.history.state, '', path);
}

const title = computed(() => (tab.value === 'create' ? 'Create your LinkUp account' : 'Sign in to LinkUp'));
</script>

<template>
    <Head :title="title" />

    <AuthLayout :step="1">
        <AccountTabs :model-value="tab" @update:model-value="select" />

        <p v-if="status" role="status" class="m-0 rounded-[12px] bg-[#DCFCE7] px-4 py-3 text-[14px] font-semibold text-[#166534]">{{ status }}</p>

        <section v-show="tab === 'create'" id="account-panel-create" role="tabpanel" aria-labelledby="account-tab-create">
            <CreateAccountForm :urls="urls" @switch="select('signin')" />
        </section>
        <section v-show="tab === 'signin'" id="account-panel-signin" role="tabpanel" aria-labelledby="account-tab-signin">
            <SignInForm :urls="urls" @switch="select('create')" />
        </section>
    </AuthLayout>
</template>
