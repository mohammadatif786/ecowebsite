<template>
    <div v-if="isOpen"
        class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center"
        @click.self="close">
        <div
            class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl p-5 max-h-[85vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between mb-4 shrink-0">
                <h3 class="text-xl font-black">Add to Quick Pay</h3>
                <button @click="close" class="hover:bg-slate-100 p-1 rounded">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <label class="text-xs font-black text-slate-500 ml-1 shrink-0">Name</label>
            <input v-model="form.name" placeholder="e.g Dad, Brother"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-1 font-bold outline-none focus:border-lkblue transition-colors shrink-0"
                autocomplete="off" />

            <label class="text-xs font-black text-slate-500 ml-1 shrink-0 mt-5">Search by @username</label>
            <input v-model="query" placeholder="@username"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-1 font-bold outline-none focus:border-lkblue transition-colors shrink-0"
                autocomplete="off" />

            <p v-if="form.errors.contact_user_id"
                class="text-[12px] font-bold text-rose-600 mt-3 bg-rose-50 p-2 rounded-xl">
                {{ form.errors.contact_user_id }}
            </p>

            <div class="mt-4 space-y-2 overflow-y-auto flex-1 pb-4">
                <div v-if="query.trim() === ''" class="text-center text-slate-400 text-sm py-8 font-bold">
                    Start typing a username to find someone on LinkUp.
                </div>

                <button v-else-if="results.length" v-for="user in results" :key="user.id" @click="addContact(user)"
                    :disabled="form.processing"
                    class="w-full flex items-center gap-3 rounded-2xl border border-slate-100 hover:border-lkblue hover:bg-blue-50/40 p-3 text-left transition shadow-sm disabled:opacity-60">
                    <img :src="user.img" class="h-12 w-12 rounded-full object-cover" />
                    <div class="min-w-0 flex-1">
                        <p class="font-black text-sm truncate">{{ user.name }}</p>
                        <p class="text-[11px] text-slate-500 font-bold mt-0.5">{{ user.tag }}</p>
                    </div>
                    <i data-lucide="plus" class="w-4 h-4 text-slate-300"></i>
                </button>

                <div v-else class="text-center text-slate-400 text-sm py-8 font-bold">
                    No LinkUp user matches that username.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
});

const emit = defineEmits(['contact-added']);

const isOpen = ref(false);
const query = ref('');

const form = useForm({
    contact_user_id: '',
    name: '',
});

const existingContactIds = computed(() => new Set(props.contacts.map(contact => Number(contact.id))));

const results = computed(() => {
    const needle = query.value.trim().toLowerCase().replace(/^[@~]/, '');

    if (!needle) {
        return [];
    }

    return props.users
        .filter(user => !existingContactIds.value.has(Number(user.id)))
        .filter(user => {
            const tag = String(user.tag || '').toLowerCase().replace(/^[@~]/, '');
            const name = String(user.name || '').toLowerCase();
            const email = String(user.email || '').toLowerCase();

            return tag.includes(needle) || name.includes(needle) || email.includes(needle);
        })
        .slice(0, 8);
});

const open = () => {
    query.value = '';
    form.clearErrors();
    isOpen.value = true;
};

const close = () => {
    isOpen.value = false;
    query.value = '';
    form.reset();
};

const addContact = (user) => {
    if (form.processing) {
        return;
    }

    form.contact_user_id = user.id;
    form.name = form.name;
    form.post(route('new_frontend.wallet.contact'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('contact-added', user);
            close();
            if (window.toast) window.toast(user.name + ' added to Quick Pay');
        },
    });
};

defineExpose({ open, close });
</script>
