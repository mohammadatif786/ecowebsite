<template>
    <div class="w-full p-6">
        <div v-if="props.images && props.images.length === 0" class="flex flex-row justify-center">
            <span class="font-bold text-xl">No Images found</span>
        </div>
        <!-- Main carousel -->
        <template v-else>
            <div class="relative overflow-hidden w-full rounded-xl ">
                <div class="flex transition-transform duration-700 ease-in-out"
                    :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
                    <div v-for="(img, index) in props.images" :key="index" class="flex-shrink-0 w-full">
                        <img :src="img" class="w-full max-h-[350px] object-contain rounded-xl" />
                    </div>
                </div>

                <!-- Nav buttons -->
                <button @click="prev"
                    class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-primary p-1 rounded-full shadow ">
                    <ChevronLeft />
                </button>
                <button @click="next"
                    class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-primary p-1 rounded-full shadow">
                    <ChevronRight />
                </button>
            </div>

            <!-- Thumbnails -->
            <div class="flex justify-center mt-4 gap-2 overflow-x-auto w-full">
                <div v-for="(img, index) in props.images" :key="'thumb-' + index"
                    class="cursor-pointer border-2 rounded-md"
                    :class="currentIndex === index ? 'border-primary-300' : 'border-transparent'"
                    @click="currentIndex = index">
                    <img :src="img" class="w-35 h-20 object-cover rounded-md" :alt="`Thumbnail ${index + 1}`" />
                </div>
            </div>
        </template>
    </div>

</template>

<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    images: Array<string>;
}
const props = defineProps<Props>();

const currentIndex = ref(0)

const prev = () => {
    currentIndex.value = (currentIndex.value - 1 + props.images.length) % props.images.length
}

const next = () => {
    currentIndex.value = (currentIndex.value + 1) % props.images.length
}
</script>
