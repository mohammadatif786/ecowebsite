import type { LiveMessage } from '@/client/index'
export type LiveMessagesTable = {
    data: Array<LiveMessage>,
    total: number,
    current_page: number,
    per_page: number,
    last_page: number,
    from: number,
    to: number,
    links?: {
        first: string,
        last: string,
        next: string,
        prev: string,
    }
}| null;
