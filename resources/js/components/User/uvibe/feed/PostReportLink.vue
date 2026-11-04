<template>
  <!-- The red "Report post" pill under other people's posts. -->
  <div class="flex justify-end mt-2">
    <button type="button" @click="$emit('report', post)"
            class="inline-flex items-center gap-[5px] rounded-full border-[1.5px] border-[#fecaca] bg-white px-[11px] py-[5px] text-[11px] font-black text-[#b91c1c]">
      🚩 Report post
    </button>
  </div>
</template>

<script setup>
defineProps({ post: { type: Object, required: true } });
defineEmits(['report']);
</script>
