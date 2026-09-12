<template>
  <Modal ref="modalRef" maxWidth="max-w-3xl" padding="p-0">
    <div class="p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-black">{{ editId ? 'Edit Event' : 'Create Event' }}</h3>
        <button @click="close"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></button>
      </div>

      <!-- Upload Image -->
      <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <p class="font-black text-lkblue2 mb-3 pb-2" style="border-bottom:2px solid;border-image:linear-gradient(90deg,#2f9bef,#f59e0b) 1">Upload Event Image</p>
        <label class="block rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/40 hover:bg-blue-50 grid place-items-center py-8 cursor-pointer transition">
          <input type="file" accept="image/*" class="hidden" @change="onImagePick"/>
          <i data-lucide="upload" class="w-6 h-6 text-lkblue mb-1"></i>
          <span class="text-sm font-bold text-slate-600">Upload photo</span>
        </label>
        <img v-if="form.image" :src="form.image" class="h-20 w-20 rounded-xl object-cover mt-3"/>
      </div>

      <!-- Event Details -->
      <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <p class="font-black text-lkblue2 mb-3 pb-2" style="border-bottom:2px solid;border-image:linear-gradient(90deg,#2f9bef,#f59e0b) 1">Event Details</p>
        
        <label class="text-xs font-black text-slate-500">Event Name *</label>
        <input v-model="form.name" placeholder="Event name" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3"/>
        
        <label class="text-xs font-black text-slate-500">Description *</label>
        <textarea v-model="form.desc" rows="3" placeholder="Describe your event" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3"></textarea>
        
        <label class="text-xs font-black text-slate-500">Category *</label>
        <select v-model="form.cat" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white">
          <option v-for="c in EV_CATS" :key="c">{{ c }}</option>
        </select>
        
        <label class="text-xs font-black text-slate-500">Audiences *</label>
        <p class="text-[11px] text-slate-400 mb-2">Select the audience types that are targeted in your event.</p>
        <div class="flex flex-wrap gap-2 mb-3">
          <button v-for="a in AUD" :key="a" @click="toggleAudience(a)" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold transition', form.audiences.includes(a) ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">
            {{ form.audiences.includes(a) ? '✓ ' : '' }}{{ a }}
          </button>
        </div>
        
        <label class="text-xs font-black text-slate-500">Attendees *</label>
        <div class="rounded-xl bg-blue-50 border border-blue-100 p-2.5 text-[11px] text-blue-700 font-bold mt-1 mb-2">Show the attendees number and list on the event page</div>
        <div class="flex gap-2 mb-3">
          <button @click="form.attendees = 'show'" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold', form.attendees === 'show' ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">Show</button>
          <button @click="form.attendees = 'hide'" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold', form.attendees === 'hide' ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">Hide</button>
        </div>
        
        <label class="text-xs font-black text-slate-500">Enable reviews *</label>
        <div class="flex gap-2 mt-1 mb-3">
          <button @click="form.reviews = 'enable'" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold', form.reviews === 'enable' ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">Enable</button>
          <button @click="form.reviews = 'disable'" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold', form.reviews === 'disable' ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">Disable</button>
        </div>
        
        <label class="text-xs font-black text-slate-500">Does this event have a seating plan? *</label>
        <div class="flex gap-2 mt-1">
          <button @click="form.seating = 'no'" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold', form.seating === 'no' ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">No</button>
          <button @click="form.seating = 'yes'" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold', form.seating === 'yes' ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">Yes</button>
        </div>
      </div>

      <!-- Date & Time -->
      <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <p class="font-black text-lkblue2 mb-3 pb-2" style="border-bottom:2px solid;border-image:linear-gradient(90deg,#2f9bef,#f59e0b) 1">Date and Time</p>
        <p class="text-xs font-black text-slate-500 mb-2">Type of event</p>
        <div class="grid grid-cols-2 gap-2 mb-3">
          <button @click="form.type = 'single'" type="button" :class="['rounded-xl border-2 p-3 text-left transition', form.type === 'single' ? 'border-lkblue bg-blue-50' : 'border-slate-200 bg-white']">
            <p class="font-black text-sm">📅 Single event</p>
            <p class="text-[11px] text-slate-500">For events that happen once</p>
          </button>
          <button @click="form.type = 'recurring'" type="button" :class="['rounded-xl border-2 p-3 text-left transition', form.type === 'recurring' ? 'border-lkblue bg-blue-50' : 'border-slate-200 bg-white']">
            <p class="font-black text-sm">📅 Recurring event</p>
            <p class="text-[11px] text-slate-500">For timed entry and multiple days</p>
          </button>
        </div>
        
        <template v-if="form.type === 'recurring'">
          <label class="text-xs font-black text-slate-500">Recurrence Pattern *</label>
          <select v-model="form.recPattern" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white">
            <option>Daily</option><option>Weekly</option><option>Monthly</option>
          </select>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="text-xs font-black text-slate-500">Start Date *</label>
              <input v-model="form.recStart" placeholder="YYYY-MM-DD" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"/>
            </div>
            <div>
              <label class="text-xs font-black text-slate-500">End Date *</label>
              <input v-model="form.recEnd" placeholder="YYYY-MM-DD" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"/>
            </div>
          </div>
        </template>
        <template v-else>
          <label class="text-xs font-black text-slate-500">Event Date *</label>
          <input v-model="form.date" placeholder="YYYY-MM-DD" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"/>
        </template>
      </div>

      <!-- Location -->
      <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <p class="font-black text-lkblue2 mb-3 pb-2" style="border-bottom:2px solid;border-image:linear-gradient(90deg,#2f9bef,#f59e0b) 1">Location</p>
        
        <div class="flex flex-wrap gap-2 mb-3">
          <button @click="form.venueType = 'venue'" type="button" :class="['rounded-xl border-2 px-4 py-2.5 text-sm font-bold flex items-center gap-2 transition', form.venueType === 'venue' ? 'border-lkblue bg-lkblue text-white' : 'border-slate-200 text-slate-600 bg-white']">📍 Venue</button>
          <button @click="form.venueType = 'online'" type="button" :class="['rounded-xl border-2 px-4 py-2.5 text-sm font-bold flex items-center gap-2 transition', form.venueType === 'online' ? 'border-lkblue bg-lkblue text-white' : 'border-slate-200 text-slate-600 bg-white']">🖥️ Online event</button>
          <button @click="form.venueType = 'tba'" type="button" :class="['rounded-xl border-2 px-4 py-2.5 text-sm font-bold flex items-center gap-2 transition', form.venueType === 'tba' ? 'border-lkblue bg-lkblue text-white' : 'border-slate-200 text-slate-600 bg-white']">📅 To be announced</button>
        </div>
        
        <template v-if="form.venueType === 'venue'">
          <label class="text-xs font-black text-slate-500">Venue Location *</label>
          <input v-model="form.venueLoc" placeholder="Enter venue name or address" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
          <div class="grid grid-cols-2 gap-3 mb-3">
            <div><label class="text-xs font-black text-slate-500">Latitude *</label><input v-model="form.lat" placeholder="Enter latitude" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"/></div>
            <div><label class="text-xs font-black text-slate-500">Longitude *</label><input v-model="form.lng" placeholder="Enter longtitude" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"/></div>
          </div>
          <div class="rounded-2xl bg-blue-50 border border-blue-100 grid place-items-center text-blue-300 text-xs font-bold mb-3" style="height:120px">Map preview</div>
        </template>
        
        <template v-else-if="form.venueType === 'online'">
          <label class="flex items-start gap-3 rounded-xl bg-blue-50 border border-blue-100 p-3 cursor-pointer mb-3">
            <input type="checkbox" v-model="form.hostLive" class="mt-0.5 accent-lkblue"/>
            <span>
              <span class="font-black text-sm block">📡 Host on LinkUp Live</span>
              <span class="text-[11px] text-slate-500">Tag this event directly to LinkUp Live so attendees watch inside the app — no external Zoom link needed.</span>
            </span>
          </label>
          <template v-if="form.hostLive">
            <label class="text-xs font-black text-slate-500">Live Category *</label>
            <select v-model="form.liveCat" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white">
              <option v-for="c in LIVE_CATS" :key="c">{{ c }}</option>
            </select>
            <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-2.5 text-[11px] text-emerald-700 font-bold mb-3">✅ Attendees will get a "Watch Live" button that opens your broadcast in LinkUp Live.</div>
          </template>
        </template>
        
        <label class="flex items-start gap-3 rounded-xl bg-slate-50 border border-slate-100 p-3 cursor-pointer">
          <input type="checkbox" v-model="form.reserved" class="mt-0.5 accent-lkblue"/>
          <span>
            <span class="font-black text-sm block">Reserved seating</span>
            <span class="text-[11px] text-slate-500">Use your venue map to set price tiers for each section and choose whether attendees can pick their seat.</span>
          </span>
        </label>
      </div>

      <!-- Additional Options -->
      <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <p class="font-black text-lkblue2 mb-3 pb-2" style="border-bottom:2px solid;border-image:linear-gradient(90deg,#2f9bef,#f59e0b) 1">Additional Options</p>
        
        <p class="text-xs font-black text-slate-500">Event Type</p>
        <div class="flex gap-4 mt-1 mb-3 text-sm font-bold">
          <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" v-model="form.visibility" value="public" class="accent-lkblue"/>Public Event</label>
          <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" v-model="form.visibility" value="private" class="accent-lkblue"/>Private Event</label>
        </div>
        
        <label class="text-xs font-black text-slate-500">Email *</label>
        <input v-model="form.email" placeholder="Contact email" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
        
        <label class="text-xs font-black text-slate-500">Phone *</label>
        <input v-model="form.phone" placeholder="Contact phone" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
        
        <label class="text-xs font-black text-slate-500">Website *</label>
        <input v-model="form.website" placeholder="www.yoursite.com" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
        
        <div class="grid grid-cols-3 gap-2 mb-3">
          <div><label class="text-xs font-black text-slate-500">Country *</label><select v-model="form.country" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"><option v-for="c in availableCountries" :key="c.value" :value="c.value">{{ c.label }}</option></select></div>
          <div><label class="text-xs font-black text-slate-500">State *</label><input v-model="form.state" placeholder="State" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"/></div>
          <div><label class="text-xs font-black text-slate-500">City *</label><input v-model="form.city" placeholder="City" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"/></div>
        </div>
        <div class="mb-3">
          <label class="text-xs font-black text-slate-500">ZIP / Postal Code</label>
          <input v-model="form.zip" placeholder="Required for API tax lookup" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 bg-white"/>
        </div>
        
        <label class="text-xs font-black text-slate-500">Does this event include taxes?</label>
        <div class="flex gap-2 mt-1 mb-3">
          <button @click="form.taxes = 'yes'" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold', form.taxes === 'yes' ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">Yes, taxes are included</button>
          <button @click="form.taxes = 'no'" type="button" :class="['rounded-xl border-2 px-4 py-2 text-sm font-bold', form.taxes === 'no' ? 'border-lkblue text-lkblue2 bg-blue-50' : 'border-slate-200 text-slate-500 bg-white']">No, taxes are not included</button>
        </div>
        <template v-if="form.taxes === 'yes'">
          <label class="text-xs font-black text-slate-500">Tax Rate *</label>
          <input :value="taxRateDisplay" readonly :placeholder="isFetchingTaxRate ? 'Checking tax rate...' : 'No tax rate available'" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none mt-1 bg-slate-50"/>
          <p v-if="isFetchingTaxRate" class="mt-1 mb-3 text-[11px] font-bold text-blue-600">Checking the configured tax API…</p>
          <p v-else-if="form.taxRate !== ''" class="mt-1 mb-3 text-[11px] font-bold text-emerald-600">
            {{ form.taxSource === 'local_database' ? 'Admin country tax fallback' : 'Tax API rate' }} · {{ form.taxType === 'fixed' ? 'Fixed tax' : 'Percentage tax' }}.
          </p>
          <p v-else-if="taxRateError" class="mt-1 mb-3 text-[11px] font-bold text-orange-500">{{ taxRateError }}</p>
        </template>
        
        <label class="text-xs font-black text-slate-500">Disclaimer</label>
        <div class="border border-slate-200 rounded-2xl mt-1 mb-3 overflow-hidden bg-white">
          <div class="flex gap-3 px-3 py-2 border-b border-slate-100 text-xs font-black text-slate-500 bg-slate-50">
            <button type="button" class="w-6 h-6 hover:bg-slate-200 rounded">B</button>
            <button type="button" class="w-6 h-6 italic hover:bg-slate-200 rounded">I</button>
            <button type="button" class="w-6 h-6 underline hover:bg-slate-200 rounded">U</button>
          </div>
          <textarea v-model="form.disclaimer" rows="2" placeholder="Please make sure arrive 10 minutes before your appointment time" class="w-full px-4 py-3 outline-none"></textarea>
        </div>
        
        <label class="text-xs font-black text-slate-500">Images gallery</label>
        <p class="text-[11px] text-slate-400 mb-2">Add multiple images for your event</p>
        <div class="flex flex-wrap gap-2 mb-3">
          <div v-for="(g, i) in form.gallery" :key="i" class="relative">
            <img :src="g" class="h-16 w-16 rounded-xl object-cover"/>
            <button @click="form.gallery.splice(i, 1)" type="button" class="absolute -top-1.5 -right-1.5 h-5 w-5 rounded-full bg-rose-500 text-white text-[10px] grid place-items-center shadow-sm">✕</button>
          </div>
          <label class="h-16 w-16 rounded-xl border-2 border-dashed border-blue-200 grid place-items-center cursor-pointer hover:bg-blue-50 transition">
            <input type="file" accept="image/*" class="hidden" @change="onGalleryPick"/>
            <i data-lucide="plus" class="w-4 h-4 text-lkblue"></i>
          </label>
        </div>
        
        <label class="text-xs font-black text-slate-500">Artists</label>
        <p class="text-[11px] text-slate-400 mb-2">Enter the list of artists that will perform in your event (press Enter after each entry)</p>
        <input v-model="artistInput" @keyup.enter="addArtist" placeholder="Type artist name and press Enter" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mb-2 bg-white"/>
        <div v-if="form.artists.length" class="flex flex-wrap gap-2 mb-1">
          <span v-for="(a, i) in form.artists" :key="i" class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold flex items-center gap-1.5">
            {{ a }}<button @click="form.artists.splice(i, 1)" type="button" class="text-slate-400 hover:text-slate-600">✕</button>
          </span>
        </div>
      </div>
      
      <!-- Scanner -->
      <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <p class="font-black text-lkblue2 mb-3 pb-2" style="border-bottom:2px solid;border-image:linear-gradient(90deg,#2f9bef,#f59e0b) 1">Scanner</p>
        <label class="text-xs font-black text-slate-500">Scanners *</label>
        <div class="flex flex-wrap gap-2 mt-2 mb-2">
          <span v-for="(s, i) in form.scanners" :key="i" class="rounded-full bg-blue-50 text-lkblue2 px-3 py-1.5 text-xs font-bold flex items-center gap-1.5">
            {{ s }}<button @click="form.scanners.splice(i, 1)" type="button" class="text-lkblue2/60 hover:text-lkblue2">✕</button>
          </span>
        </div>
        <input v-model="scannerInput" @keyup.enter="addScannerFromInput" placeholder="Search scanner..." class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue bg-white"/>
        <div v-if="availableScanners.length" class="flex flex-wrap gap-2 mt-2">
          <button v-for="n in availableScanners" :key="n" @click="addScanner(n)" type="button" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-50">+ {{ n }}</button>
        </div>
      </div>
      
      <!-- Social Media -->
      <div class="rounded-2xl border border-slate-100 p-4 mb-4">
        <p class="font-black text-lkblue2 mb-3 pb-2" style="border-bottom:2px solid;border-image:linear-gradient(90deg,#2f9bef,#f59e0b) 1">Social Media</p>
        <label class="text-xs font-black text-slate-500">X (Twitter)</label><input v-model="form.social.x" placeholder="Enter X link" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
        <label class="text-xs font-black text-slate-500">Instagram</label><input v-model="form.social.instagram" placeholder="Enter Instagram link" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
        <label class="text-xs font-black text-slate-500">Facebook</label><input v-model="form.social.facebook" placeholder="Enter Facebook link" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
        <label class="text-xs font-black text-slate-500">TikTok</label><input v-model="form.social.tiktok" placeholder="Enter TikTok link" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
        <label class="text-xs font-black text-slate-500">LinkedIn</label><input v-model="form.social.linkedin" placeholder="Enter LinkedIn link" class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-lkblue mt-1 mb-3 bg-white"/>
      </div>
      
      <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3 mb-4 text-[11px] text-slate-500 font-bold flex items-center gap-2">
        <i data-lucide="ticket" class="w-4 h-4 shrink-0"></i>Ticket price and promoter commission are now set per ticket — add a ticket from My Tickets after publishing.
      </div>
      
      <label class="flex items-center gap-2 mt-1 mb-3 font-bold text-sm cursor-pointer w-fit">
        <input type="checkbox" v-model="form.postAsOrg" class="accent-lkblue"/> Post as Organization / Group
      </label>
      
      <button @click="save" class="btn btn-primary w-full py-3.5 shadow-sm text-sm">{{ editId ? 'Save Changes' : 'Publish Event' }}</button>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, reactive, nextTick, watch } from 'vue';
