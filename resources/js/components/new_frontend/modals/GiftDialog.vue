<template>
  <!-- FX Layer for flying emoji animation -->
  <div class="lk-fx-layer" ref="fxLayer"></div>

  <!-- ═══ SEND VIBE MODAL ═══ -->
  <Teleport to="body">
    <Transition name="lk-fade">
      <div v-if="isOpen" class="lk-overlay" @click.self="close">
        <div class="lk-modal" role="dialog" aria-modal="true" aria-label="Send a Vibe">

          <!-- Header -->
          <div class="lk-modal-header">
            <div class="flex items-center gap-3">
              <div class="lk-title-icon">🌹</div>
              <div>
                <p class="lk-title">Send a Vibe</p>
                <p class="lk-subtitle">See someone you like? Send a gift to break the ice.</p>
              </div>
            </div>
            <button class="lk-x-btn" @click="close" aria-label="Close">✕</button>
          </div>

          <!-- Body -->
          <div class="lk-modal-body">
            <!-- Coin balance + hint -->
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
              <div class="lk-coin-pill">
                <span class="lk-gem"></span>
                <span>{{ (coinBalance || 0).toLocaleString() }}</span> Coins
              </div>
              <span class="text-sm font-black text-slate-500">
                {{ selectedGift ? 'Ready to send ✓' : 'Pick a gift to continue' }}
              </span>
            </div>

            <!-- To user strip -->
            <div v-if="currentRecipient" class="lk-to-strip mb-4">
              <img :src="recipientAvatar" class="h-8 w-8 rounded-full object-cover" />
              <span>Sending to <strong>{{ currentRecipient.name }}</strong></span>
            </div>

            <!-- Gift grid -->
            <div class="lk-gift-grid">
              <div
                v-for="gift in GIFTS"
                :key="gift.id"
                class="lk-gift-card"
                :class="{ selected: selectedGift?.id === gift.id }"
                @click="selectGift(gift)"
              >
                <div class="flex gap-2.5 items-start">
                  <div class="lk-gift-icon" :class="gift.cls">{{ gift.emoji }}</div>
                  <div class="min-w-0 pt-0.5">
                    <p class="font-black text-sm leading-tight">{{ gift.name }}</p>
                    <p class="text-slate-500 text-[11px] mt-0.5 leading-snug">{{ gift.desc }}</p>
                  </div>
                </div>
                <div v-if="gift.cost === 0" class="lk-free-tag">Free</div>
                <div v-else class="lk-price-tag">
                  <span class="lk-gem sm"></span>{{ gift.cost.toLocaleString() }}
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="lk-modal-footer">
            <button
              class="lk-send-btn"
              :disabled="!selectedGift || sending"
              @click="handleSend"
            >
              <span v-if="sending">Sending…</span>
              <span v-else>Send Vibe 💌</span>
            </button>
            <div class="flex justify-between text-[11px] font-black text-slate-400 mt-2 flex-wrap gap-1">
              <span>Selected: {{ selectedGift ? `${selectedGift.emoji} ${selectedGift.name}` : '—' }}</span>
              <span>Cost: {{ selectedGift ? (selectedGift.cost === 0 ? 'Free' : selectedGift.cost.toLocaleString() + ' coins') : '—' }}</span>
              <span>After: {{ selectedGift != null ? (coinBalance - selectedGift.cost >= 0 ? (coinBalance - selectedGift.cost).toLocaleString() : '⚠ Insufficient') : '—' }}</span>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- ═══ RECEIVED VIBE POPUP ═══ -->
  <Teleport to="body">
    <Transition name="lk-rx-fade">
      <div v-if="showReceived" class="lk-rx-overlay" @click.self="dismissReceived">
        <div class="lk-rx-card">
          <!-- Confetti -->
          <div class="lk-confetti" ref="confettiEl"></div>

          <!-- Top bar -->
          <div class="lk-rx-top">
            <span class="font-black text-lg">Someone likes your vibe 💘</span>
            <button class="lk-x-btn" @click="dismissReceived">✕</button>
          </div>

          <!-- Body -->
          <div class="lk-rx-body">
            <div class="text-7xl filter-emoji-drop" style="animation: lkPopIn 0.5s ease both">
              {{ receivedGift?.emoji || '🎁' }}
            </div>
            <div class="text-2xl mt-2" style="animation: lkHeartPulse 0.9s ease-in-out infinite">💖</div>
            <p class="font-black text-lg mt-3">You just received a vibe!</p>
            <p class="text-slate-500 text-sm mt-1 leading-snug max-w-xs mx-auto">
              That's a clear sign they're interested. Want to reply?
            </p>
            <div class="lk-rx-from mt-4">
              <img :src="receivedFromAvatar" class="h-8 w-8 rounded-full object-cover" />
              <span>From: <strong>{{ receivedGift?.senderName || 'Someone' }}</strong></span>
            </div>
            <!-- Gift info chip -->
            <div class="mt-3 inline-flex items-center gap-2 bg-slate-100 rounded-full px-4 py-2 text-sm font-black">
              <span>{{ receivedGift?.emoji }}</span>
              <span>{{ receivedGift?.name }}</span>
              <span class="text-slate-400">·</span>
              <span class="lk-gem sm inline-block"></span>
              <span>{{ (receivedGift?.coins || 0).toLocaleString() }} coins</span>
            </div>
            <div class="flex gap-3 justify-center mt-5">
              <button class="lk-btn-primary" @click="replyToVibe">Reply 💬</button>
              <button class="lk-btn-ghost" @click="dismissReceived">Later</button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Toast -->
  <Teleport to="body">
    <Transition name="lk-toast-fade">
      <div v-if="toastMsg" class="lk-toast">{{ toastMsg }}</div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: { type: Object, default: null },
  balance: { type: Number, default: 0 },
});

