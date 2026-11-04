<template>
    <div class="fade-in" style="animation-delay: 0.15s">
        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 ml-1">Quick Link Ups</h3>
        <div class="flex gap-4 overflow-x-auto pb-4 hide-scroll">
            <button class="flex flex-col items-center gap-2 min-w-[70px] group" @click="$emit('add-contact')">
                <div
                    class="w-14 h-14 rounded-full border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400 group-hover:border-linkup-blue group-hover:text-linkup-blue transition-colors bg-white">
                    <Plus class="w-6 h-6" />
                </div>
                <span class="text-xs font-bold text-slate-500">New</span>
            </button>

            <button v-for="contact in props.contacts" :key="contact.tag"
                class="cursor-pointer relative flex flex-col items-center gap-1 min-w-[70px] group"
                @click="$emit('send', contact.tag)">

                <X class="absolute w-5 h-5 bg-amber-500 rounded-full text-white right-0 top-0"
                    @click.stop="removeContact(contact.id)" />

                <div
                    class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold border-2 border-transparent group-hover:border-linkup-blue transition-all shadow-sm mb-1">
                    <img class="w-14 h-14 rounded-full" :src="contact.avatar" alt="Contact Avatar">
                </div>

                <span
                    class="text-[11px] font-bold text-slate-600 group-hover:text-linkup-blue truncate w-16 text-center leading-tight">
                    {{ contact.name }}
                </span>

                <span
                    class="text-[9px] font-medium text-slate-400 group-hover:text-linkup-blue truncate w-16 text-center leading-tight">
                    ~{{ contact.tag }}
                </span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { Plus, X } from 'lucide-vue-next';

const props = defineProps<{
    contacts: Array<{ name: string; tag: string; initials: string; avatar: string; id: number }>;
}>();

const emit = defineEmits<{
    (e: 'add-contact'): void;
    (e: 'send', tag: string): void;
    (e: 'remove', id: number): void;
}>();

const removeContact = async (contactId: number) => {
    try {
        const { data } = await axios.delete(route('frontend.user.remove.contact', {
            id: contactId
        }));

        if (data.success) {
            emit('remove', contactId)
        }

    } catch (error) {
        console.error(error);
    }
};
</script>
