/**
 * U Vibe messages — the inbox is the member's Vibes chat list (the same conversations as on the
 * Vibes page and in the app), newest first.
 */
import axios from 'axios';

/** [{user_id, name, handle, avatar, last_message, last_message_at}] */
export const fetchConversations = () =>
    axios.get(route('user.vibes.chats.conversations')).then((r) => r.data?.conversations || []);