// ── State ──────────────────────────────────────────────
const isOpen       = ref(false);
const sending      = ref(false);
const selectedGift = ref(null);
const coinBalance  = ref(props.balance);
const currentRecipient = ref(props.user);

const showReceived  = ref(false);
const receivedGift  = ref(null);
const seenGiftIds   = ref(new Set());
let   pollTimer     = null;

const toastMsg  = ref('');
const fxLayer   = ref(null);
const confettiEl = ref(null);

// ── Gift catalogue (same as original) ──────────────────
const GIFTS = [
  { id: 'hola',     name: 'Hola',           desc: 'A sweet hello to start.',       emoji: '👋',  cost: 0,   cls: 'lime'   },
  { id: 'smile',    name: 'Island Smile',    desc: 'A warm little signal.',          emoji: '😊',  cost: 10,  cls: 'lime'   },
  { id: 'music',    name: 'Soca Vibe',       desc: 'Because the rhythm matters.',    emoji: '🎶',  cost: 35,  cls: 'purple' },
  { id: 'dance',    name: 'Dance Move',      desc: 'Fun energy—no pressure.',        emoji: '💃',  cost: 50,  cls: 'purple' },
  { id: 'sunset',   name: 'Sunset',          desc: 'Soft romantic mood.',            emoji: '🌅',  cost: 75,  cls: 'pink'   },
  { id: 'coconut',  name: 'Coconut Drink',   desc: 'Cheers from the islands.',       emoji: '🥥',  cost: 90,  cls: 'lime'   },
  { id: 'rose',     name: 'Rose',            desc: 'Classic, respectful romance.',   emoji: '🌹',  cost: 120, cls: 'pink'   },
  { id: 'kissair',  name: 'Blown Kiss',      desc: 'Flirty, but still classy.',      emoji: '😘',  cost: 150, cls: 'pink'   },
  { id: 'candles',  name: 'Date Night',      desc: "A clear 'I'm interested.'",      emoji: '🕯️', cost: 220, cls: 'pink'   },
  { id: 'beach',    name: 'Beach LinkUp',    desc: "Let's link up by the water.",    emoji: '🏝️', cost: 250, cls: 'lime'   },
  { id: 'heart',    name: 'Heart Glow',      desc: "A louder 'I like you.'",         emoji: '💖',  cost: 300, cls: 'pink'   },
  { id: 'crown',    name: 'Caribbean Crown', desc: 'Big vibe, big respect.',         emoji: '👑',  cost: 400, cls: 'lime'   },
];

