<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/front/ui/card";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { CameraIcon, Share2, Share2Icon, Star, StarIcon } from 'lucide-vue-next';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/front/ui/carousel'

type Club = {
    place_id: string;
    name: string;
    address: string;
    rating: number | null;
    image: string;
    photos: string[];
    phone?: string | null;
    availablitiy?: boolean;
    user_ratings_total?: number;
    reviews: any[];
}

const { club } = usePage<{
    club: Club;
}>().props;
</script>

<template>
    <AuthenticatedLayout>
        <Card>
            <CardContent>
                <Carousel class="relative w-[90%] mx-auto">
                    <CarouselContent>
                        <CarouselItem v-for="(photo, index) in club.photos" :key="index">
                            <div class="relative h-80 overflow-hidden flex justify-center items-center">
                                <img
                                    :src="photo"
                                    class="w-full"
                                />
                            </div>
                        </CarouselItem>
                    </CarouselContent>
                    <CarouselPrevious />
                    <CarouselNext />
                </Carousel>
                <div class="px-3 py-4">
                    <div class="flex justify-between items-center gap-1 mb-3">
                        <h3 class="font-bold text-2xl">{{ club.name }}</h3>
                        <div class="flex items-center gap-1 text-yellow-500 font-medium">
                            <span>
                                {{ club.rating ? club.rating.toFixed(1) : 'N/A' }}
                            </span>
                            <Star class="fill-yellow-500" :size="20" />
                        </div>
                    </div>
                    <p class="text-base mb-4">{{ club.address }}</p>
                    <div class="flex flex-col gap-2 mb-4">
                        <div class="flex justify-between items-center text-lg font-medium">
                            <div class="text-gray-600">Call</div>
                            <div class="text-yellow-500">{{ club.phone }}</div>
                        </div>
                        <!-- <div class="flex justify-between items-center">
                            <div>Average cost</div>
                            <div></div>
                        </div> -->
                        <div class="flex justify-between items-center text-lg font-medium">
                            <div class="text-gray-600">Availablitiy</div>
                            <div class="text-yellow-500">{{ club.availablitiy ? 'Open now' : 'Closed' }}</div>
                        </div>
                    </div>

                    <div class="flex justify-center items-center gap-4 mb-8">
                        <div class="bg-white flex gap-1 flex-col items-center text-center p-4 w-30 rounded">
                            <Share2Icon />
                            <span class="font-medium">Share</span>
                            <span class="text-yellow-500 font-medium">500</span>
                        </div>
                        <!-- <div class="bg-white flex gap-1 flex-col items-center text-center p-4 w-30 rounded">
                            <StarIcon />
                            <span class="font-medium">Review</span>
                            <span class="text-yellow-500 font-medium">{{ club.user_ratings_total }}</span>
                        </div> -->
                        <div class="bg-white flex gap-1 flex-col items-center text-center p-4 w-30 rounded">
                            <CameraIcon />
                            <span class="font-medium">Photos</span>
                            <span class="text-yellow-500 font-medium">{{ club.photos.length }}</span>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-bold mb-4">Reviews</h2>
                        <div>
                            <div>
                                <div v-if="club.reviews && club.reviews.length">
                                    <div
                                        v-for="(review, idx) in club.reviews"
                                        :key="idx"
                                        class="bg-white rounded p-4 mb-3"
                                    >
                                        <div class="flex items-center gap-2 mb-2">
                                            <img
                                                v-if="review.profile_photo_url"
                                                :src="review.profile_photo_url"
                                                alt="User"
                                                class="w-8 h-8 rounded-full object-cover"
                                            />
                                            <span class="font-semibold">{{ review.author_name || 'Anonymous' }}</span>
                                            <span class="flex items-center text-yellow-500 ml-2">
                                                <StarIcon class="fill-yellow-500" :size="16" />
                                                <span class="ml-1 text-sm">{{ review.rating.toFixed(1) }}</span>
                                            </span>
                                        </div>
                                        <div class="text-gray-700 mb-1">
                                            {{ review.text }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ review.relative_time_description || review.time }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-gray-400 italic py-4">
                                    No reviews yet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
