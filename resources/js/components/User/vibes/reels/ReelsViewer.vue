<template>
  <!--
    Full-screen reels: scroll (or ↑ ↓) through them, the one on screen plays, tap for sound.
    Works on any page: open(reels, startIndex) or openCreator(userId). It brings its own comments
    panel and Big Up window, so a page only needs <ReelsViewer ref="viewer" />.
  -->
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-[1000] bg-slate-950/80 backdrop-blur-sm grid place-items-center p-0 sm:p-5" role="dialog" aria-modal="true" aria-label="Reels" @click.self="close">
      <div class="relative w-full sm:max-w-sm h-full sm:h-[88vh] bg-black sm:rounded-3xl overflow-hidden">
        <div class="absolute top-0 left-0 right-0 z-20 p-3 flex items-center justify-between bg-gradient-to-b from-black/60 to-transparent pointer-events-none">
          <h3 class="text-white font-black flex items-center gap-2"><Clapperboard class="w-5 h-5" />{{ title }}</h3>
          <button type="button" aria-label="Close" class="text-white pointer-events-auto p-1" @click="close"><X class="w-5 h-5" /></button>
        </div>

        <div v-if="loading" class="h-full grid place-items-center text-white/70 font-bold text-sm">Loading reels…</div>
        <div v-else-if="!reels.length" class="h-full grid place-items-center text-white/70 font-bold text-sm px-6 text-center">No reels here yet.</div>
        <div v-else ref="scroller" class="h-full overflow-y-auto snap-y snap-mandatory hide-scroll">
          <ReelSlide v-for="(r, i) in reels" :key="r.id" :ref="(el) => (slides[i] = el)" :reel="r" :muted="muted"
                     @toggle-mute="muted = !muted" @like="(dbl) => like(r, dbl)" @comments="openComments(r)" @bigup="openBigUp(r)"
                     @share="share(r)" @save="save(r)" @delete="remove(r)" @profile="goProfile(r)" />
        </div>

        <!-- Comments slide up over the reel -->
        <Transition enter-from-class="translate-y-full" leave-to-class="translate-y-full" enter-active-class="transition duration-200" leave-active-class="transition duration-200">
          <div v-if="commentsFor" class="absolute inset-x-0 bottom-0 z-30 h-[70%] bg-slate-900/95 backdrop-blur rounded-t-3xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
              <p class="text-white font-black text-sm">{{ compact(commentsFor.comments_count) }} comments</p>
              <button type="button" aria-label="Close comments" class="text-white/80 p-1" @click="commentsFor = null"><X class="w-4 h-4" /></button>
            </div>
            <CommentThread class="flex-1 min-h-0" kind="reel" :target-id="commentsFor.id" :owner-id="commentsFor.user_id" scroll dark @count="onCount" />
          </div>
        </Transition>
      </div>
    </div>
  </Teleport>
  <VibeBigUpModal ref="bigUpRef" :p="bigUpFor || {}" :is-reel="true" @sent="onBigUp" />
</template>

<script setup>
import { nextTick, onBeforeUnmount, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Clapperboard, X } from 'lucide-vue-next';
import ReelSlide from './ReelSlide.vue';
import CommentThread from '../comments/CommentThread.vue';
import VibeBigUpModal from '../../modals/VibeBigUpModal.vue';
import { creatorReels, deleteReel, likeReel, saveReel, shareReel } from '../api';
import { compact } from '../format';
import { toast } from '../useVibeActions';

const props = defineProps({ title: { type: String, default: 'Reels' } });
const emit = defineEmits(['deleted', 'closed']);

const isOpen = ref(false);
const loading = ref(false);
const reels = ref([]);
const slides = ref([]);
const scroller = ref(null);
const muted = ref(true);
const commentsFor = ref(null);
const bigUpFor = ref(null);
const bigUpRef = ref(null);
let observer = null;
let current = -1;

async function open(list, startIndex = 0) {
  reels.value = list || [];
  slides.value = [];
  isOpen.value = true;
  document.body.style.overflow = 'hidden';
  window.addEventListener('keydown', onKey);
  await nextTick();
  start(startIndex);
}

