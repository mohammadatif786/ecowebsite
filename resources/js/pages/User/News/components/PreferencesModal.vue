<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import {
    Save, X, Layers, Map as MapIcon, MapPin, Globe2, Flag,
    CheckCheck, Eraser, Tags, Radio, FileText, Users, Zap, Sparkles
} from 'lucide-vue-next';

const props = defineProps<{ show: boolean }>();
const emit = defineEmits<{
    close: [];
    save: [prefs: any];
}>();

const LS_KEY = "linkup_c360_prefs_v1";

const CATEGORIES = [
    "Breaking News", "Politics", "Business & Economy", "Entertainment", "Sports", "Culture", "Technology", "Faith & Society"
];

const CARIBBEAN_COUNTRIES = [
    { code: "JM", name: "Jamaica", region: "Caribbean" },
    { code: "BS", name: "Bahamas", region: "Caribbean" },
    { code: "TT", name: "Trinidad & Tobago", region: "Caribbean" },
    { code: "BB", name: "Barbados", region: "Caribbean" },
    { code: "HT", name: "Haiti", region: "Caribbean" },
    { code: "DO", name: "Dominican Republic", region: "Caribbean" },
    { code: "PR", name: "Puerto Rico", region: "Caribbean" },
    { code: "AG", name: "Antigua & Barbuda", region: "Caribbean" },
    { code: "GD", name: "Grenada", region: "Caribbean" },
    { code: "LC", name: "Saint Lucia", region: "Caribbean" },
    { code: "VC", name: "St. Vincent & the Grenadines", region: "Caribbean" },
    { code: "KN", name: "St. Kitts & Nevis", region: "Caribbean" },
    { code: "GY", name: "Guyana", region: "Caribbean" },
    { code: "SR", name: "Suriname", region: "Caribbean" }
];

const LATAM_COUNTRIES = [
    { code: "BR", name: "Brazil", region: "Latin America" },
    { code: "CO", name: "Colombia", region: "Latin America" },
    { code: "MX", name: "Mexico", region: "Latin America" },
    { code: "AR", name: "Argentina", region: "Latin America" },
    { code: "CL", name: "Chile", region: "Latin America" },
    { code: "PE", name: "Peru", region: "Latin America" },
    { code: "VE", name: "Venezuela", region: "Latin America" },
    { code: "PA", name: "Panama", region: "Latin America" },
    { code: "CR", name: "Costa Rica", region: "Latin America" }
];

const GLOBAL_TAG = { code: "GLB", name: "Global", region: "Global" };
const ALL_COUNTRIES = [...CARIBBEAN_COUNTRIES, ...LATAM_COUNTRIES, GLOBAL_TAG];

const DEFAULT_PREFS = {
    regions: { caribbean: true, latam: true, global: false },
    mode: "personalized",
    countries: [...CARIBBEAN_COUNTRIES.map(c => c.code), ...LATAM_COUNTRIES.map(c => c.code)],
    categories: [...CATEGORIES],
    sources: { rss: true, internal: true, community: false }
};

const prefs = ref(structuredClone(DEFAULT_PREFS));

const FLAG = (code: string) => {
    const map: Record<string, string> = {
        JM: "🇯🇲", BS: "🇧🇸", TT: "🇹🇹", BB: "🇧🇧", HT: "🇭🇹", DO: "🇩🇴", PR: "🇵🇷",
        AG: "🇦🇬", GD: "🇬🇩", LC: "🇱🇨", VC: "🇻🇨", KN: "🇰🇳", GY: "🇬🇾", SR: "🇸🇷",
        BR: "🇧🇷", CO: "🇨🇴", MX: "🇲🇽", AR: "🇦🇷", CL: "🇨🇱", PE: "🇵🇪", VE: "🇻🇪",
        PA: "🇵🇦", CR: "🇨🇷", GLB: "🌍"
    };
    return map[code] || "🏳️";
};

const shortName = (name: string) => {
    return name
        .replace("Trinidad & Tobago", "Trinidad")
        .replace("Dominican Republic", "Dominican")
        .replace("Antigua & Barbuda", "Antigua")
        .replace("St. Vincent & the Grenadines", "St. Vincent")
        .replace("Saint Lucia", "St. Lucia");
};

