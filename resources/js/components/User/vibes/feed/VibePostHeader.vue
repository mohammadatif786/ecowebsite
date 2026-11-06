<template>
  <!-- Who posted it (tap for their profile), where, and the post's quick buttons. -->
  <div class="flex items-center gap-3 p-4">
    <button type="button" class="flex items-center gap-3 flex-1 min-w-0 text-left" @click="actions.openProfile(post.created_by)">
      <img :src="post.avatar" alt="" class="w-11 h-11 rounded-full object-cover bg-slate-200 shrink-0" />
      <span class="flex-1 min-w-0">
        <span class="font-black text-sm flex items-center gap-1.5 min-w-0">
          <span v-if="badge" aria-hidden="true">{{ badge.icon }}</span>
          <span class="truncate">{{ post.handle }}</span>
          <span v-if="badge" class="text-[9px] font-black text-white px-1.5 py-0.5 rounded-full uppercase shrink-0" style="background:var(--lk-blue,#2563eb)">{{ badge.label }}</span>
        </span>
        <span class="text-xs text-slate-500 flex items-center gap-1 min-w-0">
          <template v-if="post.location"><MapPin class="w-3 h-3 shrink-0" /><span class="truncate">{{ post.location }}</span><span aria-hidden="true">·</span></template>
          <span v-if="post.kind === 'reel'" class="text-lkblue2 font-bold">Video ·</span>
          <span class="shrink-0">{{ timeAgo(post.created_at) }}</span>
        </span>
      </span>
    </button>
    <span v-if="post.shoppable" class="text-xs font-black text-purple-600 bg-purple-50 px-2.5 py-1 rounded-full flex items-center gap-1 shrink-0">
      <ShoppingBag class="w-3 h-3" />Shop
    </span>
    <button v-if="!mine" type="button" title="Send a Vibe" aria-label="Send a Vibe" class="text-lg shrink-0 hover:scale-110 transition" @click="actions.sendVibe(post.created_by, post)">🌹</button>
    <button type="button" aria-label="Post options" class="text-slate-400 hover:text-slate-600 p-1 shrink-0" @click="actions.openOptions(post)">
      <MoreHorizontal class="w-5 h-5" />
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { MapPin, MoreHorizontal, ShoppingBag } from 'lucide-vue-next';
import { useVibeActions } from '../useVibeActions';
import { timeAgo } from '../format';

const props = defineProps({ post: { type: Object, required: true } });
const actions = useVibeActions();
const page = usePage();
const mine = computed(() => String(props.post.created_by) === String(page.props.auth?.user?.id));
const badge = computed(() => ({
  organization: { icon: '🏢', label: 'Organization' },
  group: { icon: '👥', label: 'Group' },
}[props.post.publisher_type] || null));
</script>
