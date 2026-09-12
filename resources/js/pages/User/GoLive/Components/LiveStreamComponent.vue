<template>
    <div class="live-stream-container">
        <!-- Main Player Container -->
        <div class="video-player-container" :class="{ hidden: props.isHost && (!camOn || cameraUnavailable) }">
            <!-- Host's Local Video -->
            <div v-if="isHost" id="local-player" class="video-player"></div>
            
            <!-- Host's Remote Video (for audience/guests) -->
            <div v-else id="remote-player" class="video-player">
                <div v-if="!hasRemoteVideo && !isStreamLive" class="waiting-message">
                    <p>Waiting for host to start streaming...</p>
                </div>
            </div>
        </div>
        <div v-if="props.isHost && cameraUnavailable" class="waiting-message" role="alert">
            <p>{{ mediaDeviceMessage }}</p>
            <button type="button" class="btn-circle" @click="toggleCam">Retry camera</button>
        </div>

        <!-- Playback Video (for non-joined audience) -->
        <div v-if="!joined && playbackUrl" class="video-player-container">
            <iframe v-if="playbackUrl" loading="lazy" class="video-player" title="Gumlet video player"
                :src="playbackUrl"
                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen">
            </iframe>
        </div>

        <!-- Controls -->
        <div v-if="showControls" class="stream-controls">
            <button class="btn-circle" :class="{ muted: !micOn }" @click="toggleMic" title="Mic">
                {{ micOn ? '🎤' : '🚫' }}
            </button>
            <button class="btn-circle" :class="{ muted: !camOn }" @click="toggleCam" title="Cam">
                {{ camOn ? '📷' : '🚫' }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onUnmounted, nextTick, watch } from 'vue'
import { toast } from 'vue-sonner'
import axios from 'axios'
import AgoraRTC from "agora-rtc-sdk-ng"
import { useViewerTracking } from '@/composables/useRealtimeSync'

const props = defineProps<{
    streamId?: string | number | null
    isHost?: boolean
    playbackUrl?: string | null
    streamSettings?: {
        title?: string
        broadcastType?: string
        visibility?: string
        location?: string
        baseResolution?: string
        outputResolution?: string
        downscaleFilter?: string
        coverImage?: File | null
        subscriptionRate?: number
        products?: Array<Record<string, unknown>>
    }
    channelName?: string | null
    showControls?: boolean
    isStreamLive?: boolean
    ownerId?: string | number | null
}>()

const emit = defineEmits<{
    joined: [streamId?: string | number]
    left: []
    error: [error: any]
    videoReady: []
    streamEnded: []
    streamDataLoaded: [data: { stream: any, playback: string, user: any }]
    streamStateLoaded: [state: any]
    guestPublished: [data: { uid: string | number, track: any }]
    guestUnpublished: [uid: string | number]
    localGuestPublished: [track: any]
}>()

// State
const joined = ref(false)
const micOn = ref(true)
const camOn = ref(true)
const cameraUnavailable = ref(false)
const mediaDeviceMessage = ref('Camera not found. Connect a camera or choose another device, then retry.')
const hasRemoteVideo = ref(false)

// Viewer tracking
const viewerTracking = ref<any>(null)

// Agora
let client: any = null
let localVideoTrack: any = null
let localAudioTrack: any = null


// Methods
const toggleMic = () => {
    micOn.value = !micOn.value
    if (joined.value && localAudioTrack) {
        localAudioTrack.setEnabled(micOn.value)
    }
}

const toggleCam = async () => {
    // If camera was unavailable and user tries to turn on, try to create it again
    if (!camOn.value && cameraUnavailable.value && joined.value) {
        try {
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent)
            const videoConfig = isMobile
                ? { encoderConfig: "480p_1", facingMode: "user" as const }
                : { encoderConfig: "720p_1" }
            localVideoTrack = await AgoraRTC.createCameraVideoTrack(videoConfig)
            cameraUnavailable.value = false
            camOn.value = true

            await nextTick()
            const playerElement = document.getElementById("local-player")
            if (playerElement) {
                await localVideoTrack.play("local-player")
            }
            if (client) {
                await client.publish([localVideoTrack])
            }
            return
        } catch (error: any) {
            mediaDeviceMessage.value = error?.code === 'DEVICE_NOT_FOUND'
                ? 'Camera not found. Connect a camera or choose another device, then retry.'
                : 'Camera access was unavailable. Check browser permissions, then retry.'
            toast.warning(mediaDeviceMessage.value)
            return
        }
    }

    camOn.value = !camOn.value
    if (joined.value && localVideoTrack) {
        localVideoTrack.setEnabled(camOn.value)
    }
}

