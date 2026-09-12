<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { Check} from 'lucide-vue-next';
import { useForm, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{
  show: boolean;
  products: any[];
  cart: Array<{ productId: number; qty: number }>;
  shopFee?: any;
  processFee?: any;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'order-placed', order: any): void;
}>();

const items = computed(() => {
  const map = new Map(props.products.map((p: any) => [p.id, p]));
  return (props.cart || []).map(ci => ({ ci, p: map.get(ci.productId) })).filter(x => !!x.p);
});

const subtotal = computed(() => items.value.reduce((sum, x) => sum + Number(x.p?.price || 0) * Number(x.ci?.qty || 0), 0));

const feeAmount = computed(() => {
  const fee = props.shopFee;
  if (!fee || !fee.enabled) return 0;

  let amount = 0;
  const type = fee.fee_type;
  const amt = subtotal.value;

  if (type === 'percent') {
    amount = amt * (Number(fee.percent) / 100);
  } else if (type === 'fixed') {
    amount = Number(fee.fixed);
  } else if (type === 'both') {
    amount = (amt * (Number(fee.percent) / 100)) + Number(fee.fixed);
  }

  if (Number(fee.min_fee) > 0 && amount < Number(fee.min_fee)) amount = Number(fee.min_fee);
  if (Number(fee.max_fee) > 0 && amount > Number(fee.max_fee)) amount = Number(fee.max_fee);

  return amount;
});

const processFeeAmount = computed(() => {
  const fee = props.processFee;
  if (!fee) return 0;

  let amount = 0;
  const amt = subtotal.value;

  // Calculate processing fee (percentage + fixed)
  if (Number(fee.processing_fee_pct) > 0) {
    amount += amt * (Number(fee.processing_fee_pct) / 100);
  }
  if (Number(fee.processing_fee_fixed) > 0) {
    amount += Number(fee.processing_fee_fixed);
  }

  return amount;
});

const total = computed(() => subtotal.value + feeAmount.value + processFeeAmount.value);

const pickupOptionsPerItem = computed(() => items.value.map(({ p }) => {
  if (p?.listingType === 'Store') return p.store?.pickup_locations || [];
  if (p?.listingType === 'Carnival Group') return p.group?.pickup_locations || [];
  if (p?.listingType === 'Administrator') return ['LinkUp HQ Pickup'];
  return [];
}));

const pickupAllowedForAll = computed(() => items.value.length > 0 && pickupOptionsPerItem.value.every(arr => Array.isArray(arr) && arr.length > 0));
const pickupMerged = computed(() => pickupAllowedForAll.value ? Array.from(new Set(pickupOptionsPerItem.value.flat())) : []);

const name = ref('');
const email = ref('');
const phone = ref('');
const notes = ref('');
const usePickup = ref(false);
const pickupLoc = ref('');
const payMethod = ref<'wallet' | 'card'>('wallet');

const ship = ref({ address1: '', address2: '', city: '', state: '', country: '', postal: '' });

const errors = ref<{ [k: string]: string }>({});

const placeOrder = async () => {
  errors.value = {};
  if (!name.value) errors.value.name = 'Full name is required.';
  if (!email.value) errors.value.email = 'Email is required.';
  if (!usePickup.value) {
    if (!ship.value.address1) errors.value.address1 = 'Address line 1 is required.';
    if (!ship.value.city) errors.value.city = 'City is required.';
    if (!ship.value.country) errors.value.country = 'Country is required.';
  } else if (pickupAllowedForAll.value && !pickupLoc.value) {
    errors.value.pickupLoc = 'Please choose a pickup location.';
  }
  if (Object.keys(errors.value).length) return;

  const form = useForm({
    payment_method: payMethod.value,
    shipping_method: usePickup.value ? 'standard' : 'express',
    phone: phone.value,
    address: ship.value.address1 + (ship.value.address2 ? (', ' + ship.value.address2) : ''),
    city: ship.value.city,
    state: ship.value.state,
    country: ship.value.country,
    zip: ship.value.postal,
    items: items.value.map(({ ci }) => ({ id: ci.productId, qty: ci.qty })),
  });

  try {
    form.post(route('frontend.checkout.store'), {
      onSuccess: () => {
        // Notify parent to clear cart and handle any post-order UI
        emit('order-placed', null);
        emit('close');
      },
      onError: (formErrs) => {
        // Merge form errors into reactive state safely
        errors.value = { ...(errors.value || {}), ...(formErrs || {}) } as any;
        // Show toast for notable errors (e.g., wallet balance)
        const balanceMsg = (formErrs as any)?.balance;
        if (balanceMsg) {
          toast.error(balanceMsg);
        }
      },
    });

  } catch (e: any) {
    errors.value._server = e.message || 'An error occurred while processing your order.';
  }
};

