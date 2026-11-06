<template>
  <!-- One reel filling the viewer: the video (tap to sound on/off) or photo, who posted it, and the buttons. -->
  <div class="reel-slide h-full w-full relative shrink-0 snap-start bg-black" :data-id="reel.id" @click="toggleMute" @dblclick.stop="$emit('like', true)">
    <video v-if="video" ref="videoEl" :src="reel.file_path" :poster="reel.thumbnail_path || undefined"
           class="absolute inset-0 w-full h-full object-contain bg-black" :muted="muted" loop playsinline preload="metadata"></video>
    <img v-else :src="reel.file_path" alt="" class="absolute inset-0 w-full h-full object-contain bg-black" />

    <span v-if="video" class="absolute top-14 right-3 z-10 h-9 w-9 rounded-full bg-black/45 backdrop-blur grid place-items-center pointer-events-none">
      <VolumeX v-if="muted" class="w-5 h-5 text-white" /><Volume2 v-else class="w-5 h-5 text-white" />
    </span>
    <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(to top,rgba(0,0,0,.75),rgba(0,0,0,.05) 45%,rgba(0,0,0,.25))"></div>

    <div class="absolute right-3 bottom-20">
      <ReelActionRail :reel="reel" @like="$emit('like')" @comments="$emit('comments')" @bigup="$emit('bigup')"
                      @share="$emit('share')" @save="$emit('save')" @delete="$emit('delete')" />
    </div>

    <div class="absolute left-3 right-16 bottom-5 text-white">
      <button type="button" class="flex items-center gap-2 mb-1" @click.stop="$emit('profile')">
        <img :src="reel.avatar || `https://i.pravatar.cc/80?u=${reel.user_id}`" alt="" class="h-9 w-9 rounded-full object-cover border-2 border-white bg-slate-700" />
        <span class="font-black text-sm">{{ reel.is_mine ? 'You' : reel.handle }}</span>
      </button>
      <p v-if="reel.caption" class="text-sm leading-snug break-words">
        <template v-for="(part, i) in parts" :key="i"><b v-if="part.tag">{{ part.text }}</b><template v-else>{{ part.text }}</template></template>
      </p>
      <p v-if="reel.location" class="text-[11px] text-white/85 mt-1 flex items-center gap-1"><MapPin class="w-3 h-3" />{{ reel.location }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { MapPin, Volume2, VolumeX } from 'lucide-vue-next';
import ReelActionRail from './ReelActionRail.vue';
import { captionParts, isVideoReel } from '../format';

const props = defineProps({
  reel: { type: Object, required: true },
  muted: { type: Boolean, default: true },
});
const emit = defineEmits(['like', 'comments', 'bigup', 'share', 'save', 'delete', 'profile', 'toggleMute']);

const videoEl = ref(null);
const video = computed(() => isVideoReel(props.reel));
const parts = computed(() => captionParts(props.reel.caption));

function toggleMute() {
  if (video.value) emit('toggleMute');
}

defineExpose({
  play: () => videoEl.value?.play?.().catch(() => {}),
  pause: () => videoEl.value?.pause?.(),
});
</script>
