<script setup lang="ts">
import { ToastProvider, ToastRoot, ToastTitle, ToastClose, ToastViewport } from "reka-ui";

import { useToastMessages } from "@/composables/useToastMessages";

const { open, message } = useToastMessages();
console.log("message", message.value);


</script>

<template>
  <ToastProvider>
    <ToastRoot
      v-if="message"
      v-model:open="open"
      class="bg-white dark:bg-black rounded-lg shadow-sm border p-4 grid grid-cols-[auto_max-content] items-center gap-x-4 [grid-template-areas:'title_action''description_action'] data-[state=open]:animate-slideIn data-[state=closed]:animate-hide data-[swipe=move]:translate-x-[var(--reka-toast-swipe-move-x)] data-[swipe=cancel]:translate-x-0 data-[swipe=cancel]:transition-[transform_200ms_ease-out] data-[swipe=end]:animate-swipeOut"
    >
      <ToastTitle class="[grid-area:_title] font-medium dark:text-white">
        {{ message }}
      </ToastTitle>

      <ToastClose
        class="[grid-area:_action] text-slate11  text-xs hover:text-slate12"
        aria-label="Close"
      >
        ✖
      </ToastClose>
    </ToastRoot>

    <ToastViewport
      class="fixed top-2 right-0 flex flex-col p-6 gap-2 w-[390px] max-w-[100vw] z-[2147483647]"
    />
  </ToastProvider>
</template>
