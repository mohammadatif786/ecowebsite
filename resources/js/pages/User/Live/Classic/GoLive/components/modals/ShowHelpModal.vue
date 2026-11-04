<template>
    <div v-if="showHelpModal" class="modal fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-5"
        @click="$emit('close')">
        <div class="modal-content glass rounded-custom border border-line backdrop-blur-lg max-w-3xl w-full max-h-[80vh] overflow-y-auto p-5 relative"
            @click.stop>
            <button @click="$emit('close')"
                class="modal-close absolute top-2 right-2 bg-brand text-white border-none rounded-xl px-3 py-2 cursor-pointer font-semibold hover:bg-brand-2">
                Close
            </button>
            <h2 class="text-2xl mt-0 mb-4">LinkUp Live Help Guide</h2>
            <p class="mb-4">
                If you're facing issues starting your live stream using <b>OBS + Gumlet</b>, follow the steps below to
                troubleshoot:
            </p>

            <!-- Section 1 -->
            <h3 class="text-xl font-semibold mb-2 mt-4">1️⃣ Check Your Internet & OBS</h3>
            <ul class="list-disc ml-6 space-y-1">
                <li>Make sure your internet connection is stable.</li>
                <li>Open <b>OBS</b> and confirm your camera/screen is showing in the preview.</li>
                <li>Go to <b>Settings → Stream</b> and verify the <b>RTMP URL</b> and <b>Stream Key</b> are correct
                    (copied from your dashboard).</li>
                <li>Click <b>Start Streaming</b> and wait 10–30 seconds for it to go live.</li>
            </ul>

            <!-- Section 2 -->
            <h3 class="text-xl font-semibold mb-2 mt-4">2️⃣ If the Player Shows an Error</h3>
            <ul class="list-disc ml-6 space-y-1">
                <li>Ensure your OBS stream is actually running — check the status bar for “LIVE”.</li>
                <li>Refresh the page after waiting 15–30 seconds.</li>
                <li>Double-check that the playback URL matches the one generated for your stream.</li>
            </ul>

            <!-- Section 3 -->
            <h3 class="text-xl font-semibold mb-2 mt-4">3️⃣ Common Error Messages</h3>
            <div class="space-y-2">
                <div class="p-2 rounded">
                    <b>Error:</b> “This stream is offline”
                    <br />
                    This usually means OBS is not streaming or hasn’t connected yet. Start OBS or wait a few seconds.
                </div>

                <div class="p-2 rounded">
                    <b>Error:</b> “Invalid Stream Key”
                    <br />
                    You copied the wrong stream key. Go to your dashboard, copy the correct key, and paste it into OBS
                    Settings → Stream.
                </div>

                <div class="p-2 rounded">
                    <b>Error:</b> “Playback Failed”
                    <br />
                    Check your network. Sometimes browser caching causes this; refresh or open in incognito.
                </div>
            </div>

            <!-- Section 4 -->
            <h3 class="text-xl font-semibold mb-2 mt-4">4️⃣ Still Not Working?</h3>
            <ul class="list-disc ml-6 space-y-1">
                <li>Stop the stream in OBS, wait 5 seconds, then start again.</li>
                <li>Clear your browser cache or try a different browser.</li>
                <li>If all fails, contact support and share your <b>Stream Key</b> and error message.</li>
            </ul>

            <p class="mt-6 text-center text-sm text-gray-500">
                ⚠️ Note: It can take up to <b>30 seconds</b> for your stream to appear after starting OBS.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{ showHelpModal: boolean }>();
defineEmits(['close']);
</script>
