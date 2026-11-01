<template>
  <!-- Who posted: avatar + name (with the verified tick) and, for organization posts, the badge. -->
  <div class="flex items-center gap-3 min-w-0 flex-1">
    <component :is="linkable ? 'button' : 'span'" v-bind="linkable ? { type: 'button' } : {}" @click="open"
               class="h-11 w-11 rounded-full bg-violet-100 text-violet-700 grid place-items-center font-black shrink-0">
      {{ name.slice(0, 1).toUpperCase() }}
    </component>
    <div class="flex-1 min-w-0">
      <component :is="linkable ? 'button' : 'span'" v-bind="linkable ? { type: 'button' } : {}" @click="open"
                 class="font-black text-sm flex items-center gap-1 max-w-full text-left">
        <span :class="post.asOrg ? 'break-words' : 'truncate'">{{ name }}</span>
        <BadgeCheck v-if="post.verified" class="w-3.5 h-3.5 text-[#2f9bef] shrink-0" />
      </component>
      <p v-if="post.asOrg" class="mt-0.5">
        <span class="inline-block rounded-full px-2 py-0.5 text-[11px] font-black" style="background:#fef3c7;color:#92400e">{{ post.asOrg.emoji }} {{ post.asOrg.type }}</span>
      </p>
      <slot />
      <p v-if="post.asOrg" class="text-[11px] text-slate-500 font-bold mt-0.5 break-words">
        Posted by
        <button v-if="post.poster_id" type="button" @click="openPerson" class="font-black hover:underline">{{ post.postedBy || '' }}</button>
        <template v-else>{{ post.postedBy || '' }}</template><template v-if="post.role"> · {{ post.role }}</template>
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { BadgeCheck } from 'lucide-vue-next';
import { posterName } from '../../../../services/uvibeFormat';

const props = defineProps({ post: { type: Object, required: true } });

const name = computed(() => posterName(props.post));

// A person's post opens their profile. An organization or the university has no page of its own,
// so its name stays plain text (the "Posted by" line below it opens the member who posted).
const linkable = computed(() => !props.post.asOrg && !!props.post.poster_id);

const goProfile = () => router.visit(route('new_frontend.vibes.user-profile', props.post.poster_id));
const open = () => { if (linkable.value) goProfile(); };
const openPerson = () => { if (props.post.poster_id) goProfile(); };
</script>