import axios from 'axios';
import Modal from '../ui/Modal.vue';

const props = defineProps({
  countryTaxRules: { type: Array, default: () => [] },
  countries: { type: Array, default: () => [] }
});

const modalRef = ref(null);
const editId = ref(null);

const EV_CATS = ['Party/Fete', 'Wellness & Spa', 'Cookouts/Food', 'Arts', 'Music', 'Sports', 'Online', 'Business'];
const AUD = ['Adults', 'Children', 'Family', 'Youth', 'Group'];
const LIVE_CATS = ['Just Chatting', 'Music & DJ', 'Comedy', 'Education'];
const SHOP_COUNTRIES = ['Jamaica', 'Trinidad & Tobago', 'Barbados', 'Bahamas', 'Anguilla'];
const SCANNER_POOL = ['Manuel Isaac', 'Renee', 'Marcus', 'Amara', 'Simone', 'Andre', 'Denise'];

const availableCountries = computed(() => {
  const options = new Map();

  props.countries.forEach((country) => {
    const value = String(country.code || country.name || '').toUpperCase();
    if (value) options.set(value, { value, label: country.name || value });
  });

  props.countryTaxRules.forEach((rule) => {
    const value = String(rule.country || rule.country_label || '').toUpperCase();
    if (value && !options.has(value)) options.set(value, { value, label: rule.country_label || value });
  });

  SHOP_COUNTRIES.forEach((country) => {
    if (!Array.from(options.values()).some((option) => option.label === country)) {
      options.set(country, { value: country, label: country });
    }
  });

  return Array.from(options.values());
});

