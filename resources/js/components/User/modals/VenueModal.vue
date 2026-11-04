<template>
  <Modal ref="modalRef" maxWidth="max-w-lg">
    <div v-if="venue" class="pb-4">
      <div class="p-5 pb-0 flex items-start justify-between gap-3">
        <div>
          <h3 class="text-2xl font-black">{{ venue.name }}</h3>
          <p class="text-slate-500 font-semibold mt-1">{{ venue.address || 'Address not available' }}</p>
        </div>
        <button @click="close" class="text-slate-400 hover:text-slate-600 shrink-0 transition text-2xl leading-none">
          x
        </button>
      </div>

      <div class="relative mt-4 bg-slate-100">
        <img :src="activeImage" class="w-full h-[420px] md:h-[520px] object-cover shadow-inner" />
        <div v-if="photos.length > 1" class="absolute inset-x-0 bottom-3 flex items-center justify-center gap-2">
          <button
            v-for="(_, index) in photos"
            :key="index"
            @click="activePhoto = index"
            :class="['h-2.5 rounded-full transition-all', activePhoto === index ? 'w-6 bg-white' : 'w-2.5 bg-white/60']"
            type="button"
          ></button>
        </div>
      </div>

      <div class="px-5 pt-4 flex items-center justify-between gap-3">
        <span class="rounded-full bg-amber-50 text-amber-600 text-sm font-black px-3 py-1.5 shadow-sm border border-amber-100">
          {{ ratingLabel }}
        </span>
        <span class="chip border-slate-200 bg-slate-50 text-slate-700">
          {{ kind === 'club' ? 'Nightclub' : 'Restaurant' }}
        </span>
      </div>

      <div class="px-5 pt-4 grid grid-cols-2 gap-2">
        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3">
          <p class="text-[10px] font-black text-slate-400 uppercase">Call</p>
          <p class="text-sm font-black text-slate-700 mt-1 truncate">{{ venue.phone || 'Not available' }}</p>
        </div>
        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3">
          <p class="text-[10px] font-black text-slate-400 uppercase">Availability</p>
          <p class="text-sm font-black mt-1" :class="availabilityClass">{{ availabilityLabel }}</p>
        </div>
        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3">
          <p class="text-[10px] font-black text-slate-400 uppercase">Reviews</p>
          <p class="text-sm font-black text-slate-700 mt-1">{{ reviewTotal }}</p>
        </div>
        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3">
          <p class="text-[10px] font-black text-slate-400 uppercase">Photos</p>
          <p class="text-sm font-black text-slate-700 mt-1">{{ photos.length }}</p>
        </div>
      </div>

      <div class="p-5">
        <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-sm relative" style="height:208px;">
          <iframe :src="mapEmbedUrl" class="w-full h-full border-0 absolute inset-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-4">
          <a :href="directionsUrl" target="_blank" rel="noopener" class="btn btn-primary py-3.5 flex items-center justify-center gap-2 shadow hover:shadow-md hover:-translate-y-0.5 transition">
            Directions
          </a>
          <a :href="mapsSearchUrl" target="_blank" rel="noopener" class="btn btn-ghost py-3.5 flex items-center justify-center gap-2 shadow-sm border border-slate-200 hover:bg-slate-50 transition">
            View on Google
          </a>
        </div>

        <a
          v-if="venue.website"
          :href="venue.website"
          target="_blank"
          rel="noopener"
          class="btn btn-ghost w-full mt-2 py-3.5 flex items-center justify-center gap-2 shadow-sm border border-slate-200 hover:bg-slate-50 transition"
        >
          Website
        </a>

        <div v-if="reviews.length" class="mt-5">
          <h4 class="font-black text-slate-800 mb-3">Reviews</h4>
          <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
            <div v-for="(review, index) in reviews" :key="index" class="rounded-2xl bg-white border border-slate-100 p-3 shadow-sm">
              <div class="flex items-center gap-2 mb-2">
                <img
                  v-if="review.profile_photo_url"
                  :src="review.profile_photo_url"
                  alt="Reviewer"
                  class="w-8 h-8 rounded-full object-cover"
                />
                <div class="min-w-0">
                  <p class="font-black text-sm truncate">{{ review.author_name || 'Anonymous' }}</p>
                  <p class="text-xs font-bold text-amber-500">{{ formatReviewRating(review.rating) }}</p>
                </div>
              </div>
              <p class="text-sm text-slate-600 leading-snug">{{ review.text }}</p>
              <p class="text-xs text-slate-400 font-bold mt-2">{{ review.relative_time_description || review.time || '' }}</p>
            </div>
          </div>
        </div>

        <button @click="close" class="w-full text-center text-slate-500 hover:text-slate-700 font-black text-sm mt-5 transition">
          Back to list
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed, ref, onUnmounted } from 'vue';
import Modal from '../ui/Modal.vue';

