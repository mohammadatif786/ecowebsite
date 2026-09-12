<script setup lang="ts">
import { Card, CardContent, CardHeader } from "@/components/front/ui/card";
import { Link, useForm } from "@inertiajs/vue3";
import { LinkupEvent } from "@/client/models/LinkupEvent";
import { computed } from "vue";
import moment from "moment";
import Button from "./ui/button/Button.vue";
import { Heart, X } from "lucide-vue-next";

const { event } = defineProps<{
    event: LinkupEvent;
}>();

const truncatedDescription = computed(() => {
    const text = event.description || "";
    return text.length > 34 ? text.substring(0, 34) + "......" : text;
});

const formattedStartTime = computed(() => {
    return event.start_time ? moment(event.start_time).format("MMM YYYY") : "";
});

// Top-left badge month/day from event_details
const baseDate = computed(() => {
    const d = event?.event_details;
    if (!d) return null;
    if (d.event_type === "single") return d.single_event_date || null;
    if (d.event_type === "recurring") return d.recurr_end_date || null;
    return null;
});

const badgeMonth = computed(() => (baseDate.value ? moment(baseDate.value).format("MMM").toUpperCase() : ""));
const badgeDay = computed(() => (baseDate.value ? moment(baseDate.value).format("D") : ""));

// Category chip text (show only if available)
const categoryName = computed(() => (event?.category?.name ? event.category.name : (event?.type || "")));

const form = useForm({});

const toggleFavorite = (e: any) => {
    e.preventDefault();

    form.post(route("frontend.event.toggle-favorite", event.id), {
        preserveScroll: true,
        preserveState: false,
    });
};
</script>
<template>
    <Link :href="route('frontend.event.show', event.slug)">
        <Card class="w-full lg:max-w-100 p-0 rounded-xl gap-0 h-full">
            <CardHeader class="p-0 overflow-hidden">
                <div class="relative w-full h-56 overflow-hidden flex items-center justify-center">
                    <img :src="event?.image_url" @error="(e) => {
                        const img = e.target as HTMLImageElement | null;
                        if (img) img.src = 'https://picsum.photos/200/300';
                    }" alt="" class="w-full h-full object-cover" />
                    <!-- Top-left date badge -->
                    <div v-if="badgeMonth && badgeDay" class="absolute top-3 left-3 shadow-md rounded overflow-hidden">
                        <div class="badge-month">{{ badgeMonth }}</div>
                        <div class="badge-day">{{ badgeDay }}</div>
                    </div>

                    <!-- Top-right category chip -->
                    <div v-if="categoryName" class="absolute top-3 right-3">
                        <div class="category-chip">{{ categoryName }}</div>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="px-3 pt-1 pb-2 relative">
                <div class="flex items-center gap-3 text-lg mb-1">
                    <!-- <div>$100</div> -->
                    <div>{{ formattedStartTime }}</div>
                </div>
                <div class="font-semibold text-xl">{{ event.title }}</div>
                <div class="text-gray-500" v-html="truncatedDescription"></div>

                <!-- Actions -->
                <div class="flex gap-2 items-center justify-center absolute -top-[22px] right-6">
                    <Button @click="toggleFavorite" variant="outline" size="icon" class="rounded-full"
                        :disabled="form.processing">
                        <X v-if="event.auth_user_favorite" stroke-width="4" />
                        <Heart v-else fill="black" />
                    </Button>
                </div>
            </CardContent>
        </Card>
    </Link>
</template>

<style scoped>
.badge-month {
    background: linear-gradient(180deg, #34d399 0%, #14b8a6 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 12px;
    line-height: 1;
    padding: 6px 10px;
    text-transform: uppercase;
    text-align: center;
    letter-spacing: 0.06em;
}

.badge-day {
    background: #ffffff;
    color: #111827;
    font-weight: 900;
    font-size: 18px;
    line-height: 1;
    padding: 10px 12px;
    text-align: center;
    border-left: 1px solid rgba(17, 24, 39, 0.06);
    border-right: 1px solid rgba(17, 24, 39, 0.06);
    border-bottom: 1px solid rgba(17, 24, 39, 0.06);
}

.category-chip {
    background: #ffffff;
    color: #111827;
    border-radius: 9999px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}
</style>
