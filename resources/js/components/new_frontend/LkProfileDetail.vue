<template>
  <section class="profile-shell">
    <div class="profile-topbar">
      <button type="button" class="btn btn-ghost px-4 py-2.5 flex items-center gap-2" @click="$emit('close')">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ backLabel }}
      </button>
      <a v-if="showMessage" class="btn btn-primary px-4 py-2.5" href="/new_frontend/dating/chats">Message</a>
    </div>

    <article class="profile-card">
      <header class="profile-header">
        <div class="profile-identity">
          <img :src="profile.img" :alt="profile.name" class="profile-avatar" />
          <div class="min-w-0">
            <h1>{{ profile.name }}, {{ profile.age }}</h1>
            <div class="profile-meta">
              <span class="status-pill"><span></span>Active</span>
              <span class="country-chip">
                <img v-if="isFlagImage(profile.flag)" :src="profile.flag" :alt="`${profile.country} flag`" />
                <span v-else>{{ profile.flag }}</span>
                {{ profile.country || 'Unknown' }}
              </span>
              <span v-if="profile.km !== null">{{ profile.km }} km away</span>
            </div>
          </div>
        </div>

        <div class="profile-actions">
          <button
            type="button"
            class="action-pill request"
            :disabled="friendRequestSent || !profile.uid"
            @click="sendFriendRequest"
          >
            {{ friendRequestSent ? 'Request Sent' : 'Send Friend Request' }}
          </button>
          <button type="button" class="action-pill danger" @click="openBlockModal">Block</button>
          <button type="button" class="action-pill report" @click="openReportModal">Report</button>
        </div>
      </header>

      <main class="profile-content">
        <section class="photo-panel">
          <div class="hero-photo">
            <img :src="selectedPhotos[currentPhotoIndex]" :alt="profile.name" />
            <div class="verified-tag">Verified Photos</div>
            <div class="photo-nav">
              <button type="button" @click="prevPhoto">&lt;</button>
              <button type="button" @click="nextPhoto">&gt;</button>
            </div>
            <div class="photo-count">{{ currentPhotoIndex + 1 }} / {{ selectedPhotos.length }}</div>
          </div>

          <div class="thumbnail-row">
            <button
              v-for="(photo, index) in selectedPhotos"
              :key="`${photo}-${index}`"
              type="button"
              :class="{ active: index === currentPhotoIndex }"
              @click="currentPhotoIndex = index"
            >
              <img :src="photo" :alt="`${profile.name} photo ${index + 1}`" />
            </button>
          </div>

          <div class="round-actions">
            <button type="button" title="Pass" class="pass" @click="passProfile">
              <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <button type="button" title="Send Gift" class="gift" @click="openGift">
              <i data-lucide="gift" class="w-6 h-6"></i>
            </button>
            <button type="button" title="Like" class="like" @click="likeProfile">
              <i data-lucide="heart" class="w-5 h-5"></i>
            </button>
          </div>
        </section>

        <section class="detail-stack">
          <div class="info-card">
            <div class="card-heading"><span>About Me</span><small>Bio</small></div>
            <p>{{ profile.bio || 'No bio available' }}</p>
          </div>

          <div class="info-card">
            <div class="card-heading"><span>Interests</span><small>Lifestyle</small></div>
            <div v-if="profile.interests.length" class="chip-list">
              <span v-for="interest in profile.interests" :key="interest">{{ interest }}</span>
            </div>
            <p v-else>No interests listed yet.</p>
          </div>

          <div class="info-card">
            <div class="card-heading"><span>Looking for</span><small>Intent</small></div>
            <div class="chip-list">
              <span class="primary">{{ profile.goal || 'Here to Link Up' }}</span>
            </div>
          </div>

          <div class="info-card">
            <div class="card-heading"><span>Basics</span><small>Details</small></div>
            <div class="kv-grid">
              <template v-for="item in profileDetails" :key="item.label">
                <div class="label">{{ item.label }}</div>
                <div class="value">{{ item.value }}</div>
              </template>
            </div>
          </div>
        </section>
      </main>
    </article>

    <div v-if="blockModal.open" class="modal-layer" @click.self="closeBlockModal">
      <div
        class="w-full max-w-lg max-h-[90vh] flex flex-col bg-white/78 border border-white/48 shadow-[0_26px_60px_rgba(2,6,23,0.18)] backdrop-blur-[18px] saturate-[1.6] rounded-[28px] popIn overflow-hidden">
        <!-- Header -->
        <div class="p-5 border-b border-white/30 shrink-0">
          <div class="flex items-start gap-4">
            <div class="relative w-12 h-12 rounded-3xl grid place-items-center"
              style="background: radial-gradient(circle at 30% 30%, rgba(14,165,233,0.22), rgba(223,255,0,0.14)); border:1px solid rgba(255,255,255,0.40); box-shadow: 0 14px 28px rgba(2,6,23,0.12);">
              <UserX class="w-6 h-6" style="color:#0ea5e9"></UserX>
              <div
                class="absolute -right-1 -bottom-1 w-7 h-7 rounded-full grid place-items-center bg-white/90 border border-slate-200">
                <Ban class="w-4 h-4" style="color:#ef4444"></Ban>
              </div>
            </div>

            <div class="flex-1">
              <div class="text-[12px] text-slate-600 font-extrabold uppercase tracking-wide">SAFETY ACTION</div>
              <h2 class="text-[22px] leading-tight font-black tracking-tight mt-1">
                Block <span class="text-slate-900">{{ profile.name }}</span>
              </h2>
              <p class="text-[14px] font-semibold text-slate-700 mt-1">
                Please tell us why you're blocking <span class="font-black">@{{ profile.linkupId || 'user' }}</span>.
                <span class="text-slate-500">We won't tell them.</span>
              </p>
            </div>

            <button @click="closeBlockModal"
              class="bg-white/80 border border-slate-200/30 rounded-full px-3 py-2 text-[13px] font-black hover:brightness-105 transition-all">
              <X class="w-4 h-4"></X>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="p-5 overflow-y-auto flex-1 text-left">
          <div class="flex items-center justify-between gap-3">
            <div>
              <div class="text-[12px] text-slate-600 font-extrabold uppercase tracking-wide">Reason</div>
              <div class="text-[13px] text-slate-700 font-semibold">Pick one (optional).</div>
            </div>
            <span class="text-[12px] text-slate-500 font-bold">LinkUp Safety</span>
          </div>

          <div class="mt-3 flex flex-wrap gap-2">
            <button v-for="reason in blockReasons" :key="reason.id" type="button"
              @click="blockModal.selectedReason = blockModal.selectedReason === reason.id ? null : reason.id"
              class="rounded-full border border-slate-200/50 bg-white/78 shadow-[0_10px_20px_rgba(2,6,23,0.06)] font-black text-[13px] px-[14px] py-[10px] text-slate-900/92 transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99] flex items-center gap-2"
              :class="blockModal.selectedReason === reason.id ? 'border-blue-500/50 bg-blue-100 shadow-[0_14px_28px_rgba(14,165,233,0.12)]' : ''">
              <component :is="reason.icon" class="w-4 h-4" style="color:#0ea5e9"></component>
              <span :class="blockModal.selectedReason === reason.id ? 'text-blue-600' : 'text-slate-900'">{{ reason.label }}</span>
            </button>
          </div>

          <div class="mt-3">
            <textarea v-model="blockModal.note" rows="3"
              class="w-full rounded-[20px] border border-slate-200/70 bg-white/85 px-4 py-3 text-[14px] font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:shadow-[0_0_0_4px_rgba(14,165,233,0.18)] focus:border-blue-500/55 transition-all"
              placeholder="Optional: add a short note (this is private)." @input="updateBlockNoteCount"></textarea>
            <div class="mt-2 flex items-center justify-between text-[12px] text-slate-500 font-semibold">
              <div class="flex items-center gap-2">
                <Lock class="w-4 h-4"></Lock>
                <span>Private—only used for moderation and safety.</span>
              </div>
              <span>{{ blockModal.note.length }}/280</span>
            </div>
          </div>

          <div class="mt-4 bg-white/62 border border-white/40 shadow-[0_14px_30px_rgba(2,6,23,0.12)] backdrop-blur-[16px] saturate-[1.6] rounded-[22px] p-4">
            <div class="flex items-center gap-2">
              <div class="w-9 h-9 rounded-2xl grid place-items-center" style="background: rgba(14,165,233,0.12); border:1px solid rgba(14,165,233,0.25);">
                <Info class="w-4.5 h-4.5" style="color:#0ea5e9"></Info>
              </div>
              <div class="font-black text-slate-900">What happens when you block</div>
            </div>
            <div class="mt-3 space-y-3 text-[13px] text-slate-700 font-semibold">
              <div class="flex items-start gap-3">
                <div class="mt-[2px] w-9 h-9 rounded-2xl grid place-items-center border border-slate-200/60 bg-white/70">
                  <SearchX class="w-4.5 h-4.5"></SearchX>
                </div>
                <div>They won't be able to find your profile or message you.</div>
              </div>
              <div class="flex items-start gap-3">
                <div class="mt-[2px] w-9 h-9 rounded-2xl grid place-items-center border border-slate-200/60 bg-white/70">
                  <BellOff class="w-4.5 h-4.5"></BellOff>
                </div>
                <div>They won't be notified that you blocked them.</div>
              </div>
              <div class="flex items-start gap-3">
                <div class="mt-[2px] w-9 h-9 rounded-2xl grid place-items-center border border-slate-200/60 bg-white/70">
                  <Settings class="w-4.5 h-4.5"></Settings>
                </div>
                <div>You can unblock them anytime in Settings.</div>
              </div>
            </div>
          </div>

          <div class="mt-5 grid grid-cols-2 gap-3">
            <button @click="closeBlockModal"
              class="bg-white/80 border border-slate-200/30 rounded-full px-4 py-3 text-[15px] font-black hover:brightness-105 transition-all">
              Cancel
            </button>
            <button @click="confirmBlock"
              class="bg-gradient-to-r from-pink-400/98 to-red-500/92 border border-white/22 text-white rounded-full px-4 py-3 text-[15px] font-black shadow-[0_16px_34px_rgba(239,68,68,0.22)] hover:brightness-105 transition-all">
              Yes, Block
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="reportModal.open" class="modal-layer" @click.self="closeReportModal">
      <div
        class="w-full max-w-lg max-h-[90vh] flex flex-col bg-white/78 border border-white/48 shadow-[0_26px_60px_rgba(2,6,23,0.18)] backdrop-blur-[18px] saturate-[1.6] rounded-[28px] popIn overflow-hidden">
        <!-- Header -->
        <div class="p-5 border-b border-white/30 text-left shrink-0">
          <div class="flex items-start gap-4">
            <div class="relative w-12 h-12 rounded-3xl grid place-items-center"
              style="background: radial-gradient(circle at 30% 30%, rgba(14,165,233,0.22), rgba(223,255,0,0.14)); border:1px solid rgba(255,255,255,0.40); box-shadow: 0 14px 28px rgba(2,6,23,0.12);">
              <Flag class="w-6 h-6" style="color:#0ea5e9"></Flag>
              <div
                class="absolute -right-1 -bottom-1 w-7 h-7 rounded-full grid place-items-center bg-white/90 border border-slate-200">
                <TriangleAlert class="w-4 h-4" style="color:#f59e0b"></TriangleAlert>
              </div>
            </div>

            <div class="flex-1">
              <div class="text-[12px] text-slate-600 font-extrabold uppercase tracking-wide">Report</div>
              <h2 class="text-[24px] leading-tight font-black tracking-tight mt-1">
                Reporting <span class="text-slate-900">{{ profile.name }}</span>
              </h2>
              <p class="text-[14px] font-semibold text-slate-700 mt-1">
                Tell us what happened. We won't tell them.
              </p>
            </div>

            <button @click="closeReportModal"
              class="bg-white/80 border border-slate-200/30 rounded-full px-3 py-2 text-[13px] font-black hover:brightness-105 transition-all">
              <X class="w-4 h-4"></X>
            </button>
          </div>

          <div class="mt-4 text-[18px] font-black tracking-tight text-slate-900">
            Why are you reporting this user?
          </div>

          <!-- Context chips -->
          <div class="mt-3 flex flex-wrap gap-2">
            <span
              class="rounded-full border border-slate-200/50 bg-white/78 shadow-[0_10px_20px_rgba(2,6,23,0.06)] font-black text-[13px] px-[14px] py-[10px] text-slate-900/92 transition-all flex items-center gap-2">
              <Layers class="w-4 h-4" style="color:#0ea5e9"></Layers>
              Any part of LinkUp
            </span>
            <span
              class="rounded-full border border-slate-200/50 bg-white/78 shadow-[0_10px_20px_rgba(2,6,23,0.06)] font-black text-[13px] px-[14px] py-[10px] text-slate-900/92 transition-all flex items-center gap-2">
              <ShieldAlert class="w-4 h-4" style="color:#22c55e"></ShieldAlert>
              Safety review
            </span>
          </div>
        </div>

        <!-- Step 1 -->
        <div v-if="reportModal.step === 1" class="p-5 text-left overflow-y-auto flex-1">
          <div class="space-y-2.5">
            <button v-for="reason in reportReasons" :key="reason.id" type="button" @click="selectReportReason(reason.id)"
              class="w-full rounded-[18px] border border-slate-200/70 bg-white/76 shadow-[0_10px_18px_rgba(2,6,23,0.06)] px-4 py-3 flex items-center gap-3 text-left transition-all hover:brightness-105 active:translate-y-px active:scale-[0.995]"
              :class="reportModal.selectedReason === reason.id ? 'border-blue-500/55 bg-gradient-to-b from-blue-500/12 to-lime/8 shadow-[0_16px_26px_rgba(14,165,233,0.12)]' : ''">
              <div
                class="w-[22px] h-[22px] rounded-full border-[3px] border-blue-500/95 grid place-items-center bg-white/80 shadow-[0_10px_18px_rgba(2,6,23,0.08)] flex-shrink-0">
                <span class="w-[10px] h-[10px] rounded-full bg-blue-500/95 shadow-[0_0_0_6px_rgba(14,165,233,0.14)] transition-transform"
                  :style="reportModal.selectedReason === reason.id ? 'transform: scale(1)' : 'transform: scale(0)'"></span>
              </div>
              <div class="w-9 h-9 rounded-2xl grid place-items-center"
                style="background: rgba(14,165,233,0.10); border:1px solid rgba(14,165,233,0.22); box-shadow: 0 10px 18px rgba(2,6,23,0.06);">
                <component :is="reason.icon" class="w-4.5 h-4.5" style="color:#0ea5e9"></component>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-[16px] font-black text-slate-900">{{ reason.label }}</div>
                <div class="text-[12px] text-slate-500 font-semibold mt-0.5 truncate">
                  {{ reason.context.slice(0, 3).join(' • ') }}
                  {{ reason.context.length > 3 ? ' •…' : '' }}
                </div>
              </div>
              <div class="opacity-70">
                <ChevronRight class="w-5 h-5"></ChevronRight>
              </div>
            </button>
          </div>

          <div class="mt-5 flex items-center gap-3">
            <button @click="closeReportModal"
              class="rounded-full border border-slate-200/50 bg-white/82 shadow-[0_10px_22px_rgba(2,6,23,0.10)] font-black text-[15px] px-4 py-3 flex-1 transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]">
              Cancel
            </button>
            <button @click="goToReportStep2" :disabled="!reportModal.selectedReason"
              class="rounded-full bg-gradient-to-br from-blue-500/96 to-blue-600/92 border border-white/22 text-white font-black text-[15px] px-4 py-3 flex-[1.2] shadow-[0_16px_34px_rgba(14,165,233,0.22)] transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]"
              :style="reportModal.selectedReason ? 'opacity: 1; cursor: pointer;' : 'opacity: 0.55; cursor: not-allowed;'">
              Continue
            </button>
          </div>
          <p class="mt-3 text-[12px] text-slate-500 font-semibold">Tip: Keep it simple—choose the closest category. You can add details next.</p>
        </div>

        <!-- Step 2 -->
        <div v-if="reportModal.step === 2" class="p-5 text-left overflow-y-auto flex-1">
          <div class="bg-white/62 border border-white/40 shadow-[0_14px_30px_rgba(2,6,23,0.12)] backdrop-blur-[16px] saturate-[1.6] rounded-[22px] p-4">
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-[12px] text-slate-600 font-extrabold uppercase tracking-wide">Selected</div>
                <div class="text-[18px] font-black text-slate-900 mt-1">
                  {{ reportReasons.find(r => r.id === reportModal.selectedReason)?.label || '—' }}
                </div>
                <div class="text-[13px] text-slate-700 font-semibold mt-1">Optional: tell us where this happened.</div>
              </div>
              <button @click="backToReportStep1"
                class="rounded-full border border-slate-200/50 bg-white/82 shadow-[0_10px_22px_rgba(2,6,23,0.10)] font-black text-[13px] px-3 py-2 transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]">
                <ArrowLeft class="w-4 h-4"></ArrowLeft>
              </button>
            </div>

            <div v-if="reportReasons.find(r => r.id === reportModal.selectedReason)?.context?.length" class="mt-3 flex flex-wrap gap-2">
              <button v-for="area in reportReasons.find(r => r.id === reportModal.selectedReason)?.context" :key="area"
                type="button" @click="toggleContextArea(area)"
                class="rounded-full border border-slate-200/50 bg-white/72 shadow-[0_10px_18px_rgba(2,6,23,0.06)] font-black text-[13px] px-[10px] py-[10px] text-slate-900/92 transition-all flex items-center gap-2"
                :class="reportModal.selectedContextAreas.includes(area) ? 'border-blue-500/55 bg-gradient-to-b from-blue-500/12 to-lime/8 shadow-[0_16px_26px_rgba(14,165,233,0.12)]' : ''">
                <Check class="w-4 h-4" style="color:#0ea5e9"></Check>
                {{ area }}
              </button>
            </div>

            <textarea v-model="reportModal.message" rows="3"
              class="mt-3 w-full rounded-[18px] border border-slate-200/70 bg-white/85 px-4 py-3 text-[14px] font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:shadow-[0_0_0_4px_rgba(14,165,233,0.18)] focus:border-blue-500/55 transition-all"
              placeholder="Add details (optional): what happened, order/ticket ID, transaction amount, message text, etc." @input="updateReportDetailsCount"></textarea>
            <div class="mt-2 flex items-center justify-between text-[12px] text-slate-500 font-semibold">
              <span class="flex items-center gap-2">
                <Lock class="w-4 h-4"></Lock>
                Private—used for safety + fraud review.
              </span>
              <span>{{ reportModal.message.length }}/500</span>
            </div>
          </div>

          <div class="mt-5 flex items-center gap-3">
            <button @click="closeReportModal"
              class="rounded-full border border-slate-200/50 bg-white/82 shadow-[0_10px_22px_rgba(2,6,23,0.10)] font-black text-[15px] px-4 py-3 flex-1 transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]">
              Cancel
            </button>
            <button @click="submitReport"
              class="rounded-full bg-gradient-to-br from-blue-500/96 to-blue-600/92 border border-white/22 text-white font-black text-[15px] px-4 py-3 flex-[1.2] shadow-[0_16px_34px_rgba(14,165,233,0.22)] transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]">
              Submit
            </button>
          </div>
          <p class="mt-3 text-[12px] text-slate-500 font-semibold">If this involves payments, LinkUp may request receipts, screenshots, or transaction IDs.</p>
        </div>
      </div>
    </div>

    <GiftDialog ref="giftDialogRef" :user="profile" :balance="balance" />
  </section>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import GiftDialog from './modals/GiftDialog.vue';
