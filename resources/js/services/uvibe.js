/**
 * U Vibe — the one place the website talks to the U Vibe backend.
 *
 * These are the same endpoints the mobile app uses (the web routes `new_frontend.uvibe.*` and
 * the API routes `regularUserAPI.uvibe.*` point at the same controllers), so a post made here
 * is validated and saved by exactly the same code as a post made in the app.
 */
import axios from 'axios';

/** Look-and-feel choices (post types, organization types, text-card looks, limits). */
export const fetchOptions = () => axios.get(route('new_frontend.uvibe.options')).then((r) => r.data);

/** The organizations this member has added to post for. */
export const fetchOrganizations = () =>
    axios.get(route('new_frontend.uvibe.organizations.index')).then((r) => r.data.organizations);

/** Add an organization. Adding the same name again returns the existing one (`created: false`). */
export const addOrganization = ({ name, orgType }) =>
    axios
        .post(route('new_frontend.uvibe.organizations.store'), { name, org_type: orgType })
        .then((r) => r.data);

/** Publish a post. `form` is a FormData built by `buildPostForm`. */
export const createPost = (form) =>
    axios
        .post(route('new_frontend.uvibe.posts.store'), form, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then((r) => r.data);

/**
 * Turn what the member filled in into the request the backend expects.
 *
 * @param {object} s
 *   publisher   {kind:'me'|'university'|'organizer'|'custom', id?:number, role?:string}
 *   userId, universityId, campus, category, title, caption, price, allowBoosts, crossPost,
 *   textStyle   {pal,font,motion,sticker} | null   (a text card; null for a photo post)
 *   photos      [{file}]
 *   productId, eventIds
 */
export function buildPostForm(s) {
    const f = new FormData();
    const p = s.publisher || { kind: 'me' };

    if (p.kind === 'organizer') {
        f.append('publisher_type', 'organization');
        f.append('publisher_id', p.id);
    } else if (p.kind === 'custom') {
        f.append('publisher_type', 'custom_publisher');
        f.append('publisher_id', p.id);
    } else {
        // "Myself" and "the university itself" are both published by you; the university flag
        // tells the backend to show the school as the poster.
        f.append('publisher_type', 'user');
        f.append('publisher_id', s.userId);
    }
    if (p.kind === 'university') f.append('as_university', '1');
    if (p.kind !== 'me' && p.role) f.append('posted_role', p.role);

    f.append('is_uvibe', '1');
    f.append('title', s.title);
    f.append('caption', s.caption);
    f.append('category', s.category);
    f.append('university_id', s.universityId);
    if (s.campus) f.append('campus', s.campus);
    f.append('price', Number(s.price) || 0);
    f.append('allow_coin_gifts', s.allowBoosts ? '1' : '0');
    f.append('show_on_vibes', s.crossPost ? '1' : '0'); // "Also post on Vibes"

    if (s.textStyle) {
        f.append('text_style[pal]', s.textStyle.pal);
        f.append('text_style[font]', s.textStyle.font);
        f.append('text_style[motion]', s.textStyle.motion);
        if (s.textStyle.sticker) f.append('text_style[sticker]', s.textStyle.sticker);
    } else {
        (s.photos || []).forEach((m, i) => {
            f.append(`media[${i}]`, m.file);
            f.append(`media_sources[${i}]`, 'gallery');
        });
    }

    if (s.productId) f.append('product_ids[0]', s.productId);
    [...new Set((s.eventIds || []).filter(Boolean).map(Number))].forEach((id, i) => f.append(`event_ids[${i}]`, id));

    return f;
}

/** The first thing worth telling the member from a failed request. */
export function firstError(e, fallback = 'Something went wrong') {
    const errors = e?.response?.data?.errors;
    if (errors) {
        const first = Object.values(errors).flat()[0];
        if (first) return first;
    }
    return e?.response?.data?.message || fallback;
}

/** A small message at the top of the screen (the site's shared toast). */
export const notify = (msg) => {
    if (typeof window !== 'undefined' && window.toast) window.toast(msg);
};
