<template>
    <div class="gift-dialog">
        <div class="fxLayer" id="fxLayer"></div>

        <div class="overlay" :style="{ display: isOpen ? 'grid' : 'none' }" id="overlay">
            <div class="modal" id="modal" role="dialog" aria-modal="true" aria-label="Send Gift">
                <div class="header">
                    <div class="titleWrap">
                        <div class="titleIcon">🌹</div>
                        <div>
                            <div class="title">Send a Vibe</div>
                            <div class="subtitle">See someone you like? Send a gift to break the ice and spark the vibe.
                            </div>
                        </div>
                    </div>
                    <button class="xBtn" @click="close" aria-label="Close">✕</button>
                </div>

                <div class="body">
                    <div class="topRow">
                        <div class="coinPill">
                            <span class="gem"></span>
                            <span id="coinTxt">{{ formatNumber(coinBalance) }}</span> Coins
                        </div>
                        <div class="hint" id="hintTxt" v-if="!selectedGift">Pick a gift to enable Send.</div>
                        <div class="hint" id="hintTxt" v-if="selectedGift">Ready to send.</div>
                    </div>

                    <div class="grid" id="grid">
                        <div v-for="gift in gifts" :key="gift.id" class="gift"
                            :class="{ selected: selectedGift && selectedGift.id === gift.id }"
                            @click="selectGift(gift)">
                            <div class="iconRow">
                                <div class="icon" :class="gift.iconClass">{{ gift.emoji }}</div>
                                <div class="meta">
                                    <div class="name">{{ gift.name }}</div>
                                    <div class="desc">{{ gift.desc }}</div>
                                </div>
                            </div>
                            <div v-if="gift.cost === 0" class="freeTag">Free</div>
                            <div v-else class="price">
                                <span class="gem"></span>{{ formatNumber(gift.cost) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <button class="sendBtn" @click="handleSendClick" :disabled="!selectedGift"
                        style="position: relative; z-index: 1000; cursor: pointer;">
                        <span v-if="sending" class="sending-text">Sending...</span>
                        <span v-else>Send</span>
                    </button>
                    <div class="miniRow">
                        <div>Selected: {{ selectedGift ? `${selectedGift.emoji} ${selectedGift.name}` : '—' }}</div>
                        <div>Cost: {{ selectedGift ? (selectedGift.cost === 0 ? 'Free' : formatNumber(selectedGift.cost)
                            + ' coins')
                            : '—' }}</div>
                        <div>After: {{ selectedGift ? (coinBalance - selectedGift.cost >= 0 ? formatNumber(coinBalance -
                            selectedGift.cost) : 'Insufficient') : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rxOverlay" :class="{ show: showReceiver }" aria-live="polite">
            <div class="rxCard">
                <div class="confetti" id="confetti"></div>
                <div class="rxTop">
                    <div class="rxTitle">Someone likes your vibe 💘</div>
                    <button class="rxClose" @click="hideReceiver" aria-label="Close">✕</button>
                </div>
                <div class="rxBody">
                    <div class="rxBigEmoji" id="rxEmoji">{{ receivedGift?.emoji || '🎁' }}</div>
                    <div class="rxHeart">💖</div>
                    <div class="rxMsg">You just got a gift!</div>
                    <div class="rxSub">That's a clear sign they're interested. Want to reply?</div>
                    <div class="rxFrom">From: <span id="rxFromName">{{ receivedFrom }}</span></div>
                    <div class="rxActions">
                        <button class="btn primary" @click="replyToGift">Reply</button>
                        <button class="btn ghost" @click="hideReceiver">Later</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="toast" :class="{ show: showToast }">{{ toastMessage }}</div>
    </div>
</template>

<script>
import { router } from '@inertiajs/vue3';
export default {
    name: 'GiftDialog',
    props: {
        user: {
            type: Object,
            default: null
        },
        balance: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            isOpen: false,
            showReceiver: false,
            showToast: false,
            toastMessage: '',
            coinBalance: this.balance,
            sending: false,
            currentRecipient: null,
            selectedGift: null,
            receivedGift: null,
            receivedFrom: 'Sofia',
            gifts: [
                { id: 'hola', name: 'Hola', desc: 'A sweet hello to start.', emoji: '👋', cost: 0, iconClass: 'lime' },
                { id: 'smile', name: 'Island Smile', desc: 'A warm little signal.', emoji: '😊', cost: 10, iconClass: 'lime' },
                { id: 'music', name: 'Soca Vibe', desc: 'Because the rhythm matters.', emoji: '🎶', cost: 35, iconClass: 'purple' },
                { id: 'dance', name: 'Dance Move', desc: 'Fun energy—no pressure.', emoji: '💃', cost: 50, iconClass: 'purple' },
                { id: 'sunset', name: 'Sunset', desc: 'Soft romantic mood.', emoji: '🌅', cost: 75, iconClass: 'pink' },
                { id: 'coconut', name: 'Coconut Drink', desc: 'Cheers from the islands.', emoji: '🥥', cost: 90, iconClass: 'lime' },
                { id: 'rose', name: 'Rose', desc: 'Classic, respectful romance.', emoji: '🌹', cost: 120, iconClass: 'pink' },
                { id: 'kissair', name: 'Blown Kiss', desc: 'Flirty, but still classy.', emoji: '😘', cost: 150, iconClass: 'pink' },
                { id: 'candles', name: 'Date Night', desc: 'A clear \'I\'m interested.\'', emoji: '🕯️', cost: 220, iconClass: 'pink' },
                { id: 'beach', name: 'Beach LinkUp', desc: 'Let\'s link up by the water.', emoji: '🏝️', cost: 250, iconClass: 'lime' },
                { id: 'heart', name: 'Heart Glow', desc: 'A louder \'I like you.\'', emoji: '💖', cost: 300, iconClass: 'pink' },
                { id: 'crown', name: 'Caribbean Crown', desc: 'Big vibe, big respect.', emoji: '👑', cost: 400, iconClass: 'lime' },
            ]
        };
    },
    methods: {
        open() {
            this.isOpen = true;
        },
        close() {
            this.isOpen = false;
            this.selectedGift = null;
        },
        selectGift(gift) {
            this.selectedGift = gift;
        },
        formatNumber(num) {
            return (num || 0).toLocaleString();
        },
        showToastMessage(message) {
            this.toastMessage = message;
            this.showToast = true;
            setTimeout(() => {
                this.showToast = false;
            }, 1100);
        },
        handleSendClick() {

            if (!this.selectedGift) {
                this.showToastMessage('Please select a gift first');
                return;
            }
            if (!this.currentRecipient || !this.currentRecipient.id) {
                this.showToastMessage('Please select a gift first');
                return;
            }
            console.log(this.currentRecipient);
            const requestData = {
                receiver_id: this.currentRecipient.id,
                gift_name: this.selectedGift.name,
                coins: this.selectedGift.cost
            };

            router.post(route('frontend.gift.coins.store'), requestData, {
                preserveScroll: true,
                onSuccess: () => {
                    this.selectedGift = null;
                    this.currentRecipient = null;
                    this.isOpen = false;
                }
            })
        },

        replyToGift() {
            this.showReceiver = false;
            this.showToastMessage('Opening chat...');
        },
        hideReceiver() {
            this.showReceiver = false;
        },
        playSendFX(emoji, element) {
            const fxLayer = this.$el.querySelector('#fxLayer');
            if (!fxLayer) return;

            const fly = document.createElement('div');
            fly.className = 'flyEmoji';
            fly.textContent = emoji;

            const rect = element ? element.getBoundingClientRect() : this.$el.getBoundingClientRect();
            const startX = rect.left + rect.width / 2;
            const startY = rect.top + rect.height / 2;

            const cx = window.innerWidth / 2;
            const cy = window.innerHeight / 2 + 30;

            fly.style.left = startX + 'px';
            fly.style.top = startY + 'px';
            fxLayer.appendChild(fly);

            requestAnimationFrame(() => {
                fly.style.left = cx + 'px';
                fly.style.top = cy + 'px';
                fly.style.transform = 'translate(-50%, -50%) scale(1.35)';
            });

            setTimeout(() => {
                const burst = document.createElement('div');
                burst.className = 'burst';
                burst.style.left = cx + 'px';
                burst.style.top = cy + 'px';
                fxLayer.appendChild(burst);
            }, 420);

            setTimeout(() => {
                fly.style.opacity = '0';
                fly.style.transform = 'translate(-50%, -50%) scale(0.9)';
            }, 520);

            setTimeout(() => {
                fxLayer.innerHTML = '';
            }, 850);
        },
        confettiBoom() {
            const c = this.$el.querySelector('#confetti');
            if (!c) return;

            c.innerHTML = '';
            const colors = [
                'rgba(14,165,233,.95)',
                'rgba(223,255,0,.95)',
                'rgba(236,72,153,.95)',
                'rgba(99,102,241,.95)'
            ];

            for (let i = 0; i < 34; i++) {
                const p = document.createElement('i');
                p.style.left = (Math.random() * 100) + '%';
                p.style.background = colors[Math.floor(Math.random() * colors.length)];
                p.style.animationDelay = (Math.random() * 180) + 'ms';
                p.style.height = (12 + Math.random() * 14) + 'px';
                p.style.width = (7 + Math.random() * 6) + 'px';
                c.appendChild(p);
            }

            setTimeout(() => {
                c.innerHTML = '';
            }, 1400);
        }
    },
    watch: {
        user: {
            immediate: true,
            handler(newUser) {
                this.currentRecipient = newUser;
            }
        },
        balance: {
            immediate: true,
            handler(newBalance) {
                this.coinBalance = newBalance;
            }
        }
    }

};
</script>

<style scoped>
* {
    box-sizing: border-box;
}

.gift-dialog {
    position: relative;
    width: 10px;
}

.fxLayer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 50;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, .35);
    backdrop-filter: blur(14px);
    display: grid;
    place-items: center;
    padding: 18px;
    z-index: 1000;
    opacity: 0;
    animation: fadeIn 0.3s ease forwards;
}

.modal {
    width: 50%;
    height: 74.5%;
    background: rgba(255, 255, 255, .78);
    border: 1px solid rgba(148, 163, 184, .28);
    border-radius: 20px;
    box-shadow: 0 24px 80px rgba(2, 6, 23, .18);
    position: relative;
    opacity: 0;
    transform: scale(0.9);
    animation: modalPop 0.3s ease forwards;
    overflow: scroll;
}

.header {
    padding: 18px 18px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: linear-gradient(135deg, rgba(14, 165, 233, .16), rgba(223, 255, 0, .10));
    border-bottom: 1px solid rgba(148, 163, 184, .22);
    border-radius: 16px 16px 0 0;
}

.titleWrap {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.titleIcon {
    width: 44px;
    height: 44px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(14, 165, 233, .14);
    border: 1px solid rgba(14, 165, 233, .20);
    box-shadow: 0 12px 28px rgba(14, 165, 233, .12);
    font-size: 20px;
    flex-shrink: 0;
}

.title {
    font-weight: 1000;
    font-size: 28px;
    line-height: 1.05;
    margin-top: 2px;
}

.subtitle {
    color: #64748b;
    font-weight: 900;
    font-size: 13px;
    margin-top: 6px;
    max-width: 640px;
    line-height: 1.25;
}

.xBtn {
    width: 44px;
    height: 44px;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, .28);
    background: rgba(255, 255, 255, .62);
    cursor: pointer;
    display: grid;
    place-items: center;
    font-weight: 1000;
    color: #64748b;
    transition: transform .12s ease, filter .12s ease;
}

.xBtn:hover {
    transform: translateY(-1px);
    filter: brightness(1.02);
}

.body {
    padding: 14px 18px 18px;
    background: #ffffff;
}

.topRow {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.coinPill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(14, 165, 233, .95), rgba(14, 165, 233, .70));
    color: #000000;
    font-weight: 1000;
    box-shadow: 0 16px 34px rgba(14, 165, 233, .18);
}

