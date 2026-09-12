<script setup lang="ts">
    import { Card, CardContent, CardHeader } from '@/components/front/ui/card'
    import { Link, useForm } from '@inertiajs/vue3';
    import { LinkupEvent } from '@/client/models/LinkupEvent';
    import { computed } from 'vue';
    import moment from 'moment';
    import Button from './ui/button/Button.vue';
    import { Heart, X, Edit, Trash2 } from 'lucide-vue-next';
    import { ref } from 'vue';
    import ConfirmDeleteDialog from '@/components/front/ConfirmDeleteDialog.vue';


    const { ticket } = defineProps<{
        ticket: LinkupEvent;
    }>();

    const truncatedDescription = computed(() => {
        const text = ticket.description || ''
        return text.length > 34 ? text.substring(0, 34) + '......' : text
    })

    const formattedStartTime = computed(() => {
        return ticket.created_at ? moment(ticket.created_at).format('MMM YYYY') : ''
    })

    const form = useForm({});

    //delte handle
    const handleDeleteTicket = (e: Event) => {
        e.preventDefault();
        e.stopPropagation(); // Prevent the Link click
        deletingUser.value = ticket.id;
        showDialog.value = true;
    };
    const deletingUser = ref<number | null>(null);
    const showDialog = ref(false);

    function deleteticket(e: Event) {
        form.delete(route('frontend.ticket.destroy', ticket.id), {
            preserveScroll: true,
            preserveState: false,
        });
    }

</script>
<template>
    <div>
        <Card class="w-full lg:max-w-100 p-0 rounded gap-0 h-full">
            <CardContent class="px-3 pt-1 pb-2 relative">
                <div class="flex items-center gap-3 text-lg mb-1">
                    <!-- <div>$100</div> -->
                    <div>{{ formattedStartTime }}</div>
                </div>
                <div class="font-semibold text-xl">{{ ticket.name }}</div>
                <div class="text-gray-500"> {{ truncatedDescription }} </div>
                <!-- Actions -->
                <div class="flex gap-2 items-center justify-center absolute top-[22px] right-6">
                    <Link :href="route('frontend.ticket.edit', ticket.id)" variant="outline" size="icon"
                        class="rounded-full">
                    <Edit />
                    </Link>
                    <Button @click="handleDeleteTicket" variant="outline" size="icon" class="rounded-full text-red-500">
                        <Trash2 />
                    </Button>
                </div>
            </CardContent>
        </Card>
        <ConfirmDeleteDialog v-model="showDialog" :form="form" title="Confirm Deletion"
            description="Are you sure you want to delete your ticket?" @submit="deleteticket" />
    </div>
</template>
