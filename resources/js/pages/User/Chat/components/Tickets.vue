<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import QrcodeVue from 'qrcode.vue';

interface Ticket {
    ticket_sale_id: number;
    ticket_id: string;
    ticket_name: string;
    ticket_price: string;
    event: string;
    dateISO: string;
    dateLabel: string;
    venue: string;
    city: string;
    type: string;
    holder: string;
    sale_start: string;
    sale_end: string;
    available_qty: number;
    // Store all ticket and event data
    _ticketData?: any;
    _eventData?: any;
}

const emit = defineEmits<{
    sendTicket: [ticket: Ticket & { ticket_qty: number }];
}>();

const props = defineProps<{
    tickets: any[];
}>();

console.log('Tickets prop:', props.tickets);

const TICKET_DB = computed(() => {
    const db: Record<string, Ticket> = {};
    props.tickets?.forEach((t: any) => {
        const ticket_id = `LU-SALE-${t.id}`;
        const eventTitle = t.event?.title || 'Untitled Event';
        const ticketName = t?.ticket_name || t?.ticket?.name || 'Unnamed Ticket';
        const tickettype = t?.ticket_type || t?.ticket?.ticket_type || 'Ticket';
        const eventDate = t.event?.event_details?.single_event_date || t.event?.single_event_date || '2025-12-30T00:00:00';
        const dateObj = new Date(eventDate);
        const dateLabel = dateObj.toLocaleDateString() + ' • ' + dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        const venue = (t.event?.location || ((t.event?.country?.[0] || '') + (t.event?.state?.[0] || '') + ' ' + (t.event?.city || '')).trim()) || 'Unknown Venue';
        const city = t.event?.city || 'Unknown City';
        const ticketPrice = t?.stripe_price ? `USD ${t.stripe_price}` : (t?.total ? `USD ${t.total}` : 'Price not available');
        const saleStart = t.sale_start || '2025-01-01T00:00:00';
        const saleEnd = t.sale_end || '2025-12-31T23:59:59';
        db[ticket_id] = {
            ticket_sale_id: Number(t.id),
            ticket_id,
            ticket_name: ticketName || 'Unnamed Ticket',
            ticket_price: ticketPrice,
            event: eventTitle,
            dateISO: eventDate,
            dateLabel,
            venue,
            city,
            type: tickettype.length > 10 ? tickettype.substring(0, 10) + '...' : tickettype,
            holder: 'User',
            sale_start: saleStart,
            sale_end: saleEnd,
            available_qty: Number(t?.no_of_tickets ?? 0),
            // Store all ticket and event data
            _ticketData: t,
            _eventData: t.event
        };
    });
    return db;
});

const USER_TICKETS = computed(() => props.tickets?.map((t: any) => `LU-SALE-${t.id}`) || []);

const modalVisible = ref(false);
const modalState = reactive({
    ticket: null as Ticket | null,
    token: '',
    qty: 1,
    filter: 'all',
    query: '',
    selectedTicketIds: [] as string[]
});
const notice = reactive({ type: '', msg: '' });
const qrValue = ref('');
const ticketIdInput = ref('');
const qrPaste = ref('');

function fetchTicket(ticketId: string): Promise<Ticket | null> {
    return new Promise((resolve) => setTimeout(() => resolve(TICKET_DB.value[ticketId] || null), 180));
}

function isUpcoming(t: Ticket) {
    const now = new Date();
    const start = new Date(t.sale_start);
    return start > now;
}

function ticketMatchesQuery(t: Ticket, q: string) {
    if (!q) return true;
    const hay = `${t.ticket_id} ${t.event} ${t.venue} ${t.city} ${t.type} ${t.dateLabel}`.toLowerCase();
    return hay.includes(q.toLowerCase());
}

const filteredTickets = computed(() => {
    return USER_TICKETS.value
        .map(id => TICKET_DB.value[id])
        .filter(Boolean)
        .filter(t => {
            if (modalState.filter === 'upcoming') return isUpcoming(t!);
            if (modalState.filter === 'past') return !isUpcoming(t!);
            return true;
        })
        .filter(t => ticketMatchesQuery(t!, modalState.query))
        .sort((a, b) => Date.parse(a!.dateISO) - Date.parse(b!.dateISO));
});

