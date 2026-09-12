<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Send, X, Paperclip, Info, Eraser, Trash2,
    Image as ImageIcon, Video, AudioLines
} from 'lucide-vue-next';

const props = defineProps<{ show: boolean }>();
const emit = defineEmits<{
    close: [];
    published: [];
}>();

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

const FLAG = (code: string) => {
    const map: Record<string, string> = {
        JM: "🇯🇲", BS: "🇧🇸", TT: "🇹🇹", BB: "🇧🇧", HT: "🇭🇹", DO: "🇩🇴", PR: "🇵🇷",
        AG: "🇦🇬", GD: "🇬🇩", LC: "🇱🇨", VC: "🇻🇨", KN: "🇰🇳", GY: "🇬🇾", SR: "🇸🇷",
        BR: "🇧🇷", CO: "🇨🇴", MX: "🇲🇽", AR: "🇦🇷", CL: "🇨🇱", PE: "🇵🇪", VE: "🇻🇪",
        PA: "🇵🇦", CR: "🇨🇷", GLB: "🌍"
    };
    return map[code] || "🏳️";
};

const title = ref('');
const summary = ref('');
const body = ref('');
const region = ref('Caribbean');
const countryCode = ref('JM');
const category = ref('Politics');
const isBreaking = ref(false);
const expiryHrs = ref(8);
const sourceName = ref('LinkUp Caribbean 360 Desk');
const admMedia = ref<any[]>([]);

const countriesOptions = ref<any[]>([]);

const updateCountryOptions = () => {
    if (region.value === 'Caribbean') {
        countriesOptions.value = CARIBBEAN_COUNTRIES;
    } else if (region.value === 'Latin America') {
        countriesOptions.value = LATAM_COUNTRIES;
    } else {
        countriesOptions.value = [GLOBAL_TAG];
    }
    if (!countriesOptions.value.find(c => c.code === countryCode.value)) {
        countryCode.value = countriesOptions.value[0].code;
    }
};

onMounted(() => {
    updateCountryOptions();
});

watch(region, () => {
    updateCountryOptions();
});

const fileToDataURL = (file: File): Promise<string> => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result as string);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
};

const handleFiles = async (event: Event, type: 'image' | 'video' | 'audio') => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (!files) return;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const dataUrl = await fileToDataURL(file);
        admMedia.value.push({
            type,
            name: file.name,
            size: file.size,
            dataUrl,
            file: file
        });
    }
    target.value = '';
};

const removeMedia = (index: number) => {
    admMedia.value.splice(index, 1);
};

