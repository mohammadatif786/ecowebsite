<script setup lang="ts">
import { ref } from 'vue';
import { Images, RefreshCw } from 'lucide-vue-next';

const props = defineProps<{
    initialSplashScreens?: any[];
}>();

const saveAppSettings = () => {
    alert('App settings saved');
};

const saveAndSyncSplash = () => {
    alert('Splash screens saved & synced');
};

const splashScreens = ref(props.initialSplashScreens || [
    { code: 'br', title: 'Linkup Vibes!', desc: 'The only App that brings Caribbean & Latin Americans together from all over the World.', img: 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=800&q=70' },
    { code: 'bs', title: 'Linkup Vibes', desc: 'With Linkup Vibes, you can select which Caribbean or Latin American country you want to see people from.', img: 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=800&q=70' },
    { code: 'jm', title: 'Linkup Vibes Live', desc: 'Share your partying experience with the world by live streaming on Linkup from your favorite fete, party, club, or festival.', img: 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&q=70' },
    { code: 'cu', title: 'Linkup Vibes Restaurant', desc: 'Want to take your new date to a nice restaurant? Select the food section on the app and all the restaurants in your area will be visible.', img: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=800&q=70' }
]);
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div>
                <h3 class="text-3xl font-black">App Settings</h3>
                <p class="text-slate-500">Manage public app content, support documents, platform defaults, and customer-facing legal text.</p>
            </div>
            <button @click="saveAppSettings" class="rounded-2xl bg-slate-950 text-white px-5 py-3 font-black">Save App Settings</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="card rounded-3xl p-5">
                <p class="text-slate-500 font-bold">Legal Version</p>
                <h3 class="text-3xl font-black">v2026.06</h3>
                <p class="text-green-600 text-sm font-bold">Published</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="text-slate-500 font-bold">Support Articles</p>
                <h3 class="text-3xl font-black">18</h3>
                <p class="text-sky-600 text-sm font-bold">Public Help Center</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="text-slate-500 font-bold">App Mode</p>
                <h3 class="text-3xl font-black">Production</h3>
                <p class="text-purple-600 text-sm font-bold">Live</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="text-slate-500 font-bold">Last Updated</p>
                <h3 class="text-3xl font-black">Today</h3>
                <p class="text-amber-600 text-sm font-bold">Admin controlled</p>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                <div>
                    <h3 class="text-xl font-black flex items-center gap-2">
                        <Images class="w-5 h-5 text-purple-600" />
                        Splash Screens & Flags
                    </h3>
                    <p class="text-sm text-slate-500">The onboarding carousel users see on first launch. <b>Admin-only</b> — users can't change it. Saving syncs straight to the app.</p>
                </div>
                <button @click="saveAndSyncSplash" class="rounded-2xl bg-purple-600 text-white px-4 py-2 font-black flex items-center gap-2">
                    <RefreshCw class="w-4 h-4" />
                    Save & Sync
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <div v-for="(s, i) in splashScreens" :key="i" class="rounded-2xl border border-slate-200 p-3">
                    <p class="text-[11px] font-black text-slate-400 mb-2">Slide {{ i + 1 }}</p>
                    <div class="flex gap-3">
                        <img :src="s.img" class="h-24 w-16 rounded-lg object-cover bg-slate-100" />
                        <div class="flex-1 space-y-2">
                            <input v-model="s.title" placeholder="Title" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-black" />
                            <input v-model="s.code" maxlength="2" placeholder="Flag code (e.g. bs)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                    </div>
                    <textarea v-model="s.desc" rows="2" placeholder="Description" class="w-full mt-2 rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                    <input v-model="s.img" placeholder="Image URL" class="w-full mt-2 rounded-xl border border-slate-200 px-3 py-2 text-xs" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
            <div class="card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">Core App Defaults</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="font-bold text-slate-600">App Name
                        <input value="LinkUp" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="font-bold text-slate-600">Website
                        <input value="linkupvibes.com" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="font-bold text-slate-600">Support Email
                        <input value="support@linkupvibes.com" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="font-bold text-slate-600">Legal Email
                        <input value="legal@linkupvibes.com" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="font-bold text-slate-600">Default Country
                        <select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                            <option>Bahamas</option>
                            <option>United States</option>
                            <option>Jamaica</option>
                            <option>Trinidad and Tobago</option>
                        </select>
                    </label>
                    <label class="font-bold text-slate-600">Default Currency
                        <select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                            <option>USD</option>
                            <option>BSD</option>
                            <option>JMD</option>
                            <option>TTD</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">Customer Documents</h3>
                <div class="space-y-3">
                    <div class="rounded-2xl border border-slate-100 p-4 flex justify-between items-center">
                        <div>
                            <b>Terms of Service</b>
                            <p class="text-sm text-slate-500">User rules, wallet terms, subscriptions, disputes, liability.</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-black">Published</span>
                    </div>
                    <div class="rounded-2xl border border-slate-100 p-4 flex justify-between items-center">
                        <div>
                            <b>Privacy Policy</b>
                            <p class="text-sm text-slate-500">Data collection, user consent, privacy controls.</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-black">Published</span>
                    </div>
                    <div class="rounded-2xl border border-slate-100 p-4 flex justify-between items-center">
                        <div>
                            <b>Support Document</b>
                            <p class="text-sm text-slate-500">Help center guide for account, billing, events and wallet.</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-black">Draft Update</span>
                    </div>
                    <div class="rounded-2xl border border-slate-100 p-4 flex justify-between items-center">
                        <div>
                            <b>Contact Page</b>
                            <p class="text-sm text-slate-500">Support channels, office address, response times.</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-sky-50 text-sky-700 text-xs font-black">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <h3 class="text-xl font-black mb-4">Legal / Support Editor</h3>
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex gap-4 text-slate-600 font-bold">
                    <span>B</span><span>I</span><span>U</span><span>H1</span><span>H2</span><span>List</span><span>Link</span>
                </div>
                <textarea class="w-full min-h-[280px] p-5 outline-none" placeholder="Paste or write Terms, Privacy Policy, Support Document, Contact Page, or App Notice here...">Link Up Terms of Service&#10;&#10;Welcome to Link Up. This section allows administrators to update legal, support, and customer-facing app content from the backend. Developers should connect this editor to the CMS/legal-content table with version control and publish status.</textarea>
            </div>
        </div>
    </div>
</template>