function openModal() {
    modalVisible.value = true;
    modalState.ticket = null;
    modalState.token = '';
    modalState.query = '';
    modalState.filter = 'all';
    modalState.selectedTicketIds = [];
    notice.msg = '';
    ticketIdInput.value = '';
    qrPaste.value = '';
}

function closeModal() {
    modalVisible.value = false;
}

function setNotice(type: string, msg: string) {
    notice.type = type;
    notice.msg = msg;
}

function makeSignedDemoToken(ticketId: string) {
    const header = { alg: 'DEMO-HMAC', typ: 'LUTK' };
    const exp = Date.now() + (15 * 60 * 1000);
    const payload = { ticket_id: ticketId, exp, nonce: Math.random().toString(16).slice(2) };
    const h = btoa(JSON.stringify(header)).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
    const p = btoa(JSON.stringify(payload)).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
    const demoSecret = 'LINKUP_DEMO_SECRET_DO_NOT_USE_IN_PROD';
    const sig = (h + '.' + p + '.' + demoSecret).split('').reduce((a, b) => {
        a = ((a << 5) - a) + b.charCodeAt(0);
        return a & a;
    }, 0).toString(16).padStart(8, '0');
    return `${h}.${p}.${sig}`;
}

function extractTicketIdFromPastedQr(raw: string) {
    try {
        const obj = JSON.parse(raw);
        if (obj && typeof obj === 'object' && obj.kind === 'linkup_ticket_token' && obj.token) {
            const parts = obj.token.split('.');
            if (parts.length === 3) {
                const payload = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')));
                return payload.ticket_id;
            }
        }
    } catch (e) {}
    return null;
}

async function selectTicket(t: Ticket) {
    setNotice('ok', 'Loading preview…');
    const ticket = await fetchTicket(t.ticket_id);
    if (!ticket) {
        setNotice('err', 'Could not load ticket.');
        modalState.ticket = null;
        modalState.token = '';
        return;
    }
    modalState.ticket = ticket;
    modalState.qty = 1;
    modalState.token = String(ticket?._ticketData?.ticket_qrcode_id || '');
    setNotice('ok', 'Preview ready ✅');
    qrValue.value = modalState.token ? JSON.stringify({ kind: 'linkup_ticket_token', token: modalState.token }) : '';

    // Automatically add to selection if not already there
    if (!modalState.selectedTicketIds.includes(t.ticket_id)) {
        modalState.selectedTicketIds.push(t.ticket_id);
    }
}

function toggleTicketSelection(ticketId: string) {
    const idx = modalState.selectedTicketIds.indexOf(ticketId);
    if (idx > -1) {
        modalState.selectedTicketIds.splice(idx, 1);
    } else {
        modalState.selectedTicketIds.push(ticketId);
    }
}

async function fetchById() {
    setNotice('','');
    const id = ticketIdInput.value.trim().toUpperCase();
    if (!id) {
        setNotice('warn', 'Enter a Ticket ID.');
        return;
    }
    setNotice('ok', 'Fetching…');
    const ticket = await fetchTicket(id);
    if (!ticket) {
        setNotice('err', 'Ticket not found.');
        modalState.ticket = null;
        modalState.token = '';
        qrValue.value = '';
        return;
    }
    const token = makeSignedDemoToken(ticket.ticket_id);
    modalState.ticket = ticket;
    modalState.token = token;
    setNotice('ok', 'Preview ready ✅');
    qrValue.value = JSON.stringify({ kind: 'linkup_ticket_token', token });
}

function extractFromQr() {
    setNotice('','');
    const raw = qrPaste.value.trim();
    if (!raw) {
        setNotice('warn', 'Paste a QR payload JSON first.');
        return;
    }
    const id = extractTicketIdFromPastedQr(raw);
    if (!id) {
        setNotice('err', 'Could not extract Ticket ID.');
        return;
    }
    ticketIdInput.value = id;
    setNotice('ok', `Extracted: ${id}`);
}

function useSample() {
    ticketIdInput.value = 'LU-TKT-9F3K2A';
    setNotice('ok', 'Sample ID inserted.');
}

