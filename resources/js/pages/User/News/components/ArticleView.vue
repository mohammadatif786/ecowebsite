<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
    Bookmark, BookmarkCheck, X, ChevronLeft, ChevronRight,
    GalleryVerticalEnd, Paperclip, FileText, Clock, Flame, Radio
} from 'lucide-vue-next';

interface Story {
    id: string;
    title: string;
    summary: string;
    body: string;
    country: string;
    countryCode: string;
    region: string;
    category: string;
    sourceType: string;
    sourceName: string;
    publishedAt: string;
    trending: number;
    isBreaking: boolean;
    media: any[];
}

const props = defineProps<{
    show: boolean;
    story: Story | null;
    isSaved: boolean;
    hasPrev: boolean;
    hasNext: boolean;
}>();

const emit = defineEmits<{
    close: [];
    toggleSave: [id: string];
    prev: [];
    next: [];
}>();

const mediaIndex = ref(0);

const currentMedia = computed(() => {
    if (!props.story || !props.story.media?.length) return null;
    return props.story.media[mediaIndex.value];
});

watch(() => props.story, () => {
    mediaIndex.value = 0;
});

const nextMedia = () => {
    if (!props.story?.media?.length) return;
    mediaIndex.value = (mediaIndex.value + 1) % props.story.media.length;
};

const prevMedia = () => {
    if (!props.story?.media?.length) return;
    mediaIndex.value = (mediaIndex.value - 1 + props.story.media.length) % props.story.media.length;
};

const FLAG = (code: string) => {
    const map: Record<string, string> = {
        JM: "🇯🇲", BS: "🇧🇸", TT: "🇹🇹", BB: "🇧🇧", HT: "🇭🇹", DO: "🇩🇴", PR: "🇵🇷",
        AG: "🇦🇬", GD: "🇬🇩", LC: "🇱🇨", VC: "🇻🇨", KN: "🇰🇳", GY: "🇬🇾", SR: "🇸🇷",
        BR: "🇧🇷", CO: "🇨🇴", MX: "🇲🇽", AR: "🇦🇷", CL: "🇨🇱", PE: "🇵🇪", VE: "🇻🇪",
        PA: "🇵🇦", CR: "🇨🇷", GLB: "🌍"
    };
    return map[code] || "🏳️";
};

const timeAgo = (dateStr: string) => {
    const date = new Date(dateStr);
    const s = Math.floor((Date.now() - date.getTime()) / 1000);
    if (s < 60) return "Just now";
    const m = Math.floor(s / 60);
    if (m < 60) return `${m}m ago`;
    const h = Math.floor(m / 60);
    if (h < 24) return `${h}h ago`;
    const d = Math.floor(h / 24);
    return `${d}d ago`;
};

