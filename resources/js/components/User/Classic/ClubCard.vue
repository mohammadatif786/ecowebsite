<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';

type Club = {
    place_id: string;
    name: string;
    address: string;
    rating: number | null;
    image: string;
}

const { club } = defineProps<{
    club: Club;
}>();

</script>
<template>
    <Link :href="route('frontend.clubs.show', club.place_id)">
        <div class="group bg-white w-full group h-full">
            <div class="h-50 overflow-hidden flex justify-center items-center bg-black">
                <img
                    :src="club.image"
                    class="w-full transition-transform duration-300 ease-in-out group-hover:scale-110"
                />
            </div>
            <div class="px-3 py-4">
                <h3 class="font-bold">{{ club.name }}</h3>
                <p class="text-base mb-2">{{ club.address }}</p>
                <div class="flex justify-between items-center">
                    <div></div>
                    <div>
                        <div class="flex items-center gap-1 text-yellow-500 font-bold">
                            <span>
                                {{ club.rating ? club.rating.toFixed(1) : 'N/A' }}
                            </span>
                            <Star class="fill-yellow-500" :size="20" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>