const setupListeners = () => {
    if (!client) return

    client.removeAllListeners("user-published")
    client.removeAllListeners("user-unpublished")

    client.on("user-published", async (user: any, mediaType: any) => {
        try {
            await client.subscribe(user, mediaType)

            if (mediaType === "video") {
                const videoTrack = user.videoTrack
                if (videoTrack) {
                    if (String(user.uid) === String(props.ownerId)) {
                        hasRemoteVideo.value = true
                    }
                    let attempts = 0
                    let player: HTMLElement | null = null
                    while (!player && attempts < 50) {
                        await nextTick()
                        await new Promise(r => setTimeout(r, 100))
                        player = document.getElementById("remote-player")
                        attempts++
                    }

                    if (String(user.uid) === String(props.ownerId)) {
                        if (player) {
                            // Clear waiting message
                            const waitingMsg = player.querySelector('.waiting-message')
                            if (waitingMsg) {
                                waitingMsg.remove()
                            }

                            try {
                                player.style.display = 'block'
                                player.style.visibility = 'visible'
                                await videoTrack.play("remote-player")
                                emit('videoReady')
                            } catch (playError) {
                                // Try alternative method - create video element manually
                                try {
                                    const videoElement = document.createElement('video')
                                    videoElement.id = 'remote-video-' + user.uid
                                    videoElement.style.width = '100%'
                                    videoElement.style.height = '100%'
                                    videoElement.style.objectFit = 'cover'
                                    videoElement.autoplay = true
                                    videoElement.playsInline = true
                                    player.innerHTML = ''
                                    player.appendChild(videoElement)
                                    await videoTrack.play(videoElement)
                                    emit('videoReady')
                                } catch (altError) {
                                    console.error('Alternative play method also failed:', altError)
                                }
                            }
                        } else {
                            console.error('remote-player element not found after 50 attempts')
                        }
                    } else {
                        // It's a guest!
                        emit('guestPublished', { uid: user.uid, track: videoTrack })
                    }
                } else {
                    console.warn('Video track not available after subscription')
                }
            }
            if (mediaType === "audio" && user.audioTrack) {
                try {
                    user.audioTrack.play()
                } catch (audioError) {
                    console.error('Error playing audio:', audioError)
                }
            }
        } catch (error) {
            console.error('Error in user-published handler:', error)
        }
    })

    client.on("user-unpublished", (user: any, mediaType: any) => {
        if (mediaType === "video") {
            if (String(user.uid) === String(props.ownerId)) {
                hasRemoteVideo.value = false
                const player = document.getElementById("remote-player")
                if (player) {
                    // Clear video but keep the container
                    const videoElements = player.querySelectorAll('video')
                    videoElements.forEach(v => v.remove())
                    // Show waiting message again if stream is not live
                    if (!props.isStreamLive) {
                        if (!player.querySelector('.waiting-message')) {
                            const waitingDiv = document.createElement('div')
                            waitingDiv.className = 'waiting-message'
                            waitingDiv.innerHTML = '<div style="font-size:30px">⏳</div><p>Waiting for host to start streaming...</p>'
                            player.appendChild(waitingDiv)
                        }
                    }
                }
            }
            emit('guestUnpublished', user.uid)
        }
    })

    client.on("user-mute-updated", (user: any) => {
        if (String(user.uid) === String(props.ownerId)) {
            hasRemoteVideo.value = user.hasVideo
        }
    })
}