watch(() => props.show, async (v) => {
  if (v) {
    const page = usePage() as any;
    const user = page.props.auth.user;

    if (user) {
      name.value = user.name || '';
      email.value = user.email || '';
      phone.value = user.phone_number || '';
      ship.value.city = user.city || user.new_city || '';
      ship.value.state = user.state || user.new_state || '';
      ship.value.country = user.country || user.new_country || '';
    }

    await nextTick();
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }
});
</script>

<template>
  <div v-if="show" class="checkout-modal-backdrop" @click="e => { if (e.target === e.currentTarget) emit('close') }">
    <div class="card modal p-4 md:p-6">
      <div class="flex items-start gap-3">
        <div class="w-30 h-30 rounded-2xl grid place-items-center" style="margin-top: -30px;">
          <img src="/storage/avatars/marketplacelog.png" alt="Marketplace Logo" class="w-full h-full" />
        </div>
        <div class="flex-1">
          <div class="text-xl font-black">Checkout</div>
          <div class="text-sm mt-1" style="color:#64748b;">Delivery by default. Pickup appears when all items support
            it.</div>
        </div>
        <button class="btn2" @click="emit('close')">
          <span class="inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x"
              class="lucide lucide-x w-5 h-5">
              <path d="M18 6 6 18"></path>
              <path d="m6 6 12 12"></path>
            </svg> Close
          </span>
        </button>
      </div>

      <div class="mt-5 grid md:grid-cols-2 gap-4">
        <!-- Left -->
        <div class="card p-4">
          <div class="font-black text-lg">Buyer Info</div>
          <div class="grid gap-2 mt-3">
            <div>
              <input class="input" v-model="name" placeholder="Full name" />
              <div v-if="errors.name" class="error text-red-400">{{ errors.name }}</div>
            </div>
            <div>
              <input class="input" v-model="email" placeholder="Email" />
              <div v-if="errors.email" class="error text-red-400">{{ errors.email }}</div>
            </div>
            <input class="input" v-model="phone" placeholder="Phone (optional)" />
          </div>

          <div class="mt-4 card p-3">
            <div class="font-black">Delivery / Pickup</div>
            <div class="mt-2 flex items-center gap-2">
              <input type="checkbox" id="ck_pickup" :disabled="!pickupAllowedForAll" v-model="usePickup" />
              <label for="ck_pickup" class="text-sm" style="color:#64748b;">Pickup instead of delivery {{
                pickupAllowedForAll ? '' : ' (Not available for at least one item)' }}</label>
            </div>

            <div class="mt-3" v-show="usePickup && pickupAllowedForAll">
              <div class="tiny" style="color:#64748b;">Choose a pickup location</div>
              <select class="input mt-1" v-model="pickupLoc">
                <option value="" disabled>Select location…</option>
                <option v-for="loc in pickupMerged" :key="loc" :value="loc">{{ loc }}</option>
              </select>
              <div v-if="errors.pickupLoc" class="error text-red-400">{{ errors.pickupLoc }}</div>
            </div>

            <div class="mt-3" v-show="!usePickup">
              <div class="tiny" style="color:#64748b;">Shipping address (required for delivery)</div>
              <div class="grid gap-2 mt-2">
                <div>
                  <input class="input" v-model="ship.address1" placeholder="Address line 1 (street, house #)" />
                  <div v-if="errors.address1" class="error text-red-400">{{ errors.address1 }}</div>
                </div>
                <input class="input" v-model="ship.address2"
                  placeholder="Address line 2 (apt, unit, building) (optional)" />
                <div class="grid md:grid-cols-3 gap-2">
                  <div>
                    <input class="input" v-model="ship.city" placeholder="City" />
                  </div>
                  <input class="input" v-model="ship.state" placeholder="State" />
                  <div>
                    <input class="input" v-model="ship.country" placeholder="Country" />
                  </div>
                </div>
                <input class="input" v-model="ship.postal" placeholder="Postal / ZIP (optional)" />
              </div>
            </div>
          </div>

          <div class="mt-4 card p-3">
            <div class="font-black">Payment</div>
            <div class="mt-2 grid gap-2">
              <label class="card p-3" style="border-color:rgba(14,165,233,.22);">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="font-black">Credit Card (demo)</div>
                    <div class="tiny" style="color:#64748b;">Pay via Stripe Checkout.</div>
                  </div>
                  <input type="radio" name="paymethod" value="card" v-model="payMethod" />
                </div>
              </label>

              <label class="card p-3" style="border-color:rgba(34,197,94,.22);">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="font-black">Pay from LinkUp Wallet</div>
                    <div class="tiny" style="color:#64748b;">Instant checkout from wallet balance.</div>
                  </div>
                  <input type="radio" name="paymethod" value="wallet" v-model="payMethod" />
                </div>
              </label>
            </div>
          </div>

          <div class="mt-4 card p-3">
            <div class="font-black">Notes (optional)</div>
            <textarea class="input mt-2" rows="3" v-model="notes" placeholder="Add instructions…"></textarea>
          </div>
        </div>

        <!-- Right -->
        <div class="card p-4">
          <div class="font-black text-lg">Summary</div>
          <div class="mt-3 grid gap-2">
            <div v-for="{ ci, p } in items" :key="ci.productId" class="flex items-start justify-between gap-3">
              <div>
                <div class="font-black">{{ p?.name }}</div>
                <div class="tiny" style="color:#64748b;">Qty {{ ci.qty || 1 }}</div>
              </div>
              <div class="text-right">
                <div class="font-black">${{ (Number(p?.price || 0) * Number(ci.qty || 1)).toFixed(2) }}</div>
              </div>
            </div>
          </div>

          <div class="mt-4 border-t pt-4 space-y-2" style="border-color:rgba(148,163,184,.35);">
            <div v-if="errors._server" class="error mb-2 text-red-500 font-bold text-center">{{ errors._server }}</div>

            <div class="flex items-center justify-between text-sm">
              <div style="color:#64748b;">Subtotal</div>
              <div class="font-bold">${{ subtotal.toFixed(2) }}</div>
            </div>

            <div v-if="feeAmount > 0" class="flex items-center justify-between text-sm">
              <div style="color:#64748b;">{{ props.shopFee?.label || 'Marketplace Fee' }}</div>
              <div class="font-bold">${{ feeAmount.toFixed(2) }}</div>
            </div>

            <div v-if="processFeeAmount > 0" class="flex items-center justify-between text-sm">
              <div style="color:#64748b;">Processing Fee</div>
              <div class="font-bold">${{ processFeeAmount.toFixed(2) }}</div>
            </div>

            <div class="flex items-center justify-between pt-2 mt-2 border-t"
              style="border-color:rgba(148,163,184,.2);">
              <div class="font-black text-lg">Total</div>
              <div class="font-black text-xl text-sky-600">${{ total.toFixed(2) }}</div>
            </div>

            <button class="btn w-full mt-4" @click="placeOrder">
              <span class="inline-flex items-center gap-2">
                <Check class="w-4 h-4" /> Place Order
              </span>
            </button>

            <div v-if="props.shopFee?.disclaimer" class="tiny mt-3 p-2 bg-slate-50 rounded-lg text-center"
              style="color:#64748b;">
              {{ props.shopFee.disclaimer }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.checkout-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, .55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 5000;
  padding: 18px;
}

.modal {
  width: min(920px, 100%);
  max-height: 90vh;
  overflow: auto;
}

.card {
  background: #ffffff;
  border-radius: 22px;
  box-shadow: 0 18px 40px rgba(2, 6, 23, .10);
  border: 1px solid rgba(148, 163, 184, .35);
}

.btn {
  background: linear-gradient(135deg, rgba(14, 165, 233, 1), rgba(34, 197, 94, 1));
  color: #fff;
  border-radius: 16px;
  font-weight: 900;
  padding: .75rem 1rem;
  box-shadow: 0 16px 35px rgba(14, 165, 233, .22);
}

.btn2 {
  border: 1px solid rgba(148, 163, 184, .35);
  border-radius: 16px;
  font-weight: 900;
  padding: .5rem .75rem;
  background: #fff;
}

.input {
  width: 100%;
  border: 1px solid rgba(148, 163, 184, .45);
  border-radius: 16px;
  padding: .6rem .8rem;
  outline: none;
  background: #fff;
}

.tiny {
  font-size: .75rem;
}
</style>
