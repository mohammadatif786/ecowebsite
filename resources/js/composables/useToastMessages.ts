import { ref, watch, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

interface ToastMessage {
  title: string
}

export function useToastMessages() {
  const open = ref(false)
  const message = ref<string | null>(null)
  const showMessages = ref(true)

  const page = usePage()

  const showToast = () => {
    const messages = page.props.messages as ToastMessage | null

    if (showMessages.value && messages?.title) {
      message.value = messages.title
      open.value = true
      showMessages.value = false
    }
  }

  // Watch the page.props.messages and show toast if needed
  watch(
    () => page.props.messages,
    () => {
      showToast()
    },
    { immediate: true }
  )

  // Handle Inertia navigation finishes
  onMounted(() => {
    router.on('finish', () => {
      showMessages.value = true
      showToast()
    })
  })

  return {
    open,
    message,
  }
}
