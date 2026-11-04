<script setup lang="ts">
import { onMounted, ref, onUnmounted, computed } from 'vue'

const props = defineProps<{
    open: boolean
}>();
const emit = defineEmits(['close', 'select']);
const wrapperRef = ref<HTMLElement | null>(null);

const EMOJI_CATEGORIES = {

    faces: {
        label: "😀",
        items: [
            "😀", "😁", "😄", "😆", "😅", "😂", "🤣", "🥹", "😭", "😢", "😮‍💨", "😤", "😡", "🤬", "😱", "😳", "🫣", "🤔", "🫠", "🙄",
            "😌", "😊", "😉", "😎", "🤩", "🥳", "😍", "🥰", "😘", "😋", "😛", "😜", "🤪", "😴", "🥱", "🤯", "😵", "🤒", "🤧"
        ]
    },

    hearts: {
        label: "❤️",
        items: [
            "❤️", "🩷", "🧡", "💛", "💚", "🩵", "💙", "💜", "🤍", "🤎", "🖤",
            "💖", "💗", "💓", "💞", "💕", "💘", "💝", "💟", "🫶", "💋"
        ]
    },

    hype: {
        label: "🔥",
        items: [
            "🔥", "💯", "✨", "⚡", "🌟", "⭐", "🎉", "🎊", "🎈", "🎁", "🎀", "💎", "👑", "🏆", "🪩", "🕺", "💃", "🎶", "🎧", "🎤",
            "🚀", "✅", "🟢", "🔔", "💥", "🌈", "☀️", "🌙"
        ]
    },

    gestures: {
        label: "🙌",
        items: [
            "🙏", "🙌", "👏", "🤝", "🫶", "💪", "👍", "👎", "👌", "✌️", "🤞", "🤟", "🤙", "🫡", "🫱", "🫲", "👐", "✋", "🖐️", "🤲"
        ]
    },

    style: {
        label: "👑",
        items: [
            "👑", "💄", "🧴", "🪞", "👗", "👠", "🧢", "🕶️", "👜", "💍", "📸", "🎥", "💅", "🧠", "📝", "📌", "🧿", "🛡️", "🔐", "🔑"
        ]
    },

    food: {

        label: "🍹",
        items: [
            "🍾", "🥂", "🍸", "🍹", "☕", "🧃", "🍔", "🍟", "🌭", "🍕", "🍿", "🍫", "🍪", "🍩", "🍓", "🍍", "🥭", "🍉", "🥑", "🍗"
        ]
    },

    travel: {

        label: "✈️",
        items: [
            "🏝️", "🌊", "✈️", "🗺️", "🧳", "🏟️", "🏖️", "🌅", "🌆", "🚗", "🚕", "🚆", "🛳️", "🛫", "🛬", "📍"
        ]
    },

    events: {

        label: "🎟️",
        items: [
            "🎟️", "🎫", "🎪", "🎭", "🎬", "🎤", "🎧", "🎶", "🎷", "🎺", "🥁", "🎹", "🎮", "📎", "📄", "📞", "📲", "💬", "🧾", "🧨"
        ]
    }
};
const active = ref<keyof typeof EMOJI_CATEGORIES>('faces');
const emojis = computed(() => EMOJI_CATEGORIES[active.value].items);

function selectEmoji(e: string) {
    emit('select', e);
    emit('close');
}

function onClickOutside(e: MouseEvent) {
    if (!props.open) return;
    if (wrapperRef.value && !wrapperRef.value.contains(e.target as Node)) {
        emit('close');
    }
}

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', onClickOutside);
});

</script>
<template>
    <div ref="wrapperRef" class="miniPop" :class="{ show: open }" aria-hidden="true">

        <div class="miniPopHead">

            <div class="miniPopTitle">Emojis</div>

            <button class="x" id="closeEmoji" aria-label="Close" @click="emit('close')">×</button>

        </div>

        <div class="miniPopBody">

            <div class="seg">
                <button v-for="(cat, key) in EMOJI_CATEGORIES" :key="key" :class="{ active: active === key }"
                    @click="active = key">
                    {{ cat.label }}
                </button>
            </div>

            <div class="emojiGrid">
                <button v-for="emoji in emojis" :key="emoji" class="emo" @click="selectEmoji(emoji)">
                    {{ emoji }}
                </button>
            </div>

        </div>

    </div>
</template>
<style scoped>
* {
    box-sizing: border-box
}

body {

    margin: 0;

    font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";

    background: radial-gradient(1200px 500px at 15% -10%, rgba(14, 165, 233, .18), transparent 60%),

        radial-gradient(900px 500px at 100% 0%, rgba(223, 255, 0, .18), transparent 55%),

        #f5f7fb;

    color: #0f172a;

}

a {
    color: inherit;
}



