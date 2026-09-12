<template>
  <article
    class="relative h-[231px] w-[200px] max-md:w-[180px] max-sm:mx-auto max-sm:my-0 max-sm:w-[280px]"
  >
    <div
      class="absolute top-0 left-0 bg-white h-[221px] w-[200px] max-sm:w-[280px]"
    ></div>

    <div
      class="absolute top-2.5 bg-yellow-400 rounded-full h-[127px] left-[34px] w-[127px] max-md:h-[110px] max-md:left-[35px] max-md:w-[110px] max-sm:left-20 max-sm:h-[120px] max-sm:w-[120px]"
    >
      <Link :href="route('frontend.linkup.user.details', { user: id })">
        <img
          :src="
            more_photos?.length > 0 ? more_photos[0] : 'https://picsum.photos/200/300'
          "
          alt="User Profile Picture"
          class="rounded-full w-full h-full object-cover"
        />
      </Link>
    </div>

    <h3
      class="absolute left-0 right-0 top-36 text-sm tracking-wide text-center whitespace-nowrap text-black mx-auto"
    >
      {{ name }}
    </h3>

    <p
      class="absolute text-xs tracking-wide text-center text-black opacity-40 left-0 right-0 top-[170px] max-sm:left-10 max-sm:w-[200px]"
    >
      {{ age }}, {{ country }}, {{ gender }}
    </p>

    <div
      class="flex absolute rounded-b-full gap-px w-16 h-[31px] left-[90px] top-[200px] max-sm:left-[124px]"
    >
      <MessageSquare
        class="cursor-pointer hover:text-blue-600 text-black"
        @click="$emit('like')"
      />
    </div>
  </article>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { MessageSquare } from "lucide-vue-next";
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
  like: [];
  pass: [];
}>();
</script>