const escapeHtml = (str: string) => {
    return String(str ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
};

const formatBody = (text: string) => {
    if (!text) return "";
    const safe = escapeHtml(text);
    const lines = safe.split("\n");
    let out = "";
    let inList = false;

    lines.forEach(line => {
        const t = line.trim();
        if (!t) {
            if (inList) { out += "</ul>"; inList = false; }
            out += `<div class="h-3"></div>`;
            return;
        }
        if (t.startsWith("•") || t.startsWith("-") || t.startsWith("*")) {
            if (!inList) { out += `<ul class="list-disc pl-6 space-y-1 mb-4">`; inList = true; }
            out += `<li>${t.slice(1).trim()}</li>`;
        } else {
            if (inList) { out += "</ul>"; inList = false; }
            out += `<p class="mb-4 text-[#0b1220] font-semibold text-[.98rem] leading-relaxed">${t}</p>`;
        }
    });

    if (inList) out += "</ul>";
    return out;
};

const prettyBytes = (bytes: number) => {
    if (!bytes) return "0 B";
    const units = ["B", "KB", "MB", "GB"];
    let v = bytes, i = 0;
    while (v >= 1024 && i < units.length - 1) { v /= 1024; i++; }
    return `${v.toFixed(v >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
};
</script>

<template>
    <div class="modalBack" :class="{ show: props.show }" @click.self="emit('close')">
        <div v-if="story" class="modal">
            <div class="modalHeader">
                <div class="flex items-center gap-2">
                    <div class="pill">
                        {{ FLAG(story.countryCode) }} {{ story.countryCode === 'GLB' ? story.region : story.country }} •
                        {{ story.category }}
                    </div>
                    <div class="text-[.9rem] font-black text-slate-700">
                        {{ story.sourceName || (story.sourceType === 'internal' ? 'LinkUp Desk' : 'Feed') }}
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="emit('toggleSave', story.id)" class="btn">
                        <BookmarkCheck v-if="isSaved" :size="18" class="text-sky-500" />
                        <Bookmark v-else :size="18" />
                        {{ isSaved ? 'Saved' : 'Save' }}
                    </button>
                    <button @click="emit('close')" class="btn">
                        <X :size="18" />
                    </button>
                </div>
            </div>
            <div class="modalBody">
                <div class="text-[1.35rem] font-black leading-tight">{{ story.title }}</div>

                <div class="mt-2 flex flex-wrap gap-2 items-center">
                    <span class="pill">
                        <Clock :size="12" /> {{ timeAgo(story.publishedAt) }}
                    </span>
                    <span class="pill">
                        <Flame :size="12" /> {{ story.trending }} Trending
                    </span>
                    <span class="pill" style="border-color:rgba(14,165,233,.35); background:rgba(14,165,233,.07);">
                        <FileText v-if="story.sourceType === 'internal'" :size="12" />
                        <Radio v-else :size="12" />
                        {{ story.sourceType === 'internal' ? 'Internal' : 'Feed' }}
                    </span>
                    <span v-if="story.media?.length" class="pill"
                        style="border-color:rgba(14,165,233,.35); background:rgba(14,165,233,.07);">
                        <Paperclip :size="12" /> {{ story.media.length }} media
                    </span>
                    <span v-if="story.isBreaking" class="pill"
                        style="border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.07);">
                        <div class="dotPulse"></div> Breaking
                    </span>
                </div>

                <div class="mt-3 font-extrabold muted">{{ story.summary }}</div>

                <!-- Media Panel -->
                <div v-if="story.media?.length" class="mt-4 mediaTile p-4">
                    <div class="mediaBar">
                        <div class="font-black flex items-center gap-2">
                            <GalleryVerticalEnd :size="18" /> Media
                            <span class="pill">
                                <Paperclip :size="14" /> Attached
                            </span>
                        </div>
                        <div v-if="story.media.length > 1" class="flex items-center gap-2">
                            <button @click="prevMedia" class="miniBtn">
                                <ChevronLeft :size="16" /> Prev
                            </button>
                            <button @click="nextMedia" class="miniBtn">Next
                                <ChevronRight :size="16" />
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div
                            class="mediaStage aspect-video bg-black/5 rounded-2xl overflow-hidden flex items-center justify-center">
                            <template v-if="currentMedia">
                                <template v-if="currentMedia.type === 'image'">
                                    <img :src="currentMedia.dataUrl" class="max-h-full max-w-full object-contain" />
                                </template>
                                <template v-else-if="currentMedia.type === 'video'">
                                    <video :src="currentMedia.dataUrl" controls class="max-h-full max-w-full"></video>
                                </template>
                                <template v-else-if="currentMedia.type === 'audio'">
                                    <div class="p-8 text-center w-full">
                                        <div class="font-black flex items-center justify-center gap-2 mb-4">
                                            <Radio :size="32" class="text-sky-500" />
                                        </div>
                                        <audio :src="currentMedia.dataUrl" controls class="w-full"></audio>
                                    </div>
                                </template>
                            </template>
                        </div>
                        <div v-if="currentMedia" class="mt-2 mediaNote">
                            {{ currentMedia.type.toUpperCase() }} • {{ currentMedia.name || 'Attachment' }} • {{
                                prettyBytes(currentMedia.size
                                    || 0) }} • {{ mediaIndex + 1 }}/{{ story.media.length }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 card p-4">
                    <div class="flex items-center justify-between">
                        <div class="font-black">Full Story</div>
                        <div class="pill">
                            <FileText :size="14" /> Content
                        </div>
                    </div>
                    <div class="mt-3" v-html="formatBody(story.body)"></div>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row gap-2">
                    <button @click="emit('prev')" :disabled="!hasPrev" class="btn w-full"
                        :class="{ 'opacity-50 cursor-not-allowed': !hasPrev }">
                        <ChevronLeft :size="18" /> Previous
                    </button>
                    <button @click="emit('next')" :disabled="!hasNext" class="btn btnPrimary w-full"
                        :class="{ 'opacity-50 cursor-not-allowed': !hasNext }">
                        Next
                        <ChevronRight :size="18" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modalBack {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: rgba(2, 6, 23, .55);
    display: none;
    align-items: flex-end;
    justify-content: center;
    padding: 18px;
    backdrop-filter: blur(4px);
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
    padding: 24px;
    overflow-y: auto;
}

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

.btn:hover:not(:disabled) {
    transform: translateY(-1px);
    filter: brightness(1.02);
}

.btnPrimary {
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
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

.miniBtn {
    padding: .55rem .75rem;
    border-radius: 14px;
    font-weight: 900;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    display: inline-flex;
    gap: .5rem;
    align-items: center;
}

.mediaTile {
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .35);
    overflow: hidden;
    background: linear-gradient(135deg, rgba(14, 165, 233, .10), rgba(34, 197, 94, .06));
}

.mediaBar {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}

.mediaNote {
    font-size: .82rem;
    font-weight: 800;
    color: #64748b;
    text-align: center;
}

.muted {
    color: #64748b;
}

.dotPulse {
    width: 8px;
    height: 8px;
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
</style>