.gem {
    width: 14px;
    height: 14px;
    transform: rotate(45deg);
    border-radius: 3px;
    background: linear-gradient(135deg, rgba(223, 255, 0, .95), rgba(255, 255, 255, .55));
    box-shadow: 0 0 0 4px rgba(255, 255, 255, .14);
    display: inline-block;
}

.hint {
    color: #000000;
    font-weight: 900;
    font-size: 12.5px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

@media (max-width: 820px) {
    .grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .title {
        font-size: 24px;
    }
}

@media (max-width: 560px) {
    .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .title {
        font-size: 22px;
    }
}

.gift {
    position: relative;
    border-radius: 20px;
    background: rgba(255, 255, 255, .80);
    border: 1px solid rgba(148, 163, 184, .28);
    box-shadow: 0 10px 26px rgba(2, 6, 23, .10);
    padding: 14px 12px;
    min-height: 128px;
    cursor: pointer;
    transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
    overflow: hidden;
    isolation: isolate;
}

.gift:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(2, 6, 23, .12);
    filter: brightness(1.01);
}

.gift.selected {
    outline: 3px solid rgba(14, 165, 233, .22);
    box-shadow: 0 20px 46px rgba(14, 165, 233, .18);
}

.gift::before {
    content: "";
    position: absolute;
    inset: -40px -60px auto auto;
    width: 160px;
    height: 160px;
    background:
        radial-gradient(circle at 35% 35%, rgba(236, 72, 153, .22), transparent 60%),
        radial-gradient(circle at 70% 70%, rgba(223, 255, 0, .22), transparent 60%);
    filter: blur(2px);
    opacity: .7;
    transform: rotate(18deg);
    z-index: -1;
}