function regenerate() {
    if (!modalState.ticket) {
        setNotice('warn', 'Select a ticket first.');
        return;
    }
    modalState.token = String(modalState.ticket?._ticketData?.ticket_qrcode_id || '');
    qrValue.value = modalState.token ? JSON.stringify({ kind: 'linkup_ticket_token', token: modalState.token }) : '';
    setNotice('ok', 'Ready ✅');
}

async function copyId() {
    if (!modalState.ticket) {
        setNotice('warn', 'Select a ticket first.');
        return;
    }
    try {
        await navigator.clipboard.writeText(modalState.ticket.ticket_id);
        setNotice('ok', 'Ticket ID copied ✅');
    } catch (e) {
        setNotice('err', 'Copy failed.');
    }
}

async function copyToken() {
    if (!modalState.token) {
        setNotice('warn', 'No token yet.');
        return;
    }
    try {
        await navigator.clipboard.writeText(modalState.token);
        setNotice('ok', 'Token copied ✅');
    } catch (e) {
        setNotice('err', 'Copy failed.');
    }
}

function sendTicket() {
    // Guard: Prevent execution if disabled logic applies
    if (!modalState.ticket && modalState.selectedTicketIds.length === 0) return;

    if (modalState.selectedTicketIds.length > 0) {
        modalState.selectedTicketIds.forEach(id => {
            const ticket = TICKET_DB.value[id];
            if (ticket) {
                const maxQty = Number(ticket.available_qty ?? 0);
                const qty = Math.max(1, Math.min(Number(modalState.qty ?? 1), maxQty || 1));
                emit('sendTicket', {
                    ...ticket,
                    ticket_qty: qty,
                    _ticketData: ticket._ticketData,
                    _eventData: ticket._eventData
                });
            }
        });
        closeModal();
        return;
    }

    if (!modalState.ticket || !modalState.token) {
        setNotice('warn', 'Select a ticket first.');
        return;
    }

    const maxQty = Number(modalState.ticket.available_qty ?? 0);
    const qty = Math.max(1, Math.min(Number(modalState.qty ?? 1), maxQty || 1));
    emit('sendTicket', {
        ...modalState.ticket,
        ticket_qty: qty,
        _ticketData: modalState.ticket._ticketData,
        _eventData: modalState.ticket._eventData
    });
    closeModal();
}

defineExpose({ openModal });

</script>

