<script setup lang="ts">
import '@/pages/admin/AdManagement/style.css';

const emit = defineEmits(['view-changed']);
</script>

<template>
    <div id="section-restaurants" class="page">
        <div style="text-align:center;padding:60px 20px;">
            <div style="font-size:64px;margin-bottom:16px;">🍽️</div>
            <div style="font-size:20px;font-weight:800;margin-bottom:8px;">Restaurant Ads</div>
            <p class="text-muted" style="margin-bottom:20px;">Filter and manage all restaurant advertising campaigns in one place.</p>
            <button class="btn btn-primary" @click="emit('view-changed', 'adAllAdsCommand')">View All Ads →</button>
        </div>
    </div>
</template>
