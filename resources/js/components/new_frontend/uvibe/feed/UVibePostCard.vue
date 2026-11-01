<template>
  <!--
    One post in the U Vibe feed. This only lays the card out; each part is its own component in this
    folder and does its own work (open a profile, repost, apply to a gig…). The windows those parts
    open (menu, edit, report, Boost, applicants, shop) live once on the page and are told about
    them through the events below.
  -->
  <div :id="'uvpost-' + post.id" class="card overflow-hidden">
    <!-- Poster row -->
    <div class="p-4 flex items-center gap-3">
      <PostPoster :post="post">
        <div class="flex items-center gap-2 mt-0.5 min-w-0">
          <PostCampusPill :post="post" :campus="campus" @open="$emit('openCampus', $event)" />
          <span v-if="post.alumni" class="rounded-full px-2 py-0.5 text-[10px] font-black shrink-0" style="background:#e0f2fe;color:#075985">Alumni</span>
          <span class="text-[11px] text-slate-400 shrink-0">{{ post.time }}</span>
          <span v-if="post.edited" class="text-[11px] text-slate-400 shrink-0">· Edited</span>
        </div>
      </PostPoster>
      <PostBoostButton v-if="post.can_boost" :post="post" variant="rose" @open="$emit('boost', $event)" />
      <span class="chip shrink-0" style="background:#7c3aed14;color:#7c3aed;border-color:#7c3aed22">{{ post.tagLabel }}</span>
      <PostMenuButton :post="post" @open="$emit('menu', $event)" />
    </div>

    <p v-if="post.repostOf" class="px-4 pb-2 -mt-1 text-xs font-bold text-slate-500 flex items-center gap-1.5">
      <Repeat2 class="w-3.5 h-3.5 text-emerald-600" />{{ name }} reposted from {{ post.repostOf.name }}
    </p>

    <!-- Text card (words on a coloured background) or the photos / videos -->
    <UVibeTextCard v-if="post.isText" :post="post" class="w-full aspect-square" />
    <PostMedia v-else :media="post.media || []" />

    <div class="p-4">
      <div class="flex items-center justify-between gap-2 mb-1">
        <p class="font-black text-lg">{{ post.title }}</p>
        <template v-if="post.price">
          <span v-if="post.category === 'opportunity'" class="text-emerald-600 font-black shrink-0">{{ money(post.price) }}</span>
          <PostTicketsButton v-else :post="post" />
        </template>
      </div>
      <p v-if="!post.isText" class="text-sm text-slate-600">{{ post.desc }}</p>

      <PostTagCard :tag="post.tag" @open="$emit('openTag', post)" />

      <!-- Actions -->
      <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3">
        <span class="flex items-center gap-1.5 text-slate-500 text-sm font-bold"><Heart class="w-4 h-4" />{{ num(post.likes) }}</span>
        <span class="flex items-center gap-1.5 text-slate-500 text-sm font-bold"><MessageCircle class="w-4 h-4" />{{ num(post.comments) }}</span>
        <PostShareButton :post="post" />
        <PostRepostButton :post="post" @reposted="$emit('reposted', $event)" @needUniversity="$emit('needUniversity')" />
        <div class="ml-auto flex items-center gap-2">
          <PostBoostButton v-if="post.can_boost" :post="post" @open="$emit('boost', $event)" />
          <span v-if="post.onVibes" class="chip flex items-center gap-1 shrink-0" style="background:#05966914;color:#059669;border-color:#05966922">
            <CheckCircle class="w-3.5 h-3.5" />On Vibes
          </span>
        </div>
      </div>

      <PostGigRow v-if="post.category === 'opportunity'" :post="post" @applicants="$emit('applicants', $event)" @updated="$emit('updated', $event)" />

      <PostReportLink v-if="!post.mine" :post="post" @report="$emit('report', $event)" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { CheckCircle, Heart, MessageCircle, Repeat2 } from 'lucide-vue-next';
import { money, num, posterName } from '../../../../services/uvibeFormat';
import UVibeTextCard from '../UVibeTextCard.vue';
import PostPoster from './PostPoster.vue';
import PostCampusPill from './PostCampusPill.vue';
import PostBoostButton from './PostBoostButton.vue';
import PostMenuButton from './PostMenuButton.vue';
import PostMedia from './PostMedia.vue';
import PostTicketsButton from './PostTicketsButton.vue';
import PostTagCard from './PostTagCard.vue';
import PostShareButton from './PostShareButton.vue';
import PostRepostButton from './PostRepostButton.vue';
import PostGigRow from './PostGigRow.vue';
import PostReportLink from './PostReportLink.vue';

const props = defineProps({
  post: { type: Object, required: true },
  // The campus to open when the campus pill is tapped (null when unknown).
  campus: { type: Object, default: null },
});

defineEmits([
  'openCampus', 'menu', 'boost', 'report', 'applicants', 'openTag', // open a window for this post
  'updated', 'reposted', 'needUniversity', // something changed
]);

const name = computed(() => posterName(props.post));
</script>