const initialForm = {
  image: null,
  name: '',
  desc: '',
  cat: 'Party/Fete',
  audiences: ['Adults'],
  attendees: 'show',
  reviews: 'enable',
  seating: 'no',
  type: 'single',
  recPattern: 'Daily',
  recStart: '',
  recEnd: '',
  date: '',
  venueType: 'venue',
  venueLoc: '',
  lat: '',
  lng: '',
  hostLive: true,
  liveCat: 'Just Chatting',
  reserved: false,
  visibility: 'public',
  email: '',
  phone: '',
  website: '',
  country: 'JM',
  state: '',
  city: '',
  zip: '',
  taxes: 'no',
  taxRate: '',
  taxType: '',
  taxSource: '',
  disclaimer: '',
  gallery: [],
  artists: [],
  scanners: ['You'],
  social: { x: '', instagram: '', facebook: '', tiktok: '', linkedin: '' },
  postAsOrg: false
};

const form = reactive({ ...initialForm });

const taxRateDisplay = computed(() => {
  if (form.taxRate === '') return '';
  const value = Number(form.taxRate || 0).toFixed(2);
  return form.taxType === 'fixed' ? `$${value}` : `${value}%`;
});

const isFetchingTaxRate = ref(false);
const taxRateError = ref('');
let taxRequestSequence = 0;
let taxLookupTimer = null;

