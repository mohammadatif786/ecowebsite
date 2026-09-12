<script setup lang="ts">
import '@/pages/admin/AdManagement/style.css';

const emit = defineEmits(['view-changed']);
</script>

<template>
    <div id="section-clubs" class="page">
        <div style="text-align:center;padding:60px 20px;">
            <div style="font-size:64px;margin-bottom:16px;">🎭</div>
            <div style="font-size:20px;font-weight:800;margin-bottom:8px;">Clubs & Fêtes</div>
            <p class="text-muted" style="margin-bottom:20px;">Manage nightlife, event, and festival advertising campaigns.</p>
            <button class="btn btn-primary" @click="emit('view-changed', 'adAllAdsCommand')">View All Ads →</button>
        </div>
    </div>
</template>
