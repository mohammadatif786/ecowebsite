<template>
    <AuthenticatedLayout>

        <Head title="Checkout" />

        <!-- Root -->
        <section :style="styles.rootVars">
            <div :style="styles.container">
                <!-- LEFT: Cart + Shipping + Payment -->
                <div :style="styles.leftCol">

                    <!-- Cart Items -->
                    <div :style="styles.card">
                        <div :style="styles.rowBetween">
                            <h3 :style="styles.sectionTitle">Your Cart</h3>
                            <button :style="styles.removeLink" @click="emptyCart">Remove all</button>
                        </div>

                        <div v-if="cart.length === 0" :style="styles.emptyText">
                            Your cart is empty.
                        </div>

                        <div v-for="item in cart" :key="item.id" :style="styles.cartRow">
                            <div :style="styles.cartThumb">
                                <img :src=item.cover_image :alt=item.name>
                            </div>
                            <div :style="styles.cartInfo">
                                <div :style="styles.itemName">{{ item.name }}</div>
                                <div :style="styles.itemMeta">{{ fmt(item.price) }} × {{ item.qty }}</div>
                            </div>

                            <div :style="styles.qtyControls">
                                <button :style="[styles.btn, styles.outlineBtn]"
                                    @click="changeQty(item.id, -1)">−</button>
                                <span :style="styles.qtyNumber">{{ item.qty }}</span>
                                <button :style="[styles.btn, styles.outlineBtn]"
                                    @click="changeQty(item.id, 1)">+</button>
                            </div>

                            <div :style="styles.itemTotal">{{ fmt(item.price * item.qty) }}</div>
                            <button :style="styles.removeLink" @click="removeFromCart(item.id)">Remove</button>
                        </div>
                    </div>

                    <!-- Shipping -->
                    <div :style="styles.card">
                        <h3 :style="[styles.sectionTitle, { marginBottom: '12px' }]">Shipping Details</h3>

                        <div :style="styles.grid2">

                            <!-- <div>
                                <label :style="styles.label">Phone</label>
                                <input v-model="ship.phone" :style="styles.field"
                                    placeholder="e.g., +1 (242) 555-1234" :error="phone"/>
                            </div> -->
                            <div>
                                <label :style="styles.label">Phone</label>
                                <input v-model="ship.phone" :style="styles.field"
                                    placeholder="e.g., +1 (242) 555-1234" type="tel"/>
                                <span v-if="errors.phone" style="color:red; font-size:12px">{{ errors.phone }}</span>
                            </div>

                            <div>
                                <label :style="styles.label">Address</label>
                                <input v-model="ship.address" :style="styles.field"
                                    placeholder="Street, settlement, house no." />
                                <span v-if="errors.address" style="color:red; font-size:12px">
                                    {{ errors.address }}
                                </span>
                            </div>

                            <div>
                                <label :style="styles.label">City</label>
                                <input v-model="ship.city" :style="styles.field" placeholder="e.g., Nassau" />
                                <span v-if="errors.city" style="color:red; font-size:12px">
                                    {{ errors.city }}
                                </span>
                            </div>

                            <div>
                                <label :style="styles.label">Island / State</label>
                                <input v-model="ship.state" :style="styles.field" placeholder="e.g., New Providence" />
                                <span v-if="errors.state" style="color:red; font-size:12px">
                                    {{ errors.state }}
                                </span>
                            </div>

                            <div>
                                <label :style="styles.label">Country</label>
                                <select v-model="ship.country" :style="styles.field">
                                    <option>Bahamas</option>
                                    <option>Jamaica</option>
                                    <option>Trinidad & Tobago</option>
                                    <option>Barbados</option>
                                    <option>Dominican Republic</option>
                                </select>
                                <span v-if="errors.country" style="color:red; font-size:12px">
                                    {{ errors.country }}
                                </span>
                            </div>

                            <div>
                                <label :style="styles.label">Postal / ZIP</label>
                                <input v-model="ship.zip" :style="styles.field" placeholder="—" type="number"/>
                                <span v-if="errors.zip" style="color:red; font-size:12px">
                                    {{ errors.zip }}
                                </span>
                            </div>
                        </div>

                        <div :style="[styles.rowWrap, { marginTop: '12px' }]">
                            <label :style="styles.label">Shipping Method</label>
                            <label :style="styles.chip">
                                <input type="radio" style="margin-right:8px" value="standard" v-model="shipMethod" />
                                Standard (3‑7 days)
                            </label>
                            <label :style="styles.chip">
                                <input type="radio" style="margin-right:8px" value="express" v-model="shipMethod" />
                                Express (1‑3 days)
                            </label>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div :style="styles.card">
                        <h3 :style="[styles.sectionTitle, { marginBottom: '8px' }]">Payment</h3>

                        <div :style="[styles.rowWrap, { marginBottom: '12px' }]">
                            <label :style="styles.chip">
                                <input type="radio" style="margin-right:8px" value="card" v-model="payMethod" /> Card
                            </label>
                            <label :style="styles.chip">
                                <input type="radio" style="margin-right:8px" value="wallet" v-model="payMethod" /> Link
                                Up Wallet
                            </label>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Summary -->
                <aside :style="styles.rightCol">
                    <div :style="styles.card">
                        <h3 :style="[styles.sectionTitle, { marginBottom: '10px' }]">Order Summary</h3>

                        <div :style="styles.summaryRow"><span>Subtotal</span><span>{{ fmt(subtotal) }}</span></div>
                        <div :style="styles.summaryRow"><span>Shipping</span><span>{{ fmt(shipping) }}</span></div>
                        <div :style="styles.summaryRow"><span>Tax</span><span>{{ fmt(tax) }}</span></div>
                        <div :style="styles.summaryRow">
                            <span>Discount</span><span>{{ discount > 0 ? '− ' + fmt(discount) : fmt(0) }}</span>
                        </div>

                        <div :style="styles.hr"></div>

                        <div :style="styles.summaryTotal">
                            <span>Total</span><span>{{ fmt(total) }}</span>
                        </div>

                        <div :style="{ marginTop: '12px', display: 'flex', gap: '8px' }">
                            <input v-model="promoInput" :style="[styles.field, { flex: 1 }]"
                                placeholder="Promo code (ISLAND10)" />
                            <button :style="[styles.btn, styles.outlineBtn]" @click="applyPromo">Apply</button>
                        </div>

                        <button :style="[styles.btn, styles.primaryBtn, { width: '100%', marginTop: '12px' }]"
                            @click="placeOrder">
                            Place Order
                        </button>
                    </div>
                </aside>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { usePage, router } from '@inertiajs/vue3'

