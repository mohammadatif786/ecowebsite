<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    Shield, SlidersHorizontal, Zap, Globe, Flag, Tag, Sparkles, Search, RotateCcw
} from 'lucide-vue-next';

interface Story {
    id: string;
    title: string;
    country: string;
    countryCode: string;
    region: string;
    category: string;
    publishedAt: string;
    isBreaking: boolean;
    breakingExpiresAt: string | null;
}

const props = defineProps<{
    breakingStories: Story[];
    prefs: any;
    searchQuery: string;
}>();

const emit = defineEmits<{
    openAdmin: [];
    openPrefs: [];
    'update:searchQuery': [val: string];
    reset: [];
    openArticle: [id: string];
}>();

const userProfile = computed(() => {
    const authUser = (usePage().props.auth as any).user;
    return {
        name: authUser?.name || 'New User',
        tag: authUser?.tag || '~NewUser',
        linkup_id: authUser?.linkup_id || 'newuser',
        avatar: authUser?.avatar || null,
    };
});

const FLAG = (code: string) => {
    const map: Record<string, string> = {
        JM: "🇯🇲", BS: "🇧🇸", TT: "🇹🇹", BB: "🇧🇧", HT: "🇭🇹", DO: "🇩🇴", PR: "🇵🇷",
        AG: "🇦🇬", GD: "🇬🇩", LC: "🇱🇨", VC: "🇻🇨", KN: "🇰🇳", GY: "🇬🇾", SR: "🇸🇷",
        BR: "🇧🇷", CO: "🇨🇴", MX: "🇲🇽", AR: "🇦🇷", CL: "🇨🇱", PE: "🇵🇪", VE: "🇻🇪",
        PA: "🇵🇦", CR: "🇨🇷", GLB: "🌍"
    };
    return map[code] || "🏳️";
};

const internalSearch = ref(props.searchQuery);

const onSearchInput = (e: Event) => {
    const val = (e.target as HTMLInputElement).value;
    internalSearch.value = val;
    emit('update:searchQuery', val);
};

const reset = () => {
    internalSearch.value = '';
    emit('reset');
};

const goHome = () => {
    window.location.href = '/home';
};

const chipRegionsText = computed(() => {
    const r = [];
    if (props.prefs.regions.caribbean) r.push("Caribbean");
    if (props.prefs.regions.latam) r.push("Latin America");
    if (props.prefs.regions.global) r.push("Global");
    return r.length ? r.join(" + ") : "None";
});

const chipCountriesText = computed(() => {
    const countries = props.prefs.countries || [];
    if (countries.length === 0) return "None";
    if (countries.length > 3) return `${countries.length} Selected`;
    return countries.join(", ");
});

const chipCatsText = computed(() => {
    const cats = props.prefs.categories || [];
    if (cats.length === 0) return "None";
    if (cats.length === 8) return "All categories";
    return `${cats.length} selected`;
});

const chipModeText = computed(() => {
    const mode = props.prefs.mode;
    return mode.charAt(0).toUpperCase() + mode.slice(1);
});

const tickerItems = computed(() => {
    if (props.breakingStories.length === 1) {
        return props.breakingStories;
    }
    return [...props.breakingStories, ...props.breakingStories];
});
</script>