.app {
    max-width: 1020px;
    margin: 0 auto;
    padding: 14px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    gap: 12px;
}



/* Top bar */

.topbar {

    position: sticky;
    top: 10px;
    z-index: 30;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;

    padding: 12px 14px;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 16px;

    background: rgba(255, 255, 255, .78);
    backdrop-filter: blur(10px);

    box-shadow: 0 12px 26px rgba(2, 6, 23, .08);

}

.who {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.avatar {

    width: 44px;
    height: 44px;
    border-radius: 16px;

    background: linear-gradient(135deg, rgba(14, 165, 233, .95), rgba(223, 255, 0, .75));

    display: grid;
    place-items: center;
    font-weight: 950;

}

.meta {
    min-width: 0
}

.name {
    font-weight: 950;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.status {
    font-size: 12px;
    color: #64748b;
    font-weight: 800;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.topActions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.iconTop {

    width: 40px;
    height: 40px;
    border-radius: 14px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .85);

    display: grid;
    place-items: center;

    cursor: pointer;
    transition: .15s;

    box-shadow: 0 10px 18px rgba(2, 6, 23, .05);

    user-select: none;
    font-size: 18px;

}

.iconTop:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .95);
}

.pill {

    font-size: 12px;
    color: #64748b;

    border: 1px solid rgba(148, 163, 184, .35);
    padding: 6px 10px;
    border-radius: 999px;

    background: rgba(255, 255, 255, .85);
    white-space: nowrap;
    font-weight: 800;

}



/* Chat shell */

.chat {

    flex: 1;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 20px;

    background: rgba(255, 255, 255, .82);
    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);

    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 640px;

}

.msgs {
    flex: 1;
    padding: 16px;
    overflow: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bubbleRow {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.bubbleRow.me {
    justify-content: flex-end;
}

.bubble {

    max-width: 78%;

    border-radius: 18px;
    padding: 10px 12px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .9);

    box-shadow: 0 10px 18px rgba(2, 6, 23, .06);

}

.me .bubble {
    background: linear-gradient(180deg, rgba(14, 165, 233, .12), rgba(255, 255, 255, .92));
}

.text {
    font-size: 14px;
    line-height: 1.35;
    white-space: pre-wrap;
    word-break: break-word;
}

.time {
    margin-top: 6px;
    font-size: 11px;
    font-weight: 800;
    text-align: right;
}

.media {
    margin-top: 8px;
    display: grid;
    gap: 10px;
}



/* Attachment cards */

.attCard {

    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .95);

    padding: 10px;

    display: grid;
    gap: 10px;

}

.attTop {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.attTitle {
    font-weight: 950;
    font-size: 13px;
}

.attMeta {
    font-size: 12px;
    font-weight: 800;
}

.thumbImg {

    width: 100%;

    max-height: 280px;

    object-fit: cover;

    border-radius: 16px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: #fff;

}

.gifFrame {

    border-radius: 16px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: #fff;

    width: 100%;

    height: 220px;

}

.fileRow {

    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;

    padding: 10px;
    border-radius: 16px;
    border: 1px dashed rgba(148, 163, 184, .55);
    background: rgba(255, 255, 255, .86);

}

.fileName {
    font-weight: 950;
}

.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}



/* QR-first ticket card */

.ticket {

    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .95);

    padding: 12px;
    display: grid;
    gap: 12px;

}

.ticketTop {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.ticketTitle {
    font-weight: 950;
    font-size: 14px;
    margin: 0;
}

.ticketMeta {
    margin: 6px 0 0;
    font-size: 12px;
    line-height: 1.35;
    font-weight: 800;
}

.tag {
    font-size: 11px;
    font-weight: 950;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid rgba(14, 165, 233, .30);
    background: rgba(14, 165, 233, .10);
    white-space: nowrap;
}

.qrRow {
    display: flex;
    gap: 14px;
    align-items: center;
    flex-wrap: wrap;
}

.qrBig {

    width: 170px;
    height: 170px;
    border-radius: 18px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: #fff;
    display: grid;
    place-items: center;
    overflow: hidden;

    box-shadow: 0 12px 24px rgba(2, 6, 23, .08);

}

.ticketInfo {
    flex: 1;
    min-width: 260px;
    display: grid;
    gap: 8px;
}

.idLine {
    font-weight: 950;
    font-size: 13px;
    line-height: 1.25;
}

.subLine {
    font-weight: 800;
    font-size: 12px;
    line-height: 1.35;
}

.ticketBtns {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}



/* Composer */

.composer {

    border-top: 1px solid rgba(148, 163, 184, .25);

    padding: 12px;
    background: rgba(255, 255, 255, .92);

    display: grid;
    gap: 10px;

}

.tools {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}

.toolLeft {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.iconBtn {

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .86);

    border-radius: 14px;
    padding: 10px 12px;

    font-weight: 950;
    font-size: 13px;

    cursor: pointer;
    transition: .15s;

    display: inline-flex;
    gap: 8px;
    align-items: center;
    user-select: none;

}

.iconBtn:hover {
    background: rgba(255, 255, 255, .96)
}

.sendBtn {

    border: none;
    border-radius: 14px;
    padding: 12px 14px;
    background: #0ea5e9;

    font-weight: 950;
    font-size: 13px;
    cursor: pointer;
    box-shadow: 0 12px 22px rgba(14, 165, 233, .18);

}

.sendBtn:hover {
    filter: brightness(1.03)
}

.row2 {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

textarea {

    flex: 1;
    min-height: 46px;
    max-height: 160px;
    resize: none;

    border: 1px solid rgba(148, 163, 184, .45);

    border-radius: 14px;
    padding: 12px 12px;
    outline: none;
    font-size: 14px;
    background: rgba(255, 255, 255, .98);

    transition: .15s;

}

textarea:focus {
    border-color: rgba(14, 165, 233, .75);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .12)
}



/* Attachment tray (before sending) */

.tray {

    display: none;

    border-radius: 18px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);

    padding: 10px;

    gap: 10px;

}

