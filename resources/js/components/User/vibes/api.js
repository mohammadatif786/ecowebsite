// Calls the Vibes posts make. Each returns the server's answer; the caller updates the post.
import axios from 'axios';

export const likePost = (id) => axios.post(route('user.vibes.like', { vibe: id })).then((r) => r.data);
export const sharePost = (id) => axios.post(route('user.vibes.share', { vibe: id })).then((r) => r.data);
export const repostPost = (id) => axios.post(route('user.vibes.repost', { vibe: id })).then((r) => r.data);

export const likeReel = (id) => axios.post(route('user.reels.like', { reel: id })).then((r) => r.data);
export const shareReel = (id) => axios.post(route('user.reels.share', { reel: id })).then((r) => r.data);
export const saveReel = (id) => axios.post(route('user.reels.save', { reel: id })).then((r) => r.data);
export const deleteReel = (id) => axios.delete(route('user.reels.destroy', { reel: id })).then((r) => r.data);
export const creatorReels = (userId, isUvibe = false) => axios.get(route('user.reels.user', { user: userId }), { params: { is_uvibe: isUvibe ? 1 : 0 } }).then((r) => r.data);

export const toggleFollow = (userId) => axios.post(route('user.creators.toggle-follow', { user: userId })).then((r) => r.data);

/** Comments work the same on posts and reels; only the routes differ. */
export const comments = {
    vibe: {
        list: (id) => axios.get(route('user.vibes.comments.index', { vibe: id })).then((r) => r.data.data || []),
        top: (id) => axios.get(route('user.vibes.top-comments', { vibe: id })).then((r) => r.data || []),
        add: (id, comment, parentId) => axios.post(route('user.vibes.comments.store', { vibe: id }), { comment, parent_id: parentId || null }).then((r) => r.data),
        like: (commentId) => axios.post(route('user.vibes.comments.like', { comment: commentId })).then((r) => r.data),
        remove: (commentId) => axios.delete(route('user.vibes.comments.destroy', { comment: commentId })).then((r) => r.data),
    },
    reel: {
        list: (id) => axios.get(route('user.reels.comments.index', { reel: id })).then((r) => r.data.comments || []),
        add: (id, comment, parentId) => axios.post(route('user.reels.comments.store', { reel: id }), { comment, parent_id: parentId || null }).then((r) => r.data.comment),
        like: (commentId) => axios.post(route('user.reels.comments.like', { comment: commentId })).then((r) => r.data),
        remove: (commentId) => axios.delete(route('user.reels.comments.destroy', { comment: commentId })).then((r) => r.data),
    },
};
