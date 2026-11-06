<template>
  <!-- The "Write a comment…" box, with the "Replying to …" line when answering someone. -->
  <div>
    <div v-if="replyingTo" :class="['flex items-center justify-between px-3 py-1 rounded-lg mb-2 text-[11px] font-bold', dark ? 'bg-white/10 text-white/70' : 'bg-slate-50 text-slate-500']">
      <span>Replying to <span class="text-lkblue">{{ replyingTo.user?.name }}</span></span>
      <button type="button" aria-label="Stop replying" class="hover:text-rose-500" @click="$emit('cancelReply')"><X class="w-3 h-3" /></button>
    </div>
    <form class="flex items-center gap-2" @submit.prevent="send">
      <img :src="avatar" alt="" class="h-8 w-8 rounded-full object-cover shrink-0 bg-slate-200" />
      <div class="flex-1 relative flex items-center">
        <label :for="inputId" class="sr-only">Write a comment</label>
        <input :id="inputId" ref="input" v-model="text" maxlength="1000" placeholder="Write a comment…"
               :class="['w-full rounded-2xl px-4 py-2 pr-10 text-[13px] font-medium outline-none focus:ring-1 focus:ring-lkblue transition', dark ? 'bg-white/10 text-white placeholder:text-white/50' : 'bg-[#F0F2F5] focus:bg-white']" />
        <button type="submit" aria-label="Post comment" :disabled="!text.trim() || busy"
                class="absolute right-2 p-1 text-lkblue rounded-full disabled:opacity-30">
          <SendHorizontal class="w-4 h-4" />
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { SendHorizontal, X } from 'lucide-vue-next';

const props = defineProps({
  busy: { type: Boolean, default: false },
  replyingTo: { type: Object, default: null },
  dark: { type: Boolean, default: false },
});
const emit = defineEmits(['submit', 'cancelReply']);

const page = usePage();
const avatar = computed(() => page.props.auth?.user?.avatar || '');
const inputId = 'cmt-' + Math.random().toString(36).slice(2, 8);
const text = ref('');
const input = ref(null);

watch(() => props.replyingTo, (r) => r && nextTick(() => input.value?.focus()));

function send() {
  const value = text.value.trim();
  if (!value || props.busy) return;
  emit('submit', value, () => { text.value = ''; });
}

defineExpose({ focus: () => input.value?.focus() });
</script>
