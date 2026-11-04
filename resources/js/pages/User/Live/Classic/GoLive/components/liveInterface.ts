export interface Gift {
    id: string;
    name: string;
    coins: number;
    hostShare: number;
    emoji: string;
}

export interface Guest {
    id: string;
    name?: string;
    avatar?: string;
    status: 'pending' | 'joined';
}

export interface Poll {
    id: string;
    question: string;
    options: { id: string; text: string; votes: number }[];
    active: boolean;
}

export interface QnA {
    id: string;
    from: string;
    text: string;
    answered: boolean;
    timestamp: number;
}

export interface Session {
    id: string;
    host_id: string;
    user_id?: string | number;
    title: string;
    type: string;
    tags: string[];
    geo: string;
    visibility: 'public' | 'followers' | 'private';
    subscription_rate: number;
    status: 'live' | 'ended';
    started_at: number;
    ended_at?: number;
    viewer_count: number;
    like_count: number;
    gift_count: number;
    earnings_cents: number;
    events: any[];
    ledger: any[];
    polls: Poll[];
    qna: QnA[];
}

export interface AppState {
    coins: number;
    cash: number;
    totalCashEarned: number;
    session: Session | null;
    sessions: Session[];
    followedUsers: string[];
    guests: Guest[];
    totalLikes: number;
    followers: number;
    polls: Poll[];
    qna: QnA[];
    liveStartTime: number;
    recordStartTime: number;
}

export interface LogEntry {
    timestamp: string;
    message: string;
    data?: any;
}

export interface ChatMessage {
    id: string;
    from: string;
    text: string;
    timestamp: number;
}

export interface GiftAnimation {
    id: string;
    emoji: string;
    top: number;
    progress: number;
    duration: number;
}