import { isFlagImage, placeholderPhoto } from './linkupProfile';
import {
  UserX,
  Ban,
  X,
  ShieldAlert,
  AlertCircle,
  AlertTriangle,
  Bell,
  HelpCircle,
  Lock,
  Info,
  SearchX,
  BellOff,
  Settings,
  Flag,
  TriangleAlert,
  MessageCircleWarning,
  UserRoundX,
  Siren,
  PackageX,
  FileWarning,
  ChevronRight,
  ArrowLeft,
  Check,
  Layers
} from 'lucide-vue-next';

const props = defineProps({
  profile: { type: Object, required: true },
  balance: { type: Number, default: 0 },
  backLabel: { type: String, default: 'Back' },
  showMessage: { type: Boolean, default: true },
});

const emit = defineEmits(['close', 'remove']);

const currentPhotoIndex = ref(0);
const giftDialogRef = ref(null);
const friendRequestSent = ref(!!props.profile.friendRequestSent);

const selectedPhotos = computed(() => props.profile?.photos?.length ? props.profile.photos : [placeholderPhoto(props.profile?.id)]);

watch(() => props.profile?.id, () => {
  currentPhotoIndex.value = 0;
  friendRequestSent.value = !!props.profile.friendRequestSent;
});

const notify = (message) => {
  if (window.toast) window.toast(message);
};

