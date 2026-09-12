<template>
  <div class="fade space-y-6">
    <!-- Hero Section -->
    <div class="rounded-3xl brandgrad text-white p-6 md:p-8 relative overflow-hidden">
      <div class="relative z-10 flex flex-wrap items-center gap-5 justify-between">
        <div class="flex items-center gap-4">
          <img :src="user.avatar || '/images/default-avatar.png'" @error="$event.target.src = '/images/default-avatar.png'" class="w-16 h-16 rounded-2xl ring-4 ring-white/30 object-cover"/>
          <div>
            <p class="text-white/80 font-bold text-sm">Good afternoon</p>
            <h1 class="text-3xl font-black leading-tight">{{ user.name }} 👋</h1>
            <p class="text-white/80 text-sm font-semibold mt-0.5">{{ user.city }}, {{ user.country }} {{ user.flag }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="glass rounded-2xl px-4 py-3 text-lkink">
            <p class="text-[11px] font-bold text-slate-500">Wallet</p>
            <p class="text-xl font-black text-black">{{ money(wallet.balance) }}</p>
          </div>
          <div class="glass rounded-2xl px-4 py-3 text-lkink">
            <p class="text-[11px] font-bold text-slate-500">Coins</p>
            <p class="text-xl font-black text-black">{{ num(wallet.coins) }}</p>
          </div>
          <div class="hidden md:block glass rounded-2xl px-4 py-3 text-lkink">
            <p class="text-[11px] font-bold text-slate-500">Weather · {{ user.city }}</p>
            <p class="text-xl font-black text-black">{{ weather.icon }} {{ weather.temp !== null ? weather.temp + '°C' : '--' }}</p>
          </div>
        </div>
      </div>
      <button @click="openSearch" class="relative z-10 mt-6 w-full text-left bg-white/15 hover:bg-white/25 rounded-2xl px-5 py-4 flex items-center gap-3 transition">
        <span class="w-10 h-10 rounded-xl bg-white/20 grid place-items-center">
          <i data-lucide="sparkles" class="w-5 h-5"></i>
        </span>
        <span>
          <span class="block font-black">Ask AI · What do you want to do?</span>
          <span class="text-white/80 text-sm">Find tickets, food, products, people — just ask.</span>
        </span>
        <i data-lucide="arrow-right" class="w-5 h-5 ml-auto"></i>
      </button>
      <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10"></div>
      <div class="absolute -right-6 bottom-0 w-40 h-40 rounded-full bg-white/10"></div>
    </div>

    <!-- Breaking News Ticker -->
    <div class="card px-4 py-3 flex items-center gap-3 overflow-hidden">
      <span class="bg-red-500 text-white text-xs font-black px-3 py-1 rounded-full shrink-0">BREAKING</span>
      <span class="text-sm font-semibold text-slate-600 truncate">🌍 Iran War Triggers Global Energy Crisis · 🇯🇲 Central Bank Signals Focus on Economic Stability · 🇹🇹 Soca stars confirm Carnival 2026 lineup</span>
    </div>

    <!-- Quick Links Grid -->
    <div>
      <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-9 gap-3">
        <Link v-for="q in quickLinks" :key="q.dest" :href="route('new_frontend.' + q.dest)" class="card hover:-translate-y-0.5 transition p-3 flex flex-col items-center gap-2">
          <span class="w-12 h-12 rounded-2xl grid place-items-center text-white" :style="{ background: q.color }">
            <i :data-lucide="q.icon" class="w-6 h-6"></i>
          </span>
          <span class="text-xs font-black text-slate-600">{{ q.label }}</span>
        </Link>
      </div>
    </div>

    <!-- Featured Grid -->
    <div class="grid lg:grid-cols-3 gap-5">
      <!-- Large Hero Card -->
      <div class="lg:col-span-2">
        <FeatureCard
          variant="large"
          tag="Trending Event"
          :title="featEvent.title"
          :sub="featEvent.location"
          :img="featEvent.image"
          :date="featEvent.date"
          cta="Get Tickets"
          dest="events"
          color="#f59e0b"
        />
      </div>

      <!-- Right Column Stack -->
      <div class="grid grid-rows-2 gap-5">
        <FeatureCard
          tag="Featured Live"
          :title="featLive.title"
          :sub="'by ' + featLive.host + ' · live now'"
          :img="featLive.thumb"
          cta="Watch Now"
          dest="live"
          color="#e11d48"
        />
        <FeatureCard
          tag="New on Market"
          title="Fresh drops near you"
          sub="Caribbean stores & sellers"
          :img="featProduct.image"
          cta="Shop Now"
          dest="marketplace"
          color="#8b5cf6"
        />
      </div>
    </div>

    <!-- Main Content Columns -->
    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-black">Latest Vibes</h2>
          <Link :href="route('new_frontend.vibes')" class="text-lkblue2 font-bold text-sm">See all →</Link>
        </div>
        <VibeCard v-for="vibe in vibes" :key="vibe.id" :p="vibe" />
      </div>
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-black">Live now</h2>
          <Link :href="route('new_frontend.live')" class="text-lkblue2 font-bold text-sm">All →</Link>
        </div>
        <LiveMiniCard v-for="live in activeLives" :key="live.id" :live="live" />

        <div class="card p-4">
          <h3 class="font-black mb-2">Upcoming events</h3>
          <button v-for="e in upcomingEvents" :key="e.id" class="w-full flex items-center gap-3 py-2 hover:bg-slate-50 rounded-xl px-2 text-left">
            <img :src="e.image" class="w-12 h-12 rounded-xl object-cover" />
            <span class="min-w-0">
              <span class="block font-bold text-sm truncate">{{ e.title }}</span>
              <span class="text-xs text-slate-500">{{ e.date }} · {{ money(e.price) }}</span>
            </span>
          </button>
        </div>
      </div>
    </div>


    <!-- About Section -->
    <div class="rounded-3xl bg-white border border-slate-100 p-6 md:p-8 mt-2">
      <h2 class="text-2xl md:text-3xl font-black text-lkink">One app for the whole Caribbean &amp; LatAm.</h2>
      <p class="text-slate-500 font-semibold mt-2 max-w-3xl">LinkUp Vibes brings social, dating, events, food, live streaming, marketplace, news and a full digital wallet together in one place — built for the culture, from Anguilla to Brazil.</p>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        <div v-for="f in features" :key="f.title" class="rounded-2xl border border-slate-100 p-4 flex gap-3">
          <span class="h-11 w-11 rounded-xl brandgrad text-white grid place-items-center shrink-0">
            <i :data-lucide="f.icon" class="w-5 h-5"></i>
          </span>
          <div>
            <p class="font-black">{{ f.title }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Site footer -->
    <footer class="rounded-3xl bg-slate-900 text-white p-6 md:p-8 mt-2">
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div>
          <div class="flex items-center gap-2 mb-3"><span class="logo text-2xl">Link<span style="color:#F5C518">üp</span></span></div>
          <p class="text-white/60 text-sm font-semibold">Reimagining digital commerce &amp; connection in Latin America &amp; the Caribbean.</p>
          <div class="flex gap-2 mt-4">
            <button @click="toast('instagram')" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition grid place-items-center"><i data-lucide="instagram" class="w-4 h-4"></i></button>
            <button @click="toast('facebook')" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition grid place-items-center"><i data-lucide="facebook" class="w-4 h-4"></i></button>
            <button @click="toast('twitter')" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition grid place-items-center"><i data-lucide="twitter" class="w-4 h-4"></i></button>
            <button @click="toast('youtube')" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition grid place-items-center"><i data-lucide="youtube" class="w-4 h-4"></i></button>
          </div>
        </div>
        <div><p class="font-black mb-3">Product</p><ul class="space-y-2 text-sm text-white/70 font-semibold">
          <li><Link :href="route('new_frontend.vibes')" class="hover:text-white">Vibes</Link></li>
          <li><Link :href="route('new_frontend.events')" class="hover:text-white">Events</Link></li>
          <li><Link :href="route('new_frontend.eats')" class="hover:text-white">Eats</Link></li>
          <li><Link :href="route('new_frontend.marketplace')" class="hover:text-white">Marketplace</Link></li>
          <li><Link :href="route('new_frontend.wallet')" class="hover:text-white">Wallet &amp; Save</Link></li>
        </ul></div>
        <div><p class="font-black mb-3">Company</p><ul class="space-y-2 text-sm text-white/70 font-semibold">
          <li><button @click="openLegal('about')" class="hover:text-white">About us</button></li>
          <li><button @click="openLegal('what')" class="hover:text-white">What we do</button></li>
          <li><button @click="openLegal('sell')" class="hover:text-white">Sell on LinkUp</button></li>
          <li><button @click="openLegal('contact')" class="hover:text-white">Contact</button></li>
        </ul></div>
        <div><p class="font-black mb-3">Legal</p><ul class="space-y-2 text-sm text-white/70 font-semibold">
          <li v-if="$page.props.legalPagesStatus?.['terms-of-service'] !== false"><Link :href="route('legal.terms')" class="hover:text-white">Terms of Service</Link></li>
          <li v-if="$page.props.legalPagesStatus?.['privacy-policy'] !== false"><Link :href="route('legal.privacy')" class="hover:text-white">Privacy Policy</Link></li>
          <li v-if="$page.props.legalPagesStatus?.['acceptable-use-policy'] !== false"><Link :href="route('legal.acceptable-use')" class="hover:text-white">Acceptable Use Policy</Link></li>
          <li v-if="$page.props.legalPagesStatus?.['vibes-acceptable-use-policy'] !== false"><Link :href="route('legal.vibes-policy')" class="hover:text-white">Vibes AUP</Link></li>
          <li v-if="$page.props.legalPagesStatus?.['prohibited-activities'] !== false"><Link :href="route('legal.prohibited')" class="hover:text-white">Prohibited Activities</Link></li>
          <li v-if="$page.props.legalPagesStatus?.['refund-policy'] !== false"><Link :href="route('legal.refund')" class="hover:text-white">Refund Policy</Link></li>
          <li v-if="$page.props.legalPagesStatus?.['law-enforcement-guidelines'] !== false"><Link :href="route('legal.law-enforcement')" class="hover:text-white">Law Enforcement</Link></li>
          <li v-if="$page.props.legalPagesStatus?.['pricing-and-fees'] !== false"><Link :href="route('legal.pricing')" class="hover:text-white">Pricing & Fees</Link></li>
          <li v-if="$page.props.legalPagesStatus?.['contact-and-customer-support'] !== false"><Link :href="route('legal.support')" class="hover:text-white">Contact & Support</Link></li>
        </ul></div>
      </div>
      <div class="border-t border-white/10 mt-6 pt-4 flex flex-wrap items-center justify-between gap-3 text-white/50 text-xs font-semibold">
        <span>&copy; {{ new Date().getFullYear() }} LinkUp Vibes. All rights reserved.</span>
        <span class="flex gap-4">
          <Link v-if="$page.props.legalPagesStatus?.['terms-of-service'] !== false" :href="route('legal.terms')" class="hover:text-white">Terms</Link>
          <Link v-if="$page.props.legalPagesStatus?.['privacy-policy'] !== false" :href="route('legal.privacy')" class="hover:text-white">Privacy</Link>
        </span>
      </div>
    </footer>
  </div>

  <!-- Legal Modal -->
  <div v-if="legalOpen" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center p-4" @click.self="legalOpen = false">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden fade">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-xl font-black">{{ legalContent.title }}</h3>
        <button @click="legalOpen = false" class="h-8 w-8 rounded-lg hover:bg-slate-100 grid place-items-center"><i data-lucide="x" class="w-5 h-5"></i></button>
      </div>
      <div class="p-5 space-y-3 text-sm text-slate-600 leading-relaxed max-h-[65vh] overflow-y-auto" v-html="legalContent.body"></div>
      <div class="p-4 border-t border-slate-100">
        <button @click="legalOpen = false" class="btn btn-primary w-full py-3">Got it</button>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div v-if="showToast" class="fixed top-20 left-1/2 -translate-x-1/2 z-[100]">
    <div class="bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl font-bold">{{ toastMsg }}</div>
  </div>

</template>

<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import FeatureCard from '../../../components/new_frontend/cards/FeatureCard.vue';
import VibeCard from '../../../components/new_frontend/cards/VibeCard.vue';
import LiveMiniCard from '../../../components/new_frontend/cards/LiveMiniCard.vue';
import { SEED } from '../../../components/new_frontend/MockDataStore';

defineOptions({ layout: MainLayout });

const props = defineProps({
  user: { type: Object, default: () => ({ name: 'Guest', avatar: null, city: '', country: '', flag: '' }) },
  wallet: { type: Object, default: () => ({ balance: 0, coins: 0 }) },
  featEvent: { type: Object, default: () => ({ title: '', location: '', image: null }) },
  upcomingEvents: { type: Array, default: () => [] },
  featLive: { type: Object, default: () => ({ title: '', host: '', thumb: null }) },
  activeLives: { type: Array, default: () => [] },
  featProduct: { type: Object, default: () => ({ image: null }) },
  weather: { type: Object, default: () => ({ temp: null, icon: '⛅' }) },
});

const user = props.user;
const wallet = props.wallet;
const featEvent = props.featEvent || { title: '', location: '', image: null };
const featLive = props.featLive || { title: '', host: '', thumb: null };
const featProduct = props.featProduct || { image: null };
const upcomingEvents = props.upcomingEvents;
const activeLives = props.activeLives;
const weather = props.weather || { temp: null, icon: '⛅' };

const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const num = (n) => Number(n).toLocaleString();

const quickLinks = [
  { dest: 'wallet', label: 'Wallet', icon: 'wallet', color: '#2f9bef' },
  { dest: 'events', label: 'Events', icon: 'party-popper', color: '#f97316' },
  { dest: 'vibes', label: 'Vibes', icon: 'sparkles', color: '#a855f7' },
  { dest: 'uvibe', label: 'U Vibe', icon: 'graduation-cap', color: '#7c3aed' },
  { dest: 'dating', label: 'LinkUp', icon: 'heart', color: '#ef4444' },
  { dest: 'eats', label: 'Eats', icon: 'utensils', color: '#22c55e' },
  { dest: 'live', label: 'Live', icon: 'radio', color: '#e11d48' },
  { dest: 'nightlife', label: 'Nightlife', icon: 'moon', color: '#4f46e5' },
  { dest: 'news', label: '360 News', icon: 'newspaper', color: '#0ea5e9' },
  { dest: 'marketplace', label: 'Market', icon: 'shopping-bag', color: '#8b5cf6' },
  { dest: 'profile', label: 'Profile', icon: 'user', color: '#64748b' }
];

const vibes = computed(() => SEED.vibes_posts.slice(0, 2));

const features = [
  { icon: 'sparkles', title: 'Vibes & Live', desc: 'Share reels, go live, get Big-Up coin gifts.' },
  { icon: 'heart', title: 'LinkUp Dating', desc: 'Swipe, match & chat across the region.' },
  { icon: 'party-popper', title: 'Events & Tickets', desc: 'Discover fetes, buy & manage tickets.' },
  { icon: 'utensils', title: 'Eats', desc: 'Order in or dine in with live tracking.' },
  { icon: 'shopping-bag', title: 'Marketplace', desc: 'Sell products, costumes & Carnival sections.' },
  { icon: 'wallet', title: 'Wallet & Save', desc: 'Send, pay bills, and earn on savings.' }
];

const openSearch = () => {
  if (window.openSearch) window.openSearch();
};

// Toast
const toastMsg = ref('');
const showToast = ref(false);
const toast = (m) => { toastMsg.value = m; showToast.value = true; setTimeout(() => { showToast.value = false; }, 2000); };

// Legal Modal
const legalOpen = ref(false);
const legalContent = ref({ title: '', body: '' });

const LEGAL = {
  about: {
    title: 'About LinkUp Vibes',
    body: '<p>LinkUp Vibes is a Caribbean &amp; Latin American super-app that unifies social, dating, events, food delivery, live streaming, a multi-seller marketplace, regional news and a digital wallet &mdash; all in one place.</p><p class="mt-2">Our mission is to connect the region\'s people, culture and commerce on one trusted platform, from Anguilla to Brazil.</p>'
  },
  what: {
    title: 'What we do',
    body: '<p>We bring the everyday things you do online into a single app:</p><ul class="list-disc pl-5 space-y-1 mt-2"><li><b>Connect</b> &mdash; Vibes, Live and LinkUp dating.</li><li><b>Experience</b> &mdash; Events, tickets and Eats.</li><li><b>Trade</b> &mdash; a marketplace for products, costumes and Carnival sections.</li><li><b>Pay &amp; save</b> &mdash; send money, pay bills, and earn interest with LinkUp Save.</li></ul>'
  },
  sell: {
    title: 'Sell on LinkUp',
    body: '<p>List your products, event tickets, or Carnival costumes on the LinkUp Marketplace. Reach thousands of buyers across the Caribbean &amp; Latin America. Create your seller account from your Profile page.</p>'
  },
  terms: {
    title: 'Terms of Service',
    body: '<p><b>1. Acceptance.</b> By using LinkUp Vibes you agree to these terms.</p><p class="mt-2"><b>2. Accounts.</b> You are responsible for activity on your account and keeping your credentials secure.</p><p class="mt-2"><b>3. Payments.</b> Wallet, tickets and marketplace payments are processed securely; marketplace payouts are held in escrow until delivery is confirmed.</p><p class="mt-2"><b>4. Content.</b> You retain rights to what you post; you grant LinkUp a licence to display it in the app. Prohibited content may be removed.</p><p class="mt-2"><b>5. Selling.</b> Sellers must accurately describe items and honour orders. Fees may apply.</p>'
  },
  privacy: {
    title: 'Privacy Policy',
    body: '<p><b>Data we collect.</b> Account details, content you post, transactions, and device/usage data.</p><p class="mt-2"><b>How we use it.</b> To provide and improve the service, process payments, personalise content, and keep the platform safe.</p><p class="mt-2"><b>Sharing.</b> We share data with payment and infrastructure partners as needed to run the service; we do not sell your personal data.</p><p class="mt-2"><b>Your choices.</b> Access, correct or delete your data, and control notifications and discoverability in Settings.</p>'
  },
  cookies: {
    title: 'Cookie Policy',
    body: '<p>We use cookies and local storage to keep you signed in, remember preferences, and understand usage. You can decline non-essential cookies. Essential storage is required for the app to function (e.g. your wallet and cart state).</p>'
  },
  community: {
    title: 'Community Guidelines',
    body: '<p>Keep it respectful. No harassment, hate speech, illegal goods, or fraud. Report content that violates these rules. Accounts that break the guidelines may be limited or removed.</p>'
  },
  contact: {
    title: 'Contact us',
    body: '<p>Questions, partnerships or support?</p><p class="mt-3">&#x1F4E7; <b>hello@linkupvibes.com</b><br>&#x1F310; linkupvibes.com<br>&#x1F4CD; Caribbean &amp; Latin America</p>'
  }
};

const openLegal = (kind) => {
  legalContent.value = LEGAL[kind] || LEGAL.about;
  legalOpen.value = true;
};
</script>
