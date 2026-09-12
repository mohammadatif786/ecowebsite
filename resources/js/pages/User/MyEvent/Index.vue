<script setup lang="ts">
    import { LinkupEvent } from "@/client/models/LinkupEvent";
    import EventCard from "@/components/front/EventCard.vue";
    import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
    import { router, usePage } from "@inertiajs/vue3";
    import FilterDialog from "@/components/front/FilterDialog.vue";
    import {
        Select,
        SelectContent,
        SelectGroup,
        SelectItem,
        SelectLabel,
        SelectTrigger,
        SelectValue,
    } from '@/components/front/ui/select'
    import { ref } from "vue";
    import Label from "@/components/front/ui/label/Label.vue";
    import MyEventCard from "@/components/front/MyEventCard.vue";

    const { events } = usePage<{
        events: LinkupEvent[];
    }>().props;

    const filters = ref({
        status: new URL(window.location.href).searchParams.get('status') ?? 'all',
    })

    const reloadWithFilters = () => {
        router.visit(route('frontend.event.index', filters.value));
    }
</script>
<template>
    <AuthenticatedLayout>

        <Head title="Events" />
        <!-- <div class="mb-4 flex items-center justify-end">
            <FilterDialog @apply="reloadWithFilters">
                <Label class="mb-2">Status</Label>
                <Select v-model="filters.status">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select a fruit" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="all"> All </SelectItem>
                            <SelectItem value="upcomming"> Upcomming </SelectItem>
                            <SelectItem value="live"> Live </SelectItem>
                            <SelectItem value="completed"> Completed </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </FilterDialog>
        </div> -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <MyEventCard v-for="event in events" :event="event" :key="event.id" />
        </div>
    </AuthenticatedLayout>
</template>