const prevPhoto = () => {
  currentPhotoIndex.value = (currentPhotoIndex.value - 1 + selectedPhotos.value.length) % selectedPhotos.value.length;
};

const nextPhoto = () => {
  currentPhotoIndex.value = (currentPhotoIndex.value + 1) % selectedPhotos.value.length;
};

const openGift = () => {
  giftDialogRef.value?.open(props.profile);
};

const removeCurrent = () => {
  emit('remove', props.profile.id);
};

const likeProfile = () => {
  router.post(route('frontend.profile.like', props.profile.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      notify(`You liked ${props.profile.name}.`);
      removeCurrent();
    },
  });
};

const passProfile = () => {
  router.post(route('frontend.profile.dislike', props.profile.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      notify(`You passed on ${props.profile.name}.`);
      removeCurrent();
    },
  });
};

const sendFriendRequest = () => {
  if (!props.profile.uid) return;
  router.post(route('frontend.friend-request.send', { slug: props.profile.name, user: props.profile.uid }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      friendRequestSent.value = true;
      notify('Friend request sent.');
    },
  });
};

const blockModal = reactive({
  open: false,
  reason: 'spam',
  note: '',
  processing: false,
  selectedReason: null,
});

const blockReasons = [
  { id: 'spam', label: 'Spam / Scam', icon: ShieldAlert },
  { id: 'harassment', label: 'Harassment', icon: AlertCircle },
  { id: 'fake', label: 'Fake profile', icon: UserX },
  { id: 'inappropriate', label: 'Inappropriate', icon: AlertTriangle },
  { id: 'threats', label: 'Threats', icon: Bell },
  { id: 'other', label: 'Other', icon: HelpCircle },
];

