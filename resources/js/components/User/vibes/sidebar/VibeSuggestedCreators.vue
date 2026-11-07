<template>
  <!-- Creators to follow, with a search and "See more". -->
  <div class="card p-4">
    <h3 class="font-black mb-3">Suggested creators</h3>
    <div class="relative mb-3">
      <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
      <label for="creator-search" class="sr-only">Search creators</label>
      <input id="creator-search" v-model="query" type="search" placeholder="Search creators…"
             class="w-full bg-slate-100 focus:bg-white border border-slate-200 focus:border-lkblue rounded-xl pl-9 pr-3 py-2 text-xs font-semibold outline-none transition" />
    </div>
    <div v-if="filtered.length" class="space-y-1">
      <div v-for="c in filtered.slice(0, limit)" :key="c.user_id" class="flex items-center gap-3 py-2">
        <button type="button" class="flex items-center gap-3 flex-1 min-w-0 text-left" @click="actions.openProfile(c.user_id)">
          <img :src="c.avatar || `https://i.pravatar.cc/120?u=${c.user_id}`" alt="" class="w-10 h-10 rounded-full object-cover bg-slate-200 shrink-0" />
          <span class="min-w-0">
            <span class="block font-bold text-sm truncate">{{ c.handle }}</span>
            <span v-if="c.name && c.name !== c.handle" class="block text-xs text-slate-400 truncate">{{ c.name }}</span>
          </span>
        </button>
        <button type="button" :disabled="busy === c.user_id" :class="['btn text-xs px-3 py-1.5 shrink-0', c.is_following ? 'btn-ghost opacity-60' : 'btn-ghost']" @click="follow(c)">
          {{ c.is_following ? 'Following' : 'Follow' }}
        </button>
      </div>
    </div>
    <p v-else class="text-xs font-bold text-slate-400 py-4 text-center">{{ query ? 'No creators found' : 'No suggested creators yet' }}</p>
    <button v-if="filtered.length > limit" type="button" class="w-full mt-2 py-2.5 text-xs font-black text-lkblue bg-slate-50 hover:bg-slate-100 rounded-xl flex items-center justify-center gap-1" @click="limit += 10">
      See more <ChevronDown class="w-3.5 h-3.5" />
    </button>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ChevronDown, Search } from 'lucide-vue-next';
import { toggleFollow } from '../api';
import { toast, useVibeActions } from '../useVibeActions';

const props = defineProps({ creators: { type: Array, default: () => [] } });
const emit = defineEmits(['followed']);
const actions = useVibeActions();
const query = ref('');
const limit = ref(6);
const busy = ref(null);

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase();
  return q ? props.creators.filter((c) => `${c.handle} ${c.name}`.toLowerCase().includes(q)) : props.creators;
});

async function follow(c) {
  busy.value = c.user_id;
  try {
    const data = await toggleFollow(c.user_id);
    c.is_following = !!data.is_following;
    toast(c.is_following ? `Following ${c.handle}` : `Unfollowed ${c.handle}`);
    emit('followed', c, data);
  } catch {
    toast('Couldn’t update follow');
  } finally {
    busy.value = null;
  }
}
</script>
