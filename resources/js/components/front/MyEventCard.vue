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


    const { event } = defineProps<{
        event: LinkupEvent;
    }>();

    const truncatedDescription = computed(() => {
        const text = event.description || ''
        return text.length > 34 ? text.substring(0, 34) + '......' : text
    })

    const formattedStartTime = computed(() => {
        return event.start_time ? moment(event.start_time).format('MMM YYYY') : ''
    })

    const form = useForm({});

    const toggleFavorite = (e: any) => {
        e.preventDefault();

        form.post(route('frontend.event.toggle-favorite', event.id), {
            preserveScroll: true,
            preserveState: false,
        })
    }
    //delte handle
    const handleDeleteEvent = (e: Event) => {
        e.preventDefault();
        e.stopPropagation(); // Prevent the Link click
        deletingUser.value = event.id;
        showDialog.value = true;
    };
    const deletingUser = ref<number | null>(null);
    const showDialog = ref(false);

    function deleteEvent(e: Event) {
        form.delete(route('frontend.myevents.destroy', event.id), {
            preserveScroll: true,
            preserveState: false,
        });
    }

</script>
<template>
    <div>
        <Card class="w-full lg:max-w-100 p-0 rounded gap-0 h-full">
            <CardHeader class="p-0 overflow-hidden">
                <div class="w-full h-56 overflow-hidden flex items-center justify-center">
                    <img :src="event.image_url || 'https://community.softr.io/uploads/db9110/original/2X/7/74e6e7e382d0ff5d7773ca9a87e6f6f8817a68a6.jpeg'"
                        alt="" />
                </div>
            </CardHeader>
            <CardContent class="px-3 pt-1 pb-2 relative">
                <div class="flex items-center gap-3 text-lg mb-1">
                    <!-- <div>$100</div> -->
                    <div>{{ formattedStartTime }}</div>
                </div>
                <div class="font-semibold text-xl">{{ event.title }}</div>
                <div class="text-gray-500"> {{ truncatedDescription }} </div>
                <!-- Actions -->
                <div class="flex gap-2 items-center justify-center absolute -top-[22px] right-6">
                    <!-- <Button @click="toggleFavorite" variant="outline" size="icon" class="rounded-full"
                        :disabled="form.processing">
                        <X v-if="event.auth_user_favorite" stroke-width="4" />
                        <Heart v-else fill="black" />
                    </Button> -->
                    <Link :href="route('frontend.myevents.edit', event.id)" variant="outline" size="icon"
                        class="rounded-full">
                    <Edit />
                    </Link>
                    <Button @click="handleDeleteEvent" variant="outline" size="icon" class="rounded-full text-red-500">
                        <Trash2 />
                    </Button>
                </div>
            </CardContent>
        </Card>
        <ConfirmDeleteDialog v-model="showDialog" :form="form" title="Confirm Deletion"
            description="Are you sure you want to delete your Event?" @submit="deleteEvent" />
    </div>
</template>