// ── Computed ───────────────────────────────────────────
const recipientAvatar = computed(() => {
  const a = currentRecipient.value?.avatar || currentRecipient.value?.img;
  if (!a) return `https://i.pravatar.cc/100?img=${currentRecipient.value?.id % 70 || 1}`;
  return a.startsWith('http') ? a : `/storage/${a}`;
});

const receivedFromAvatar = computed(() => {
  const a = receivedGift.value?.senderAvatar;
  if (!a) return `https://i.pravatar.cc/100?img=5`;
  return a.startsWith('http') ? a : `/storage/${a}`;
});

// ── Public API ─────────────────────────────────────────
const open = (recipient) => {
  if (recipient) currentRecipient.value = recipient;
  selectedGift.value = null;
  isOpen.value = true;
};
const close = () => { isOpen.value = false; selectedGift.value = null; };

// ── Gift actions ───────────────────────────────────────
const selectGift = (gift) => { selectedGift.value = gift; };

const handleSend = async () => {
  if (!selectedGift.value) return toast('Please pick a gift first');
  if (!currentRecipient.value?.id) return toast('No recipient selected');
  if (coinBalance.value < selectedGift.value.cost) return toast('⚠ Not enough coins');

  sending.value = true;
  try {
    await axios.post(route('frontend.gift.coins.store'), {
      receiver_id: currentRecipient.value.id,
      gift_name: selectedGift.value.name,
      coins: selectedGift.value.cost,
    });

    playSendFX(selectedGift.value.emoji);
    coinBalance.value = Math.max(0, coinBalance.value - selectedGift.value.cost);
    toast(`${selectedGift.value.emoji} Vibe sent to ${currentRecipient.value.name}!`);
    close();
  } catch (e) {
    const msg = e.response?.data?.message || 'Could not send gift. Try again.';
    toast(`⚠ ${msg}`);
  } finally {
    sending.value = false;
  }
};

// ── Received gifts polling ─────────────────────────────
const pollReceivedGifts = async () => {
  try {
    const { data } = await axios.get('/api/user/received-gifts');
    const gifts = data?.gifts ?? [];
    for (const g of gifts) {
      if (!seenGiftIds.value.has(g.id) && !g.viewed_at) {
        seenGiftIds.value.add(g.id);
        // Map to gift emoji
        const catalogue = GIFTS.find(x => x.name === g.name) || GIFTS[6];
        receivedGift.value = {
          id: g.id,
          name: g.name,
          emoji: catalogue.emoji,
          coins: g.coins,
          senderName: g.sender?.name || 'Someone',
          senderAvatar: g.sender?.avatar || null,
          senderId: g.sender?.id,
        };
        showReceived.value = true;
        confettiBoom();
        // Mark as read
        axios.post(`/api/user/gifts/${g.id}/mark-read`).catch(() => {});
        break; // Show one at a time
      }
    }
  } catch (_) { /* silent */ }
};

const dismissReceived = () => { showReceived.value = false; };

const replyToVibe = () => {
  const gift = receivedGift.value;
  if (!gift?.senderId) return dismissReceived();

  currentRecipient.value = {
    id: gift.senderId,
    name: gift.senderName || 'Someone',
    avatar: gift.senderAvatar || null,
  };
  selectedGift.value = null;
  dismissReceived();
  isOpen.value = true;
};

