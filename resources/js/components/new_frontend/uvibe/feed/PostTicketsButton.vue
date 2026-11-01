<template>
  <!--
    A price on a U Vibe post isn't sold here — tickets are sold through Events. This opens Events,
    searched for the event the poster linked (or, if none, the post's title).
  -->
  <button type="button" @click="go" title="Get tickets in Events"
          class="shrink-0 inline-flex items-center gap-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 text-sm font-black">
    🎟️ {{ money(post.price) }} · Tickets ›
  </button>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { money } from '../../../../services/uvibeFormat';

const props = defineProps({ post: { type: Object, required: true } });

const go = () => {
  const linked = props.post.tag?.kind === 'event' ? props.post.tag.title : '';
  router.visit(route('new_frontend.events', { search: linked || props.post.title }));
};
</script>