const prettyBytes = (bytes: number) => {
    if (!bytes) return "0 B";
    const units = ["B", "KB", "MB", "GB"];
    let v = bytes, i = 0;
    while (v >= 1024 && i < units.length - 1) { v /= 1024; i++; }
    return `${v.toFixed(v >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
};

const clearForm = () => {
    title.value = '';
    summary.value = '';
    body.value = '';
    isBreaking.value = false;
    expiryHrs.value = 8;
    admMedia.value = [];
};

const baseBody = (countryName: string) => (
    `Today’s developments are drawing attention across the region. Observers say the situation will likely impact public confidence, consumer behavior, and regional relationships.\n\n` +
    `What it means for ${countryName}:\n` +
    `• Officials are expected to provide more details within 24–48 hours.\n` +
    `• Businesses are watching for policy signals and market shifts.\n` +
    `• Community leaders are urging calm and clarity as new information emerges.\n\n` +
    `LinkUp Caribbean 360 will continue tracking this story as updates come in.`
);

const publishStory = () => {
    if (!title.value || !summary.value) {
        alert("Please fill Headline and Summary at minimum.");
        return;
    }

    const selectedCountry = ALL_COUNTRIES.find(c => c.code === countryCode.value);
    const countryName = selectedCountry ? selectedCountry.name : countryCode.value;

    const payload: any = {
        title: title.value,
        summary: summary.value,
        body: body.value || baseBody(countryName),
        country: countryName,
        countryCode: countryCode.value,
        region: region.value,
        category: category.value,
        sourceName: sourceName.value || "LinkUp Desk",
        sourceType: "internal",
        isBreaking: isBreaking.value,
        breakingExpiresAt: isBreaking.value ? new Date(Date.now() + expiryHrs.value * 60 * 60 * 1000).toISOString() : null,
        trending: isBreaking.value ?? 0,
        news_media: admMedia.value.map(m => m.file),
        media_info: JSON.stringify(admMedia.value.map(m => ({
            type: m.type,
            name: m.name
        })))
    };

    router.post(route('frontend.news.store'), payload, {
        onSuccess: () => {
            clearForm();
            emit('published');
            emit('close');
        },
        onError: (err) => {
            console.error(err);
            alert("Error publishing story. Check console.");
        }
    });
};

const deleteInternal = () => {
    if (confirm("Delete ALL news items? (This will delete database records)")) {
        alert("Mass delete not implemented for DB. Delete individually from the feed.");
    }
};

const closeAdminModal = () => {
    emit('close');
}
</script>

<template>
    <div v-if="props.show" id="adminModalBack" class="modalBack" :class="{ show: props.show }"
        @click.self="closeAdminModal">
        <div class="modal">
            <div class="modalHeader">
                <div>
                    <div class="font-black text-[1.02rem]">Post News Update</div>
                    <div class="muted font-extrabold text-[.85rem]">Share regional updates + mark Breaking + add media
                    </div>
                </div>
                <div class="flex gap-2">
                    <button id="btnAdminPublish" @click="publishStory" class="btn btnPrimary">
                        <Send :size="18" /> Publish
                    </button>
                    <button id="btnAdminClose" @click="closeAdminModal" class="btn">
                        <X :size="18" />
                    </button>
                </div>
            </div>
            <div class="modalBody">
                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12 lg:col-span-7 card p-4">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12">
                                <label class="text-[.85rem] font-black muted">Headline</label>
                                <input v-model="title" id="admTitle" class="input mt-1"
                                    placeholder="Write a strong headline..." />
                            </div>
                            <div class="col-span-12">
                                <label class="text-[.85rem] font-black muted">Summary (2–3 lines)</label>
                                <textarea v-model="summary" id="admSummary" class="input mt-1" rows="3"
                                    placeholder="Short summary for the cards..."></textarea>
                            </div>

                            <!-- Media upload inputs -->
                            <div class="col-span-12 card p-4"
                                style="background:linear-gradient(135deg, rgba(14,165,233,.10), rgba(34,197,94,.06));">
                                <div class="flex items-center justify-between">
                                    <div class="font-black flex items-center gap-2">
                                        <Paperclip :size="16" /> Attach Media
                                    </div>
                                    <div class="pill">
                                        <Info :size="14" /> Stored locally
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-12 gap-3">
                                    <div class="col-span-12 sm:col-span-4">
                                        <label class="text-[.85rem] font-black muted">Images</label>
                                        <input @change="handleFiles($event, 'image')" id="admImages" type="file"
                                            accept="image/*" multiple class="input mt-1" />
                                    </div>
                                    <div class="col-span-12 sm:col-span-4">
                                        <label class="text-[.85rem] font-black muted">Videos</label>
                                        <input @change="handleFiles($event, 'video')" id="admVideos" type="file"
                                            accept="video/*" multiple class="input mt-1" />
                                    </div>
                                    <div class="col-span-12 sm:col-span-4">
                                        <label class="text-[.85rem] font-black muted">Audio</label>
                                        <input @change="handleFiles($event, 'audio')" id="admAudio" type="file"
                                            accept="audio/*" multiple class="input mt-1" />
                                    </div>
                                </div>

                                <div id="admMediaPreview" class="mt-3 grid grid-cols-12 gap-2">
                                    <div v-for="(m, idx) in admMedia" :key="idx"
                                        class="col-span-12 sm:col-span-6 lg:col-span-4">
                                        <div class="card p-3 h-full flex flex-col" style="box-shadow:none;">
                                            <div class="flex items-center justify-between">
                                                <div class="pill">
                                                    <ImageIcon v-if="m.type === 'image'" :size="14" />
                                                    <Video v-else-if="m.type === 'video'" :size="14" />
                                                    <AudioLines v-else :size="14" />
                                                    {{ m.type }}
                                                </div>
                                                <button @click="removeMedia(idx)" class="btn"
                                                    style="padding:.45rem .6rem;" title="Remove">
                                                    <X :size="14" />
                                                </button>
                                            </div>
                                            <div class="mt-2 text-center h-32 flex items-center justify-center overflow-hidden rounded-2xl border bg-slate-50"
                                                style="border-color:rgba(148,163,184,.35);">
                                                <img v-if="m.type === 'image'" :src="m.dataUrl"
                                                    class="w-full h-full object-cover" />
                                                <video v-else-if="m.type === 'video'" :src="m.dataUrl" controls
                                                    class="w-full h-full object-cover" muted></video>
                                                <div v-else-if="m.type === 'audio'"
                                                    class="w-full h-full flex flex-col items-center justify-center px-2">
                                                    <AudioLines :size="28" class="text-sky-500 mb-1" />
                                                    <audio :src="m.dataUrl" controls
                                                        class="w-full scale-[0.85]"></audio>
                                                </div>
                                                <div v-else
                                                    class="imgPh w-full h-full flex items-center justify-center font-black">
                                                    {{ m.type.toUpperCase() }}</div>
                                            </div>
                                            <div class="mt-2 text-[.82rem] font-extrabold muted truncate">{{ m.name }}
                                            </div>
                                            <div class="text-[.8rem] font-extrabold muted">{{ prettyBytes(m.size) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-12">
                                <label class="text-[.85rem] font-black muted">Full Story (Optional - uses template if
                                    empty)</label>
                                <textarea v-model="body" id="admBody" class="input mt-1" rows="8"
                                    placeholder="Write the full story"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 lg:col-span-5 card p-4">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12">
                                <label class="text-[.85rem] font-black muted">Region</label>
                                <select v-model="region" id="admRegion" class="select mt-1">
                                    <option value="Caribbean">Caribbean</option>
                                    <option value="Latin America">Latin America</option>
                                    <option value="Global">Global</option>
                                </select>
                            </div>
                            <div class="col-span-12">
                                <label class="text-[.85rem] font-black muted">Country</label>
                                <select v-model="countryCode" id="admCountry" class="select mt-1">
                                    <option v-for="c in countriesOptions" :key="c.code" :value="c.code">
                                        {{ FLAG(c.code) }} {{ c.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-span-12">
                                <label class="text-[.85rem] font-black muted">Category</label>
                                <select v-model="category" id="admCategory" class="select mt-1">
                                    <option v-for="cat in CATEGORIES.filter(c => c !== 'Breaking News')" :key="cat"
                                        :value="cat">
                                        {{ cat }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-span-12 card p-4"
                                style="background:linear-gradient(135deg, rgba(239,68,68,.12), rgba(14,165,233,.06));">
                                <div class="flex items-center justify-between">
                                    <div class="font-black">Mark as Breaking</div>
                                    <input v-model="isBreaking" id="admBreaking" type="checkbox"
                                        class="accent-red-500 w-5 h-5" />
                                </div>
                                <div class="mt-2 text-[.85rem] font-extrabold muted">
                                    Breaking appears in the top strip. You can set an expiry time.
                                </div>
                                <div class="mt-3" v-if="isBreaking">
                                    <label class="text-[.85rem] font-black muted">Breaking expiry (hours)</label>
                                    <input v-model="expiryHrs" id="admExpiryHrs" class="input mt-1" type="number"
                                        min="1" max="72" />
                                </div>
                            </div>

                            <div class="col-span-12">
                                <label class="text-[.85rem] font-black muted">Source Name</label>
                                <input v-model="sourceName" id="admSourceName" class="input mt-1" />
                            </div>

                            <div class="col-span-12 flex gap-2">
                                <button id="btnAdminClear" @click="clearForm" class="btn w-full">
                                    <Eraser :size="16" />
                                    Clear
                                </button>
                                <button id="btnAdminDeleteAllInternal" @click="deleteInternal"
                                    class="btn btnDanger w-full">
                                    <Trash2 :size="16" /> Delete Internal
                                </button>
                            </div>
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

.btnDanger {
    background: linear-gradient(135deg, #ef4444, #fb7185);
    border-color: transparent;
    color: #fff;
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

.imgPh {
    background: linear-gradient(135deg, rgba(14, 165, 233, .16), rgba(34, 197, 94, .14));
    border: 1px dashed rgba(148, 163, 184, .7);
    border-radius: 18px;
}
</style>