const openBlockModal = () => {
  blockModal.open = true;
  blockModal.selectedReason = null;
  blockModal.note = '';
  document.body.style.overflow = 'hidden';
};

const closeBlockModal = () => {
  blockModal.open = false;
  document.body.style.overflow = '';
};

const updateBlockNoteCount = () => {
  const max = 280;
  if (blockModal.note.length > max) {
    blockModal.note = blockModal.note.slice(0, max);
  }
};

const confirmBlock = () => {
  if (blockModal.processing) return;
  blockModal.processing = true;

  router.post(route('frontend.profile.block.user'), {
    blocked_user_id: props.profile.id,
    reason: blockModal.selectedReason || 'unspecified',
    note: blockModal.note.trim(),
  }, {
    preserveScroll: true,
    onSuccess: () => {
      notify('User blocked successfully.');
      blockModal.processing = false;
      closeBlockModal();
      removeCurrent();
    },
    onError: () => {
      blockModal.processing = false;
    },
  });
};

const reportModal = reactive({
  open: false,
  step: 1,
  selectedReason: null,
  selectedContextAreas: [],
  message: '',
});

const reportReasons = [
  {
    id: "scam_fraud",
    label: "Scam / Fraud / Suspicious Payments",
    icon: ShieldAlert,
    context: ["Wallet", "Marketplace", "Tickets & Events", "Subscriptions", "Crypto/Transfers"]
  },
  {
    id: "harassment",
    label: "Harassment / Bullying / Hate",
    icon: MessageCircleWarning,
    context: ["Chat", "Comments", "Live", "Profile"]
  },
  {
    id: "explicit",
    label: "Sexual Content / Unwanted Advances",
    icon: TriangleAlert,
    context: ["Chat", "Profile", "Live", "Images/Videos"]
  },
  {
    id: "impersonation",
    label: "Fake Profile / Impersonation",
    icon: UserRoundX,
    context: ["Profile", "Verification", "Photos"]
  },
  {
    id: "threats",
    label: "Threats / Violence / Extortion",
    icon: Siren,
    context: ["Chat", "Calls", "Live", "Off-platform threats"]
  },
  {
    id: "privacy",
    label: "Privacy / Doxxing / Blackmail",
    icon: Lock,
    context: ["Shared personal info", "Screenshots", "Address/Phone", "Nudes/Leaks"]
  },
  {
    id: "illegal_goods",
    label: "Prohibited or Illegal Goods",
    icon: PackageX,
    context: ["Marketplace", "Listings", "Drop-off/Meetups"]
  },
  {
    id: "terms",
    label: "Other Policy Violation",
    icon: FileWarning,
    context: ["Spam", "Content rules", "Event rules", "Multiple accounts"]
  }
];