.tray.show {
    display: grid;
}

.trayHead {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.trayTitle {
    font-weight: 950;
    font-size: 13px;
}

.trayList {
    display: grid;
    gap: 10px;
}

.trayItem {

    border-radius: 16px;

    border: 1px dashed rgba(148, 163, 184, .55);

    background: rgba(255, 255, 255, .86);

    padding: 10px;

    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;

}

.trayLeft {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.miniPreview {

    width: 54px;
    height: 54px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: #fff;
    display: grid;
    place-items: center;
    overflow: hidden;

}

.miniPreview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.removeBtn {

    border: none;
    cursor: pointer;

    border-radius: 12px;

    padding: 8px 10px;

    font-weight: 950;

    background: rgba(239, 68, 68, .10);

    border: 1px solid rgba(239, 68, 68, .25);

}

.removeBtn:hover {
    filter: brightness(1.02)
}



/* Overlay modal */

.overlay {
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 23, .48);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 80;
}

.overlay.show {
    display: flex;
}

.modal {

    width: min(960px, 100%);
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(10px);
    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);
    overflow: hidden;

}

.modalHead {

    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;

    padding: 14px 16px;
    border-bottom: 1px solid rgba(148, 163, 184, .25);
    background: rgba(255, 255, 255, .85);

}

.modalTitle {
    font-weight: 950;
    margin: 0;
    font-size: 16px;
    letter-spacing: -.2px;
}

.modalSub {
    margin: 6px 0 0;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.35;
}

.x {
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 22px;
    line-height: 1;
    padding: 6px 10px;
    opacity: .75;
}

.x:hover {
    opacity: 1
}

.modalBody {
    padding: 14px 16px 16px;
    display: grid;
    gap: 14px;
}

.grid2 {
    display: grid;
    grid-template-columns: 1.05fr .95fr;
    gap: 14px;
    align-items: start;
}

@media (max-width: 930px) {
    .grid2 {
        grid-template-columns: 1fr;
    }
}



.field {
    display: grid;
    gap: 8px;
}

.label {
    font-size: 12px;
    font-weight: 950;
}

.input {

    width: 100%;
    border: 1px solid rgba(148, 163, 184, .45);
    border-radius: 14px;
    padding: 10px 12px;
    outline: none;
    background: rgba(255, 255, 255, .98);
    font-size: 13px;

}

.input:focus {
    border-color: rgba(14, 165, 233, .75);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .12)
}

.notice {

    border-radius: 16px;
    padding: 10px 12px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .85);
    font-size: 12px;
    font-weight: 900;
    line-height: 1.35;
    display: none;

}

.notice.show {
    display: block;
}

.notice.ok {
    border-color: rgba(34, 197, 94, .35);
    background: rgba(34, 197, 94, .10);
    color: #14532d;
}

.notice.warn {
    border-color: rgba(245, 158, 11, .35);
    background: rgba(245, 158, 11, .12);
}

.notice.err {
    border-color: rgba(239, 68, 68, .35);
    background: rgba(239, 68, 68, .10);
}



/* Ticket picker */

.panel {

    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);

    padding: 12px;
    display: grid;
    gap: 10px;

}

.panelTop {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}

.kicker {
    font-size: 11px;
    font-weight: 950;
    letter-spacing: .2px;
    text-transform: uppercase;
}

.hint {
    font-size: 12px;
    font-weight: 800;
    line-height: 1.35;
}

.seg {

    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;

    border: 1px solid rgba(148, 163, 184, .35);
    background: rgba(255, 255, 255, .85);

    padding: 6px;
    border-radius: 999px;

}

