<template>
  <article
    class="relative flex flex-col justify-end rounded-[30px] overflow-hidden shadow-lg h-[350px] text-white"
  >
    <Link :href="route('frontend.linkup.user.details', { user: id })">
      <img
        :src="more_photos?.length > 0 ? more_photos[0] : 'https://picsum.photos/200/300'"
        alt="User Profile Picture"
        class="absolute inset-0 w-full h-full object-cover"
      />
    </Link>
    <!-- Gradient overlay -->
    <div
      class="absolute bottom-0 w-full h-[50%] bg-gradient-to-t from-purple-800/90 via-purple-600/60 to-transparent z-10"
    />

    <!-- Match Percentage -->
    <!-- <div
      class="absolute top-3 right-3 z-20 bg-white/20 rounded-full w-12 h-12 flex items-center justify-center text-sm font-semibold"
    >
      {{ matchPercentage }}%
    </div> -->

    <!-- Premium badge -->
    <div
      v-if="isPremium"
      class="absolute top-3 left-3 z-20 flex items-center bg-white text-purple-800 text-xs font-semibold px-2 py-1 rounded-full"
    >
      <svg class="w-4 h-4 mr-1 fill-current" viewBox="0 0 24 24">
        <path
          d="M12 2l2.39 4.84L20 7.27l-4 3.89.94 5.48L12 15.77 7.06 16.64 8 11.16 4 7.27l5.61-.43L12 2z"
        />
      </svg>
      Premium
    </div>

    <!-- User info -->
    <div class="z-20 px-4 pb-4 text-white">
      <h3 class="text-lg font-semibold">{{ name }}, {{ age }}</h3>
      <p class="text-sm text-white/80 flex items-center gap-1 mt-0.5">
        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.66-2.76 6.82-5 9.88C9.76 15.82 7 11.66 7 9z"
          />
        </svg>
        {{ distance ?? "Unknown" }} KM
      </p>
      <p class="text-sm mt-1 truncate">{{ about_me }}</p>
    </div>

    <!-- Buttons -->
    <div class="z-20 flex justify-around items-center px-4 pb-5">
      <ActionButton
        icon-type="check"
        :aria-label="`Like ${name}`"
        :mask-id="`heart_${cardId}`"
        @click="$emit('accept')"
      />
      <ActionButton
        icon-type="close"
        :aria-label="`Pass on ${name}`"
        :mask-id="`close_${cardId}`"
        @click="$emit('reject')"
      />
    </div>
  </article>
</template>

<script setup lang="ts">
import ActionButton from "@/components/front/ActionButton.vue";
import { Link } from "@inertiajs/vue3";
interface Props {
  id: number;
  name: string | null;
  age: number | null;
  country: string | null;
  gender: string | null;
  cardId: string;
  more_photos: string[] | null;
}
defineProps<Props>();

defineEmits<{
  accept: [];
  reject: [];
}>();
</script>
