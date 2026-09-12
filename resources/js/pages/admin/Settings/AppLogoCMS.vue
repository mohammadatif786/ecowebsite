<script setup lang="ts">
import { ref } from 'vue';

const logoUrl = ref('');
const curLogo = ref('');

const saveLogo = () => {
    alert('Logo saved');
};

const useDefaultLogo = () => {
    alert('Reverted to default text logo');
};
</script>

<template>
    <div style="max-width:560px;margin:0 auto">
        <h2 style="font-size:26px;font-weight:800;margin-bottom:4px">App Logo</h2>
        <p style="color:#64748b;margin-bottom:16px">Upload your LinkUp logo image. It replaces the text logo <b>everywhere the logo appears</b> — the startup screen, top bar, splash screens and sign-in. (This does not change the word "LinkUp" inside body text.) Use a transparent PNG/SVG for best results.</p>
        <div style="border:1px solid #e2e8f0;border-radius:16px;padding:18px;background:#fff">
            <div style="background:linear-gradient(135deg,#0b2942,#2f9bef);border-radius:12px;padding:22px;text-align:center;margin-bottom:14px">
                <img v-if="curLogo" :src="curLogo" style="height:54px;max-width:80%;object-fit:contain;" />
                <div v-else style="color:#fff;font-weight:800;font-size:24px">Link <span style="color:#ffe14d">üp</span> <span style="opacity:.6;font-size:13px">(current default)</span></div>
            </div>
            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:5px">Logo image</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <label style="background:#f1f5f9;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:800;cursor:pointer">⬆ Upload PNG/SVG<input type="file" accept="image/*,.svg" style="display:none" /></label>
                <input v-model="logoUrl" placeholder="…or paste image URL" style="flex:1;min-width:160px;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:13px" />
            </div>
            <div style="display:flex;gap:8px;margin-top:14px">
                <button @click="saveLogo" style="flex:1;background:#2f9bef;color:#fff;border:none;border-radius:10px;padding:12px;font-weight:800;font-size:14px;cursor:pointer">Save Logo</button>
                <button @click="useDefaultLogo" style="background:#fff;border:2px solid #e2e8f0;border-radius:10px;padding:12px 16px;font-weight:800;font-size:14px;cursor:pointer">Use default</button>
            </div>
        </div>
    </div>
</template>
