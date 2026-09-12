<template>
    <AppLayout>

        <div class="mx-auto p-5 bg-white rounded-[20px] border border-slate-100 shadow-sm">
            <!-- Show Stats on Scanner App (yn helper at 4655) -->
            <div class="mb-5 border-b border-slate-50 pb-5">
                <p class="font-black mb-1 text-slate-900">Show event date stats on the scanner app <span class="text-rose-500">*</span></p>
                <div class="flex items-start gap-1.5 text-[12px] text-slate-400 mb-3 font-bold">
                    <span>ℹ️</span>
                    The event date stats (sales and attendance) will be visible on the scanner app
                </div>
                <div class="flex gap-8">
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer">
                        <input type="radio" :value="1" v-model="form.show_stats" class="accent-blue-600 w-4 h-4" />
                        Yes
                    </label>
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer">
                        <input type="radio" :value="0" v-model="form.show_stats" class="accent-blue-600 w-4 h-4" />
                        No
                    </label>
                </div>
            </div>

            <!-- Allow Tap to Check In -->
            <div class="mb-5">
                <p class="font-black mb-1 text-slate-900">Allow tap to check in on the scanner app <span class="text-rose-500">*</span></p>
                <div class="flex items-start gap-1.5 text-[12px] text-slate-400 mb-3 font-bold">
                    <span>ℹ️</span>
                    Besides the QR code scanning feature, the scanner account will be able to check in attendees using a list and a button
                </div>
                <div class="flex gap-8">
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer">
                        <input type="radio" :value="1" v-model="form.tap_checkin" class="accent-blue-600 w-4 h-4" />
                        Yes
                    </label>
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer">
                        <input type="radio" :value="0" v-model="form.tap_checkin" class="accent-blue-600 w-4 h-4" />
                        No
                    </label>
                </div>
            </div>

            <!-- Save Button -->
            <button @click="saveSettings" :disabled="form.processing"
                class="w-full py-3.5 rounded-2xl font-black text-white text-sm transition hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 flex items-center justify-center gap-2 mt-4 shadow-lg shadow-teal-500/20"
                style="background: linear-gradient(90deg,#2dd4bf,#eab308)">
                <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                SAVE
            </button>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/organizer/AppLayout.vue'
import { Settings, Loader2 } from 'lucide-vue-next'
import { useForm } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

const props = defineProps<{
    settings?: {
        show_stats: number | string;
        tap_checkin: number | string;
    }
}>()

const form = useForm({
    show_stats: props.settings?.show_stats ? 1 : 0,
    tap_checkin: props.settings?.tap_checkin ? 1 : 0,
})

function saveSettings() {
    form.post(route('organizer.scanner.app.setting.store'), {
        onSuccess: () => {
            toast.success('✅ Scanner app settings saved')
        }
    })
}
</script>