/* -------------------------
   Inline style objects
------------------------- */
const styles = {
    rootVars: {
        '--ink': '#0b2342',
        '--muted': '#6b7c92',
        '--card': '#ffffff',
        '--surface': '#f6f9fc',
        display: 'block',
        padding: '20px',
    },
    container: {
        maxWidth: '1100px',
        margin: 'auto',
        display: 'grid',
        gridTemplateColumns: '2fr 1fr',
        gap: '20px',
    },
    leftCol: { display: 'flex', flexDirection: 'column', gap: '16px' },
    rightCol: { display: 'flex', flexDirection: 'column', gap: '12px' },
    card: {
        background: '#fff',
        borderRadius: '16px',
        padding: '16px',
        boxShadow: '0 6px 18px rgba(11,35,66,0.04)',
        border: '1px solid rgba(0,0,0,0.05)',
    },
    sectionTitle: { fontSize: '1.15rem', fontWeight: 700, color: 'var(--ink)' },
    rowBetween: { display: 'flex', justifyContent: 'space-between', alignItems: 'center' },
    rowWrap: { display: 'flex', gap: '10px', alignItems: 'center', flexWrap: 'wrap' },
    grid2: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' },
    chip: {
        display: 'inline-flex',
        alignItems: 'center',
        gap: '8px',
        padding: '6px 10px',
        borderRadius: '9999px',
        background: '#fff',
        border: '1px solid rgba(0,0,0,0.05)',
        fontSize: '0.9rem',
    },
    btn: {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        gap: '8px',
        padding: '8px 12px',
        borderRadius: '12px',
        fontWeight: 600,
        border: '1px solid rgba(0,0,0,0.05)',
        cursor: 'pointer',
    },
    primaryBtn: { color: '#fff', background: 'linear-gradient(135deg,#3aa0ea,#0b2342)' },
    outlineBtn: { background: '#fff' },
    field: {
        width: '100%',
        borderRadius: '12px',
        border: '1px solid rgba(0,0,0,0.1)',
        padding: '10px 12px',
        outline: 'none',
    },
    label: { fontSize: '0.9rem', color: '#64748b', fontWeight: 500 },
    cartRow: {
        display: 'flex',
        gap: '10px',
        alignItems: 'center',
        padding: '12px 0',
        borderTop: '1px solid rgba(0,0,0,0.03)',
    },
    cartThumb: { width: '56px', height: '56px', borderRadius: '10px', background: '#f1f5f9' },
    cartInfo: { flex: 1 },
    itemName: { fontWeight: 600, color: 'var(--ink)' },
    itemMeta: { fontSize: '13px', color: '#64748b' },
    qtyControls: { display: 'flex', alignItems: 'center', gap: '8px' },
    qtyNumber: { width: '28px', textAlign: 'center' },
    itemTotal: { width: '88px', textAlign: 'right', fontWeight: 700 },
    removeLink: { color: '#ef4444', background: 'transparent', border: 'none', cursor: 'pointer', fontSize: '0.85rem' },
    emptyText: { color: '#64748b', padding: '12px 0' },
    walletBar: { marginBottom: '10px', fontSize: '14px' },
    badge: {
        display: 'inline-flex',
        alignItems: 'center',
        fontSize: '0.72rem',
        padding: '2px 8px',
        borderRadius: '9999px',
        border: '1px solid rgba(0,0,0,0.08)',
        background: '#fff',
    },
    summaryRow: { display: 'flex', justifyContent: 'space-between', margin: '6px 0', fontSize: '14px' },
    hr: { height: '1px', background: 'rgba(0,0,0,0.06)', margin: '12px 0' },
    summaryTotal: { display: 'flex', justifyContent: 'space-between', fontWeight: 700, fontSize: '18px' },
};

