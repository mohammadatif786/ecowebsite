<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import ProfileDetail from './components/ProfileDetail.vue';
import AboutCategories from './components/AboutCategories.vue';
import AdditionalDetails from './components/AdditionalDetails.vue';
import OrganizerMedia from './components/OrganizerMedia.vue';
import ProfileVisibility from './components/ProfileVisibility.vue';
import BankingInformation from './components/BankingInformation.vue';
import OrganizerKYC from './components/OrganizerKYC.vue';
import { UserCircle } from 'lucide-vue-next';

const props = defineProps<{
    organizer_detail: Record<string, any>;
    categories: Array<Record<string, any>>;
    appURL: string;
}>();
</script>

<template>
    <Head title="Organizer Profile" />

    <AppLayout>
       
        <div class="space-y-4">
            <ProfileDetail :organizer_detail="props.organizer_detail" />
            <AboutCategories :organizer_detail="props.organizer_detail" :categories="categories" />
            <AdditionalDetails :organizer_media="props.organizer_detail?.media" :appURL="appURL" />
            <OrganizerMedia :organizer_contacts="props.organizer_detail?.contacts" />
            <ProfileVisibility :organizer_setting="props.organizer_detail?.settings" />
            <BankingInformation :organizer_bank_accounts="props.organizer_detail?.bank_accounts" />
            <OrganizerKYC :organizer_kyc="props.organizer_detail?.kyc" :appURL="appURL"/>
        </div>

    </AppLayout>
</template>

<style scoped>
/* No extra styles needed here as they are handled by Tailwind and components */
</style>
