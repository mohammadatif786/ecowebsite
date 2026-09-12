<script setup lang="ts">
import { computed } from 'vue';
import AccessMatrix from './AccessMatrix.vue';
import AdminAuditTrail from './AdminAuditTrail.vue';
import AdminUsers from './AdminUsers.vue';
import PartnerScotiabankAccess from './PartnerScotiabankAccess.vue';
import RolesPermissions from './RolesPermissions.vue';
import SecurityAccess from './SecurityAccess.vue';

type AdminUser = {
    id?: number;
    name: string;
    email: string;
    role: string;
    scope: string;
    lastLogin: string;
    twofa: 'Enabled' | 'Pending';
    status: 'Active' | 'Suspended';
};

const props = defineProps<{
    viewId?: string;
    initialAdminUsers?: AdminUser[];
    initialAdminRoles?: any[];
    initialPermissionModules?: string[];
}>();

const emit = defineEmits<{
    (event: 'change-view', viewId: string): void;
}>();

const activeView = computed(() => props.viewId || 'adminRolesCommand');
const adminUsers = computed<AdminUser[]>(() => props.initialAdminUsers ?? []);
</script>

<template>
    <div class="space-y-6">
        <RolesPermissions
            v-if="activeView === 'adminRolesCommand'"
            :initial-roles="initialAdminRoles"
            :initial-permission-modules="initialPermissionModules"
        />

        <AdminUsers v-else-if="activeView === 'adminUsersCommand'" :initial-admin-users="adminUsers" />

        <AccessMatrix v-else-if="activeView === 'adminAccessMatrixCommand'" />

        <SecurityAccess v-else-if="activeView === 'settingsSecurityCommand'" />

        <PartnerScotiabankAccess v-else-if="activeView === 'scotiaAccessCommand'" @change-view="emit('change-view', $event)" />

        <AdminAuditTrail v-else-if="activeView === 'adminAuditCommand'" />
    </div>
</template>
