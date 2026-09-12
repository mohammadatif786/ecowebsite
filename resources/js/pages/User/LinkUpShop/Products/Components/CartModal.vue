<script setup lang="ts">
import { CreditCard, ShoppingBag, Trash2, XCircle } from 'lucide-vue-next';
import { computed, nextTick, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
  show: boolean;
  products: any[];
  cart: Array<{ productId: number; qty: number }>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update-cart', value: Array<{ productId: number; qty: number }>): void;
  (e: 'checkout'): void;
}>();

const items = computed(() => {
  const map = new Map(props.products.map((p: any) => [p.id, p]));
  return (props.cart || []).map(ci => ({ ci, p: map.get(ci.productId) })).filter(x => !!x.p);
});

const total = computed(() => items.value.reduce((sum, x) => sum + Number(x.p?.price || 0) * Number(x.ci?.qty || 0), 0));

const isCartEmpty = computed(() => items.value.length === 0);

function close() { emit('close'); }

function remove(productId: number) {
  const next = (props.cart || []).filter(c => c.productId !== productId);
  toast.success("Product removed from cart");
  emit('update-cart', next);
}

function clearAll() {
  emit('update-cart', []);
  toast.success("Cart cleared");
}

function changeQty(productId: number, delta: number) {
  const item = items.value.find(x => x.ci.productId === productId);
  if (!item) return;

  const currentQty = item.ci.qty || 1;
  const stock = item.p?.qty || 0;
  const nextQty = currentQty + delta;

  if (nextQty > stock && delta > 0) {
    toast.error(`Only ${stock} items available in stock`);
    return;
  }

  const next = (props.cart || []).map(c => 
    c.productId === productId ? { ...c, qty: Math.max(1, nextQty) } : c
  );
  emit('update-cart', next);
}

watch(() => props.show, async (v) => {
  if (v) {
    await nextTick();
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }
});

// const itemsWithSeller = computed(() => {
//   return (items.value || []).map((c: any) => {
//     if (c.p.listing_type === "Carnival Group" || c.p.listing_type === "Store") {
//       return {
//         ...c,
//         seller_name: `${c.p.merchant.name} • ${c.p.category.name}`
//       };
//     }

//     if (c.p.listing_type === "Individual") {
//       return { ...c, seller_name: `Individual Seller • ${c.p.category.name}` };
//     }

//     if (c.p.listing_type === "Administrative") {
//       return { ...c, seller_name: `LinkUp Network • ${c.p.category.name}` };
//     }

//     return { ...c, seller_name: "" };
//   });
// });

const itemsWithSeller = computed(() => {
  return (items.value || []).map((c: any) => {
    const merchantName = c.p?.merchant?.name || "Unknown Merchant";
    const categoryName = c.p?.category?.name || "Unknown Category";

    if (c.p.listing_type === "Carnival Group" || c.p.listing_type === "Store") {
      return {
        ...c,
        seller_name: `${merchantName} • ${categoryName}`
      };
    }

    if (c.p.listing_type === "Individual") {
      return { ...c, seller_name: `Individual Seller • ${categoryName}` };
    }

    if (c.p.listing_type === "Administrative") {
      return { ...c, seller_name: `LinkUp Network • ${categoryName}` };
    }

    return { ...c, seller_name: "" };
  });
});
</script>

<template>
  <div v-if="show" class="cart-modal-backdrop" @click="e => { if (e.target === e.currentTarget) close(); }">
    <div class="card modal p-4 md:p-6">
      <div class="flex items-start gap-3">
        <div class="w-32 h-32 rounded-2xl grid place-items-center" style="margin-top: -30px;">
          <img src="/storage/avatars/marketplacelog.png" alt="Marketplace Logo" class="w-full h-full" />
        </div>
        <div class="flex-1">
          <div class="text-xl font-black">Cart</div>
          <div class="text-sm mt-1" style="color:#64748b;">
            {{ items.length ? 'Review items and checkout.' : 'Your cart is empty.' }}
          </div>
        </div>
        <button class="btn2" @click="close">
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

      <div class="mt-5 grid gap-3">
        <template v-if="items.length">
          <div v-for="{ ci, p, seller_name  } in itemsWithSeller" :key="ci.productId" class="card p-3 flex items-center gap-3">
            <img :src="p?.cover_image || ''" class="w-16 h-16 rounded-2xl object-cover border"
              style="border-color:rgba(148,163,184,.35);" />
            <div class="flex-1">
              <div class="font-black">{{ p?.name || 'Item' }}</div>
              <div class="text-sm" style="color:#64748b;"> {{ seller_name }}</div>
              <div class="mt-2 flex items-center gap-2">
                <span class="chip">${{ Number(p?.price || 0).toFixed(2) }} each</span>
                <span class="chip">Qty: {{ ci.qty || 1 }}</span>
                <span v-if="ci.qty >= (p?.qty || 0)" class="text-[10px] font-bold text-red-500 ml-1">Max Stock reached</span>
                <div class="inline-flex gap-1 ml-2">
                  <button class="btn2" :disabled="ci.qty <= 1" @click="changeQty(ci.productId, -1)">-</button>
                  <button class="btn2" :disabled="ci.qty >= (p?.qty || 0)" @click="changeQty(ci.productId, 1)">+</button>
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-black">${{ (Number(p?.price || 0) * Number(ci.qty || 1)).toFixed(2) }}</div>
              <button class="btn2 mt-2" @click="remove(ci.productId)">
                <span class="inline-flex items-center gap-2">
                  <Trash2 class="w-4 h-4" /> Remove
                </span>
              </button>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="card p-10 text-center">
            <div class="text-2xl font-black">Nothing here yet</div>
            <div class="mt-2" style="color:#64748b;">Add products to checkout.</div>
          </div>
        </template>

        <div class="card p-4">
          <div class="flex items-center justify-between">
            <div class="font-black">Total</div>
            <div class="font-black text-xl">${{ total.toFixed(2) }}</div>
          </div>
          <div class="mt-3 flex gap-2">
            <button class="btn flex-1" :disabled="isCartEmpty" @click="emit('checkout')">
              <span class="inline-flex items-center gap-2">
                <CreditCard class="w-5 h-5" /> Checkout
              </span>
            </button>
            <button class="btn2 flex-1" :disabled="!items.length" @click="clearAll">
              <span class="inline-flex items-center gap-2">
                <XCircle class="w-5 h-5" />
                Clear
              </span>
            </button>
          </div>
          <div class="tiny mt-2" style="color:#64748b;">
            Pickup option appears at checkout only when seller is Store/Carnival Group (inherited).
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cart-modal-backdrop {
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

.btn:disabled {
  background: linear-gradient(135deg, rgba(14, 165, 233, 1), rgba(34, 197, 94, 1));
  color: #fff;
  border-radius: 16px;
  font-weight: 900;
  padding: .75rem 1rem;
  box-shadow: 0 16px 35px rgba(14, 165, 233, .22);
  cursor: not-allowed;
  opacity: 0.6;
}

.btn2 {
  border: 1px solid rgba(148, 163, 184, .35);
  border-radius: 16px;
  font-weight: 900;
  padding: .5rem .75rem;
  background: #fff;
}

.btn2:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f8fafc;
}

.chip {
  border: 1px solid rgba(148, 163, 184, .35);
  border-radius: 999px;
  padding: .2rem .5rem;
  font-weight: 800;
  font-size: .75rem;
}

.tiny {
  font-size: .75rem;
}
</style>
