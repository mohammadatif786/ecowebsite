// The windows a Vibes post opens (comments, Big Up, options, tagged item, Send a Vibe, reels) live
// once on the page, not once per post. The page hands them to every post through provide/inject;
// a post shown somewhere without them (e.g. the Home page) falls back to opening the post on Vibes.
import { inject, provide } from 'vue';
import { router } from '@inertiajs/vue3';

const KEY = Symbol('vibeActions');

const openOnVibes = (post) => {
    if (post?.id) router.visit(`/user/vibes?id=${post.id}`);
};

const fallback = {
    openComments: openOnVibes,
    openBigUp: openOnVibes,
    openOptions: openOnVibes,
    openTag: openOnVibes,
    sendVibe: () => router.visit('/user/vibes'),
    openReels: () => router.visit('/user/vibes'),
    openCreatorReels: () => router.visit('/user/vibes'),
    filterTag: (tag) => router.visit('/user/vibes?tag=' + encodeURIComponent(String(tag).replace(/^#/, ''))),
    openProfile: (userId) => userId && router.visit(`/user/vibes/profile/${userId}`),
};

export function provideVibeActions(actions) {
    provide(KEY, { ...fallback, ...actions });
}

export function useVibeActions() {
    return inject(KEY, fallback);
}

export function toast(message) {
    if (typeof window !== 'undefined' && window.toast) window.toast(message);
}