const openReportModal = () => {
  reportModal.open = true;
  reportModal.step = 1;
  reportModal.selectedReason = null;
  reportModal.selectedContextAreas = [];
  reportModal.message = '';
  document.body.style.overflow = 'hidden';
};

const closeReportModal = () => {
  reportModal.open = false;
  document.body.style.overflow = '';
};

const selectReportReason = (reasonId) => {
  reportModal.selectedReason = reasonId;
};

const toggleContextArea = (area) => {
  const index = reportModal.selectedContextAreas.indexOf(area);
  if (index > -1) {
    reportModal.selectedContextAreas.splice(index, 1);
  } else {
    reportModal.selectedContextAreas.push(area);
  }
};

const goToReportStep2 = () => {
  if (reportModal.selectedReason) {
    reportModal.step = 2;
  }
};

const backToReportStep1 = () => {
  reportModal.step = 1;
};

const updateReportDetailsCount = () => {
  const max = 500;
  if (reportModal.message.length > max) {
    reportModal.message = reportModal.message.slice(0, max);
  }
};

const submitReport = () => {
  const selectedReason = reportReasons.find(r => r.id === reportModal.selectedReason);
  if (!selectedReason) return;

  router.post(route('frontend.flag.user'), {
    reported_user_id: props.profile.id,
    reason: selectedReason.label,
    selected_reason: JSON.stringify(reportModal.selectedContextAreas),
    message: reportModal.message.trim(),
  }, {
    preserveScroll: true,
    onSuccess: () => {
      notify('Report submitted.');
      closeReportModal();
    },
  });
};