const startHost = async () => {
    if (!client) return

    try {
        try {
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent)
            const videoConfig = isMobile
                ? { encoderConfig: "480p_1", facingMode: "user" as const }
                : { encoderConfig: "720p_1" }
            localVideoTrack = await AgoraRTC.createCameraVideoTrack(videoConfig)
            cameraUnavailable.value = false

            let attempts = 0
            let playerElement: HTMLElement | null = null
            while (!playerElement && attempts < 30) {
                await nextTick()
                await new Promise(r => setTimeout(r, 100))
                playerElement = document.getElementById("local-player")
                attempts++
            }
            if (playerElement) {
                await localVideoTrack.play("local-player")
            }
        } catch (error: any) {
            cameraUnavailable.value = true
            camOn.value = false
            mediaDeviceMessage.value = error?.code === 'DEVICE_NOT_FOUND'
                ? 'Camera not found. Connect a camera or choose another device, then retry.'
                : 'Camera access was unavailable. Check browser permissions, then retry.'
            toast.warning(mediaDeviceMessage.value)
        }

        try {
            localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack()
        } catch {
            toast.warning("Microphone not available.")
        }

        const tracksToPublish = []
        if (localVideoTrack) tracksToPublish.push(localVideoTrack)
        if (localAudioTrack) tracksToPublish.push(localAudioTrack)

        if (tracksToPublish.length > 0) {
            await client.publish(tracksToPublish)
            toast.success("Broadcasting started!")

            // If I am a guest, let the parent know so it can play my local track in a guest slot
            if (props.isHost === false && localVideoTrack) {
                emit('localGuestPublished', localVideoTrack)
            }
        }
    } catch (error) {
        console.error('Failed to start broadcast:', error)
        emit('error', error)
    }
}

