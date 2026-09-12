<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Active sessions"
        icon="monitor-smartphone"
        color="#2563eb"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-2">
            <div v-for="session in localSessions" :key="session.id" class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600">
                    <i data-lucide="monitor-smartphone" class="h-4 w-4"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold">
                        {{ session.device || `Device ${session.id}` }}
                        <span v-if="session.current" class="text-[10px] font-black text-emerald-600">CURRENT</span>
                    </p>
                    <p class="text-[11px] text-slate-400">{{ session.location || 'Unknown location' }}</p>
                </div>
                <button v-if="!session.current" type="button" @click="revoke(session.id)" class="shrink-0 text-xs font-black text-rose-600">
                    Log out
                </button>
            </div>
        </div>
    </SettingsSubModal>
</template>

<script setup>
import { DB } from '@/components/new_frontend/MockDataStore.js';
import { ref, watch } from 'vue';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({
    modelValue: Boolean,
    sessions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'close-all', 'updated', 'toast']);
const localSessions = ref([]);

const sync = () => {
    localSessions.value = props.sessions.map((session) => ({ ...session }));
};

const revoke = (id) => {
    localSessions.value = localSessions.value.filter((session) => session.id !== id);
    DB.set('lk_active_sessions', localSessions.value);
    emit('updated', localSessions.value);
    emit('toast', 'Session logged out');
};

watch(() => props.sessions, sync, { immediate: true, deep: true });
</script>