const profileDetails = computed(() => [
  { label: 'Work', value: props.profile.job },
  { label: 'University', value: props.profile.university },
  { label: 'Languages', value: props.profile.languages?.length ? props.profile.languages.join(', ') : '' },
  { label: 'City', value: props.profile.city },
  { label: 'LinkUp ID', value: props.profile.linkupId },
].filter((item) => item.value));
</script>

<style scoped>
.profile-shell {
  width: 100%;
}

.profile-topbar {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.profile-card {
  overflow: hidden;
  border-radius: 26px;
  background: rgba(255, 255, 255, 0.96);
  box-shadow: 0 24px 80px rgba(15, 23, 42, 0.12);
}

.profile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  padding: 24px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.18);
}

.profile-identity {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.profile-avatar {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  object-fit: cover;
}

.profile-header h1 {
  margin: 0;
  color: #172033;
  font-size: 30px;
  line-height: 1;
  font-weight: 900;
}

.profile-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
  color: #64748b;
  font-size: 14px;
  font-weight: 800;
}

.status-pill,
.country-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border-radius: 999px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 5px 12px;
}

.status-pill span {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: #22c55e;
}

.country-chip img {
  width: 22px;
  height: 16px;
  border-radius: 3px;
  object-fit: cover;
}

.profile-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 10px;
}