.iconRow {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    font-size: 26px;
    background: rgba(14, 165, 233, .10);
    border: 1px solid rgba(14, 165, 233, .18);
    box-shadow: 0 12px 26px rgba(2, 6, 23, .06);
    flex: 0 0 auto;
}

.icon.pink {
    background: rgba(236, 72, 153, .10);
    border-color: rgba(236, 72, 153, .18);
}

.icon.lime {
    background: rgba(223, 255, 0, .18);
    border-color: rgba(223, 255, 0, .30);
}

.icon.purple {
    background: rgba(99, 102, 241, .10);
    border-color: rgba(99, 102, 241, .18);
}

.meta {
    min-width: 0;
    padding-top: 2px;
}

.name {
    font-weight: 1100;
    font-size: 14px;
    line-height: 1.1;
    margin-bottom: 6px;
}

.desc {
    color: #64748b;
    font-weight: 850;
    font-size: 12.5px;
    line-height: 1.25;
}

.price,
.freeTag {
    position: absolute;
    left: 12px;
    bottom: 12px;
    padding: 8px 12px;
    border-radius: 999px;
    font-weight: 1100;
    z-index: 2;
}

.price {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(14, 165, 233, .95), rgba(14, 165, 233, .70));
    color: #fff;
    box-shadow: 0 16px 34px rgba(14, 165, 233, .16);
}