// ── Visual effects ─────────────────────────────────────
const playSendFX = (emoji) => {
  const layer = fxLayer.value;
  if (!layer) return;

  const fly = document.createElement('div');
  fly.textContent = emoji;
  fly.style.cssText = `
    position:fixed; font-size:44px; pointer-events:none; z-index:99999;
    left:50%; top:80%; transform:translate(-50%,-50%) scale(1);
    transition: top 0.6s cubic-bezier(.2,.8,.2,1), opacity 0.6s ease, transform 0.6s ease;
    filter: drop-shadow(0 10px 16px rgba(0,0,0,.2));
  `;
  document.body.appendChild(fly);
  requestAnimationFrame(() => {
    fly.style.top = '30%';
    fly.style.opacity = '0';
    fly.style.transform = 'translate(-50%,-50%) scale(1.5)';
  });
  setTimeout(() => fly.remove(), 700);
};

const confettiBoom = () => {
  const c = confettiEl.value;
  if (!c) return;
  c.innerHTML = '';
  const colors = ['#0ea5e9','#dfff00','#ec4899','#6366f1'];
  for (let i = 0; i < 36; i++) {
    const p = document.createElement('i');
    p.style.cssText = `
      position:absolute; top:-12px; border-radius:3px; opacity:.9;
      left:${Math.random()*100}%;
      background:${colors[Math.floor(Math.random()*colors.length)]};
      animation-delay:${Math.random()*180}ms;
      height:${12 + Math.random()*14}px;
      width:${7 + Math.random()*6}px;
      animation: lkFall 1.2s linear forwards;
    `;
    c.appendChild(p);
  }
  setTimeout(() => { if (c) c.innerHTML = ''; }, 1500);
};

const toast = (msg) => {
  toastMsg.value = msg;
  setTimeout(() => { toastMsg.value = ''; }, 2500);
};

// ── Watchers ───────────────────────────────────────────
watch(() => props.user, (u) => { if (u) currentRecipient.value = u; }, { immediate: true });
watch(() => props.balance, (b) => { coinBalance.value = b; }, { immediate: true });

// ── Lifecycle ──────────────────────────────────────────
onMounted(() => {
  pollReceivedGifts(); // Check immediately
  pollTimer = setInterval(pollReceivedGifts, 30_000); // Then every 30s
});
onUnmounted(() => clearInterval(pollTimer));

// Expose for parent ref
defineExpose({ open, close });
</script>

<style scoped>
/* ── Overlay / Modal ── */
.lk-overlay {
  position: fixed; inset: 0; z-index: 9000;
  background: rgba(15,23,42,.40);
  backdrop-filter: blur(14px);
  display: grid; place-items: center; padding: 16px;
}
.lk-modal {
  width: min(680px, 96vw);
  max-height: 90vh;
  overflow-y: auto;
  background: rgba(255,255,255,.92);
  border: 1px solid rgba(148,163,184,.28);
  border-radius: 24px;
  box-shadow: 0 28px 80px rgba(2,6,23,.18);
  display: flex; flex-direction: column;
}