const modalRef = ref(null);
const venue = ref(null);
const kind = ref('club');
const activePhoto = ref(0);
let autoSwipeTimer = null;

const startAutoSwipe = () => {
  stopAutoSwipe();

  if (photos.value.length <= 1) {
    return;
  }

  autoSwipeTimer = window.setInterval(() => {
    activePhoto.value = (activePhoto.value + 1) % photos.value.length;
  }, 3000);
};

const stopAutoSwipe = () => {
  if (autoSwipeTimer !== null) {
    clearInterval(autoSwipeTimer);
    autoSwipeTimer = null;
  }
};

onUnmounted(() => {
  stopAutoSwipe();
});

const photos = computed(() => {
  const list = Array.isArray(venue.value?.photos) ? venue.value.photos.filter(Boolean) : [];
  if (list.length) return list;
  return venue.value?.image ? [venue.value.image] : ['https://picsum.photos/seed/linkup-nightlife-detail/900/600'];
});

const activeImage = computed(() => photos.value[activePhoto.value] || photos.value[0]);

const reviews = computed(() => Array.isArray(venue.value?.reviews) ? venue.value.reviews : []);

const mapQuery = computed(() => {
  if (!venue.value) return '';
  return [venue.value.name, venue.value.address].filter(Boolean).join(' ');
});

const mapEmbedUrl = computed(() => {
  return `https://maps.google.com/maps?q=${encodeURIComponent(mapQuery.value)}&output=embed`;
});

const directionsUrl = computed(() => {
  return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(mapQuery.value)}`;
});

const mapsSearchUrl = computed(() => {
  return venue.value?.maps_url || `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(mapQuery.value)}`;
});

const ratingLabel = computed(() => {
  const rating = Number(venue.value?.rating);
  return Number.isFinite(rating) && rating > 0 ? rating.toFixed(1) + ' star' : 'N/A';
});

const availabilityLabel = computed(() => {
  if (venue.value?.availability === true) return 'Open now';
  if (venue.value?.availability === false) return 'Closed';
  return 'Not available';
});

const availabilityClass = computed(() => {
  if (venue.value?.availability === true) return 'text-emerald-600';
  if (venue.value?.availability === false) return 'text-rose-500';
  return 'text-slate-500';
});

const reviewTotal = computed(() => {
  const total = Number(venue.value?.user_ratings_total);
  if (Number.isFinite(total) && total > 0) return total;
  return reviews.value.length;
});

const formatReviewRating = (rating) => {
  const value = Number(rating);
  return Number.isFinite(value) ? value.toFixed(1) + ' star' : 'No rating';
};

const open = (nextVenue, nextKind) => {
  venue.value = nextVenue;
  kind.value = nextKind;
  activePhoto.value = 0;

  if (modalRef.value) {
    modalRef.value.open();
  }

  startAutoSwipe();
};

const close = () => {
  stopAutoSwipe();
  if (modalRef.value) modalRef.value.close();
};

defineExpose({ open, close });
</script>