.price .gem {
    width: 10px;
    height: 10px;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, .12);
}

.freeTag {
    background: rgba(223, 255, 0, .22);
    border: 1px solid rgba(223, 255, 0, .42);
    color: #000000;
}

.footer {
    padding: 16px 18px 18px;
    background: rgba(255, 255, 255, .64);
    border-top: 1px solid rgba(148, 163, 184, .22);
}

.sendBtn {
    width: 100%;
    border: none;
    cursor: pointer;
    border-radius: 30px;
    padding: 16px 18px;
    font-weight: 1200;
    letter-spacing: .2px;
    color: #fff;
    background: linear-gradient(135deg, rgba(14, 165, 233, .96), rgba(14, 165, 233, .72));
    box-shadow: 0 18px 44px rgba(14, 165, 233, .20);
    transition: transform .12s ease, filter .12s ease;
    position: relative;
    z-index: 1000;
    pointer-events: auto !important;
    min-width: 100px;
    text-align: center;
}

.sendBtn:hover:not(:disabled) {
    transform: translateY(-1px);
    filter: brightness(1.02);
}

.sendBtn:active:not(:disabled) {
    transform: translateY(0);
}

.sendBtn:disabled {
    background: #94a3b8;
    opacity: .55;
    cursor: not-allowed;
    box-shadow: none;
    filter: grayscale(.15);
}

.sendBtn:not(:disabled):hover {
    transform: translateY(-1px);
    filter: brightness(1.02);
}

.miniRow {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 10px;
    color: #000000;
    font-weight: 900;
    font-size: 12.5px;
}

.flyEmoji {
    position: fixed;
    font-size: 44px;
    filter: drop-shadow(0 18px 22px rgba(0, 0, 0, .18));
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
    transition: left .55s cubic-bezier(.2, .8, .2, 1),
        top .55s cubic-bezier(.2, .8, .2, 1),
        transform .55s cubic-bezier(.2, .8, .2, 1),
        opacity .55s ease;
    pointer-events: none;
    z-index: 10000;
}

.burst {
    position: fixed;
    width: 14px;
    height: 14px;
    border-radius: 999px;
    background: rgba(223, 255, 0, .95);
    box-shadow: 0 0 0 10px rgba(223, 255, 0, .14), 0 22px 60px rgba(14, 165, 233, .18);
    transform: translate(-50%, -50%) scale(.6);
    opacity: 0;
    animation: burst 700ms ease forwards;
    pointer-events: none;
    z-index: 10000;
}