/* Header */
.lk-modal-header {
  padding: 18px; display: flex; align-items: center;
  justify-content: space-between; gap: 12px;
  background: linear-gradient(135deg,rgba(14,165,233,.14),rgba(223,255,0,.08));
  border-bottom: 1px solid rgba(148,163,184,.18);
  border-radius: 24px 24px 0 0;
}
.lk-title-icon {
  width: 44px; height: 44px; border-radius: 16px; font-size: 22px;
  display: grid; place-items: center;
  background: rgba(14,165,233,.12); border: 1px solid rgba(14,165,233,.20);
}
.lk-title   { font-weight: 900; font-size: 22px; line-height: 1.1; }
.lk-subtitle { color:#64748b; font-size: 12px; font-weight: 700; margin-top:4px; }
.lk-x-btn {
  width:40px;height:40px;border-radius:14px;border:1px solid rgba(148,163,184,.28);
  background:rgba(255,255,255,.6);cursor:pointer;font-weight:900;color:#64748b;
  display:grid;place-items:center;transition:transform .12s;
}
.lk-x-btn:hover { transform: translateY(-1px); }

/* Body */
.lk-modal-body { padding: 18px; flex: 1; }

.lk-coin-pill {
  display: inline-flex; align-items: center; gap: 8px;
  background: linear-gradient(135deg,rgba(14,165,233,.92),rgba(14,165,233,.65));
  color: #000; font-weight: 900; padding: 10px 16px; border-radius: 999px;
  box-shadow: 0 8px 24px rgba(14,165,233,.18);
}
.lk-gem {
  display: inline-block; width: 13px; height: 13px;
  border-radius: 3px; transform: rotate(45deg);
  background: linear-gradient(135deg,rgba(223,255,0,.95),rgba(255,255,255,.55));
  box-shadow: 0 0 0 3px rgba(255,255,255,.14);
}
.lk-gem.sm { width: 10px; height: 10px; }

.lk-to-strip {
  display: flex; align-items: center; gap: 10px;
  background: rgba(14,165,233,.06); border: 1px solid rgba(14,165,233,.14);
  border-radius: 999px; padding: 8px 14px; font-size: 13px; font-weight: 700;
}

/* Gift grid */
.lk-gift-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}
@media(max-width:520px){ .lk-gift-grid { grid-template-columns: repeat(2,1fr); } }

.lk-gift-card {
  position: relative; border-radius: 18px; min-height: 120px;
  background: rgba(255,255,255,.85); border: 1px solid rgba(148,163,184,.22);
  box-shadow: 0 8px 24px rgba(2,6,23,.08);
  padding: 12px 10px; cursor: pointer;
  transition: transform .12s, box-shadow .12s;
  overflow: hidden; isolation: isolate;
}
.lk-gift-card:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(2,6,23,.12); }
.lk-gift-card.selected {
  outline: 3px solid rgba(14,165,233,.35);
  box-shadow: 0 18px 40px rgba(14,165,233,.18);
}
.lk-gift-icon {
  width: 50px; height: 50px; border-radius: 16px; font-size: 24px;
  display: grid; place-items: center; flex-shrink: 0;
  background: rgba(14,165,233,.10); border: 1px solid rgba(14,165,233,.18);
}
.lk-gift-icon.pink   { background:rgba(236,72,153,.10); border-color:rgba(236,72,153,.18); }
.lk-gift-icon.lime   { background:rgba(223,255,0,.16);  border-color:rgba(223,255,0,.28); }
.lk-gift-icon.purple { background:rgba(99,102,241,.10); border-color:rgba(99,102,241,.18); }

.lk-price-tag, .lk-free-tag {
  position: absolute; bottom: 10px; left: 10px;
  padding: 5px 10px; border-radius: 999px; font-size: 11px; font-weight: 900;
  display: inline-flex; align-items: center; gap: 5px;
}
.lk-price-tag {
  background: linear-gradient(135deg,rgba(14,165,233,.9),rgba(14,165,233,.65));
  color: #fff;
}
.lk-free-tag {
  background: rgba(223,255,0,.22); border: 1px solid rgba(223,255,0,.4); color: #000;
}