const shortCat = (name: string) => {
    return name
        .replace("Business & Economy", "Business")
        .replace("Faith & Society", "Faith");
};

const loadPrefs = () => {
    try {
        const raw = localStorage.getItem(LS_KEY);
        if (!raw) return structuredClone(DEFAULT_PREFS);
        const obj = JSON.parse(raw);
        return {
            regions: { ...DEFAULT_PREFS.regions, ...(obj.regions || {}) },
            mode: obj.mode || DEFAULT_PREFS.mode,
            countries: Array.isArray(obj.countries) ? obj.countries : [...DEFAULT_PREFS.countries],
            categories: Array.isArray(obj.categories) ? obj.categories : [...DEFAULT_PREFS.categories],
            sources: { ...DEFAULT_PREFS.sources, ...(obj.sources || {}) }
        };
    } catch (e) {
        return structuredClone(DEFAULT_PREFS);
    }
};

onMounted(() => {
    prefs.value = loadPrefs();
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        prefs.value = loadPrefs();
    }
});

const savePrefs = () => {
    localStorage.setItem(LS_KEY, JSON.stringify(prefs.value));
    emit('save', prefs.value);
    emit('close');
};

const setDefaults = () => {
    prefs.value = structuredClone(DEFAULT_PREFS);
};

const setOneCountry = () => {
    prefs.value.regions = { caribbean: true, latam: false, global: false };
    prefs.value.countries = ["BS"];
    prefs.value.categories = [...CATEGORIES];
    prefs.value.sources = { rss: true, internal: true, community: false };
    prefs.value.mode = "country-first";
};

const toggleAllCaribbean = () => {
    const codes = CARIBBEAN_COUNTRIES.map(c => c.code);
    prefs.value.countries = [...new Set([...prefs.value.countries, ...codes])];
};

const toggleAllLatam = () => {
    const codes = LATAM_COUNTRIES.map(c => c.code);
    prefs.value.countries = [...new Set([...prefs.value.countries, ...codes])];
};

const clearCountries = () => {
    prefs.value.countries = [];
};

const selectAllCats = () => {
    prefs.value.categories = [...CATEGORIES];
};

const clearCats = () => {
    prefs.value.categories = [];
};

const toggleCountry = (code: string) => {
    const idx = prefs.value.countries.indexOf(code);
    if (idx >= 0) {
        prefs.value.countries.splice(idx, 1);
    } else {
        prefs.value.countries.push(code);
    }
};

const toggleCategory = (cat: string) => {
    const idx = prefs.value.categories.indexOf(cat);
    if (idx >= 0) {
        prefs.value.categories.splice(idx, 1);
    } else {
        prefs.value.categories.push(cat);
    }
};

const closePreferencesModal = () => {
    emit('close');
}
</script>

