export type Region = 'Caribbean' | 'Latin America' | 'Global';
export type StoryStatus = 'Published' | 'Review' | 'Scheduled' | 'Draft';
export type AdFormat = 'image' | 'video' | 'audio';

export type Story = {
    id: string;
    title: string;
    summary: string;
    body: string;
    country: string;
    countryCode?: string;
    flag: string;
    category: string;
    region: Region;
    hours: number;
    fire: number;
    breaking: boolean;
    breakingExpiresAt?: string | null;
    status: StoryStatus;
    author: string;
    source: string;
    sourceType?: string;
    publishedAt?: string;
    views: number;
    img: string;
    mediaVideo?: string;
    mediaAudio?: string;
    media?: Array<{
        type?: string;
        name?: string;
        dataUrl?: string;
        url?: string;
        size?: number;
    }>;
};

export type SponsorAd = {
    id: string;
    sponsor: string;
    title: string;
    body: string;
    format: AdFormat;
    media: string;
    cta: string;
    package: string;
    target: string;
    targetType: 'story' | 'region' | 'category' | 'all';
    status: 'Active' | 'Scheduled' | 'Paused';
};

const CATEGORY_STYLES: Record<string, string> = {
    'Breaking News': 'bg-rose-50 text-rose-700',
    Politics: 'bg-slate-100 text-slate-700',
    'Business & Economy': 'bg-emerald-50 text-emerald-700',
    Entertainment: 'bg-fuchsia-50 text-fuchsia-700',
    Sports: 'bg-orange-50 text-orange-700',
    Culture: 'bg-violet-50 text-violet-700',
    Technology: 'bg-sky-50 text-sky-700',
    Faith: 'bg-amber-50 text-amber-700',
    Events: 'bg-cyan-50 text-cyan-700',
    Travel: 'bg-teal-50 text-teal-700',
    Environment: 'bg-green-50 text-green-700',
};

export const categoryStyle = (category: string) => CATEGORY_STYLES[category] || 'bg-slate-100 text-slate-700';

export const numFmt = (value: number) => (value >= 1000 ? `${(value / 1000).toFixed(value >= 100000 ? 0 : 1)}K` : `${value}`);

export const relTime = (hours: number) => (hours >= 720 ? `${Math.round(hours / 720)}mo ago` : hours >= 24 ? `${Math.round(hours / 24)}d ago` : `${hours}h ago`);

export const readTimeFor = (story: Pick<Story, 'summary' | 'body'>) =>
    `${Math.max(1, Math.round(`${story.summary} ${story.body}`.trim().split(/\s+/).length / 200))} min read`;