.action-pill {
  border: 0;
  border-radius: 999px;
  padding: 12px 18px;
  color: white;
  font-weight: 900;
  transition: transform 0.16s ease, opacity 0.16s ease;
}

.action-pill:disabled {
  opacity: 0.6;
}

.action-pill:not(:disabled):hover {
  transform: translateY(-1px);
}

.action-pill.request {
  background: #d7df19;
}

.action-pill.danger {
  background: #ef232b;
}

.action-pill.report {
  background: #3482f7;
}

.profile-content {
  display: grid;
  grid-template-columns: minmax(300px, 0.9fr) minmax(320px, 1.45fr);
  gap: 26px;
  padding: 28px;
}

.hero-photo {
  position: relative;
  overflow: hidden;
  aspect-ratio: 4 / 5;
  border-radius: 24px;
  background: #e2e8f0;
}

.hero-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.verified-tag {
  position: absolute;
  top: 18px;
  left: 18px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.92);
  color: #334155;
  padding: 10px 16px;
  font-size: 13px;
  font-weight: 900;
}

.photo-nav {
  position: absolute;
  top: 18px;
  right: 18px;
  display: flex;
  gap: 8px;
}

.photo-nav button {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border: 0;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.42);
  color: white;
  font-size: 22px;
  font-weight: 900;
}

