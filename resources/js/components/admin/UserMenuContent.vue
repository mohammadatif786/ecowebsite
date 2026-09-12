<script setup lang="ts">
import UserInfo from '@/components/admin/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/admin/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';
import { initializeOneSignal } from '../../services/OneSignalService';


interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
    // Remove external user ID (if used)
    OneSignal.removeExternalUserId().then(() => {
        console.log("External user ID removed.");
    });

    // Optionally unsubscribe from push notifications
    // OneSignal.setSubscription(false);
};

const handleEnablePushNotification = ()=>{
    initializeOneSignal();
}

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuItem :as-child="true">
        <button class="block w-full" method="post" :href="'/admin'" @click="handleEnablePushNotification" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Enable Push Notification
        </button>
    </DropdownMenuItem>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('admin.profile.edit')" prefetch as="button">
            <Settings class="mr-2 h-4 w-4" />
            Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('admin.logout')" @click="handleLogout" as="button">
        <LogOut class="mr-2 h-4 w-4" />
        Log out
        </Link>
    </DropdownMenuItem>
</template>
