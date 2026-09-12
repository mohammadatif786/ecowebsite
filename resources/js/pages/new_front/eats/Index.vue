<template>
  <div class="fade">
    <!-- EATS LIST VIEW -->
    <div v-if="currentView === 'list'" class="fade">
      <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <i data-lucide="utensils" class="h-8 w-8 text-slate-950"></i>
            <h1 class="text-3xl font-black tracking-tight text-slate-950">LinkUp Eats</h1>
          </div>
          <p class="mt-1 text-base font-bold text-slate-500">Delivery to {{ locationLabel }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <div class="inline-flex rounded-full">
            <button
              v-for="t in ['Delivery', 'Pickup']"
              :key="t"
              @click="eatsType = t"
              :class="[
                'rounded-2xl px-5 py-3 text-sm font-black transition',
                eatsType === t ? 'border border-slate-200 bg-white text-slate-950 shadow' : 'text-slate-500 hover:text-slate-900'
              ]"
            >
              {{ t }}
            </button>
          </div>
          <button
            @click="openReservationsModal"
            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-950 shadow-sm transition hover:bg-slate-50"
          >
            Reservations
          </button>
          <button
            @click="openCartModal"
            class="flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700"
          >
            <i data-lucide="shopping-cart" class="h-4 w-4"></i>
            Cart ({{ cartItemCount }})
          </button>
        </div>
      </div>

      <div class="category-scroll mb-5 flex gap-2 overflow-x-auto scroll-smooth pb-3">
        <button
          v-for="c in cuisines"
          :key="c"
          @click="eatsCuisine = c"
          :class="[
            'shrink-0 rounded-full border px-4 py-2 text-sm font-black transition',
            c === eatsCuisine ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
          ]"
        >
          {{ c }}
        </button>
      </div>

      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <article
          v-for="r in filteredRestaurants"
          :key="r.id"
          class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
          <button @click="openRestaurant(r)" class="relative block h-[178px] w-full overflow-hidden text-left">
            <img :src="r.image" class="h-full w-full object-cover" :alt="r.name" />
            <span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 text-[11px] font-black text-slate-950 shadow-sm">
              {{ displayDistance(r.km) }} km
            </span>
            <span
              v-if="isTopRated(r)"
              class="absolute right-3 top-3 rounded-full bg-amber-400 px-3 py-1 text-[11px] font-black text-slate-950 shadow-sm"
            >
              <i data-lucide="flame" class="mr-1 inline h-3 w-3"></i>Top rated
            </span>
          </button>

          <div class="p-4">
            <div class="flex items-start justify-between gap-3">
              <button @click="openRestaurant(r)" class="min-w-0 text-left">
                <h3 class="truncate text-lg font-black leading-tight text-slate-950">{{ r.name }}</h3>
              </button>
              <span class="flex shrink-0 items-center gap-1 text-sm font-black text-amber-600">
                <i data-lucide="star" class="h-4 w-4 fill-amber-400 text-amber-400"></i>{{ r.rating }}
              </span>
            </div>
            <p class="mt-1 truncate text-xs font-semibold text-slate-500">
              {{ r.cuisine }} - {{ r.eta }} - {{ displayDistance(r.km) }} km
            </p>

            <div class="mt-3 grid grid-cols-2 gap-2">
              <button
                @click="deliveryForRestaurant(r)"
                class="rounded-full bg-slate-950 px-4 py-2 text-sm font-black text-white transition hover:bg-slate-800"
              >
                Delivery
              </button>
              <button
                @click="pickupForRestaurant(r)"
                class="rounded-full bg-green-600 px-4 py-2 text-sm font-black text-white transition hover:bg-green-700"
              >
                Pickup
              </button>
            </div>

            <button
              @click="reserveRestaurant(r)"
              class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-950 transition hover:bg-slate-50"
            >
              <i data-lucide="calendar-days" class="h-4 w-4"></i>
              Reserve a Table
            </button>
          </div>
        </article>
      </div>
    </div>

    <!-- EATS MENU VIEW -->
    <div v-else-if="currentView === 'menu' && selectedRestaurant" class="fade">
      <div class="mx-auto max-w-[1320px]">
        <div class="relative mb-6 h-[230px] overflow-hidden rounded-[22px] bg-slate-950">
          <img :src="selectedRestaurant.image" class="h-full w-full object-cover opacity-75" :alt="selectedRestaurant.name" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-black/25"></div>

          <button
            @click="closeRestaurant"
            class="absolute left-4 top-4 grid h-10 w-10 place-items-center rounded-full bg-white text-slate-900 shadow transition hover:bg-slate-100"
          >
            <i data-lucide="arrow-left" class="h-5 w-5"></i>
          </button>

          <span class="absolute right-4 top-4 rounded-full bg-white px-4 py-2 text-xs font-black text-slate-950 shadow">
            <i :data-lucide="eatsType === 'Pickup' ? 'store' : 'bike'" class="mr-1 inline h-3.5 w-3.5"></i>{{ eatsType }}
          </span>

          <div class="absolute bottom-4 left-5 text-white">
            <h1 class="text-3xl font-black leading-none">{{ selectedRestaurant.name }}</h1>
            <p class="mt-2 text-sm font-black">
              <span class="text-amber-400">★</span>
              {{ selectedRestaurant.rating }} - {{ selectedRestaurant.cuisine }} - {{ selectedRestaurant.eta }} - {{ displayDistance(selectedRestaurant.km) }} km
            </p>
          </div>
        </div>

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_420px]">
          <div class="min-w-0">
            <section class="min-h-[86px] rounded-[20px] bg-lime-100/70 p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h2 class="font-black text-slate-950">Home Order Type</h2>
                  <p class="mt-3 text-xs font-semibold text-slate-500">{{ orderTypeDescription }}</p>
                </div>
                <div class="inline-flex rounded-full bg-white/70 p-1">
                  <button
                    v-for="t in ['Delivery', 'Pickup']"
                    :key="t"
                    @click="eatsType = t"
                    :class="[
                      'rounded-full px-5 py-2 text-xs font-black transition',
                      eatsType === t ? 'border border-slate-200 bg-white text-slate-950 shadow' : 'text-slate-500'
                    ]"
                  >
                    {{ t }}
                  </button>
                </div>
              </div>
            </section>

            <div class="category-scroll mt-4 flex gap-2 overflow-x-auto scroll-smooth pb-3">
              <button
                v-for="c in menuCategories"
                :key="c"
                @click="eatsCat = c"
                :class="[
                  'shrink-0 rounded-full border px-4 py-2 text-sm font-black transition',
                  c === eatsCat ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                ]"
              >
                {{ c }}
              </button>
            </div>

            <div v-if="activeMenuSection" class="mt-3 space-y-4">
              <article
                v-for="(it, i) in activeMenuSection.items"
                :key="i"
                class="flex min-h-[108px] gap-4 rounded-[18px] bg-white p-3"
              >
                <img :src="menuItemImage(it)" class="h-[88px] w-[88px] shrink-0 rounded-xl object-cover" :alt="it.n" />
                <div class="min-w-0 flex-1">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <h3 class="truncate text-base font-black text-slate-950">{{ it.n }}</h3>
                      <p class="mt-2 line-clamp-2 text-sm font-medium text-slate-500">{{ it.d || '' }}</p>
                    </div>
                    <p class="shrink-0 text-base font-black text-blue-600">{{ money(it.p) }}</p>
                  </div>
                  <button
                    @click="addMenuItemToCart(it)"
                    class="mt-3 rounded-full bg-green-600 px-4 py-1.5 text-sm font-black text-white transition hover:bg-green-700"
                  >
                    Add
                  </button>
                </div>
              </article>
            </div>
          </div>

          <aside class="rounded-[20px] bg-white p-4">
            <h3 class="font-black text-slate-950">Your order - {{ eatsType }}</h3>
            <template v-if="cart.length">
              <div class="mt-3 space-y-2">
                <div v-for="(i, idx) in cart" :key="idx" class="flex items-center justify-between gap-3 text-sm">
                  <span class="min-w-0 truncate font-semibold text-slate-600">{{ i.qty }}x {{ i.n }}</span>
                  <span class="flex shrink-0 items-center gap-2">
                    <b class="text-slate-950">{{ money(i.p * i.qty) }}</b>
                    <button @click="removeFromCart(idx)" class="text-slate-300 transition hover:text-rose-500">
                      <i data-lucide="x" class="h-3.5 w-3.5"></i>
                    </button>
                  </span>
                </div>
              </div>
              <div class="mt-3 flex justify-between border-t border-slate-100 pt-3 font-black">
                <span>Subtotal</span>
                <span>{{ money(cartSubtotal) }}</span>
              </div>
              <button @click="openCartModal" class="mt-3 w-full rounded-xl bg-blue-600 py-3 text-sm font-black text-white transition hover:bg-blue-700">
                Review & checkout
              </button>
            </template>
            <p v-else class="mt-3 text-sm font-black text-slate-400">No items yet - add from the menu.</p>
          </aside>
        </div>
      </div>
    </div>

    <!-- EATS TRACKING VIEW -->
    <div v-else-if="currentView === 'tracking' && activeOrder" class="fade mx-auto max-w-[604px]">
      <div class="mb-5">
        <div class="flex items-center gap-2">
          <i data-lucide="utensils" class="h-7 w-7 text-slate-950"></i>
          <h1 class="text-3xl font-black leading-tight text-slate-950">Order tracking</h1>
        </div>
        <p class="mt-1 text-base font-bold text-slate-500">{{ activeOrder.rest.name }} - {{ activeOrder.id }}</p>
      </div>

      <div class="overflow-hidden rounded-2xl bg-white">
        <div class="grid h-[158px] place-items-center bg-gradient-to-br from-green-600 to-lime-500 text-center text-white">
          <div>
            <i :data-lucide="orderDone ? 'check-circle-2' : (activeOrder.delivery ? 'bike' : 'shopping-bag')" class="mx-auto h-10 w-10"></i>
            <p class="mt-3 text-2xl font-black">
              {{ orderDone ? (activeOrder.delivery ? 'Delivered!' : 'Picked up') : (activeOrder.delivery ? 'Arriving in ~' + orderEtaMin + ' min' : 'Ready in ~' + orderEtaMin + ' min') }}
            </p>
          </div>
        </div>

        <div class="p-5">
          <div class="space-y-4">
            <div v-for="(s, i) in orderSteps" :key="i" class="flex items-center gap-3">
              <span
                :class="[
                  'grid h-7 w-7 shrink-0 place-items-center rounded-full',
                  i < activeOrder.step
                    ? 'bg-green-500 text-white'
                    : i === activeOrder.step
                      ? 'bg-green-500 text-white ring-4 ring-green-100'
                      : 'border border-slate-300 bg-slate-50 text-slate-400'
                ]"
              >
                <i :data-lucide="i <= activeOrder.step ? 'check' : 'circle'" class="h-4 w-4"></i>
              </span>
              <span :class="['font-black', i <= activeOrder.step ? 'text-slate-950' : 'text-slate-400']">{{ s }}</span>
            </div>
          </div>

          <div class="mt-4 rounded-2xl bg-slate-50 p-3 text-sm">
            <div class="flex justify-between"><span class="font-black text-slate-500">Total paid</span><b>{{ money(activeOrder.total) }}</b></div>
            <div class="flex justify-between"><span class="font-black text-slate-500">Items</span><b>{{ activeOrder.items.reduce((a, i) => a + i.qty, 0) }}</b></div>
            <div class="flex justify-between"><span class="font-black text-slate-500">Reward points</span><b class="text-amber-600">+{{ activeOrder.points }}</b></div>
          </div>

          <button
            @click="finishTracking"
            class="mt-3 w-full rounded-xl border border-slate-200 bg-white py-3 text-sm font-black text-slate-950 transition hover:bg-slate-50"
          >
            Back to Eats
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <EatsCartModal ref="cartModalRef" :cart="cart" :eatsType="eatsType" :restaurant="selectedRestaurant" @remove="removeFromCart" @placeOrder="handlePlaceOrder" />
    <ReservationsModal ref="reservationsModalRef" />
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import EatsCartModal from '../../../components/new_frontend/modals/EatsCartModal.vue';
import ReservationsModal from '../../../components/new_frontend/modals/ReservationsModal.vue';
import { AV, getRestaurants, getUser } from '../../../components/new_frontend/MockDataStore';

