import { ref, watch, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

interface ToastMessage {
    title: string
}

export function useFlashedMessage() {
    const open = ref(false)
    const message = ref<string | null>(null)
    const showMessage = ref(true)
    const type = ref('success');

    const page = usePage()

    const showToast = () => {
        const messages = page.props.messages as ToastMessage | null

        if (showMessage.value && messages?.title) {
            message.value = messages.title
            open.value = true
            showMessage.value = false
        }
    }

    // Watch the page.props.messages and show toast if needed
    watch(
        () => page.props.success,
        () => {
            showToast()
        },
        { immediate: true }
    )

    // Watch the page.props.messages and show toast if needed
    watch(
        () => page.props.error,
        () => {
            showToast()
        },
        { immediate: true }
    )

    // Handle Inertia navigation finishes
    onMounted(() => {
        router.on('finish', () => {
            showMessage.value = true
            showToast()
        })
    })

    return {
        open,
        message,
        type,
    }
}