.photo-count {
  position: absolute;
  right: 16px;
  bottom: 16px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.6);
  color: white;
  padding: 8px 12px;
  font-weight: 900;
}

.thumbnail-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 14px;
}

.thumbnail-row button {
  overflow: hidden;
  width: 76px;
  height: 76px;
  border-radius: 16px;
  border: 3px solid transparent;
  padding: 0;
  background: transparent;
}

.thumbnail-row button.active {
  border-color: #0ea5e9;
}

.thumbnail-row img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.round-actions {
  display: grid;
  grid-template-columns: repeat(3, 76px);
  justify-content: center;
  gap: 28px;
  margin-top: 24px;
  padding: 18px;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  background: #fff;
}

.round-actions button {
  display: grid;
  place-items: center;
  width: 64px;
  height: 64px;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  background: white;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
}

.round-actions .pass {
  color: #94a3b8;
}

.round-actions .gift {
  color: white;
  border: 0;
  background: linear-gradient(135deg, #a855f7, #7c3aed);
}

.round-actions .like {
  color: #f43f5e;
}

.detail-stack {
  display: grid;
  gap: 16px;
  align-content: start;
}

.info-card {
  border: 1px solid #e5e7eb;
  border-radius: 24px;
  background: white;
  padding: 22px;
}

.card-heading {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
  color: #172033;
  font-size: 20px;
  font-weight: 900;
}

.card-heading small {
  color: #64748b;
  font-size: 13px;
}

.info-card p {
  margin: 0;
  color: #475569;
  font-weight: 700;
}

.chip-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.chip-list span {
  border: 1px solid #dbeafe;
  border-radius: 999px;
  background: #f8fafc;
  color: #334155;
  padding: 9px 13px;
  font-size: 14px;
  font-weight: 900;
}

.chip-list .primary {
  background: #effcff;
  color: #0876a8;
}

.kv-grid {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 14px 18px;
}

.kv-grid .label {
  color: #64748b;
  font-weight: 900;
}

.kv-grid .value {
  color: #172033;
  font-weight: 900;
}

.modal-layer {
  position: fixed;
  inset: 0;
  z-index: 80;
  display: grid;
  place-items: center;
  padding: 18px;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(8px);
}

.modal-card {
  width: min(520px, 100%);
  border-radius: 28px;
  background: white;
  box-shadow: 0 24px 80px rgba(15, 23, 42, 0.28);
  padding: 22px;
}

.modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.modal-head h2 {
  margin: 0;
  color: #172033;
  font-size: 24px;
  font-weight: 900;
}

.eyebrow {
  margin: 0 0 2px;
  color: #64748b;
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
}

.modal-head button {
  display: grid;
  place-items: center;
  width: 38px;
  height: 38px;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  background: white;
}

.modal-card label {
  display: grid;
  gap: 8px;
  margin-bottom: 14px;
  color: #475569;
  font-size: 13px;
  font-weight: 900;
  text-transform: uppercase;
}

.modal-card select,
.modal-card textarea {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 18px;
  padding: 13px 15px;
  color: #172033;
  font-weight: 700;
  text-transform: none;
}

@media (max-width: 900px) {
  .profile-header,
  .profile-actions {
    align-items: stretch;
    flex-direction: column;
  }

  .profile-content {
    grid-template-columns: 1fr;
    padding: 18px;
  }

  .profile-header h1 {
    font-size: 24px;
  }

  .round-actions {
    grid-template-columns: repeat(3, 64px);
    gap: 16px;
  }
}

.fadeIn {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.popIn {
  animation: popIn 0.18s ease-out;
}

@keyframes popIn {
  from {
    opacity: 0;
    transform: scale(0.92);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
