<template>
    <div class="glass rounded-custom rounded-md overflow-hidden" id="User-Streams">
        <div class="p-3 flex justify-between border-b border-line bg-gradient-to-b from-black/30 to-black/20">
            <h3 class="m-0 text-white text-xl font-bold">
                User Streams
            </h3>
            <button @click="OpenStreamModal"
                class=" btn primary px-2 py-2 rounded-xl font-bold text-gray-900 cursor-pointer transition-transform hover:scale-105 bg-gradient-to-r from-[#28a4ff] to-[#6a5cff] text-white">
                Go Live
            </button>
        </div>

        <div class="p-3">
            <div class="flex gap-3 overflow-x-auto pb-2">
                <!-- Session Card 1 -->
                <div v-for="item in user_Streams" :key="item.id"
                    class="card min-w-[18rem] rounded-2xl border border-line bg-gradient-to-b from-white/16 to-white/8 overflow-hidden cursor-pointer transition-all duration-250">
                    <!-- Thumbnail -->
                    <div class="relative aspect-[16/10] bg-gray-200 flex items-center justify-center text-gray-500 rounded-lg overflow-hidden"
                        :style="item.thumbnail ? `background-image: url('/storage/${item.thumbnail}'); background-size: cover; background-position: center;` : ''">
                        <!-- Status badge -->
                        <span class="absolute top-2 left-2 px-2 py-1 rounded-lg text-xs font-extrabold z-10"
                            :class="statusBadgeClass(item.status)">
                            {{ item.status }}
                        </span>

                        <!-- Fallback text if no thumbnail -->
                        <template v-if="!item.thumbnail">
                            <span>No Thumbnail</span>
                        </template>
                    </div>

                    <!-- Meta -->
                    <div class="p-3">
                        <div class="mb-2">
                            <strong class="text-ink">{{ item.title }}</strong><br />
                            <small class="text-muted">{{ item.broadcast_type }} • {{ item.visibility }}</small><br />
                            <small class="text-muted">{{ item.start_time }}</small>
                        </div>

                        <div class="flex space-x-2 justify-between">
                            <button class="p-2 rounded-full hover:bg-yellow-300 hover:text-black"
                                @click="handelEdit(item)">
                                <Edit />
                            </button>
                            <button class="p-2 rounded-full hover:bg-red-300 hover:text-black"
                                @click="handelDelete(item.id)">
                                <Trash2 />
                            </button>
                            <button class="p-2 rounded-full hover:bg-green-300 hover:text-black"
                                @click="startStream(item)">
                                <Video />
                            </button>
                            <!-- End Live Button -->
                            <button v-if="item.status == 'live'"
                                class="p-2 rounded-full hover:bg-red-400 hover:text-white" @click="endStream(item.id)">
                                <VideoOff />
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal with Form -->
    <Modal :show="isOpen" @close="isOpen = false">
        <h3 class="text-lg text-black font-semibold mb-4"> {{ is_edit ? 'Update' : 'Create' }} Stream</h3>
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-black">Title</label>
                <input v-model="form.title" type="text"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300 text-black"
                    placeholder="enter the title" />
                <span v-if="form.errors.title" class="mt-2 text-red-600">
                    {{ form.errors.title }}
                </span>
            </div>
            <!-- Start Time -->
            <div>
                <label class="block text-sm font-medium text-black">Start Time</label>
                <input v-model="form.start_time" type="datetime-local"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300 text-black" />
                <span v-if="form.errors.start_time" class="mt-2 text-red-600">
                    {{ form.errors.start_time }}
                </span>
            </div>
            <!-- Resulation -->
            <div>
                <label class="block text-sm font-medium text-black mb-1">Resolution Type</label>
                <select v-model="form.resolution"
                    class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300 text-black">
                    <option value="240p">240p</option>
                    <option value="360p">360p</option>
                    <option value="480p">480p</option>
                    <option value="540p">540p</option>
                    <option value="720p">720p</option>
                    <option value="1080p">1080p</option>
                    <option value="1440p">1440p</option>
                </select>
                <span v-if="form.errors.resolution" class="mt-2 text-red-600">
                    {{ form.errors.resolution }}
                </span>
            </div>

            <!-- Broadcast Type -->
            <div>
                <label class="block text-sm font-medium text-black mb-1">Broadcast Type</label>
                <select v-model="form.broadcast_type"
                    class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300 text-black">
                    <option value="Entertainment">Entertainment</option>
                    <option value="Politics">Politics</option>
                    <option value="Current Events">Current Events</option>
                    <option value="News">News</option>
                </select>
                <span v-if="form.errors.broadcast_type" class="mt-2 text-red-600">
                    {{ form.errors.broadcast_type }}
                </span>
            </div>

            <!-- Visibility -->
            <div>
                <label class="block text-sm font-medium text-black mb-1">Visibility</label>
                <select v-model="form.visibility"
                    class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300 text-black">
                    <option value="public">Public</option>
                    <option value="followers">Followers</option>
                    <option value="private">Private</option>
                </select>
                <span v-if="form.errors.visibility" class="mt-2 text-red-600">
                    {{ form.errors.visibility }}
                </span>
            </div>
            <!-- ThumbNail -->
            <div>
                <label class="block text-sm font-medium text-black mb-1">Thumbnail</label>
                <input type="file" accept="image/*" @change="handleFileUpload"
                    class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300 text-black" />
                <span v-if="form.errors.thumbnail" class="mt-2 text-red-600">
                    {{ form.errors.thumbnail }}
                </span>

                <!-- Preview -->
                <div v-if="form.thumbnailPreview" class="mt-3">
                    <img :src="form.thumbnailPreview" alt="Thumbnail Preview"
                        class="w-full h-40 object-cover rounded-lg border" />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2">
                <button type="button" @click="isOpen = false"
                    class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 cursor-pointer">
                    Save
                </button>
            </div>
        </form>
    </Modal>

    <!-- Information modal  -->
    <Modal :show="isInfoOpen" @close="isInfoOpen = false">
        <h3 class="text-lg font-semibold mb-3 text-black">🎥 How to Start Live Streaming</h3>

        <div class="space-y-4 text-sm text-gray-700 leading-relaxed">
            <!-- Step 1 -->
            <div>
                <h4 class="font-semibold text-black">Step 1: Install & Open OBS Studio</h4>
                <p>
                    Download OBS from
                    <a href="https://obsproject.com/" target="_blank" class="text-blue-600 underline">obsproject.com</a>
                    if you haven’t already. Open OBS on your system.
                </p>
            </div>

            <!-- Step 2 -->
            <div>
                <h4 class="font-semibold text-black">Step 2: Add Stream Info</h4>
                <p>In OBS, go to <strong>Settings → Stream</strong> and choose <strong>Custom...</strong> as the
                    service.
                </p>
                <div class="mt-2 p-3 bg-gray-100 rounded-md border">
                    <p><strong>Server (Ingest URL):</strong></p>
                    <code class="block bg-white rounded p-2 mt-1 text-gray-800 break-words">
                    {{ gumletIngestUrl }}
                </code>

                    <p class="mt-3"><strong>Stream Key:</strong></p>
                    <code class="block bg-white rounded p-2 mt-1 text-gray-800 break-words">
                    {{ gumletStreamKey }}
                </code>
                </div>
            </div>

            <!-- Step 3 -->
            <div>
                <h4 class="font-semibold text-black">Step 3: Configure Video Settings</h4>
                <ul class="list-disc list-inside">
                    <li>Resolution: <strong>{{ recommendedResolution }}</strong></li>
                    <li>Bitrate: <strong>2500–4000 kbps</strong></li>
                    <li>Encoder: <strong>x264</strong> or GPU encoder</li>
                    <li>FPS: 30 or 60</li>
                </ul>
            </div>

            <!-- Step 4 -->
            <div>
                <h4 class="font-semibold text-black">Step 4: Start Streaming</h4>
                <p>
                    Once everything is set, click the <strong>Start Streaming</strong> button in OBS.
                    Your live stream will begin, and viewers can watch through your site’s player.
                </p>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button @click="startLiveStream"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer">
                Start Stream
            </button>
        </div>
    </Modal>