.seg button {

    border: none;
    background: transparent;
    cursor: pointer;

    font-weight: 950;
    font-size: 12px;
    padding: 8px 10px;
    border-radius: 999px;

}

.seg button.active {

    background: rgba(14, 165, 233, .12);
    border: 1px solid rgba(14, 165, 233, .18);

}

.list {
    display: grid;
    gap: 10px;
}

.ticketRow {

    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .95);

    padding: 12px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px;
    align-items: center;

}

.ticketRow:hover {
    box-shadow: 0 14px 26px rgba(2, 6, 23, .06)
}

.rowTitle {
    font-weight: 950;
    margin: 0;
    font-size: 14px;
}

.rowMeta {
    margin: 6px 0 0;
    font-size: 12px;
    line-height: 1.35;
    font-weight: 800;
}

.rowRight {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.miniTag {

    font-size: 11px;
    font-weight: 950;
    padding: 6px 10px;
    border-radius: 999px;

    border: 1px solid rgba(148, 163, 184, .35);
    background: rgba(15, 23, 42, .04);
    white-space: nowrap;

}

.empty {

    padding: 14px;
    border-radius: 18px;
    border: 1px dashed rgba(148, 163, 184, .55);

    background: rgba(255, 255, 255, .86);
    font-weight: 850;
    font-size: 12px;
    line-height: 1.35;

}



/* Buttons */

.btn {

    border: none;
    cursor: pointer;
    border-radius: 14px;
    padding: 10px 12px;

    font-weight: 950;
    font-size: 13px;
    letter-spacing: .2px;
    transition: .15s;

    display: inline-flex;
    align-items: center;
    gap: 8px;

}

.btnPrimary {
    background: #0ea5e9;
    color: #fff;
    box-shadow: 0 12px 22px rgba(14, 165, 233, .18);
}

.btnPrimary:hover {
    filter: brightness(1.03)
}

.btnGhost {
    background: rgba(15, 23, 42, .04);
    border: 1px solid rgba(148, 163, 184, .35);
}

.btnGhost:hover {
    background: rgba(15, 23, 42, .06)
}

.btnDanger {
    background: rgba(239, 68, 68, .10);
    border: 1px solid rgba(239, 68, 68, .25);
}

.btnDanger:hover {
    filter: brightness(1.02)
}



/* Token box */

.tokenBox {

    border-radius: 16px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(2, 6, 23, .92);
    padding: 10px 12px;

    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;

    font-size: 12px;

    line-height: 1.35;

    white-space: pre-wrap;

    overflow: auto;

    max-height: 120px;

}



/* Emoji / GIF picker overlays (small) */

.miniPop {

    position: fixed;

    right: 16px;

    bottom: 110px;

    width: min(520px, calc(100vw - 32px));

    border-radius: 20px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);

    backdrop-filter: blur(10px);

    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);

    display: none;

    z-index: 75;

    overflow: hidden;

}

.miniPop.show {
    display: block;
}

.miniPopHead {

    padding: 12px 14px;

    border-bottom: 1px solid rgba(148, 163, 184, .25);

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;

    background: rgba(255, 255, 255, .85);

}

.miniPopTitle {
    font-weight: 950;
}

.miniPopBody {
    padding: 12px 14px;
    display: grid;
    gap: 10px;
    overflow-y: auto;
    max-height: 400px;
}

.emojiGrid {

    display: grid;

    grid-template-columns: repeat(10, 1fr);

    gap: 8px;

}

@media (max-width: 560px) {
    .emojiGrid {
        grid-template-columns: repeat(8, 1fr);
    }
}

.emo {

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .92);

    border-radius: 14px;

    height: 38px;

    display: grid;
    place-items: center;

    cursor: pointer;

    transition: .12s;

    user-select: none;

    font-size: 18px;

}

.emo:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .98);
}



.gifGrid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 10px;

}

@media (max-width: 560px) {
    .gifGrid {
        grid-template-columns: 1fr;
    }
}

.gifCard {

    border-radius: 18px;

    border: 1px solid rgba(148, 163, 184, .35);

    background: rgba(255, 255, 255, .95);

    overflow: hidden;

    cursor: pointer;

    transition: .12s;

}

.gifCard:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 22px rgba(2, 6, 23, .08);
}

.gifCard img {
    width: 100%;
    height: 170px;
    border: 0;
}

.gifMsg {
    max-width: 300px;
    height: auto;
}

.gifCap {
    padding: 10px;
    font-size: 12px;
    color: #64748b;
    font-weight: 900;
}

.sr {
    position: absolute;
    left: -9999px
}



/* Hidden file inputs */

input[type="file"] {
    display: none
}
.chat {
    display: flex;
    flex-direction: column;
    height: 100%;
}

</style>