<script setup lang="ts">
import RestaurantCard from "@/components/front/RestaurantCard.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/front/ui/card";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { usePage } from "@inertiajs/vue3";

import SponsoredAdCard from "@/components/front/SponsoredAdCard.vue";
import { computed } from "vue";

type Restaurant = {
    place_id: string;
    name: string;
    address: string;
    rating: number | null;
    image: string;
    url: string;
}

const props = usePage<{
    restaurants: Restaurant[];
    swipeAds?: Record<string, any>[];
}>().props;

const gridItems = computed(() => {
    const items = [];
    const ads = props.swipeAds || [];

    // Shuffle ads randomly
    const shuffledAds = [...ads].sort(() => Math.random() - 0.5);
    let adIndex = 0;

    props.restaurants.forEach((restaurant) => {
        items.push({ type: 'restaurant', data: restaurant });

        // Randomly insert an ad with ~20% probability
        if (Math.random() < 0.1 && shuffledAds.length > 0) {
            items.push({ type: 'ad', data: shuffledAds[adIndex % shuffledAds.length] });
            adIndex++;
        }
    });

    return items;
});

</script>

<template>
    <AuthenticatedLayout>
        <Card>
            <CardHeader>
                <CardTitle>Restaurants near by</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <template v-for="(item, index) in gridItems" :key="index">
                        <RestaurantCard v-if="item.type === 'restaurant'" :restaurant="item.data" />
                        <!-- <SponsoredAdCard v-else-if="item.type === 'ad'" :ad="item.data" /> -->
                    </template>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