const fetchTaxRate = async () => {
  if (!form.country) return;

  const sequence = ++taxRequestSequence;
  isFetchingTaxRate.value = true;
  taxRateError.value = '';

  try {
    const response = await axios.post(route('new_frontend.events.tax-rate'), {
      country: form.country,
      state: form.state || null,
      city: form.city || null,
      zip: form.zip || null
    });

    if (sequence !== taxRequestSequence) return;

    if (response.data.success) {
      const source = response.data.data?.source || 'tax_api';
      const isLocal = source === 'local_database';
      const rate = Number(response.data.rate || 0);
      form.taxRate = isLocal ? rate : rate * 100;
      form.taxType = response.data.data?.tax_type || 'percentage';
      form.taxSource = source;
      form.taxes = 'yes';
    } else {
      form.taxRate = '';
      form.taxType = '';
      form.taxSource = '';
      form.taxes = 'no';
      taxRateError.value = response.data.message || 'No tax rate is available for this country.';
    }
  } catch (error) {
    if (sequence !== taxRequestSequence) return;
    form.taxRate = '';
    form.taxType = '';
    form.taxSource = '';
    form.taxes = 'no';
    taxRateError.value = error.response?.data?.message || 'Unable to retrieve a tax rate.';
  } finally {
    if (sequence === taxRequestSequence) isFetchingTaxRate.value = false;
  }
};