defineOptions({ layout: MainLayout });

const user = getUser();
const restaurants = getRestaurants();

const currentView = ref('list');
const eatsType = ref('Delivery');
const eatsCuisine = ref('All');
const selectedRestaurant = ref(null);
const eatsCat = ref('');
const cart = ref([]);
const activeOrder = ref(null);

const cartModalRef = ref(null);
const reservationsModalRef = ref(null);

const cuisines = [
  'All',
  'Trini',
  'Jamaican',
  'Bahamian',
  'Bajan',
  'Haitian',
  'Dominican',
  'Puerto Rican',
  'Cuban',
  'Guyanese',
  'Belizean',
  'St. Lucian',
  'Grenadian',
  'Street food',
  'Seafood',
  'Vegan'
];
const EATS_STEPS = ['Order confirmed', 'Restaurant preparing', 'Driver at restaurant', 'On the way'];
const PICKUP_STEPS = ['Order confirmed', 'Restaurant preparing', 'Ready for pickup', 'Picked up'];
const driver = { name: 'Kareem T.', rating: '4.9', vehicle: 'Honda Fit (White)', img: AV(48) };
let eatsTimer = null;

const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};

const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const getMenuImg = (name) => `https://picsum.photos/seed/lk_${encodeURIComponent(name)}/700/500`;
const displayDistance = (km) => Number(km || 0).toLocaleString(undefined, { maximumFractionDigits: 1 });
const isTopRated = (restaurant) => Number(restaurant?.rating || 0) >= 4.7;