<template>
    <div id="prefsModalBack" class="modalBack" v-if="props.show" :class="{ show: props.show }"
        @click.self="closePreferencesModal">
        <div class="modal">
            <div class="modalHeader">
                <div>
                    <div class="font-black text-[1.02rem]">Feed Preferences</div>
                    <div class="muted font-extrabold text-[.85rem]">Choose countries, categories, and sources</div>
                </div>
                <div class="flex gap-2">
                    <button id="btnPrefsSave" class="btn btnPrimary" @click="savePrefs">
                        <Save :size="18" />
                        Save
                    </button>
                    <button id="btnPrefsClose" class="btn" @click="closePreferencesModal">
                        <X :size="18" />
                    </button>
                </div>
            </div>

            <div class="modalBody">
                <div class="grid grid-cols-12 gap-4">
                    <!-- Regions -->
                    <div class="col-span-12 lg:col-span-4 card p-4">
                        <div class="flex items-center justify-between">
                            <div class="font-black">Regions</div>
                            <div class="pill">
                                <Layers :size="14" /> Scope
                            </div>
                        </div>
                        <div class="mt-3 space-y-2 text-[.92rem] font-extrabold">
                            <label class="flex items-center justify-between gap-2 cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <MapIcon :size="16" /> Caribbean
                                </span>
                                <input v-model="prefs.regions.caribbean" type="checkbox"
                                    class="accent-sky-500 w-5 h-5 focus:ring-0" />
                            </label>
                            <label class="flex items-center justify-between gap-2 cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <MapPin :size="16" /> Latin America
                                </span>
                                <input v-model="prefs.regions.latam" type="checkbox"
                                    class="accent-sky-500 w-5 h-5 focus:ring-0" />
                            </label>
                            <label class="flex items-center justify-between gap-2 cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <Globe2 :size="16" /> Global 360
                                </span>
                                <input v-model="prefs.regions.global" type="checkbox"
                                    class="accent-sky-500 w-5 h-5 focus:ring-0" />
                            </label>
                        </div>

                        <div class="mt-4 card p-4"
                            style="background:linear-gradient(135deg, rgba(14,165,233,.10), rgba(34,197,94,.06));">
                            <div class="font-black">Personalization Mode</div>
                            <div class="mt-2 text-[.85rem] font-extrabold muted">
                                Choose how strict filtering should be.
                            </div>
                            <select v-model="prefs.mode" id="modeSelect" class="select mt-3">
                                <option value="personalized">Personalized (recommended)</option>
                                <option value="country-first">Country-first (strict)</option>
                                <option value="broad">Broad (more discovery)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Countries -->
                    <div class="col-span-12 lg:col-span-4 card p-4">
                        <div class="flex items-center justify-between">
                            <div class="font-black">Countries</div>
                            <div class="pill">
                                <Flag :size="14" /> Choose
                            </div>
                        </div>
                        <div class="mt-2 text-[.85rem] font-extrabold muted">
                            Select what you want to see.
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <button id="btnAllCaribbean" class="btn btnSoft flex-1" @click="toggleAllCaribbean"
                                title="Select all Caribbean">
                                <CheckCheck :size="16" /> Carib.
                            </button>
                            <button id="btnAllLatam" class="btn btnSoft flex-1" @click="toggleAllLatam"
                                title="Select all Latin America">
                                <CheckCheck :size="16" /> LatAm.
                            </button>
                            <button id="btnClearCountries" class="btn flex-1" @click="clearCountries">
                                <Eraser :size="16" /> Clear
                            </button>
                        </div>

                        <div id="countryList"
                            class="mt-3 grid grid-cols-2 gap-2 text-[.9rem] font-extrabold overflow-y-auto max-h-[400px] pr-1">
                            <label v-for="c in [...CARIBBEAN_COUNTRIES, ...LATAM_COUNTRIES]" :key="c.code"
                                class="card p-2 flex items-center justify-between gap-2 cursor-pointer hover:bg-slate-50 transition-colors"
                                style="box-shadow:none;">
                                <span class="flex items-center gap-2">
                                    <span class="text-[1.05rem]">{{ FLAG(c.code) }}</span>
                                    <span class="font-black">{{ shortName(c.name) }}</span>
                                </span>
                                <input type="checkbox" :value="c.code" :checked="prefs.countries.includes(c.code)"
                                    @change="toggleCountry(c.code)" class="accent-sky-500 w-5 h-5 focus:ring-0" />
                            </label>
                        </div>
                    </div>

                    <!-- Categories + Sources -->
                    <div class="col-span-12 lg:col-span-4 space-y-4">
                        <div class="card p-4">
                            <div class="flex items-center justify-between">
                                <div class="font-black">Categories</div>
                                <div class="pill">
                                    <Tags :size="14" /> Filter
                                </div>
                            </div>

                            <div class="mt-3 flex gap-2">
                                <button id="btnCatsAll" class="btn btnSoft w-full" @click="selectAllCats">
                                    <CheckCheck :size="16" />
                                    Select All
                                </button>
                                <button id="btnCatsClear" class="btn w-full" @click="clearCats">
                                    <Eraser :size="16" /> Clear
                                </button>
                            </div>

                            <div id="catList" class="mt-3 grid grid-cols-2 gap-2 text-[.9rem] font-extrabold">
                                <label v-for="cat in CATEGORIES" :key="cat"
                                    class="card p-2 flex items-center justify-between gap-2 cursor-pointer hover:bg-slate-50 transition-colors"
                                    style="box-shadow:none;">
                                    <span class="font-black">{{ shortCat(cat) }}</span>
                                    <input type="checkbox" :value="cat" :checked="prefs.categories.includes(cat)"
                                        @change="toggleCategory(cat)" class="accent-sky-500 w-5 h-5 focus:ring-0" />
                                </label>
                            </div>
                        </div>

                        <div class="card p-4">
                            <div class="flex items-center justify-between">
                                <div class="font-black">Source Types</div>
                                <div class="pill">
                                    <Radio :size="14" /> Inputs
                                </div>
                            </div>
                            <div class="mt-3 space-y-2 text-[.92rem] font-extrabold">
                                <label class="flex items-center justify-between gap-2 cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <Radio :size="16" /> Feeds
                                        (Simulated)
                                    </span>
                                    <input v-model="prefs.sources.rss" type="checkbox"
                                        class="accent-sky-500 w-5 h-5 focus:ring-0" />
                                </label>
                                <label class="flex items-center justify-between gap-2 cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <FileText :size="16" /> Internal
                                        Uploads
                                    </span>
                                    <input v-model="prefs.sources.internal" type="checkbox"
                                        class="accent-sky-500 w-5 h-5 focus:ring-0" />
                                </label>
                                <label class="flex items-center justify-between gap-2 opacity-50 cursor-not-allowed">
                                    <span class="flex items-center gap-2">
                                        <Users :size="16" /> Community (Phase
                                        2)
                                    </span>
                                    <input v-model="prefs.sources.community" type="checkbox"
                                        class="accent-sky-500 w-5 h-5 focus:ring-0" disabled />
                                </label>
                            </div>

                            <div class="mt-4 card p-4"
                                style="background:linear-gradient(135deg, rgba(239,68,68,.10), rgba(14,165,233,.08));">
                                <div class="flex items-center justify-between">
                                    <div class="font-black">Breaking News Strip</div>
                                    <div class="pill">
                                        <Zap :size="14" /> Always On
                                    </div>
                                </div>
                                <div class="mt-2 text-[.85rem] font-extrabold muted">
                                    Breaking stories show regardless of category filters.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 card p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <div class="font-black">Developer Note</div>
                            <div class="muted font-extrabold text-[.85rem]">
                                Production feed = backend fetch + moderation + caching + country/category tagging.
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button id="btnPrefsDefaults" class="btn" @click="setDefaults">
                                <Sparkles :size="16" /> Defaults
                            </button>
                            <button id="btnPrefsOneCountry" class="btn btnPrimary" @click="setOneCountry">
                                <Flag :size="16" /> One
                                Country
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 12px 26px rgba(2, 6, 23, .08);
    border: 1px solid rgba(148, 163, 184, .35);
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

.modalBack {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: rgba(2, 6, 23, .55);
    display: none;
    align-items: flex-end;
    justify-content: center;
    padding: 18px;
}

.modalBack.show {
    display: flex;
}

.modal {
    width: min(980px, 100%);
    background: #fff;
    border-radius: 26px;
    border: 1px solid rgba(148, 163, 184, .35);
    box-shadow: 0 30px 80px rgba(2, 6, 23, .35);
    overflow: hidden;
    max-height: 86vh;
    display: flex;
    flex-direction: column;
}

.modalHeader {
    padding: 14px 16px;
    border-bottom: 1px solid rgba(148, 163, 184, .35);
    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, rgba(14, 165, 233, .10), rgba(34, 197, 94, .06));
}

.modalBody {
    padding: 14px 16px;
    overflow: auto;
}

.select {
    width: 100%;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 16px;
    padding: .75rem .85rem;
    outline: none;
    font-weight: 900;
    background: #fff;
}

.muted {
    color: #64748b;
}

/* Custom scrollbar for country list */
#countryList::-webkit-scrollbar {
    width: 6px;
}

#countryList::-webkit-scrollbar-track {
    background: transparent;
}

#countryList::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, .3);
    border-radius: 10px;
}

#countryList::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, .5);
}
</style>