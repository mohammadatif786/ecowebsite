<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { ArrowLeftIcon } from 'lucide-vue-next';
import './style.css';
import axios from 'axios';
import { toast } from 'vue-sonner';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps<{
    all_Streams: any,
    live_stream_categories: any,
}>();

const liveStreams = ref(props.all_Streams || []);

watch(() => props.all_Streams, (newVal) => {
    if (newVal) {
        liveStreams.value = newVal;
    }
});

const liveCategories = computed(() => {
    const rows = props.live_stream_categories || []
    return rows
        .map((r: any) => String(r?.category || '').trim())
        .filter((v: string) => Boolean(v))
})

const networkTopics = computed(() => {
    const cats = liveCategories.value.filter((c: string) => c !== 'Private')
    return ['All', 'Private', ...cats]
})

const activeNetworkTopic = ref<string>('All')

watch(networkTopics, (topics) => {
    if (!topics?.length) {
        activeNetworkTopic.value = 'All'
        return
    }
    if (!topics.includes(activeNetworkTopic.value)) {
        activeNetworkTopic.value = 'All'
    }
}, { immediate: true })

const filteredLiveStreams = computed(() => {
    const topic = activeNetworkTopic.value
    if (topic === 'All') return liveStreams.value || []
    if (topic === 'Private') {
        return (liveStreams.value || []).filter((s: any) => String(s?.visibility) === 'private')
    }
    return (liveStreams.value || []).filter((s: any) => {
        const t = String(s?.broadcast_type || s?.type || '')
        return t === topic
    })
})

const fetchLiveStreamDetail = (stream: any) => {
    window.location.href = route('frontend.go-live.show', { go_live: stream.id });
}

const handleUnsubscribe = async (stream: any) => {
    if (!confirm(`Unsubscribe from ${stream.user?.name || 'this creator'}?`)) return;

    try {
        const response = await axios.post(route('frontend.live.unsubscribe'), {
            user_streamer_id: stream.user_id
        });

        if (response.data.success) {
            toast.success("Unsubscribed successfully");
            // Refresh logic handled by router.reload in interval or manually
            refreshNetworkStats();
        } else {
            toast.error(response.data.message || "Failed to unsubscribe");
        }
    } catch (error: any) {
        toast.error(error.response?.data?.message || "Error unsubscribing");
    }
}

const goBack = () => {
    window.history.back();
}

// --- Real-time Logic ---

let networkStatsInterval: any = null;

const refreshNetworkStats = () => {
    router.reload({
        only: ['all_Streams'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            // Watcher on props.all_Streams will update liveStreams
        }
    });
}

const handleStreamStartedEvent = (streamData: any) => {
    const streamId = streamData.id
    if (!streamId) return

    const existingIndex = liveStreams.value.findIndex((s: any) => String(s.id) === String(streamId))

    if (existingIndex === -1) {
        // Compute is_subscribed for new private streams (basic check)
        if (String(streamData.visibility) === 'private' && user.value?.id && String(streamData.user_id) !== String(user.value.id)) {

            const monthlySub = liveStreams.value.find((s: any) =>
                String(s.visibility) === 'private' &&
                String(s.user_id) === String(streamData.user_id) &&
                s.is_subscribed === true
            )
            streamData.is_subscribed = !!monthlySub;
        } else {
            streamData.is_subscribed = true // public or host
        }
        liveStreams.value.unshift(streamData)
    } else {
        liveStreams.value[existingIndex] = { ...liveStreams.value[existingIndex], ...streamData }
    }
}

const setupEchoSubscription = (retryCount = 0) => {
    const maxRetries = 20
    if (!window.Echo) {
        if (retryCount < maxRetries) {
            setTimeout(() => setupEchoSubscription(retryCount + 1), 500)
        }
        return
    }

    try {
        const channel = window.Echo.channel('live-streams')

        channel.listen('.StreamStarted', (e: any) => {
            handleStreamStartedEvent(e)
        })
            .listen('.StreamEnded', (e: any) => {
                const streamId = e.id
                if (streamId) {
                    liveStreams.value = liveStreams.value.filter((s: any) => String(s.id) !== String(streamId))
                }
            })
            .listen('.ViewerCountUpdated', (e: any) => {
                const streamIndex = liveStreams.value.findIndex((s: any) => String(s.id) === String(e.stream_id))
                if (streamIndex !== -1) {
                    liveStreams.value[streamIndex].viewer_count = e.viewer_count || 0
                }
            })
            .listen('.LiveReactionSent', (e: any) => {
                const streamIndex = liveStreams.value.findIndex((s: any) => String(s.id) === String(e.stream_id))
                if (streamIndex !== -1) {
                    liveStreams.value[streamIndex].like_count = e.like_count || 0
                }
            })
            .listen('.LiveGiftSent', (e: any) => {
                const streamIndex = liveStreams.value.findIndex((s: any) => String(s.id) === String(e.stream_id))
                if (streamIndex !== -1) {
                    liveStreams.value[streamIndex].gift_count = e.gift_count || 0
                }
            });

        console.log('[GoLiveNetwork] Subscribed to live-streams');
    } catch (e) {
        console.error('[GoLiveNetwork] Echo error:', e);
    }
}

