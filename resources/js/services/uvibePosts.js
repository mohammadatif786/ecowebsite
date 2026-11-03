/**
 * U Vibe post actions — the one place the website talks to the U Vibe post endpoints.
 *
 * These are the same endpoints the mobile app uses (web routes `new_frontend.uvibe.posts.*` and API
 * routes `regularUserAPI.uvibe.posts.*` point at UVibePostController), so every rule — who can edit,
 * what a Boost costs, who can pay a gig worker — is decided by the server, never by this file.
 */
import axios from 'axios';
import { notify } from './uvibe';

const url = (name, params) => route(`new_frontend.uvibe.posts.${name}`, params);

/** Edit your own post. `data`: {title, caption, price?, event_id?}. Resolves to the updated post. */
export const editPost = (id, data) => axios.put(url('update', { vibe: id }), data).then((r) => r.data);

export const deletePost = (id) => axios.delete(url('destroy', { vibe: id })).then((r) => r.data);

/** Hide someone else's post from your feed. */
export const hidePost = (id) => axios.post(url('hide', { vibe: id })).then((r) => r.data);

/** Report a post. `reason` is a key from the options' `report_reasons`. */
export const reportPost = (id, reason, notes) => axios.post(url('report', { vibe: id }), { reason, notes }).then((r) => r.data);

/** Repost onto your own campus. Resolves to {message, post, original}. */
export const repostPost = (id) => axios.post(url('repost', { vibe: id })).then((r) => r.data);

/** The Boosts you can send and your coin balance: {coins, boosts:[{key,label,emoji,coins}]}. */
export const fetchBoosts = () => axios.get(route('new_frontend.uvibe.boosts')).then((r) => r.data);

/** Send a Boost (coins only). Resolves to {message, coins, boost_coins}. */
export const sendBoost = (id, gift) => axios.post(url('boost', { vibe: id }), { gift }).then((r) => r.data);

/** Apply to a Campus Gig. Resolves to {already, message, status}. */
export const applyToGig = (id) => axios.post(url('apply', { vibe: id })).then((r) => r.data);

/** Who applied to your gig: {pay, applicants:[{id,user_id,name,avatar,status,applied,paid_at,paid_amount}]}. */
export const fetchApplicants = (id) => axios.get(url('applicants', { vibe: id })).then((r) => r.data);

/** Pay a worker the gig's pay from your wallet (needs your 4-digit transaction PIN). */
export const payApplicant = (id, applicationId, pin) =>
    axios.post(url('pay', { vibe: id, application: applicationId }), { pin }).then((r) => r.data);

/** Put a post's link on the clipboard and say so. */
export async function copyPostLink(post) {
    const link = post?.share_url;
    if (!link) return;
    try {
        await navigator.clipboard.writeText(link);
    } catch (e) {
        // Older browsers / insecure pages: fall back to a temporary input.
        const input = document.createElement('input');
        input.value = link;
        document.body.appendChild(input);
        input.select();
        try { document.execCommand('copy'); } catch (err) { /* nothing more to try */ }
        input.remove();
    }
    notify('🔗 Link copied');
}
