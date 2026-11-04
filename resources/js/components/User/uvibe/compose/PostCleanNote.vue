<template>
  <p class="text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-2.5 py-2 leading-snug">
    🌴 Family-friendly only. Nudity or pornography means your coins are seized and your account is deleted.
    <button type="button" class="font-black underline" @click="rules?.open()">Community rules</button>
    <Teleport to="body"><KeepVibesCleanModal ref="rules" /></Teleport>
  </p>
</template>

<script setup>
import { ref } from 'vue';
import KeepVibesCleanModal from '../../modals/KeepVibesCleanModal.vue';

const rules = ref(null);
</script>
