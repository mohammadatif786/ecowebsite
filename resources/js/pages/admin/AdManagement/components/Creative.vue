<script setup lang="ts">
import { ref, watchEffect } from 'vue';

const props = defineProps<{
    creative: {
        adType: string;
        imagePreview: string;
        videoPreview: string;
        thumbnailPreview: string;
        maxDuration: string;
        autoplaySound: string;
        ctaOverlayTiming: string;
        loopVideo: string;
        ctaText: string;
        brandColor: string;
    };
    errors?: Record<string, string[]>;
}>();

const emit = defineEmits<{
    (e: 'update-creative', payload: Partial<typeof props.creative>): void;
}>();

const activeAdType = ref(props.creative.adType || 'image');
const imagePreview = ref(props.creative.imagePreview || '');
const videoPreview = ref(props.creative.videoPreview || '');
const thumbnailPreview = ref(props.creative.thumbnailPreview || '');
const maxDuration = ref(props.creative.maxDuration || '15');
const autoplaySound = ref(props.creative.autoplaySound || 'muted');
const ctaOverlayTiming = ref(props.creative.ctaOverlayTiming || 'start');
const loopVideo = ref(props.creative.loopVideo || 'yes');
const ctaText = ref(props.creative.ctaText || 'Visit Website');
const brandColor = ref(props.creative.brandColor || '#6FBD1E');

const previewFile = (event: Event, previewKey: 'imagePreview' | 'videoPreview' | 'thumbnailPreview') => {
    const target = event.target as HTMLInputElement;
    if (!target.files || target.files.length === 0) return;

    const file = target.files[0];
    const reader = new FileReader();
    reader.onload = (e) => {
        const result = e.target?.result as string;
        if (previewKey === 'imagePreview') {
            imagePreview.value = result;
        } else if (previewKey === 'videoPreview') {
            videoPreview.value = result;
        } else if (previewKey === 'thumbnailPreview') {
            thumbnailPreview.value = result;
        }
    };
    reader.readAsDataURL(file);
};

watchEffect(() => {
    emit('update-creative', {
        adType: activeAdType.value,
        imagePreview: imagePreview.value,
        videoPreview: videoPreview.value,
        thumbnailPreview: thumbnailPreview.value,
        maxDuration: maxDuration.value,
        autoplaySound: autoplaySound.value,
        ctaOverlayTiming: ctaOverlayTiming.value,
        loopVideo: loopVideo.value,
        ctaText: ctaText.value,
        brandColor: brandColor.value,
    });
});
</script>

