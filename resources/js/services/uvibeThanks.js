/**
 * U Vibe "Say Thank You" — who sent you a gift or a Boost, and sending the thank-you.
 *
 * Same endpoints the mobile app uses (web routes `new_frontend.uvibe.thank-you.*`, API routes
 * `regularUserAPI.uvibe.thank-you.*`): the server decides who can be thanked.
 */
import axios from 'axios';

/** Who is waiting for a thank-you: {senders:[{user_id,name,avatar,gift,emoji,coins,count,time}], template, max}. */
export const fetchThankYous = () => axios.get(route('new_frontend.uvibe.thank-you.index')).then((r) => r.data);

/** Thank someone. Resolves to {message, senders} — `senders` is who is left. */
export const sendThankYou = (userId, message) =>
    axios.post(route('new_frontend.uvibe.thank-you.store'), { user_id: userId, message }).then((r) => r.data);
