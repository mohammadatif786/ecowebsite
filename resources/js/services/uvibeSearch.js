/**
 * U Vibe search — the one place the website asks the server for people and campuses.
 *
 * Same endpoint the mobile app uses (web route `user.uvibe.search`, API route
 * `regularUserAPI.uvibe.search`): who shows up and in what order is decided by the server.
 */
import axios from 'axios';

/**
 * People who post on U Vibe and campuses, for the text typed (empty = everyone who posts, A–Z).
 * Resolves to {q, people:[{user_id,name,handle,avatar,campus,posts,orgs}], campuses:[…]}.
 */
export const searchUVibe = (q = '') =>
    axios.get(route('user.uvibe.search'), { params: { q } }).then((r) => r.data);
