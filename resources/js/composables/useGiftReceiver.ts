import { ref, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

interface Gift {
  id: number;
  sender_id: number;
  recieved_id: number;
  name: string;
  coins: number;
  status: string;
  created_at: string;
  viewed_at?: string;
  responded_at?: string;
  sender?: {
    id: number;
    name: string;
    avatar?: string;
  };
}

interface GiftWithEmoji extends Gift {
  emoji: string;
}

export function useGiftReceiver() {
  const { props } = usePage();
  const currentUser = (props as any).auth?.user;

  const showReceiver = ref(false);
  const receivedGift = ref<GiftWithEmoji | null>(null);
  const receivedFrom = ref('');
  const gifts = ref<Gift[]>([]);
  let pollingInterval: number | null = null;

  // Check for gifts specifically for the overlay
  const checkForInteractions = async () => {
    if (!currentUser?.id) return;

    // If we're already showing a gift, don't fetch more until it's closed
    if (showReceiver.value) return;

    try {
      // Check for Gifts only for the big overlay
      const giftRes = await axios.get('/api/user/received-gifts');
      const giftsList = giftRes.data.gifts || [];
      const unreadGifts = giftsList.filter((g: any) => !g.viewed_at);

      if (unreadGifts.length > 0) {
        const gift = unreadGifts[0];
        receivedGift.value = {
          ...gift,
          emoji: getGiftEmoji(gift.name)
        };
        receivedFrom.value = gift.sender?.name || 'Someone';
        showReceiver.value = true;
        // Do NOT mark as read here. Mark as read only when closed or replied.
      }
    } catch (error) {
      console.error('Error checking for gifts:', error);
    }
  };

  const getGiftEmoji = (giftName: string): string => {
    const emojiMap: { [key: string]: string } = {
      'Hola': '👋',
      'Island Smile': '😊',
      'Soca Vibe': '🎶',
      'Dance Move': '💃',
      'Sunset': '🌅',
      'Coconut Drink': '🥥',
      'Rose': '🌹',
      'Blown Kiss': '😘',
      'Date Night': '🕯️',
      'Beach LinkUp': '🏝️',
      'Heart Glow': '💖',
      'Caribbean Crown': '👑',
    };
    return emojiMap[giftName] || '🎁';
  };

  const markGiftAsRead = async (giftId: number) => {
    try {
      await axios.post(`/api/user/gifts/${giftId}/mark-read`);
    } catch (error) {
      console.error('Error marking gift as read:', error);
    }
  };

  const hideReceiver = async () => {
    if (receivedGift.value) {
      // Mark as read when the user closes the overlay
      await markGiftAsRead(receivedGift.value.id);
    }
    showReceiver.value = false;
    receivedGift.value = null;
    receivedFrom.value = '';
  };

  const replyToGift = async () => {
    const giftId = receivedGift.value?.id;
    const senderId = receivedGift.value?.sender_id;

    if (giftId) {
      await markGiftAsRead(giftId);
    }

    if (senderId) {
      window.location.href = `/chat/user/${senderId}`;
    }

    showReceiver.value = false;
    receivedGift.value = null;
    receivedFrom.value = '';
  };

  onMounted(() => {
    checkForInteractions();
    // Poll every 15 seconds for new gifts to reduce server load
    pollingInterval = window.setInterval(checkForInteractions, 15000);
  });

  onBeforeUnmount(() => {
    if (pollingInterval) {
      clearInterval(pollingInterval);
      pollingInterval = null;
    }
  });

  return {
    showReceiver,
    receivedGift,
    receivedFrom,
    gifts,
    checkForInteractions,
    hideReceiver,
    replyToGift,
  };
}
