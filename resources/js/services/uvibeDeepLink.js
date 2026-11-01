import { nextTick } from 'vue';

/**
 * A shared U Vibe link ends in `?post=ID`. Open the feed on that post: show everything (so it isn't
 * filtered out), scroll to it and flash it. `posts` is the page's list (a ref).
 */
export async function focusLinkedPost({ posts, showEverything, notFound }) {
    const id = Number(new URLSearchParams(window.location.search).get('post'));
    if (!id) return;

    if (!(posts.value || []).some((p) => p.id === id)) {
        notFound?.();
        return;
    }

    showEverything?.();
    await nextTick();
    const el = document.getElementById(`uvpost-${id}`);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.classList.add('uv-flash');
    setTimeout(() => el.classList.remove('uv-flash'), 2400);
}
