<template>
    <transition name="fade">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="$emit('close')">
            <div class="bg-white rounded-2xl shadow-lg max-w-lg w-full p-6 transform transition-all">
                <slot />
                <!-- <div class="text-right mt-4">
                    <button @click="$emit('close')"
                        class="px-4 py-2 rounded-lg bg-blue-400 text-white text-sm hover:bg-dark">
                        Close
                    </button>
                </div> -->
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { onMounted, onBeforeUnmount } from "vue";

const props = defineProps<{ show: boolean }>();
const emit = defineEmits(["close"]);

// Close modal on Esc key
function handleKey(e: KeyboardEvent) {
    if (e.key === "Escape" && props.show) {
        emit("close");
    }
}

onMounted(() => {
    window.addEventListener("keydown", handleKey);
});
onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKey);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
