<script setup lang="ts">
import { LinkupEvent } from '@/client/models/LinkupEvent';
import Button from '@/components/admin/ui/button/Button.vue';
import EventCard from '@/components/front/EventCard.vue';
import FilterDialog from '@/components/front/FilterDialog.vue';
import Input from '@/components/front/Input.vue';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/front/ui/carousel';
import Label from '@/components/front/ui/label/Label.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/front/ui/select';
import { useCountryStateCity } from '@/composables/useCountryStateCity';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const { countries, fetchCountries } = useCountryStateCity();
const { events, allCategories, newProvidence, eventLowCost, jamaicaEvent, freeEvents, user } = usePage<{
    events: LinkupEvent[];
    allCategories: any;
    newProvidence: LinkupEvent[];
    eventLowCost: LinkupEvent[];
    jamaicaEvent: LinkupEvent[];
    freeEvents: LinkupEvent[];
    user: any;
}>().props;

const searchParams = new URL(window.location.href).searchParams;

type EventFilters = {
    status: string;
    search: string;
    country: string;
    category_id: string;
};

const filters = ref<EventFilters>({
    status: searchParams.get('status') ?? 'all',
    search: searchParams.get('search') ?? '',
    country: searchParams.get('country') ?? 'all',
    category_id: '',
});

const reloadWithFilters = () => {
    router.visit(route('frontend.event.filter', filters.value));
};

const selectedCategory = (categoryId: string) => {
    filters.value.category_id = categoryId;
    reloadWithFilters();
};

onMounted(() => {
    fetchCountries();
});

