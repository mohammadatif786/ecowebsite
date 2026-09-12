<script setup lang="ts">
import {
    Coins, PlusSquare, Heart, Home, Search, PlusCircle, Megaphone, User,
    MessageCircle, Repeat2, Send, Bookmark, ChevronRight, PlayCircle,
    ArrowLeft, HelpCircle, Gift
} from 'lucide-vue-next';

defineProps<{
    getRankedFeed: any[];
    vibesAds: any[];
    feedViewer: any;
    num: (n: number) => string;
}>();

const emit = defineEmits(['fpFollow', 'fpLike', 'openComments', 'openCoinGift', 'fpSave', 'showExecutive']);
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-300">
        <div class="flex items-center gap-3">
            <button @click="emit('showExecutive')" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 font-black flex items-center gap-2 hover:bg-slate-50 transition shadow-sm">
                <ArrowLeft class="w-4 h-4" /> Back to LinkUp
            </button>
            <div>
                <h3 class="text-3xl font-black text-slate-950">LinkUp Vibes — App Preview</h3>
                <p class="text-slate-500 font-medium">A live mock of the consumer feed. Tap ♥ to like, 💬 to comment, 🪙 to send coins, and Buy on shoppable posts — exactly what users will see.</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-8 items-start justify-center xl:justify-start">
            <!-- Mobile Mock Container -->
            <div class="mx-auto xl:mx-0">
                <div class="rounded-[2.5rem] bg-slate-900 p-3 shadow-2xl" style="width:390px">
                    <div class="rounded-[2rem] bg-white overflow-hidden" style="height:760px;display:flex;flex-direction:column">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 shrink-0">
                            <span class="text-xl font-black text-slate-950" style="font-family:cursive">LinkUp Vibes</span>
                            <div class="flex items-center gap-3 text-slate-700">
                                <span class="flex items-center gap-1 rounded-full bg-amber-50 text-amber-600 px-2 py-0.5 text-xs font-black">
                                    <Coins class="w-3.5 h-3.5" />
                                    <span>{{ num(feedViewer.coins) }}</span>
                                </span>
                                <PlusSquare class="w-5 h-5 cursor-pointer" />
                                <div class="relative cursor-pointer">
                                    <Gift class="w-5 h-5" />
                                    <span class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white rounded-full text-[9px] font-black px-1 hidden"></span>
                                </div>
                                <div class="relative cursor-pointer">
                                    <Heart class="w-5 h-5" />
                                    <span class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white rounded-full text-[9px] font-black px-1">8</span>
                                </div>
                            </div>
                        </div>

                        <!-- Verbatim Stories Bar Mock -->
                        <div class="flex gap-3 px-3 py-2 overflow-x-auto scrollbar border-b border-slate-100 shrink-0">
                            <div class="flex flex-col items-center gap-1 shrink-0">
                                <div class="h-14 w-14 rounded-full border-2 border-dashed border-slate-300 grid place-items-center bg-slate-50 text-slate-400">
                                    <PlusCircle class="w-6 h-6" />
                                </div>
                                <span class="text-[10px] font-bold text-slate-500">Your story</span>
                            </div>
                            <div v-for="i in 5" :key="i" class="flex flex-col items-center gap-1 shrink-0">
                                <div class="h-14 w-14 rounded-full p-0.5 bg-gradient-to-tr from-amber-400 to-fuchsia-600">
                                    <div class="h-full w-full rounded-full bg-white p-0.5">
                                        <div class="h-full w-full rounded-full bg-slate-100 grid place-items-center font-black text-xs text-slate-400">CR</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-500">creator{{i}}</span>
                            </div>
                        </div>

                        <div class="overflow-y-auto scrollbar relative bg-slate-50/50" style="flex:1">
                            <template v-for="(p, i) in getRankedFeed" :key="p.id">
                                <div class="bg-white border-b border-slate-100 mb-2 shadow-sm">
                                    <div class="flex items-center gap-2 px-3 py-2">
                                        <div class="h-9 w-9 rounded-full bg-slate-100 grid place-items-center text-xs font-black text-slate-500 uppercase">{{ p.handle.slice(1,2) }}</div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-black text-sm leading-tight truncate text-slate-900">{{ p.handle }}</p>
                                            <p class="text-[11px] text-slate-500">{{ p.country }} · {{ p.type }}</p>
                                        </div>
                                        <button @click="emit('fpFollow', p.handle)" class="text-xs font-black" :class="feedViewer.following[p.handle] ? 'text-slate-400' : 'text-indigo-600'">
                                            {{ feedViewer.following[p.handle] ? 'Following' : 'Follow' }}
                                        </button>
                                        <MoreHorizontal class="w-5 h-5 text-slate-400 ml-1" />
                                    </div>
                                    <div class="relative bg-slate-100" @dblclick="emit('fpLike', p.id)">
                                        <img :src="p.img" class="w-full object-cover" style="max-height:320px" loading="lazy">
                                        <span class="absolute top-2 right-2 rounded-full bg-black/50 text-white px-2 py-0.5 text-[10px] font-black">{{ (p.views/1000).toFixed(0) }}k views</span>
                                        <div v-if="p.type === 'Reel'" class="absolute bottom-2 right-2 text-white drop-shadow-md"><PlayCircle class="w-7 h-7" /></div>
                                    </div>
                                    <div class="flex items-center gap-4 px-3 pt-2 text-slate-700">
                                        <button @click="emit('fpLike', p.id)" class="flex items-center gap-1 transition" :class="p.liked ? 'text-rose-500' : ''">
                                            <Heart class="w-5 h-5" :fill="p.liked ? 'currentColor' : 'none'" />
                                            <span class="text-xs font-black">{{ (p.likes/1000).toFixed(1) }}k</span>
                                        </button>
                                        <button @click="emit('openComments', p.id)" class="flex items-center gap-1 hover:text-indigo-600 transition">
                                            <MessageCircle class="w-5 h-5" />
                                            <span class="text-xs font-black">{{ (p.comments/1000).toFixed(1) }}k</span>
                                        </button>
                                        <Repeat2 class="w-5 h-5 hover:text-green-600 transition cursor-pointer" />
                                        <Send class="w-5 h-5 hover:text-blue-600 transition cursor-pointer" />
                                        <button @click="emit('openCoinGift', p.id)" class="flex items-center gap-1 ml-auto text-amber-500 transition active:scale-110">
                                            <Coins class="w-5 h-5" />
                                            <span class="text-xs font-black">{{ p.coins }}</span>
                                        </button>
                                        <button @click="emit('fpSave', p.id)" class="transition" :class="feedViewer.saved[p.id] ? 'text-indigo-600' : 'text-slate-700'">
                                            <Bookmark class="w-5 h-5" :fill="feedViewer.saved[p.id] ? 'currentColor' : 'none'" />
                                        </button>
                                    </div>
                                    <div class="px-3 pt-1 pb-3">
                                        <p class="text-sm leading-relaxed"><span class="font-black mr-2 text-slate-900">{{ p.handle }}</span> {{ p.caption }}</p>
                                        <div v-if="p.music" class="flex items-center gap-1 text-[11px] text-slate-500 mt-1"><PlayCircle class="w-3 h-3" /> {{ p.music }}</div>
                                        <button @click="emit('openComments', p.id)" class="text-[11px] text-slate-400 mt-1 font-bold">View all comments</button>

                                        <div v-if="p.tag" class="mt-2 flex items-center justify-between rounded-2xl bg-slate-900 text-white px-3 py-2 shadow-lg">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <ShoppingCart class="w-4 h-4 shrink-0 text-white"/>
                                                <span class="text-xs font-black truncate uppercase tracking-tighter">{{ p.tag.name }} · ${{ p.tag.price }}</span>
                                            </div>
                                            <button class="rounded-full bg-white text-slate-900 px-3 py-1 text-xs font-black shrink-0 transition active:scale-95">Buy</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Interleaved Ad Unit -->
                                <div v-if="(i+1) % 2 === 0 && vibesAds[Math.floor(i/2)]" class="bg-white border-b border-slate-100 mb-2 shadow-sm">
                                    <div class="flex items-center gap-2 px-3 py-2">
                                        <div class="h-9 w-9 rounded-full bg-amber-50 grid place-items-center text-lg shadow-sm border border-amber-100">{{ vibesAds[Math.floor(i/2)].logo }}</div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-black text-sm leading-tight truncate text-slate-900">{{ vibesAds[Math.floor(i/2)].handle }} <span class="rounded-full bg-amber-100 text-amber-700 px-2 py-0.5 text-[10px] font-black uppercase ml-1">Sponsored</span></p>
                                            <p class="text-[11px] text-slate-500 font-bold">{{ vibesAds[Math.floor(i/2)].advertiser }} · Paid Partnership</p>
                                        </div>
                                    </div>
                                    <div class="relative bg-slate-100">
                                        <img :src="vibesAds[Math.floor(i/2)].img" class="w-full object-cover" style="max-height:320px">
                                    </div>
                                    <button class="w-full flex items-center justify-between px-3 py-2.5 bg-slate-50 border-y border-slate-100 transition active:bg-slate-100">
                                        <span class="font-black text-sm text-slate-900 flex items-center gap-2 uppercase tracking-widest text-[10px]"><LinkIcon class="w-4 h-4 text-indigo-600"/> {{ vibesAds[Math.floor(i/2)].cta }}</span>
                                        <ChevronRight class="w-4 h-4 text-slate-400" />
                                    </button>
                                    <div class="px-3 pt-2 pb-4">
                                        <p class="text-sm font-medium text-slate-600 leading-relaxed"><span class="font-black mr-2 text-slate-950">{{ vibesAds[Math.floor(i/2)].handle }}</span> {{ vibesAds[Math.floor(i/2)].caption }}</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="flex items-center justify-around py-2 border-t border-slate-100 text-slate-700 shrink-0 bg-white/95 backdrop-blur-md">
                            <Home class="w-6 h-6 text-slate-900" />
                            <Search class="w-6 h-6" />
                            <button><PlusCircle class="w-7 h-7 text-indigo-600 shadow-xl active:scale-90 transition" /></button>
                            <Megaphone class="w-6 h-6" />
                            <User class="w-6 h-6" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side Info Cards -->
            <div class="flex-1 min-w-[260px] space-y-4 font-bold">
                <div class="card rounded-3xl p-6 bg-white border border-slate-100 shadow-sm">
                    <h3 class="text-xl font-black mb-2 text-slate-950">What you're seeing</h3>
                    <ul class="text-sm text-slate-600 space-y-2 list-disc pl-5 font-medium leading-relaxed">
                        <li>Posts are ordered by the <b>Feed Engine</b> ranking weights.</li>
                        <li><b>Stories</b> bar up top, photos & reels in the feed.</li>
                        <li>♥ like, 💬 comment, ↗ share, 🔖 save, and the <b>🪙 Coin tip</b>.</li>
                        <li><b>Shoppable posts</b> show a Buy bar → Marketplace / Events.</li>
                        <li>Sponsored posts are labelled; reels show music.</li>
                    </ul>
                </div>
                <div class="card rounded-3xl p-6 bg-white border border-slate-100 shadow-sm">
                    <h3 class="text-lg font-black mb-2 text-slate-950">Try it</h3>
                    <p class="text-sm text-slate-500 mb-4 font-medium leading-relaxed">Tap the <b>🪙 coin</b> on any post in the phone to open the condensed in-app <b>Send a Gift</b> sheet — it uses your live coin balance.</p>
                    <button class="w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-pink-500 text-white px-5 py-4 font-black uppercase text-xs tracking-widest shadow-xl active:scale-95 transition">
                        📣 Advertise My Business <span class="opacity-80 font-bold block mt-1">(front-end self-serve)</span>
                    </button>
                </div>
                <div class="card rounded-3xl p-6 bg-white border border-slate-100 shadow-sm">
                    <h3 class="text-lg font-black mb-2 text-slate-950">Backend ↔ Front-end</h3>
                    <p class="text-sm text-slate-500 font-medium leading-relaxed">This preview reads the same data your admin manages. When the public app is built, it consumes these same structures (posts, creators, tags, ranking, monetization) via API.</p>
                </div>
            </div>
        </div>
    </div>
</template>