/* -------------------------
   State & Logic
------------------------- */
const cart = ref(JSON.parse(localStorage.getItem('lum_cart') || '[]'));
const ship = reactive({ name: '', phone: '', address: '', city: '', state: '', country: 'Bahamas', zip: '' });
const shipMethod = ref<'standard' | 'express'>('standard');
const payMethod = ref<'card' | 'wallet'>('card');
const promoInput = ref('');
const promo = ref<string | null>(null);
const page = usePage()
const cardName = ref(''); const cardNumber = ref(''); const cardExp = ref(''); const cardCvc = ref('');
const wallet = reactive({ BSD: Number(localStorage.getItem('lum_wallet') || '120') });
const walletBalance = computed(() => Number(wallet.BSD));
const errors = reactive<Record<string, string>>({ ...page.props.errors });
const subtotal = computed(() => cart.value.reduce((a: any, b: any) => a + b.price * b.qty, 0));
// const shipping = computed(() => (cart.value.length === 0 ? 0 : shipMethod.value === 'express' ? 18 : 0));
// const tax = computed(() => +(subtotal.value * 0.07).toFixed(2));
// const discount = computed(() => (promo.value === 'ISLAND10' ? +(subtotal.value * 0.1).toFixed(2) : 0));
const shipping = computed(() => 0);
const tax = computed(() => 0);
const discount = computed(() => 0);
const total = computed(() => +(subtotal.value + shipping.value + tax.value - discount.value).toFixed(2));

function saveCart() { localStorage.setItem('lum_cart', JSON.stringify(cart.value)); }
function saveWallet() { localStorage.setItem('lum_wallet', String(wallet.BSD)); }
function fmt(n: number) { return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'BSD' }).format(n); }

function removeFromCart(id: string) { cart.value = cart.value.filter(i => i.id !== id); saveCart(); }
function changeQty(id: string, d: number) { const it = cart.value.find(i => i.id === id); if (!it) return; it.qty = Math.max(1, it.qty + d); saveCart(); }
function emptyCart() { cart.value = []; saveCart(); }

function applyPromo() { promo.value = promoInput.value.trim().toUpperCase() || null; }
function topUp(amount = 50) { wallet.BSD += amount; saveWallet(); }
function placeOrder() {
    if (cart.value.length === 0) return alert('Cart empty');

    const payload = {
        name: ship.name,
        phone: ship.phone,
        address: ship.address,
        city: ship.city,
        state: ship.state,
        country: ship.country,
        zip: ship.zip,
        shipping_method: shipMethod.value,
        payment_method: payMethod.value,
        subtotal_amount: subtotal.value,
        shipping_amount: shipping.value,
        tax_amount: tax.value,
        discount_amount: discount.value,
        net_total: total.value,
        items: cart.value,
    };

    // Clear previous errors
    Object.keys(errors).forEach(k => delete errors[k]);

    router.post(route('frontend.checkout.store'), payload, {
        onSuccess: () => {
            cart.value = [];
            saveCart();
        },
        onError: (err) => {
            // err contains validation errors from backend
            Object.assign(errors, err);
        },
    });
}
</script>
