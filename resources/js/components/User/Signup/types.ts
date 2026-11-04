export type AccountMode = 'create' | 'signin';

export interface AccountUrls {
    register: string;
    login: string;
    checkLinkupId: string;
    forgotPassword: string;
    terms: string;
    privacy: string;
    acceptableUse: string;
}

/** Where the LinkUp ID being typed stands. */
export type LinkupIdState = 'waiting' | 'short' | 'checking' | 'taken' | 'ok' | 'unknown';