const locationLabel = computed(() => {
  const parts = [user.city, user.country].filter(Boolean);
  return parts.length ? parts.join(', ') : 'The Valley, Anguilla';
});

const cartItemCount = computed(() => cart.value.reduce((sum, item) => sum + Number(item.qty || 1), 0));

const filteredRestaurants = computed(() => {
  if (eatsCuisine.value === 'All') return restaurants;

  const selected = eatsCuisine.value.toLowerCase();
  return restaurants.filter((restaurant) => {
    const tags = (restaurant.tags || []).map(tag => String(tag).toLowerCase());
    const cuisine = String(restaurant.cuisine || '').toLowerCase();

    return tags.includes(selected) || cuisine.includes(selected);
  });
});

const menuCategories = computed(() => {
  if (!selectedRestaurant.value) return [];
  return selectedRestaurant.value.menu.map(s => s.sec);
});

const activeMenuSection = computed(() => {
  if (!selectedRestaurant.value) return null;
  return selectedRestaurant.value.menu.find(s => s.sec === eatsCat.value) || selectedRestaurant.value.menu[0];
});

const cartSubtotal = computed(() => {
  return cart.value.reduce((s, i) => s + (i.p * i.qty), 0);
});

const orderTypeDescription = computed(() => {
  return eatsType.value === 'Pickup'
    ? 'Free pickup at the restaurant - no delivery fee.'
    : `Delivery to ${locationLabel.value}. Delivery fee ${money(selectedRestaurant.value?.fee || 0)}.`;
});

