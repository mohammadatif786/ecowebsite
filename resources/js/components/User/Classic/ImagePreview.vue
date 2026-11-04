<template>
  <div
    class="w-24 h-24 md:w-28 md:h-28 border-4 border-primary rounded-full flex items-center justify-center text-4xl text-sky-500"
  >
    <img
      :src="imageUrl ? imageUrl : 'https://picsum.photos/200/300'"
      alt="Image Preview"
      class="w-full h-full object-cover rounded-full shadow transition-transform duration-300"
    />
  </div>
</template>
<script setup lang="ts">
defineProps({
  imageUrl: {
    type: String,
    default: null,
  },
});
</script>
