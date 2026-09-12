<script setup lang="ts">
    import { Linkupticket } from "@/client/models/Linkupticket";
    import ticketCard from "@/components/front/TicketCard.vue";
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
    import MyticketCard from "@/components/front/MyticketCard.vue";

    const { tickets } = usePage<{
        tickets: Linkupticket[];
    }>().props;

    const filters = ref({
        status: new URL(window.location.href).searchParams.get('status') ?? 'all',
    })

    const reloadWithFilters = () => {
        router.visit(route('frontend.ticket.index', filters.value));
    }
</script>
<template>
    <AuthenticatedLayout>

        <Head title="tickets" />
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
            <ticketCard v-for="ticket in tickets" :ticket="ticket" :key="ticket.id" />
        </div>
    </AuthenticatedLayout>
</template>