let debounceTimeout: number = 0;
const handleSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        reloadWithFilters();
    }, 400);
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Events" />
        <div v-if="allCategories?.length > 0">
            <div class="carousel-wrapper">
                <div class="carousel-container">
                    <div class="category-card flex flex-row items-center justify-center" v-for="category in allCategories"
                        :key="category.id" @click="selectedCategory(category.id)">
                        <img class="border-primary-front mb-2 h-10 w-10 rounded-full border-2 object-cover"
                            :src="category.image_object ? category.image_object : 'https://picsum.photos/200'" />
                        <h1 class="ml-4">{{ category.name }}</h1>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-primary py-4 bg-white rounded-lg">No categories found</div>
        <div class="mt-6 mb-4 flex items-center justify-end gap-3">
            <!-- Filter -->
            <FilterDialog @apply="reloadWithFilters">
                <Label class="mb-2">Status</Label>
                <Select v-model="filters.status">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="select filter" />
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

                <Label class="my-2">Country</Label>
                <Select v-model="filters.country">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select filter" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="all"> All </SelectItem>
                            <SelectItem v-for="country in countries" :key="country.value" :value="country.label">
                                {{ country.label }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>

                <Label class="my-2">Category</Label>
                <Select v-model="filters.category_id">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select filter" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="all"> All </SelectItem>
                            <SelectItem v-for="category in allCategories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </FilterDialog>

            <!-- Create Event Button -->
            <!-- <Button as-child>
                <Link :href="route('frontend.myevents.create')">Create Event</Link>
            </Button> -->
        </div>


        <div class="mb-16 w-full">
            <!-- Row: Search + Button + Heading -->
            <div class="flex items-center gap-4">
                <!-- Search -->
                <div class="relative flex">
                    <Search :size="18" class="absolute top-[9px] left-3" />
                    <Input type="search" placeholder="Search..." class="pl-10" v-model="filters.search" />
                    <Button @click="handleSearch()" class="ml-2 bg-[#D5DB2B] text-[#111827] hover:bg-[#c8ce28]">Go</Button>
                </div>


            </div>
            <!-- Heading (immediately after search) -->
            <h1 class="mt-5 text-base font-bold text-white md:text-xl lg:text-3xl">
                Top Trending Events
            </h1>
            <!-- Carousel -->
            <div class="mt-3 bg-white rounded-lg">
               <div v-if="newProvidence?.length > 0">

                <Carousel>
                    <CarouselPrevious class="absolute top-[-40px] left-[calc(100%-100px)] z-10 -translate-y-1/2" />
                    <CarouselNext class="absolute top-[-40px] -right-[-20px] z-10 -translate-y-1/2" />
                    <CarouselContent>
                        <CarouselItem v-for="event in newProvidence" :key="event.id"
                            class="basis-full sm:basis-1/2 md:basis-1/2 lg:basis-1/2 xl:basis-1/4">
                            <EventCard :event="event" />
                            </CarouselItem>
                        </CarouselContent>
                    </Carousel>
                </div>
                <div v-else class="text-center text-primary py-4">No Trending Events found</div>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-white">Event 30$ and under</h1>
        <div class="mt-3 mb-16 w-full bg-white rounded-lg">
            <div v-if="eventLowCost?.length > 0">
                <Carousel class="mt-5">
                    <CarouselPrevious class="absolute top-[-40px] left-[calc(100%-100px)] z-10 -translate-y-1/2" />
                    <CarouselNext class="absolute top-[-40px] -right-[-20px] z-10 -translate-y-1/2" />
                    <CarouselContent>
                        <CarouselItem v-for="event in eventLowCost" :key="event.id"
                            class="basis-full sm:basis-1/1 md:basis-1/1 lg:basis-1/2 xl:basis-1/4">
                            <EventCard :event="event" />
                        </CarouselItem>
                    </CarouselContent>
                </Carousel>
            </div>
            <div v-else class="text-center text-primary py-4">
                No Events 30$ and under found
            </div>
        </div>
        <h1 class="text-3xl font-bold text-white">Free Events</h1>
        <div class="mt-3 mb-16 w-full bg-white rounded-lg">
            <div v-if="freeEvents?.length > 0">
                <Carousel class="mt-5">
                    <CarouselPrevious class="absolute top-[-40px] left-[calc(100%-100px)] z-10 -translate-y-1/2" />
                    <CarouselNext class="absolute top-[-40px] -right-[-20px] z-10 -translate-y-1/2" />
                    <CarouselContent>
                        <CarouselItem v-for="event in freeEvents" :key="event.id"
                            class="basis-full sm:basis-1/1 md:basis-1/1 lg:basis-1/2 xl:basis-1/4">
                            <EventCard :event="event" />
                        </CarouselItem>
                    </CarouselContent>
                </Carousel>
            </div>
            <div v-else class="text-center text-primary py-4">
                No Free Events found
            </div>
        </div>
        <h1 class="text-3xl font-bold text-white">Events in {{ user?.country }}</h1>
        <div class="mt-4 mb-16 w-full rounded-lg bg-white">
            <div v-if="jamaicaEvent?.length > 0">
                <Carousel class="p-5">
                    <CarouselPrevious class="absolute top-[-40px] left-[calc(100%-100px)] z-10 -translate-y-1/2" />
                    <CarouselNext class="absolute top-[-40px] -right-[-20px] z-10 -translate-y-1/2" />
                    <CarouselContent>
                        <CarouselItem v-for="event in jamaicaEvent" :key="event.id"
                            class="basis-full sm:basis-1/1 md:basis-1/1 lg:basis-1/2 xl:basis-1/4">
                            <EventCard :event="event" />
                        </CarouselItem>
                    </CarouselContent>
                </Carousel>
            </div>
            <div v-else class="text-center text-primary py-4">
                No Events in {{ user?.country }} found
            </div>
        </div>
        <h1 class="text-3xl font-bold text-white">More Events</h1>
        <div class="mt-4 w-full rounded-lg bg-white mb-5">
            <div v-if="events?.length > 0">
                <Carousel class="p-5">
                    <CarouselPrevious class="absolute top-[-40px] left-[calc(100%-100px)] z-10 -translate-y-1/2" />
                    <CarouselNext class="absolute top-[-40px] -right-[-20px] z-10 -translate-y-1/2" />
                    <CarouselContent>
                        <CarouselItem v-for="event in events" :key="event.id"
                            class="basis-full sm:basis-1/1 md:basis-1/1 lg:basis-1/2 xl:basis-1/4">
                            <EventCard :event="event" />
                        </CarouselItem>
                    </CarouselContent>
                </Carousel>
            </div>
            <div v-else class="text-center text-primary py-4">
                No More Events found
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
.carousel-wrapper {
    background-color: #ffffff15;
    border-radius: 12px;
    padding: 20px;
    margin: 0 auto;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.carousel-container {
    overflow-x: auto;
    display: flex;
    gap: 16px;
    padding-bottom: 10px;
    scroll-behavior: smooth;
}

.category-card {
    flex: 0 0 auto;
    background-color: white;
    color: #222;
    padding: 10px 20px;
    border-radius: 40px;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    white-space: nowrap;
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.category-card:hover {
    transform: scale(1.05);
    background-color: #f7f7f7;
}
</style>