/* Footer */
.lk-modal-footer {
  padding: 16px 18px 18px;
  background: rgba(255,255,255,.7);
  border-top: 1px solid rgba(148,163,184,.18);
  border-radius: 0 0 24px 24px;
}
.lk-send-btn {
  width: 100%; border: none; border-radius: 999px; padding: 14px;
  font-weight: 900; font-size: 15px; color: #fff; cursor: pointer;
  background: linear-gradient(135deg,rgba(14,165,233,.95),rgba(14,165,233,.70));
  box-shadow: 0 12px 36px rgba(14,165,233,.22);
  transition: transform .12s, filter .12s;
}
.lk-send-btn:hover:not(:disabled) { transform: translateY(-1px); filter: brightness(1.03); }
.lk-send-btn:disabled { background: #94a3b8; opacity: .55; cursor: not-allowed; box-shadow: none; }

/* ── Received Vibe popup ── */
.lk-rx-overlay {
  position: fixed; inset: 0; z-index: 9500;
  background: rgba(15,23,42,.42); backdrop-filter: blur(14px);
  display: flex; align-items: center; justify-content: center; padding: 18px;
}
.lk-rx-card {
  width: min(520px, 96vw); border-radius: 28px;
  background: rgba(255,255,255,.88); border: 1px solid rgba(148,163,184,.22);
  box-shadow: 0 28px 80px rgba(2,6,23,.18);
  overflow: hidden; position: relative;
}
.lk-confetti {
  position: absolute; inset: 0; pointer-events: none; overflow: hidden; z-index: 0;
}
.lk-rx-top {
  position: relative; z-index: 1; padding: 18px;
  display: flex; align-items: center; justify-content: space-between;
  background: linear-gradient(135deg,rgba(236,72,153,.12),rgba(14,165,233,.12),rgba(223,255,0,.08));
  border-bottom: 1px solid rgba(148,163,184,.14);
}
.lk-rx-body { position: relative; z-index: 1; padding: 24px; text-align: center; }
.lk-rx-from {
  display: inline-flex; align-items: center; gap: 8px; margin-top: 12px;
  background: rgba(14,165,233,.08); border: 1px solid rgba(14,165,233,.16);
  border-radius: 999px; padding: 8px 14px; font-size: 13px; font-weight: 700;
}
.lk-btn-primary {
  background: linear-gradient(135deg,rgba(14,165,233,.92),rgba(14,165,233,.68));
  color: #fff; border: none; border-radius: 999px; padding: 12px 20px;
  font-weight: 900; cursor: pointer;
  box-shadow: 0 10px 28px rgba(14,165,233,.20);
}
.lk-btn-ghost {
  background: rgba(255,255,255,.7); border: 1px solid rgba(148,163,184,.28);
  color: #64748b; border-radius: 999px; padding: 12px 20px;
  font-weight: 900; cursor: pointer;
}

/* ── Toast ── */
.lk-toast {
  position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
  background: rgba(15,23,42,.88); color: #fff; font-weight: 800; font-size: 13px;
  padding: 12px 20px; border-radius: 999px;
  box-shadow: 0 12px 32px rgba(0,0,0,.18); z-index: 99999;
  backdrop-filter: blur(8px);
  white-space: nowrap;
}

/* ── Transitions ── */
.lk-fade-enter-active, .lk-fade-leave-active { transition: opacity .25s ease; }
.lk-fade-enter-from, .lk-fade-leave-to { opacity: 0; }

.lk-rx-fade-enter-active { transition: opacity .3s ease, transform .3s ease; }
.lk-rx-fade-leave-active { transition: opacity .2s ease; }
.lk-rx-fade-enter-from { opacity: 0; transform: scale(0.92); }
.lk-rx-fade-leave-to { opacity: 0; }

.lk-toast-fade-enter-active, .lk-toast-fade-leave-active { transition: opacity .2s, transform .2s; }
.lk-toast-fade-enter-from, .lk-toast-fade-leave-to { opacity: 0; transform: translateX(-50%) translateY(8px); }

/* ── Keyframes ── */
@keyframes lkFall {
  to { transform: translateY(110vh) rotate(320deg); opacity: 1; }
}
@keyframes lkPopIn {
  0%   { opacity:0; transform: scale(0.4); }
  70%  { transform: scale(1.15); }
  100% { opacity:1; transform: scale(1); }
}
@keyframes lkHeartPulse {
  0%,100% { transform: scale(1); }
  50%     { transform: scale(1.2); }
}
</style>