watch([() => form.country, () => form.state, () => form.city, () => form.zip], () => {
  clearTimeout(taxLookupTimer);
  form.taxRate = '';
  form.taxType = '';
  form.taxSource = '';
  taxLookupTimer = setTimeout(fetchTaxRate, 350);
}, { immediate: true });

const artistInput = ref('');
const scannerInput = ref('');

const availableScanners = computed(() => {
  return SCANNER_POOL.filter(n => !form.scanners.includes(n));
});

const toggleAudience = (a) => {
  const i = form.audiences.indexOf(a);
  if (i > -1) form.audiences.splice(i, 1);
  else form.audiences.push(a);
};

const onImagePick = (e) => {
  if (e.target.files && e.target.files[0]) {
    form.image = URL.createObjectURL(e.target.files[0]);
  }
};

const onGalleryPick = (e) => {
  if (e.target.files && e.target.files[0]) {
    form.gallery.push(URL.createObjectURL(e.target.files[0]));
  }
};

const addArtist = () => {
  const val = artistInput.value.trim();
  if (val && !form.artists.includes(val)) {
    form.artists.push(val);
  }
  artistInput.value = '';
};

const addScanner = (name) => {
  if (name && !form.scanners.includes(name)) {
    form.scanners.push(name);
  }
};

const addScannerFromInput = () => {
  addScanner(scannerInput.value.trim());
  scannerInput.value = '';
};

const open = (existingEvent = null) => {
  Object.assign(form, initialForm);
  if (existingEvent) {
    editId.value = existingEvent.id;
    form.name = existingEvent.title || '';
    form.desc = existingEvent.desc || '';
    form.cat = existingEvent.category || 'Party/Fete';
    form.image = existingEvent.image || null;
    form.date = existingEvent.date || '';
    form.venueLoc = existingEvent.location || '';
  } else {
    editId.value = null;
  }
  
  if (modalRef.value) {
    modalRef.value.open();
    nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const save = () => {
  if (!form.name.trim()) {
    if (window.toast) window.toast('Enter an event name');
    return;
  }
  
  if (window.toast) {
    window.toast(editId.value ? `✅ "${form.name}" updated` : `✅ "${form.name}" published`);
  }
  close();
};

defineExpose({ open, close });
</script>