const orderSteps = computed(() => activeOrder.value?.delivery ? EATS_STEPS : PICKUP_STEPS);
const orderDone = computed(() => activeOrder.value && activeOrder.value.step >= orderSteps.value.length - 1);
const orderEtaMin = computed(() => activeOrder.value ? Math.max(1, (orderSteps.value.length - 1 - activeOrder.value.step) * 4) : 0);

const refreshIcons = () => {
  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
  });
};

const menuItemImage = (item) => {
  if (!item?.n) return selectedRestaurant.value?.image || '';
  const firstItem = activeMenuSection.value?.items?.[0]?.n;
  return item.n === firstItem ? selectedRestaurant.value?.image : getMenuImg(item.n);
};

const openReservationsModal = () => {
  if (reservationsModalRef.value) reservationsModalRef.value.open();
};

const reserveRestaurant = (restaurant) => {
  selectedRestaurant.value = restaurant;
  if (reservationsModalRef.value) reservationsModalRef.value.open(restaurant);
};

const openRestaurant = (restaurant) => {
  selectedRestaurant.value = restaurant;
  eatsCat.value = restaurant.menu[0].sec;
  currentView.value = 'menu';
  window.scrollTo(0, 0);
  refreshIcons();
};

const deliveryForRestaurant = (restaurant) => {
  eatsType.value = 'Delivery';
  openRestaurant(restaurant);
};

