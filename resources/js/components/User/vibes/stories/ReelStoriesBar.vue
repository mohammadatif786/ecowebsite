<template>
  <!--
    The round bar at the top: "Add" a reel, "Your reel" when you have any, then one circle per creator
    who posted a reel in the last day. Tapping a circle opens that person's reels. Used on Vibes and U Vibe.
  -->
  <div class="card p-4">
    <div class="flex gap-4 overflow-x-auto hide-scroll">
      <button type="button" class="flex flex-col items-center gap-1 shrink-0" @click="$emit('add')">
        <span class="w-16 h-16 rounded-full border-2 border-dashed border-slate-300 grid place-items-center text-slate-400"><Plus class="w-6 h-6" /></span>
        <span class="text-xs font-bold text-slate-500">Add</span>
      </button>
      <button v-if="myReelsCount > 0" type="button" class="flex flex-col items-center gap-1 shrink-0" @click="$emit('mine')">
        <span class="w-16 h-16 rounded-full p-[3px] bg-gradient-to-tr from-emerald-400 to-sky-500">
          <img :src="myAvatar" alt="" class="w-full h-full rounded-full object-cover border-2 border-white bg-slate-200" />
        </span>
        <span class="text-xs font-semibold text-slate-600">Your reel</span>
      </button>
      <button v-for="s in stories" :key="s.user_id" type="button" class="flex flex-col items-center gap-1 shrink-0" @click="$emit('open', s)">
        <span :class="['w-16 h-16 rounded-full p-[3px] bg-gradient-to-tr', ring === 'violet' ? 'from-violet-500 via-fuchsia-500 to-purple-600' : 'from-lkyellow via-pink-500 to-lkblue']">
          <img :src="s.avatar || `https://i.pravatar.cc/120?u=${s.user_id}`" alt="" class="w-full h-full rounded-full object-cover border-2 border-white bg-slate-200" />
        </span>
        <span class="text-xs font-semibold text-slate-600 max-w-[64px] truncate">{{ s.handle }}</span>
      </button>
      <p v-if="!stories.length && !myReelsCount" class="self-center text-xs font-bold text-slate-400 pl-1">Follow creators to see their reels here.</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

defineProps({
  stories: { type: Array, default: () => [] },
  myReelsCount: { type: Number, default: 0 },
  // Story ring colours: 'brand' on Vibes, 'violet' on U Vibe (as in the design).
  ring: { type: String, default: 'brand' },
});
defineEmits(['add', 'mine', 'open']);
const page = usePage();
const myAvatar = computed(() => page.props.auth?.user?.avatar || `https://i.pravatar.cc/120?u=${page.props.auth?.user?.id || 'me'}`);
</script>