<template>
    <div v-if="modalVisible" class="overlay" :class="{ show: modalVisible }" role="dialog" aria-modal="true">
        <div class="modal">
            <div class="modalHead">
                <div>
                    <p class="modalTitle">Share a Ticket</p>
                    <p class="modalSub">
                        Search your <b>My Tickets</b> → select → preview → send.
                        QR payload is a <b>signed token</b> (simulated).
                    </p>
                </div>
                <button class="x" @click="closeModal">×</button>
            </div>

            <div class="modalBody" style="overflow-y: auto; max-height: 70vh;">
                <div class="grid2">
                    <!-- Left: My Tickets -->
                    <div class="panel">
                        <div class="panelTop">
                            <div>
                                <div class="kicker">Ticket section</div>
                                <div style="font-weight:950;">My Tickets</div>
                                <div class="hint">Search by event name, venue, date, city, type…</div>
                            </div>
                            <div class="seg" title="Filter tickets">
                                <button id="segAll" :class="{ active: modalState.filter === 'all' }"
                                    @click="modalState.filter = 'all'">All</button>
                                <button id="segUpcoming" :class="{ active: modalState.filter === 'upcoming' }"
                                    @click="modalState.filter = 'upcoming'">Upcoming</button>
                                <button id="segPast" :class="{ active: modalState.filter === 'past' }"
                                    @click="modalState.filter = 'past'">Past</button>
                            </div>
                        </div>

                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                            <input id="ticketSearch" class="input" placeholder="Search tickets…" v-model="modalState.query" />
                            <button id="clearSearchBtn" class="btn btnGhost" @click="modalState.query = ''">Clear</button>
                        </div>

                        <div class="list" id="ticketList">
                            <div v-if="filteredTickets.length === 0" class="empty">
                                No tickets found. Try: <b>Nassau</b>, <b>VIP</b>, <b>Toronto</b>, <b>Friday</b>.
                            </div>
                            <div v-for="t in filteredTickets" :key="t.ticket_id" 
                                 class="ticketRow" 
                                 :class="{ selected: modalState.selectedTicketIds.includes(t.ticket_id) }"
                                 @click="toggleTicketSelection(t.ticket_id)">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <input type="checkbox" 
                                           :checked="modalState.selectedTicketIds.includes(t.ticket_id)"
                                           @click.stop="toggleTicketSelection(t.ticket_id)" />
                                    <div>
                                        <p class="rowTitle">🎟️ {{ t.ticket_name }}</p>
                                        <p class="rowMeta">🗓️ {{ t.dateLabel }} • 📍 {{ t.venue }} • <span class="mono">{{
                                                t.ticket_id }}</span></p>
                                    </div>
                                </div>
                                <div class="rowRight">
                                    <div class="miniTag">{{ isUpcoming(t) ? 'Upcoming' : 'Past' }}</div>
                                    <div class="miniTag">{{ t.type }}</div>
                                    <button class="btn btnPrimary" @click.stop="selectTicket(t)">Preview →</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Preview -->
                    <div class="panel">
                        <div class="panelTop">
                            <div>
                                <div class="kicker">Preview & send</div>
                                <div style="font-weight:950;">Ticket Preview (QR-first)</div>
                                <div class="hint">Select a ticket → preview QR → send to chat.</div>
                            </div>
                            <div class="miniTag" id="previewTag">{{ modalState.ticket ? 'Selected ✅' : 'No selection' }}</div>
                        </div>

                        <div class="notice" id="notice" :class="notice.type">{{ notice.msg }}</div>

                        <div v-if="!modalState.ticket" id="previewEmpty" class="empty">Select a ticket to preview the QR ✅</div>

                        <div v-else id="previewBody" style="display:grid; gap:12px;">
                            <div class="previewCard">
                                <div class="pcTop">
                                    <div>
                                        <div class="pcTitle">{{ modalState.ticket.ticket_name }}</div>
                                        <div class="pcSub">{{ modalState.ticket.event }} • {{ modalState.ticket.dateLabel }}</div>
                                        <button id="copyIdBtn" class="btn btnGhost" @click="copyId">📋 Copy ID</button>
                                        <button id="copyTokenBtn" class="btn btnGhost" @click="copyToken">📋 Copy Token</button>
                                    </div>
                                </div>
                            </div>
                            <div class="kicker">Signed token</div>
                            <div id="tokenBox" class="tokenBox">{{ modalState.token }}</div>
                        </div>

                        <div style="border-top:1px solid rgba(148,163,184,.25);padding-top:12px;margin-top:12px;">
                            <div class="field">
                                <label class="label">Or enter Ticket ID manually</label>
                                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                    <input id="ticketIdInput" class="input" placeholder="e.g. LU-TKT-9F3K2A" v-model="ticketIdInput" />
                                    <button id="useSampleBtn" class="btn btnGhost" @click="useSample">Use Sample</button>
                                    <button id="fetchByIdBtn" class="btn btnPrimary" @click="fetchById">Fetch</button>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;margin-top:12px;">
                            <div v-if="modalState.ticket" style="display:flex;align-items:center;gap:8px;">
                                <span class="miniTag">Available: {{ modalState.ticket.available_qty }}</span>
                                <input type="number" class="input" style="width:90px; padding:6px 10px;"
                                    v-model.number="modalState.qty" :min="1"
                                    :max="Number(modalState.ticket.available_qty || 1)" />
                            </div>
                            <button id="sendFinalBtn" class="btn btnPrimary" @click="sendTicket" 
                                    :disabled="(modalState.selectedTicketIds.length === 0 && (!modalState.ticket || !modalState.token))">
                                {{ modalState.selectedTicketIds.length > 1 
                                    ? `Send ${modalState.selectedTicketIds.length} Tickets` 
                                    : 'Send to Chat' }}
                            </button>
                        </div>
                    </div>
                </div>
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

    width: min(1100px, 100%);
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
    cursor: pointer;
    transition: all 0.2s ease;

}

.ticketRow.selected {
    border-color: #0ea5e9;
    background: rgba(14, 165, 233, 0.05);
    box-shadow: 0 0 0 1px #0ea5e9;
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

.btnPrimary:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    opacity: 0.6;
    filter: grayscale(0.2);
    box-shadow: none;
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
    color: #fff;
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