const joinStream = async (channel: string, role: "host" | "audience" | "guest") => {
    try {
        if (joined.value) await leaveStream()

        client = AgoraRTC.createClient({ mode: "live", codec: "vp8" })
        const channelToJoin = channel || props.channelName || `live_${props.streamId}`

        setupListeners()
        let appId, token, uid

        if (role === 'host') {
            const formData = new FormData()
            if (props.streamSettings) {
                if (props.streamSettings.title) formData.append('title', props.streamSettings.title)
                if (props.streamSettings.broadcastType) formData.append('category', props.streamSettings.broadcastType)
                if (props.streamSettings.visibility) formData.append('visibility', props.streamSettings.visibility)
                if (props.streamSettings.location) formData.append('location', props.streamSettings.location)
                if (props.streamSettings.subscriptionRate !== undefined) formData.append('subscription_rate', props.streamSettings.subscriptionRate.toString())
                if (props.streamSettings.baseResolution) formData.append('base_resolution', props.streamSettings.baseResolution)
                if (props.streamSettings.outputResolution) formData.append('output_resolution', props.streamSettings.outputResolution)
                if (props.streamSettings.downscaleFilter) formData.append('downscale_filter', props.streamSettings.downscaleFilter)
                if (props.streamSettings.coverImage) {
                    formData.append('cover_image', props.streamSettings.coverImage)
                }
                formData.append('products', JSON.stringify(props.streamSettings.products || []))
            }

            const res = await axios.post(route("new_frontend.live.start"), formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            // JsonResource may be serialized directly when nested in a JSON
            // response, while some Laravel responses retain the `data` wrapper.
            // Accept both shapes so initial host startup always receives id and
            // public_id; the refresh/Inertia path already had those fields.
            const createdStream = res.data.stream?.data ?? res.data.stream

            // Give the parent the canonical stream resource immediately. This is
            // independent of Agora media availability and contains public_id for
            // the private real-time session channel.
            if (createdStream?.id) {
                emit('streamDataLoaded', {
                    stream: createdStream,
                    playback: null,
                    user: null,
                })
            }

            appId = res.data.credentials.app_id
            token = res.data.credentials.token
            uid = res.data.credentials.uid
            const finalChannel = res.data.credentials.channel

            await client.join(appId, finalChannel, token, uid)
            await client.setClientRole("host")

            // If we are invited guest, we also publish
            await startHost()

            joined.value = true
            await nextTick()
            await new Promise(r => setTimeout(r, 200))

            if (createdStream?.id) {
                emit('joined', createdStream.id)
            } else {
                emit('joined')
            }

        } else {
            if (!props.streamId) throw new Error('A stream is required to join.')
            const res = await axios.post(route("new_frontend.live.credentials", { stream: props.streamId }))
            appId = res.data.credentials.app_id
            token = res.data.credentials.token
            uid = res.data.credentials.uid

            await client.join(appId, res.data.credentials.channel, token, uid)

            // If role is explicitly 'guest' (co-host), set as host and start publishing
            if (role === 'guest') {
                await client.setClientRole("host")
                await startHost()
            } else {
                await client.setClientRole("audience", { level: 2 })
            }

            joined.value = true
            await nextTick()
            await new Promise(r => setTimeout(r, 200))

            emit('joined')
        }
    } catch (error: any) {
        const validationErrors = error.response?.data?.errors || {}
        const message = Object.values(validationErrors).flat()[0]
            || error.response?.data?.message
            || 'Unable to start the live stream.'
        console.error('Failed to join stream:', {
            status: error.response?.status,
            message,
            errors: validationErrors,
        })
        toast.error(String(message))
        emit('error', error)
    }
}

const leaveStream = async () => {
    // Close tracks immediately
    if (localVideoTrack) {
        try {
            localVideoTrack.close()
        } catch { }
        localVideoTrack = null
    }
    if (localAudioTrack) {
        try {
            localAudioTrack.close()
        } catch { }
        localAudioTrack = null
    }

    // Leave client with timeout
    if (client) {
        try {
            await Promise.race([
                client.leave(),
                new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout')), 2000))
            ])
        } catch (error) {
            console.log('Client leave timeout or error:', error)
        }
        client = null
    }

    joined.value = false
    cameraUnavailable.value = false
    camOn.value = true
    hasRemoteVideo.value = false
    emit('left')
}

const endStream = async () => {
    // Stop viewer tracking before leaving
    await stopViewerTracking()
    await leaveStream()
    emit('streamEnded')
}

// Fetch live stream details (for joining as audience)
const fetchLiveStreamDetail = async (streamId: string | number) => {
    try {
        const response = await axios.get(route('frontend.livestream.join', { id: streamId }) + '?t=' + new Date().getTime())
        if (response.data.status) {
            const streamData = response.data.liveStream
            const playback = response.data.playBackKey
            const userData = response.data.data

            const isSubscribed = response.data.is_subscribed

            // Emit stream data to parent
            emit('streamDataLoaded', {
                stream: streamData,
                playback: playback,
                user: userData,
                is_subscribed: isSubscribed
            } as any)

            // Fetch stream state
            try {
                const stateRes = await axios.get(route('frontend.live.state', { stream: streamData.id }))
                if (stateRes.data?.ok) {
                    emit('streamStateLoaded', stateRes.data.session || {})
                }
            } catch (e) {
                console.error('Error loading stream state:', e)
            }

            return { stream: streamData, playback, user: userData, is_subscribed: isSubscribed }
        }
    } catch (error: any) {
        console.error('Failed to fetch stream details:', error)
        emit('error', error)
        throw error
    }
}

// Viewer tracking functions
const startViewerTracking = async (streamId: string | number) => {
    if (!streamId) return
    try {
        const tracking = useViewerTracking(streamId)
        viewerTracking.value = tracking
        await tracking.startTracking()
    } catch (error) {
        console.error('Viewer tracking failed:', error)
    }
}

const stopViewerTracking = async () => {
    if (viewerTracking.value) {
        try {
            await viewerTracking.value.stopTracking()
        } catch (error) {
            console.error('Error stopping viewer tracking:', error)
        }
        viewerTracking.value = null
    }
}

// Expose methods to parent
defineExpose({
    joinStream,
    leaveStream,
    endStream,
    toggleMic,
    toggleCam,
    fetchLiveStreamDetail,
    startViewerTracking,
    stopViewerTracking,
    joined: computed(() => joined.value),
    micOn: computed(() => micOn.value),
    camOn: computed(() => camOn.value),
    cameraUnavailable: computed(() => cameraUnavailable.value),
    hasRemoteVideo: computed(() => hasRemoteVideo.value)
})

// Watch for streamId changes
watch(() => props.streamId, (newId) => {
    if (!newId && joined.value) {
        leaveStream()
    }
}, { immediate: false })

onUnmounted(() => {
    stopViewerTracking()
    leaveStream()
})
</script>