const pickupForRestaurant = (restaurant) => {
  eatsType.value = 'Pickup';
  openRestaurant(restaurant);
};

const closeRestaurant = () => {
  selectedRestaurant.value = null;
  currentView.value = 'list';
  refreshIcons();
};

const addMenuItemToCart = (item) => {
  if (!selectedRestaurant.value || !item) return;

  addToCart({
    rid: selectedRestaurant.value.id,
    n: item.n,
    p: item.p,
    d: item.d,
    img: menuItemImage(item),
    qty: 1,
    notes: ''
  });
};

const addToCart = (cartItem) => {
  const existing = cart.value.find(i => i.rid === cartItem.rid && i.n === cartItem.n && i.notes === cartItem.notes);
  if (existing) {
    existing.qty += cartItem.qty;
  } else {
    cart.value.push(cartItem);
  }
  showToast(`Added ${cartItem.qty}x ${cartItem.n}`);
};

const removeFromCart = (idx) => {
  cart.value.splice(idx, 1);
};

const openCartModal = () => {
  if (cartModalRef.value) cartModalRef.value.open();
};

const handlePlaceOrder = (details) => {
  const isDelivery = eatsType.value === 'Delivery';

  activeOrder.value = {
    id: 'LU-' + Math.floor(Math.random() * 90000 + 10000),
    rest: selectedRestaurant.value || { name: 'LinkUp Eats' },
    items: [...cart.value],
    type: eatsType.value,
    total: details.total,
    step: 0,
    delivery: isDelivery,
    code: isDelivery ? String(Math.floor(1000 + Math.random() * 9000)) : null,
    points: details.points,
  };

  cart.value = [];
  currentView.value = 'tracking';
  showToast(`Order placed - ${activeOrder.value.id}`);
  startTracking();
  refreshIcons();
};

const startTracking = () => {
  if (eatsTimer) clearInterval(eatsTimer);

  eatsTimer = setInterval(() => {
    if (!activeOrder.value) {
      clearInterval(eatsTimer);
      return;
    }

    if (activeOrder.value.step < orderSteps.value.length - 1) {
      activeOrder.value.step++;
      const restName = activeOrder.value.rest.name;
      const msg = orderSteps.value[activeOrder.value.step];
      showToast(`${restName} - ${msg}`);
      refreshIcons();
    } else {
      clearInterval(eatsTimer);
      showToast(activeOrder.value.delivery ? 'Delivered!' : 'Ready for pickup!');
      refreshIcons();
    }
  }, 3000);
};

const finishTracking = () => {
  if (eatsTimer) clearInterval(eatsTimer);
  activeOrder.value = null;
  selectedRestaurant.value = null;
  currentView.value = 'list';
  refreshIcons();
};

onMounted(() => {
  refreshIcons();
});

onUnmounted(() => {
  if (eatsTimer) clearInterval(eatsTimer);
});
</script>

<style scoped>
.category-scroll {
  scrollbar-width: thin;
  scrollbar-color: #bfdbfe transparent;
}

.category-scroll::-webkit-scrollbar {
  height: 8px;
}

.category-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.category-scroll::-webkit-scrollbar-thumb {
  background: #bfdbfe;
  border-radius: 999px;
}

.category-scroll::-webkit-scrollbar-thumb:hover {
  background: #93c5fd;
}
</style>
