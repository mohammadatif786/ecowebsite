<template>
  <button class="relative w-8 h-8" :aria-label="ariaLabel" @click="$emit('click')">
    <!-- Circular background with mask -->
    <div class="">
      <svg
        width="33"
        height="33"
        viewBox="0 0 33 33"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="w-10 h-10"
      >
        <mask
          :id="`mask_${maskId}`"
          style="mask-type: alpha"
          maskUnits="userSpaceOnUse"
          x="2"
          y="2"
          width="29"
          height="29"
        >
          <path
            d="M16.7922 3.05566C24.4507 3.05566 30.6594 9.27737 30.6594 16.9531C30.6592 24.6285 24.4505 30.8496 16.7922 30.8496C9.13384 30.8496 2.92529 24.6285 2.92505 16.9531C2.92505 9.27738 9.13369 3.05568 16.7922 3.05566Z"
            fill="black"
            stroke="#898989"
            stroke-width="0.3"
            stroke-miterlimit="16"
          />
        </mask>
        <g :mask="`url(#mask_${maskId})`">
          <rect
            width="31.6"
            height="31.6"
            transform="translate(0.7 0.7)"
            fill="#015987"
          />
          <path
            d="M16.7922 3.05566C24.4507 3.05566 30.6594 9.27737 30.6594 16.9531C30.6592 24.6285 24.4505 30.8496 16.7922 30.8496C9.13384 30.8496 2.92529 24.6285 2.92505 16.9531C2.92505 9.27738 9.13369 3.05568 16.7922 3.05566Z"
            stroke="#898989"
            stroke-width="0.3"
            stroke-miterlimit="16"
          />
        </g>
      </svg>
    </div>

    <!-- The Icon -->
    <component
      :is="iconComponent"
      :size="iconSize"
      class="absolute top-2 left-2 text-white"
      :fill="iconFill"
    />
  </button>
</template>

<script setup lang="ts">
import { Heart, X, Check, MessageCircle, Gift } from "lucide-vue-next";
import { computed } from "vue";

interface Props {
  iconType: "heart" | "close" | "check" | "message" | "gift";
  ariaLabel: string;
  maskId: string;
}

const props = defineProps<Props>();

defineEmits<{
  click: [];
}>();

const iconComponent = computed(() => {
  return {
    heart: Heart,
    close: X,
    check: Check,
    message: MessageCircle,
    gift: Gift,
  }[props.iconType];
});

const iconSize = computed(() => {
  switch (props.iconType) {
    case "heart":
      return 24;
    case "message":
    case "gift":
      return 24;
    default:
      return 24;
  }
});

const iconPositionClass = computed(() => {
  switch (props.iconType) {
    case "heart":
      return "top-2.5 left-[9px]";
    case "message":
      return "top-[8px] left-[9px]";
    case "gift":
      return "top-2.5 left-[9px]";
    case "close":
      return "top-[9px] left-[9px]";
    default:
      return "top-2.5 left-[9px]";
  }
});

const iconFill = computed(() => {
  switch (props.iconType) {
    case "heart":
      return "red";
    case "message":
      return "#2874e2";
    case "gift":
      return "#ffcd3c";
    default:
      return "";
  }
});
</script>