</template>

<script setup lang="ts">
import { ref } from "vue";
import Modal from "@/components/front/Modal.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { Edit, Trash2, Video, VideoOff } from 'lucide-vue-next';
import { router } from "@inertiajs/vue3";
const isInfoOpen = ref(false)
const is_edit = ref(false)
const stream_id = ref('')

const gumletIngestUrl = ref('')
const gumletStreamKey = ref('')
const recommendedResolution = '1280x720 (720p)'

defineProps<{
    user_Streams: any
}>();

// Function to return Tailwind gradient class based on status
const statusBadgeClass = (status: any) => {
    switch (status?.toLowerCase()) {
        case 'live':
            return 'bg-gradient-to-r from-green-500 to-green-400 text-white'
        case 'upcoming':
            return 'bg-gradient-to-r from-blue-500 to-blue-400 text-white'
        case 'ended':
            return 'bg-gradient-to-r from-gray-500 to-gray-400 text-white'
        case 'error':
            return 'bg-gradient-to-r from-red-500 to-red-400 text-white'
        default:
            return 'bg-gradient-to-r from-yellow-500 to-yellow-400 text-black'
    }
}

const isOpen = ref(false);

const form = useForm({
    title: "",
    start_time: "",
    resolution: "240p",
    broadcast_type: "Entertainment",
    visibility: "public",
    thumbnail: null as File | null,
    thumbnailPreview: null as string | null,
});

function OpenStreamModal() {
    is_edit.value = false;
    stream_id.value = '';
    form.reset();
    isOpen.value = true;
}

function handleSubmit() {

    if (is_edit.value == true) {
        form.post(route('frontend.go-live.updated', { stream: stream_id.value }), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                isOpen.value = false;
                stream_id.value = '';
                is_edit.value = false;
            },
        })
    } else {
        form.post(route('frontend.go-live.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                isOpen.value = false;
            },
        });
    }
}

function handelDelete(id: any) {
    router.delete(route('frontend.go-live.destroy', id), {
        preserveScroll: true,
    })
}

function handelEdit(data: any) {
    is_edit.value = true;
    if (is_edit.value) {
        stream_id.value = data.id;
        form.title = data.title;
        form.start_time = data.start_time;
        form.resolution = data.resolution;
        form.broadcast_type = data.broadcast_type;
        form.visibility = data.visibility;
        form.thumbnailPreview = '/storage/' + data.thumbnail;

        isOpen.value = true;
    }
}

function startLiveStream() {
    router.post(route('frontend.go-live.start'), {
        id: stream_id.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            stream_id.value = '';
            isInfoOpen.value = false;
        },
    })
}

function endStream(id: any) {
    router.get(route('frontend.go-live.end', { id: id }), {
        preserveScroll: true,
    });
}

function startStream(data: any) {
    stream_id.value = data.id;
    gumletIngestUrl.value = data.stream_url;
    gumletStreamKey.value = data.stream_key;
    isInfoOpen.value = true
}

function handleFileUpload(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.thumbnail = file;
        form.thumbnailPreview = URL.createObjectURL(file);
    }
}

</script>
