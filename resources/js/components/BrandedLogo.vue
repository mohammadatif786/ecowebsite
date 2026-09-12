 <template>
    <div :class="['flex items-center gap-2 shrink-0', customClass]">
        <svg viewBox="0 0 120 120" :width="size" :height="size">
            <ellipse cx="42" cy="20" rx="13" ry="16" fill="#9EDB2F"/>
            <ellipse cx="80" cy="20" rx="13" ry="16" fill="#2f9bef"/>
            <path d="M34 44 v34 a16 16 0 0 0 32 0 v-34" fill="none" stroke="#2f9bef" stroke-width="18" stroke-linecap="round"/>
            <path d="M86 44 v50" fill="none" stroke="#9EDB2F" stroke-width="18" stroke-linecap="round"/>
        </svg>
        <span v-if="showText" :class="['logo text-lkink leading-none', textClass]">
            Link<span style="color:#F5C518">üp</span>
        </span>
    </div>
</template>

<script setup>
defineProps({
    size: {
        type: [Number, String],
        default: 30
    },
    showText: {
        type: Boolean,
        default: true
    },
    customClass: {
        type: String,
        default: ''
    },
    textClass: {
        type: String,
        default: 'text-2xl side-full'
    }
});
</script>
