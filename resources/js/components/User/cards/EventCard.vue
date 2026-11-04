<template>
  <div @click="open"
    :class="['card overflow-hidden cursor-pointer hover:shadow-lg transition shrink-0', horizontal ? 'w-72' : '']">
    <div class="relative h-40">
      <!-- Image Slider -->
      <div class="absolute inset-0 w-full h-full">
        <div v-for="(img, idx) in images" :key="idx"
             class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
             :class="currentIndex === idx ? 'opacity-100' : 'opacity-0'">
          <img :src="img" class="w-full h-full object-contain bg-slate-100" />
        </div>
      </div>

      <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent pointer-events-none"></div>

      <!-- Dots Indicators -->
      <div v-if="images.length > 1" class="absolute bottom-10 left-0 right-0 flex justify-center gap-1.5 z-10 pointer-events-none">
        <div v-for="(_, idx) in images" :key="idx"
             :class="['w-1.5 h-1.5 rounded-full transition-all duration-300', currentIndex === idx ? 'bg-white scale-125' : 'bg-white/40']">
        </div>
      </div>

      <span class="absolute top-3 left-3 bg-white rounded-lg text-center leading-tight shadow overflow-hidden z-10"
        style="width:50px">
        <span class="block text-[10px] font-black text-white py-1" :style="{ background: catColor }">{{
          fmtMonth(eventDate) }}</span>
        <span class="block text-xl font-black py-0.5">{{ fmtDay(eventDate) }}</span>
      </span>

      <span class="absolute top-3 right-3 text-white text-[11px] font-black rounded-full px-2.5 py-1 z-10"
        :style="{ background: catColor }">{{ event.category.name }}</span>

      <button @click.stop="toggleFav"
        class="absolute top-14 right-3 h-8 w-8 rounded-full bg-black/30 grid place-items-center z-10">
        <i data-lucide="heart" :class="['w-4 h-4', isFav ? 'text-rose-500 fill-rose-500' : 'text-white']"></i>
      </button>

      <p class="absolute bottom-3 left-3 right-3 text-white font-black text-lg leading-tight drop-shadow line-clamp-1 z-10">
        {{ event.title }}</p>
    </div>

    <div class="p-4 flex items-center justify-between gap-1">
      <div class="min-w-0 flex-1">
        <p class="text-[12px] text-slate-400 truncate">{{ event.description || event.location }}</p>
        <p v-if="event.ticket_count > 1" class="text-[10px] text-lkblue2 font-bold">{{ event.ticket_count }} Ticket Types</p>
      </div>
      <span class="shrink-0 rounded-full text-white text-[11px] font-black px-2.5 py-1"
        style="background:linear-gradient(135deg,#2f9bef,#7c3aed)">
        {{ priceDisplay }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  event: { type: Object, required: true },
  favs: { type: Object, default: () => ({}) },
  horizontal: { type: Boolean, default: false }
});

const emit = defineEmits(['open', 'toggleFav']);

const currentIndex = ref(0);
const images = computed(() => {
  const urls = props.event.gallery_urls || [];
  return urls.length > 0 ? urls : [props.event.image_url];
});

let timer = null;
const startTimer = () => {
  if (images.value.length > 1) {
    timer = setInterval(() => {
      currentIndex.value = (currentIndex.value + 1) % images.value.length;
    }, 4000);
  }
};

const stopTimer = () => {
  if (timer) clearInterval(timer);
};

onMounted(() => {
  startTimer();
});

onUnmounted(() => {
  stopTimer();
});

const EV_CATCOLOR = {
  'Wellness and Spa': '#10b981', // Emerald/Green
  'Wellness & Spa': '#10b981',   // Emerald/Green (alt)
  'Cookouts/Food': '#f97316',    // Orange
};

const catColor = computed(() => {
  const name = props.event.category?.name || props.event.category;
  return EV_CATCOLOR[name] || '#e11d48'; // Default to Rose/Red for all others
});
const isFav = computed(() => props.event.auth_user_favorite !== null);

const money = (n) => '$' + Number(n).toFixed(2);

const priceDisplay = computed(() => {
  if (props.event.ticket_count === 0) return 'No Tickets';
  if (props.event.max_price === 0) return 'FREE';

  const min = Number(props.event.min_price);
  const max = Number(props.event.max_price);

  if (min === max) {
    return money(min);
  }

  // If the range is small enough, show both, otherwise "From $X.XX"
  // For the UI's sake, "$25.00 - $60.00" might be long, so "From $25.00" is safer
  // but let's try the range first as requested.
  return `${money(min)} - ${money(max)}`;
});

const eventDate = computed(() => {
  const d = props.event.event_details;
  if (d) {
    if (d.event_type === 'single') return d.single_event_date || props.event.start_time;
    if (d.event_type === 'recurring') return d.recurr_start_date || props.event.start_time;
  }
  return props.event.start_time || props.event.created_at;
});

const fmtMonth = (d) => {
  if (!d) return '';
  const dt = new Date(d);
  return dt.toLocaleString('en-US', { month: 'short' }).toUpperCase();
};

const fmtDay = (d) => {
  if (!d) return '';
  return new Date(d).getDate();
};

const open = () => emit('open', props.event);
const toggleFav = () => emit('toggleFav', props.event.id);
</script>