<template>
    <!-- Ad Type Selector -->
    <div style="margin-bottom:6px;font-size:13px;font-weight:700;color:var(--mid);">Ad Format</div>
    <div class="adtype-toggle">
        <button class="adtype-btn" :class="activeAdType === 'image' ? 'active' : ''" id="btn-image"
            @click="activeAdType = 'image'">🖼️ Image Ad</button>
        <button class="adtype-btn" :class="activeAdType === 'video' ? 'active' : ''" id="btn-video"
            @click="activeAdType = 'video'">🎬 Video Ad</button>
        <button class="adtype-btn" :class="activeAdType === 'both' ? 'active' : ''" id="btn-both"
            @click="activeAdType = 'both'">✨ Image + Video</button>
    </div>

    <!-- IMAGE PANEL -->
    <div class="adtype-panel" id="panel-image" :class="activeAdType === 'image' ? 'active' : ''">
        <div class="form-grid">
            <div class="field full">
                <label>Ad Image <span style="color:var(--red)">*</span></label>
                <div class="upload-zone">
                    <input type="file" accept="image/*" @change="previewFile($event, 'imagePreview')" />
                    <div class="upload-icon">🖼️</div>
                    <p><strong>Click to upload</strong> or drag &amp; drop</p>
                    <div class="spec-pills">
                        <span class="spec-pill">PNG · JPG · WebP</span>
                        <span class="spec-pill">Recommended 1080×1920</span>
                        <span class="spec-pill">Max 10 MB</span>
                        <span class="spec-pill">Ratio 9:16</span>
                    </div>
                </div>
                <img v-if="imagePreview" :src="imagePreview"
                    style="margin-top:12px;border-radius:10px;max-height:180px;object-fit:cover;" />
            </div>
        </div>
    </div>

    <!-- VIDEO PANEL -->
    <div class="adtype-panel" id="panel-video" :class="activeAdType === 'video' ? 'active' : ''">
        <div
            style="background:linear-gradient(135deg,#1a0a2e,#2d1060);border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:flex-start;gap:16px;">
            <span style="font-size:28px;">🎬</span>
            <div>
                <div style="font-size:14px;font-weight:700;color:#c4b5fd;">Video Ads get 3× more engagement</div>
                <div style="font-size:12px;color:rgba(255,255,255,.55);margin-top:3px;line-height:1.5;">
                    Video ads auto-play silently as users swipe. They can tap to unmute. Swipe right still visits the
                    website.
                    Videos under 15 seconds have the highest completion rate on LinkUp Vibes.
                </div>
            </div>
        </div>
        <div class="form-grid">
            <div class="field full">
                <label>Ad Video <span style="color:var(--red)">*</span></label>
                <div v-if="errors?.video" class="error-message">{{ errors.video.join(', ') }}</div>

                <div class="upload-zone upload-zone-video">
                    <input type="file" accept="video/mp4,video/mov,video/quicktime"
                        @change="previewFile($event, 'videoPreview')" />
                    <div class="upload-icon">▶️</div>
                    <p><strong>Click to upload</strong> or drag &amp; drop</p>
                    <div class="spec-pills">
                        <span class="spec-pill">MP4 · MOV</span>
                        <span class="spec-pill">Max 60 seconds</span>
                        <span class="spec-pill">Max 200 MB</span>
                        <span class="spec-pill">Ratio 9:16 preferred</span>
                        <span class="spec-pill">Min 720p</span>
                    </div>
                </div>
                <video v-if="videoPreview" :src="videoPreview"
                    style="margin-top:12px;border-radius:10px;max-height:200px;width:100%;object-fit:cover;" controls
                    :loop="loopVideo === 'yes'" :muted="autoplaySound !== 'on'" autoplay></video>
            </div>
            <div class="field full">
                <label>Video Thumbnail <span class="text-muted">(shown before play)</span></label>
                <div class="upload-zone">
                    <input type="file" accept="image/*" @change="previewFile($event, 'thumbnailPreview')" />
                    <div class="upload-icon">🖼️</div>
                    <p><strong>Upload thumbnail</strong> or we auto-generate from frame 1</p>
                </div>
                <img v-if="thumbnailPreview" :src="thumbnailPreview"
                    style="margin-top:12px;border-radius:10px;max-height:120px;object-fit:cover;" />
            </div>
            <div class="field">
                <label>Max Duration Shown</label>
                <select id="vid-max-dur" v-model="maxDuration">
                    <option value="15">15 seconds (recommended · highest completion)</option>
                    <option value="30">30 seconds</option>
                    <option value="60">60 seconds</option>
                    <option value="full">Full video</option>
                </select>
                <span class="field-hint">Longer clips can be trimmed for the swipe tile — full video plays if user
                    taps.</span>
            </div>
            <div class="field">
                <label>Autoplay Sound</label>
                <select v-model="autoplaySound">
                    <option value="muted">🔇 Muted (recommended)</option>
                    <option value="on">🔊 Sound on (premium only)</option>
                </select>
                <span class="field-hint">Muted videos play automatically. Sound-on requires Premium package.</span>
            </div>
            <div class="field">
                <label>CTA Overlay Timing</label>
                <select v-model="ctaOverlayTiming">
                    <option value="start">Show CTA from the start</option>
                    <option value="5">Show CTA after 5 seconds</option>
                    <option value="10">Show CTA after 10 seconds</option>
                    <option value="end">Show CTA at video end</option>
                </select>
            </div>
            <div class="field">
                <label>Loop Video?</label>
                <select v-model="loopVideo">
                    <option value="yes">Yes — loop until swiped</option>
                    <option value="no">No — pause on last frame</option>
                </select>
            </div>
        </div>
    </div>

    <!-- BOTH PANEL -->
    <div class="adtype-panel" id="panel-both" :class="activeAdType === 'both' ? 'active' : ''">
        <div
            style="background:var(--brand-pale);border:1.5px solid var(--brand);border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--mid);line-height:1.5;">
            ✨ <strong>Image + Video</strong> — the image tile appears first. If the user taps the tile,
            the video begins playing inline. Swipe right from either state visits the website.
        </div>
        <div class="form-grid">
            <div class="field">
                <label>Ad Image <span style="color:var(--red)">*</span></label>
                <div class="upload-zone" style="padding:20px;">
                    <input type="file" accept="image/*" @change="previewFile($event, 'imagePreview')" />
                    <div style="font-size:28px;margin-bottom:6px;">🖼️</div>
                    <p style="font-size:13px;"><strong>Upload image</strong></p>
                    <div class="spec-pills"><span class="spec-pill">1080×1920 · PNG/JPG</span></div>
                </div>
                <img v-if="imagePreview" :src="imagePreview"
                    style="margin-top:8px;border-radius:8px;max-height:120px;object-fit:cover;" />
            </div>
            <div class="field">
                <label>Ad Video <span style="color:var(--red)">*</span></label>
                <div class="upload-zone upload-zone-video" style="padding:20px;">
                    <input type="file" accept="video/*" @change="previewFile($event, 'videoPreview')" />
                    <div style="font-size:28px;margin-bottom:6px;">🎬</div>
                    <p style="font-size:13px;"><strong>Upload video</strong></p>
                    <div class="spec-pills"><span class="spec-pill">MP4/MOV · Max 60s</span></div>
                </div>
                <video v-if="videoPreview" :src="videoPreview"
                    style="margin-top:8px;border-radius:8px;max-height:120px;width:100%;object-fit:cover;" controls
                    :loop="loopVideo === 'yes'" :muted="autoplaySound !== 'on'" autoplay></video>
            </div>
        </div>
    </div>

    <!-- Shared fields -->
    <div class="form-grid" style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border);">
        <div class="field">
            <label>CTA Button Text</label>
            <input type="text" v-model="ctaText" placeholder="Order Now · Book Table · Get Deal" />
            <div v-if="errors?.cta_text" class="error-message">{{ errors.cta_text.join(', ') }}</div>
        </div>
        <div class="field">
            <label>Brand Color</label>
            <input type="color" v-model="brandColor" style="height:42px;padding:4px 8px;" />
            <div v-if="errors?.brand_color" class="error-message">{{ errors.brand_color.join(', ') }}</div>
        </div>
    </div>
</template>

<style scoped>
.error-message {
    color: #ef4444;
    font-size: 12px;
    margin-top: 4px;
}
</style>
