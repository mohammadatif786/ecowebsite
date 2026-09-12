<template>
    <SettingsSubModal
        :model-value="modelValue"
        title="Change password"
        icon="key"
        color="#2563eb"
        @back="$emit('update:modelValue', false)"
        @close-all="$emit('close-all')"
    >
        <div class="space-y-3">
            <label class="block">
                <span class="text-xs font-black text-slate-500">Current Password</span>
                <input v-model="form.current_password" type="password" class="field" autocomplete="current-password" />
                <span v-if="form.errors.current_password" class="error-text">{{ form.errors.current_password }}</span>
            </label>
            <label class="block">
                <span class="text-xs font-black text-slate-500">New Password</span>
                <input v-model="form.password" type="password" class="field" autocomplete="new-password" />
                <span v-if="form.errors.password" class="error-text">{{ form.errors.password }}</span>
            </label>
            <label class="block">
                <span class="text-xs font-black text-slate-500">Confirm New Password</span>
                <input v-model="form.password_confirmation" type="password" class="field" autocomplete="new-password" />
                <span v-if="form.errors.password_confirmation" class="error-text">{{ form.errors.password_confirmation }}</span>
            </label>
        </div>
        <button type="button" @click="save" :disabled="form.processing" class="btn btn-primary mt-4 w-full py-3 disabled:opacity-50">
            {{ form.processing ? 'Saving Password...' : 'Save Password' }}
        </button>
    </SettingsSubModal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import SettingsSubModal from './SettingsSubModal.vue';

const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(['update:modelValue', 'close-all', 'toast']);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const save = () => {
    if (!form.current_password || !form.password) {
        emit('toast', 'Fill in all fields');
        return;
    }
    if (form.password !== form.password_confirmation) {
        emit('toast', 'New passwords do not match');
        return;
    }

    form.put('/new_frontend/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            emit('toast', 'Password updated successfully');
            emit('close-all');
        },
        onError: () => {
            emit('toast', 'Please check the password fields');
        },
    });
};

watch(
    () => props.modelValue,
    (open) => {
        if (!open) return;
        form.reset();
        form.clearErrors();
    },
);
</script>

<style scoped>
.field {
    margin-top: 0.25rem;
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    outline: none;
}
.field:focus {
    border-color: #2f9bef;
}
.error-text {
    margin-top: 0.25rem;
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: #e11d48;
}
</style>