@keyframes burst {
    0% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(.5);
    }

    20% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }

    100% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(3.2);
    }
}

.rxOverlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .38);
    backdrop-filter: blur(14px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    z-index: 200;
}

.rxOverlay.show {
    display: flex;
}

.rxCard {
    width: min(760px, 94vw);
    border-radius: 28px;
    background: rgba(255, 255, 255, .84);
    border: 1px solid rgba(148, 163, 184, .28);
    box-shadow: 0 24px 80px rgba(2, 6, 23, .18);
    overflow: hidden;
    position: relative;
}

.rxTop {
    padding: 18px 18px 14px;
    background: linear-gradient(135deg, rgba(236, 72, 153, .14), rgba(14, 165, 233, .14), rgba(223, 255, 0, .10));
    border-bottom: 1px solid rgba(148, 163, 184, .22);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.rxTitle {
    font-weight: 1100;
    font-size: 22px;
    line-height: 1.1;
}

.rxClose {
    width: 44px;
    height: 44px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, .28);
    background: rgba(255, 255, 255, .68);
    cursor: pointer;
    font-weight: 1000;
    color: #64748b;
}

.rxBody {
    padding: 18px;
    text-align: center;
    background: rgba(255, 255, 255, .72);
}

.rxBigEmoji {
    font-size: 78px;
    filter: drop-shadow(0 18px 22px rgba(0, 0, 0, .18));
    animation: popIn 520ms ease both;
}

.rxHeart {
    display: inline-block;
    margin-top: 10px;
    font-size: 26px;
    animation: heartPulse 900ms ease-in-out infinite;
}

.rxMsg {
    margin-top: 12px;
    font-weight: 1000;
    font-size: 18px;
}

.rxSub {
    margin-top: 8px;
    color: #64748b;
    font-weight: 900;
    font-size: 13px;
    line-height: 1.35;
}

.rxFrom {
    margin-top: 12px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 999px;
    background: rgba(14, 165, 233, .10);
    border: 1px solid rgba(14, 165, 233, .18);
    font-weight: 1000;
}

.rxActions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 16px;
}

.btn {
    border: none;
    cursor: pointer;
    border-radius: 999px;
    padding: 12px 14px;
    font-weight: 1100;
}

.btn.primary {
    color: #000000;
    background: linear-gradient(135deg, rgba(14, 165, 233, .96), rgba(14, 165, 233, .72));
    box-shadow: 0 18px 44px rgba(14, 165, 233, .20);
}

.btn.ghost {
    background: rgba(255, 255, 255, .68);
    border: 1px solid rgba(148, 163, 184, .28);
    color: #64748b;
}

.confetti {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.confetti i {
    position: absolute;
    top: -12px;
    width: 10px;
    height: 18px;
    border-radius: 3px;
    opacity: .9;
    animation: fall 1200ms linear forwards;
}

@keyframes fall {
    to {
        transform: translateY(110vh) rotate(320deg);
        opacity: 1;
    }
}

.toast {
    position: fixed;
    left: 50%;
    bottom: 18px;
    transform: translateX(-50%);
    padding: 12px 14px;
    border-radius: 16px;
    background: rgba(255, 255, 255, .80);
    border: 1px solid rgba(148, 163, 184, .28);
    box-shadow: 0 18px 50px rgba(0, 0, 0, .16);
    font-weight: 950;
    backdrop-filter: blur(10px);
    opacity: 0;
    pointer-events: none;
    transition: opacity .18s ease;
    z-index: 120;
}

.toast.show {
    opacity: 1;
}

@keyframes fadeIn {
    to {
        opacity: 1;
    }
}

@keyframes modalPop {
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes popIn {
    0% {
        opacity: 0;
        transform: translateY(8px) scale(.82);
    }

    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes heartPulse {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.18);
    }
}

@media (max-width: 1280px) {

    .modal {
        width: 100%;
        height: 74.5%;
        background: rgba(255, 255, 255, .78);
        border: 1px solid rgba(148, 163, 184, .28);
        border-radius: 20px;
        box-shadow: 0 24px 80px rgba(2, 6, 23, .18);
        position: relative;
        opacity: 0;
        transform: scale(0.9);
        animation: modalPop 0.3s ease forwards;
        overflow: scroll;
    }
}
</style>
