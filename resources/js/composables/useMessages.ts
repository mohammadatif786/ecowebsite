import { ref, watch, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

type Message = {
    type: string;
    message: string;
}

// Track shown messages to prevent duplicates
const shownMessages = new Set<string>();

export default function useMessages() {
    const page = usePage<{
        messages: Message[]
    }>();

    // Function to show messages
    const displayMessages = () => {
        if (page.props.messages && Array.isArray(page.props.messages) && page.props.messages.length > 0) {
            page.props.messages.forEach(message => {
                // Create unique key for this message (without timestamp)
                const messageKey = `${message.type}:${message.message}`;
                
                // Skip if already shown recently
                if (shownMessages.has(messageKey)) {
                    return;
                }
                
                // Mark as shown
                shownMessages.add(messageKey);
                
                // Remove from set after 500ms to allow same message later
                setTimeout(() => {
                    shownMessages.delete(messageKey);
                }, 500);

                switch (message.type) {
                    case 'success':
                        toast.success(message.message);
                        break;
                    case 'error':
                        toast.error(message.message);
                        break;
                }
            });
        }
    };

    // Show messages on initial load
    displayMessages();

    // Setup Inertia event listener for subsequent navigations
    const removeFinishListener = router.on('finish', () => {
        displayMessages();
    });

    // Cleanup on unmount
    onUnmounted(() => {
        removeFinishListener();
    });
}