<template>
    <div class="stickyTop">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white font-black"
                        style="background:linear-gradient(135deg, #0ea5e9, #38bdf8); box-shadow:0 14px 30px rgba(14,165,233,.25);">
                        360
                    </div>
                    <div>
                        <div class="text-[1.05rem] font-black leading-tight">Caribbean 360 News</div>
                        <div class="text-[.85rem] font-extrabold muted leading-tight">Caribbean + Latin America • Choose
                            your countries • See what matters</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- User Details -->
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-white border border-gray-200">
                        <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
                            <img 
                                v-if="userProfile.avatar" 
                                :src="userProfile.avatar" 
                                :alt="userProfile.name"
                                class="w-full h-full object-cover"
                            />
                            <div 
                                v-else 
                                class="w-full h-full bg-gradient-to-br from-blue-500 to-cyan-500 text-white text-xs font-bold flex items-center justify-center"
                            >
                                {{ userProfile.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <div class="text-sm font-bold truncate">{{ userProfile.name }}</div>
                            <div class="text-xs text-gray-500 truncate">~{{ userProfile.linkup_id }}</div>
                        </div>
                    </div>
                    
                    <button @click="goHome" class="btn">
                        Home
                    </button>
                    
                    <button id="btnOpenPrefs" @click="emit('openPrefs')" class="btn btnPrimary">
                        <SlidersHorizontal :size="18" /> Preferences
                    </button>
                </div>
            </div>

            <!-- Breaking Ticker -->
            <div class="mt-3 ticker fadeMask" v-if="props.breakingStories.length">
                <div class="flex items-center gap-2 px-4 pt-4">
                    <div class="dotPulse"></div>
                    <div class="font-black text-[1.05rem] tracking-tight">BREAKING</div>
                    <div class="pill" title="Auto-refresh" style="font-size:.9rem; padding:.45rem .8rem;">
                        <Zap :size="14" /> Live Strip
                    </div>
                    <div class="ml-auto text-[.9rem] font-extrabold muted hidden sm:block">
                        Tip: press <span class="kbd">/</span> to search
                    </div>
                </div>
                <div class="tickerTrack" id="breakingTrack"
                    :style="{ animation: props.breakingStories.length === 1 ? 'none' : '' }">
                    <button v-for="(s, idx) in tickerItems" :key="s.id + '-' + idx" class="pill tickerItem"
                        style="border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.06);"
                        @click="emit('openArticle', s.id)">
                        <span class="font-black">🔴</span>
                        <span class="font-black">{{ s.title.replace(/^BREAKING:\s*/i, '') }}</span>
                        <span class="muted font-extrabold">•</span>
                        <span class="font-black">{{ FLAG(s.countryCode) }} {{ s.countryCode === 'GLB' ? s.region :
                            s.country }}</span>
                    </button>
                </div>
            </div>

            <!-- Quick Controls -->
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <div class="chip">
                    <Globe :size="14" />
                    <span id="chipRegions">{{ chipRegionsText }}</span>
                </div>
                <div class="chip">
                    <Flag :size="14" />
                    <span id="chipCountries">{{ chipCountriesText }}</span>
                </div>
                <div class="chip">
                    <Tag :size="14" />
                    <span id="chipCats">{{ chipCatsText }}</span>
                </div>
                <div class="chip">
                    <Sparkles :size="14" />
                    <span id="chipMode">{{ chipModeText }}</span>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <div class="relative w-[280px] max-w-full">
                        <input id="searchInput" class="input pl-11" :value="internalSearch" @input="onSearchInput"
                            placeholder="Search headlines, keywords..." />
                    </div>
                    <button id="btnReset" class="btn" @click="reset" title="Reset filters">
                        <RotateCcw :size="18" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.stickyTop {
    position: sticky;
    top: 0;
    z-index: 50;
    backdrop-filter: saturate(180%) blur(10px);
    background: rgba(245, 247, 251, .85);
    border-bottom: 1px solid rgba(148, 163, 184, .35);
}

.ticker {
    background: linear-gradient(135deg, rgba(239, 68, 68, .16), rgba(14, 165, 233, .14));
    border: 2px solid rgba(239, 68, 68, .35);
    border-radius: 26px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 20px 50px rgba(239, 68, 68, .18), inset 0 1px 0 rgba(255, 255, 255, .6);
}

.tickerTrack {
    display: flex;
    gap: 36px;
    align-items: center;
    white-space: nowrap;
    will-change: transform;
    animation: scroll 22s linear infinite;
    padding: 1.25rem 1.5rem;
    font-size: 1.05rem;
    font-weight: 900;
    letter-spacing: -0.01em;
}

.tickerItem {
    padding: .55rem 1rem;
    font-size: .95rem;
    font-weight: 900;
    border-width: 2px;
    cursor: pointer;
    transition: transform 0.1s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.tickerItem:hover {
    transform: scale(1.02);
}

@keyframes scroll {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(-50%);
    }
}

.fadeMask {
    -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 6%, #000 94%, transparent 100%);
    mask-image: linear-gradient(90deg, transparent 0, #000 6%, #000 94%, transparent 100%);
}

.dotPulse {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: #ef4444;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, .6);
    animation: pulse 1.2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, .55);
    }

    70% {
        box-shadow: 0 0 0 12px rgba(239, 68, 68, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
    }
}

.btn {
    border-radius: 16px;
    font-weight: 900;
    padding: .75rem 1rem;
    display: inline-flex;
    gap: .6rem;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    transition: transform .12s ease, filter .12s ease;
    user-select: none;
}

.btn:hover {
    transform: translateY(-1px);
    filter: brightness(1.02);
}

.btnPrimary {
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    border-color: transparent;
    color: #fff;
}

.btnSoft {
    background: linear-gradient(135deg, rgba(14, 165, 233, .10), rgba(34, 197, 94, .10));
}

.pill {
    border-radius: 999px;
    padding: .25rem .6rem;
    font-size: .75rem;
    font-weight: 900;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    white-space: nowrap;
}

.chip {
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 999px;
    padding: .45rem .75rem;
    font-weight: 800;
    font-size: .8rem;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: #fff;
}

.input {
    width: 100%;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 16px;
    padding: .8rem .9rem;
    outline: none;
    font-weight: 800;
    background: #fff;
}

.input:focus {
    border-color: rgba(14, 165, 233, .55);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
}

.muted {
    color: #64748b;
}

.kbd {
    font-weight: 900;
    font-size: .75rem;
    padding: .18rem .45rem;
    border-radius: 10px;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
}
</style>