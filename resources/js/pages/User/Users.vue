<template>
    <AuthenticatedLayout>
        <div>
            <div class="min-h-48 p-5 bg-white text-sm rounded-md">
                <div class=" flex justify-between items-center">
                    <p>just for you</p>
                    <div class="flex items-center">
                        <p>Apply Filters</p>
                        <i class="ri-filter-2-line text-2xl cursor-pointer"></i>
                    </div>
                </div>
                <div class="py-20 md:py-40 text-center">
                    <i class="ri-group-line text-4xl text-gray-400"></i>
                    <p class="mt-3">No more user to show, update your filter to view more</p>
                </div>
            </div>
            <div class="bg-white mt-5 p-5 text-sm">
                <div class=" flex justify-between items-center">
                    <p>Other user Profiles</p>
                    <div class="flex items-center">
                        <p>Apply Filters</p>
                        <i class="ri-filter-2-line text-2xl cursor-pointer"></i>
                    </div>
                </div>
                <div class="flex gap-5 flex-wrap justify-evenly mt-6">
                    <div v-for="n in 12"
                        class="flex flex-col justify-center items-center w-fit bg-gray-200 rounded-md px-6 pt-3 ">
                        <span class="bg-primary h-32 w-32 rounded-full"></span>
                        <p>Name</p>
                        <p class="text-gray-500">i8, markaz Male</p>
                        <div class="flex gap-2 mt-3 -mb-2">
                            <i
                                class="ri-heart-fill text-xl border rounded-full h-8 w-8 text-center border-gray-400 "></i>
                            <i
                                class="ri-close-line text-xl border rounded-full h-8 w-8 text-center border-gray-400 font-bold text-red-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup lang="ts">
    import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
</script>
<style scoped></style>