onMounted(() => {
    // Initial fetch to be sure (already have props but good to be fresh)
    // refreshNetworkStats(); // disable to avoid double load on mount

    setupEchoSubscription();

    // Poll every 10 seconds as backup
    networkStatsInterval = setInterval(refreshNetworkStats, 10000);
})

onUnmounted(() => {
    if (networkStatsInterval) clearInterval(networkStatsInterval);
    if (window.Echo) {
        window.Echo.leave('live-streams');
    }
})

</script>

<template>
    <div class="network-page-container">
        <!-- Reusing network-modal styles but as a full page -->
        <div class="network-modal active"
            style="position:relative; inset:0; z-index:1; background: #000; height: 100vh; width: 100%;">
            <div class="network-shell"
                style="position: absolute; inset: 0; border-radius: 0; width: 100%; height: 100%; max-width: none; border: none;">
                <div class="network-topbar">
                    <div class="network-topbar-left">
                        <button class="back-btn" @click="goBack" style="margin-right:15px;">
                            <ArrowLeftIcon class="w-6 h-6" />
                        </button>
                        <span class="network-dot" aria-hidden="true"></span>
                        <span>Live Network</span>
                    </div>
                </div>

                <div class="network-topics-wrap">
                    <div class="network-topics">
                        <button v-for="topic in networkTopics" :key="topic" class="network-topic-btn"
                            :class="{ active: topic === activeNetworkTopic }" @click="activeNetworkTopic = topic">
                            {{ topic }}
                        </button>
                    </div>
                </div>

                <div class="network-content">
                    <div v-if="!filteredLiveStreams.length" class="network-empty">
                        No live streams in <b>{{ activeNetworkTopic }}</b> right now.
                    </div>

                    <div v-else class="network-grid">
                        <article v-for="stream in filteredLiveStreams" :key="stream.id" class="network-live-card"
                            @click="fetchLiveStreamDetail(stream)">
                            <div class="network-thumb-wrap">
                                <span class="network-live-badge">LIVE</span>

                                <span v-if="stream.is_subscribed && String(stream?.visibility) === 'private'" :style="{
                                    position: 'absolute',
                                    top: '10px',
                                    right: '10px',
                                    padding: '8px 10px',
                                    borderRadius: '12px',
                                    background: 'rgba(34, 197, 94, 0.85)',
                                    border: '1px solid rgba(255,255,255,0.22)',
                                    backdropFilter: 'blur(10px)',
                                    color: '#fff',
                                    fontWeight: 900,
                                    fontSize: '12px',
                                    letterSpacing: '.06em',
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: '8px',
                                    boxShadow: '0 12px 26px rgba(0,0,0,.18)'
                                }">
                                    ✅ SUBSCRIBED
                                    <button @click.stop="handleUnsubscribe(stream)" :style="{
                                        background: 'rgba(255,255,255,0.2)',
                                        border: '1px solid rgba(255,255,255,0.3)',
                                        borderRadius: '6px',
                                        padding: '2px 6px',
                                        fontSize: '10px',
                                        cursor: 'pointer',
                                        marginLeft: '4px'
                                    }" title="Unsubscribe from this creator">
                                        ✕
                                    </button>
                                </span>

                                <span v-else-if="String(stream?.visibility) === 'private'" :style="{
                                    position: 'absolute',
                                    top: '10px',
                                    right: '10px',
                                    padding: '8px 10px',
                                    borderRadius: '12px',
                                    background: 'rgba(255,255,255,0.10)',
                                    border: '1px solid rgba(255,255,255,0.22)',
                                    backdropFilter: 'blur(10px)',
                                    color: '#eaf2ff',
                                    fontWeight: 900,
                                    fontSize: '12px',
                                    letterSpacing: '.06em',
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: '8px',
                                    boxShadow: '0 12px 26px rgba(0,0,0,.18)'
                                }">
                                    🔒 PRIVATE
                                    <b :style="{ color: '#dfff00', textShadow: '0 0 10px rgba(223,255,0,.18)' }">
                                        {{ `$${Number(parseFloat(stream?.subscription_rate ?? 0) || 0).toFixed(2)}`
                                        }}
                                    </b>
                                </span>
                                <img class="network-thumb" :src="stream?.image_url" loading="lazy"
                                    :alt="(stream.title || 'Live Stream') + ' thumbnail'" />
                            </div>
                            <div class="network-card-body">
                                <div class="network-title">{{ stream.title || 'Live Stream' }}</div>
                                <div class="network-sub">{{ stream.broadcast_type || 'Entertainment' }}</div>
                                <div class="network-stats">
                                    <div class="network-stat"><span class="network-icon">👤</span><span
                                            class="network-num">{{ stream.viewer_count || 0 }}</span></div>
                                    <div class="network-stat"><span class="network-icon">❤️</span><span
                                            class="network-num">{{ stream.like_count || 0 }}</span></div>
                                    <div class="network-stat"><span class="network-icon">🎁</span><span
                                            class="network-num">{{ stream.gift_count || 0 }}</span></div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ensure the page takes full height and dark background */
.network-page-container {
    min-height: 100vh;
    background: #000;
    width: 100%;
}
</style>