/** One creator's reels (stories circles, profiles). */
async function openCreator(userId, isUvibe = false) {
  reels.value = [];
  isOpen.value = true;
  loading.value = true;
  document.body.style.overflow = 'hidden';
  window.addEventListener('keydown', onKey);
  try {
    const data = await creatorReels(userId, isUvibe);
    reels.value = data.reels || [];
  } catch {
    reels.value = [];
  } finally {
    loading.value = false;
  }
  await nextTick();
  start(0);
}

function start(index) {
  const el = scroller.value;
  if (!el) return;
  const target = el.children[Math.max(0, Math.min(index, reels.value.length - 1))];
  if (target) el.scrollTop = target.offsetTop;
  observer?.disconnect();
  observer = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      const i = Array.prototype.indexOf.call(el.children, e.target);
      if (e.isIntersecting) { current = i; slides.value[i]?.play(); }
      else slides.value[i]?.pause();
    });
  }, { root: el, threshold: 0.6 });
  Array.from(el.children).forEach((c) => observer.observe(c));
}

function close() {
  slides.value.forEach((s) => s?.pause());
  observer?.disconnect();
  isOpen.value = false;
  commentsFor.value = null;
  document.body.style.overflow = '';
  window.removeEventListener('keydown', onKey);
  emit('closed');
}

function onKey(e) {
  if (!isOpen.value) return;
  if (e.key === 'Escape') return commentsFor.value ? (commentsFor.value = null) : close();
  if (commentsFor.value || !scroller.value) return;
  if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
    e.preventDefault();
    const next = Math.max(0, Math.min(reels.value.length - 1, current + (e.key === 'ArrowDown' ? 1 : -1)));
    scroller.value.children[next]?.scrollIntoView({ behavior: 'smooth' });
  }
}

async function like(r, onlyIfNotLiked = false) {
  if (onlyIfNotLiked && r.is_liked) return;
  r.is_liked = !r.is_liked;
  r.likes_count = Number(r.likes_count || 0) + (r.is_liked ? 1 : -1);
  try {
    const d = await likeReel(r.id);
    r.is_liked = !!d.is_liked;
    r.likes_count = d.likes_count;
  } catch {
    r.is_liked = !r.is_liked;
    r.likes_count += r.is_liked ? 1 : -1;
  }
}

async function save(r) {
  try { const d = await saveReel(r.id); r.is_saved = !!d.is_saved; toast(r.is_saved ? '🔖 Saved' : 'Removed from saved'); } catch { toast('Couldn’t save this reel'); }
}

async function share(r) {
  const url = `${window.location.origin}/user/vibes?reel=${r.id}`;
  shareReel(r.id).then((d) => (r.shares_count = d.shares_count)).catch(() => {});
  try {
    if (navigator.share) await navigator.share({ title: 'LinkUp Reel', text: r.caption || '', url });
    else { await navigator.clipboard.writeText(url); toast('Link copied'); }
  } catch { /* closed */ }
}

async function remove(r) {
  if (!window.confirm('Delete this reel? This can’t be undone.')) return;
  try {
    await deleteReel(r.id);
    reels.value = reels.value.filter((x) => x.id !== r.id);
    toast('🗑️ Reel deleted');
    emit('deleted', r.id);
    if (!reels.value.length) close();
  } catch (e) {
    toast(e.response?.data?.message || 'Couldn’t delete this reel');
  }
}

function openComments(r) { commentsFor.value = r; }
function onCount(change) {
  const r = commentsFor.value;
  if (r) r.comments_count = typeof change === 'object' ? change.total : Math.max(0, Number(r.comments_count || 0) + change);
}

function openBigUp(r) {
  bigUpFor.value = r;
  nextTick(() => bigUpRef.value?.open());
}
function onBigUp(count) { if (bigUpFor.value) bigUpFor.value.bigups_count = count; }

function goProfile(r) {
  close();
  router.visit(`/user/vibes/profile/${r.user_id}`);
}

onBeforeUnmount(() => { if (isOpen.value) close(); });

defineExpose({ open, openCreator, close });
</script>
