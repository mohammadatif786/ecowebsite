<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { Ticket } from "@/client/models/Ticket";
import { computed, ref } from "vue";
import moment from "moment";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/front/ui/dialog";
import { Coupon } from "@/client";
import EventCouponCard from "@/components/front/EventCouponCard.vue";
import { LinkupEvent } from "@/client/models/LinkupEvent";
import { Heart, X, ArrowUpFromLine } from "lucide-vue-next";
import { Button } from "@/components/front/ui/button";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselPrevious,
  CarouselNext,
} from "@/components/front/ui/carousel";
import EventCard from "@/components/front/EventCard.vue";
import TicketAddonSelector from "@/components/front/TicketAddonSelector.vue";
import { toast } from "vue-sonner";

const { tickets, walletBalance, coupons, fee, tax, taxConfig, event, otherEvents } = usePage<{
  tickets: Ticket[];
  walletBalance: number;
  coupons: Coupon[];
  fee: number;
  tax: number;
  taxConfig?: { rate: number; tax_fees: boolean; no_state_sales_tax: boolean };
  event: LinkupEvent;
  otherEvents: LinkupEvent[];
}>().props;

const calculateTax = (price: number) => {
  const taxRate = taxConfig?.rate ?? tax ?? 0;
  const taxFees = taxConfig?.tax_fees ?? false;
  
  let taxableAmount = Number(price);
  if (taxFees) {
    const feeAmount = (fee / 100) * taxableAmount;
    taxableAmount += feeAmount;
  }
  
  return (taxRate / 100) * taxableAmount;
};

type CartItem = {
  ticketId: number;
  title: string;
  price: number;
  qty: number;
  fee: number;
  tax: number;
  addons?: AddonSelection[];
  addon_total?: number;
};

type AddonSelection = {
  addon_id: number;
  quantity: number;
  price: number;
};

type AppliedCoupon = {
  couponId: number;
  code: string;
  discount: string;
  discountType: string;
};

type Cart = {
  items: CartItem[];
  appliedCoupons: AppliedCoupon[];
};

const cart = ref<Cart>({
  items: [],
  appliedCoupons: [],
});

const errorDialog = ref({
  open: false,
  title: "",
  description: "",
});

const getCartItem = (ticketId: number) => {
  return cart.value.items.find((item) => item.ticketId === ticketId);
};

const getDrinkAddons = (ticketId: number) => {
  const ticket = tickets.find((t) => t.id === ticketId);
  return ticket ? ticket.drink_addons : null;
};

const addToCart = (ticket: Ticket) => {
  const itemIndex = cart.value.items.findIndex((item) => item.ticketId === ticket.id);

  let item = cart.value.items[itemIndex] ?? null;

  console.log(ticket);
  if (item) {
    if (item.qty < ticket.tickets_per_attendee) {
      ++item.qty;
    }
  } else {
    const ticketPrice = Number(ticket.price) ?? 0;
    const feeAmount = (fee / 100) * ticketPrice;
    const taxAmount = calculateTax(ticketPrice);

    item = {
      ticketId: ticket.id,
      title: ticket.name,
      price: ticketPrice,
      qty: ticket.tickets_per_attendee ?? 1,
      fee: feeAmount,
      tax: taxAmount,
      addons: [],
      addon_total: 0,
    };
    cart.value.items.push(item);
  }
};

const removeFromCart = (ticket: Ticket) => {
  const itemIndex = cart.value.items.findIndex(
    (item) => item.ticketId === ticket.id
  );

  const item = cart.value.items[itemIndex] ?? null;

  if (item) {
    const step = ticket.tickets_per_attendee ?? 1;

    if (item.qty <= step) {
      // If the quantity is equal or less than the step, remove the item completely
      cart.value.items = cart.value.items.filter(
        (item) => item.ticketId !== ticket.id
      );
    } else {
      // Otherwise, subtract by the step
      item.qty -= step;
    }
  }
};

const applyCoupon = (coupon: Coupon) => {
  if (cart.value.appliedCoupons.find((c) => c.couponId === coupon.id)) {
    toast.error("Coupon already applied");
    return;
  }
  cart.value.appliedCoupons.push({
    couponId: coupon.id,
    code: coupon.code,
    discount: coupon.discount,
    discountType: coupon.discount_type,
  });
  toast.success("Coupon applied successfully");
};

const total = computed(() => {
  let total = cart.value.items.reduce((accumulator, item) => {
    // Calculate the total for tickets: (price + fee + tax) * quantity
    const ticketTotal = (item.price + item.fee + item.tax) * item.qty;
    // Add the add-on total for this cart item
    const addonTotal = item.addon_total || 0;
    return accumulator + ticketTotal + addonTotal;
  }, 0);

  // Apply coupon discounts
  cart.value.appliedCoupons.forEach((coupon) => {
    if (coupon.discountType === "percentage") {
      total -= (Number(coupon.discount) / 100) * total;
    } else {
      total -= Number(coupon.discount);
    }
  });

  // Ensure total is not negative
  return Math.max(total, 0);
});

const form = useForm<{ items: CartItem[]; appliedCoupons: AppliedCoupon[] }>({
  items: [],
  appliedCoupons: [],
});

const payFromWallet = () => {
  if (cart.value.items.length === 0) {
    errorDialog.value = {
      open: true,
      title: "No ticket selected",
      description: "Please select at least one ticket",
    };
    return;
  }

  if (walletBalance < total.value) {
    errorDialog.value = {
      open: true,
      title: "Insufficient balance",
      description: "Your wallet does not have sufficient balance",
    };
    return;
  }

  form.items = cart.value.items;
  form.appliedCoupons = cart.value.appliedCoupons;
  form.post(route("frontend.user.wallet.buy-ticket"), {
    onError: (errors) => {
      console.error("Wallet payment errors:", errors);
      errorDialog.value = {
        open: true,
        title: "Payment Error",
        description: errors.cart || "Failed to process wallet payment",
      };
    },
  });
};

const payFromStripe = () => {
  if (cart.value.items.length === 0) {
    errorDialog.value = {
      open: true,
      title: "No ticket selected",
      description: "Please select at least one ticket",
    };
    return;
  }

  console.log("Sending cart to Stripe:", JSON.stringify(cart.value, null, 2));
  form.items = cart.value.items;
  form.appliedCoupons = cart.value.appliedCoupons;
  form.post(route("frontend.stripe.buy-ticket"), {
    onError: (errors) => {
      console.error("Stripe payment errors:", errors);
      errorDialog.value = {
        open: true,
        title: "Payment Error",
        description: errors.payment || errors.items || "Failed to initiate payment",
      };
    },
    onSuccess: () => {
      console.log("Redirecting to Stripe checkout");
    },
  });
};

const formattedStartTime = computed(() => {
  return event.start_time ? moment(event.start_time).format("DD MMM YYYY") : "";
});

const toggleFavorite = (e: any) => {
  e.preventDefault();
  form.post(route("frontend.event.toggle-favorite", event.id), {
    preserveScroll: true,
    preserveState: false,
  });
};

const copyLink = (e: any) => {
  e.preventDefault();
  navigator.clipboard.writeText(window.location.href);
  toast.success("Link Copied Successfully");
};

const updateTicketAddons = (ticketId: number, addons: AddonSelection[]) => {
  const item = cart.value.items.find((item) => item.ticketId === ticketId);
  if (item) {
    item.addons = addons;
    item.addon_total = addons.reduce((sum, addon) => sum + (addon.price || 0) * addon.quantity, 0);
  }
};

const onAddonPriceChange = (ticketId: number, price: number) => {
  const item = cart.value.items.find((item) => item.ticketId === ticketId);
  if (item) {
    item.addon_total = price;
  }
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="bg-gray-100 sm:px-9 lg:px-2 xl:px-9 py-12 rounded-2xl">
      <div class="grid grid-cols-[65%_35%] gap-6">
        <div>
          <h2 class="text-2xl font-bold text-black">{{ event.title }}</h2>
          <h1 class="mb-6">{{ formattedStartTime }}</h1>
          <div class="grid grid-cols-1 xl:grid-cols-2 gap-2">
            <div v-for="ticket in tickets" :key="ticket.id"
              class="xl:max-w-sm w-full bg-[#E6F4FA] rounded-lg border-[3px] border-blue-400 py-2 space-y-2">
              <h2 class="text-blue-800 text-lg px-4">{{ ticket.name }}</h2>
              <div class="flex items-center justify-between py-2 text-white bg-blue-400">
                <div class="text-sm font-semibold px-4">
                  ${{ ticket.price ?? 0 }}
                  <span v-if="ticket.type === 'paid'" class="text-xs font-normal">
                    + ${{ ((fee / 100) * Number(ticket.price)).toFixed(2) }} Fees +
                    ${{ calculateTax(Number(ticket.price)).toFixed(2) }} Taxes
                  </span>
                </div>
                <div class="flex items-center space-x-2 px-4">
                  <button @click="removeFromCart(ticket)"
                    class="w-6 h-6 cursor-pointer rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">
                    −
                  </button>
                  <span class="font-medium">{{ getCartItem(ticket.id)?.qty ?? 0 }}</span>
                  <button @click="addToCart(ticket)"
                    class="w-6 h-6 cursor-pointer rounded-full bg-blue-200 text-blue-700 text-lg leading-none flex items-center justify-center">
                    +
                  </button>
                </div>
              </div>
              <p class="text-sm text-gray-700 px-4">
                Sales end on {{ moment(ticket.sales_end).format("MMM D, YYYY") }}
              </p>
              <p class="text-xs text-gray-500 px-4">{{ ticket.description }}</p>
            </div>
          </div>

          <div v-if="cart.items.length > 0" class="mt-6">
            <h3 class="text-lg font-semibold mb-4">Add-ons</h3>
            <div v-for="cartItem in cart.items" :key="cartItem.ticketId" class="mb-6">
              <div class="bg-white rounded-lg p-4 border">
                <h4 class="font-medium text-gray-900 mb-3">{{ cartItem.title }} ({{ cartItem.qty }}x)</h4>
                <TicketAddonSelector :ticket-id="cartItem.ticketId" :drink-addons="getDrinkAddons(cartItem.ticketId)"
                  :model-value="cartItem.addons || []"
                  @update:model-value="(addons) => updateTicketAddons(cartItem.ticketId, addons)"
                  @price-change="(price) => onAddonPriceChange(cartItem.ticketId, price)" />
              </div>
            </div>
          </div>

          <div class="mt-4 flex gap-2 justify-start">
            <div>
              <Dialog>
                <DialogTrigger as-child>
                  <button v-if="coupons.length > 0 && cart.items.length > 0"
                    class="cursor-pointer bg-primary px-1 sm:px-2 text-xs sm:text-sm md:text-base rounded text-white">
                    Event Coupon
                    <i class="ri-play-fill border rounded-full text-xs ms-1"></i>
                  </button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Event Coupons</DialogTitle>
                  </DialogHeader>
                  <div class="space-y-6">
                    <EventCouponCard v-for="coupon in coupons" :key="coupon.id" :coupon="coupon"
                      :is-applied="!!cart.appliedCoupons.find((c) => c.couponId === coupon.id)" @apply="applyCoupon" />
                  </div>
                </DialogContent>
              </Dialog>
              <button @click="payFromWallet"
                class="cursor-pointer mt-2 bg-blue-500 px-1 sm:px-2 text-xs sm:text-sm md:text-base rounded text-white ms-1">
                Pay From Wallet
              </button>
            </div>
            <div>
              <button @click="payFromStripe"
                class="cursor-pointer mt-2 bg-primary px-1 sm:px-2 text-xs sm:text-sm md:text-base rounded text-white">
                Checkout
              </button>
            </div>
            <div>
              <Button @click="toggleFavorite" variant="outline" size="icon" class="rounded-full ml-2"
                :disabled="form.processing">
                <X v-if="event.auth_user_favorite" stroke-width="4" />
                <Heart v-else fill="black" />
              </Button>
              <Button @click="copyLink" variant="outline" size="icon" class="rounded-full ml-4">
                <ArrowUpFromLine fill="black" />
              </Button>
            </div>
          </div>
        </div>
        <div class="bg-gray-300 mr-8 rounded-xl">
          <div class="w-full h-[40%] overflow-hidden flex justify-center items-center">
            <img class="rounded-xl w-full h-full"
              :src="event.image_url || './../../../../assets/images/bgimageBuyticket.png'" alt="" />
          </div>
          <div class="p-2">
            <h1 class="font-bold text-xl text-black">Order Summary</h1>
            <div class="grid grid-cols-2 mt-4" v-for="cartItem in cart.items" :key="cartItem.ticketId">
              <p class="text-start text-sm text-gray-700">
                {{ cartItem.qty }} x {{ cartItem.title }}
              </p>
              <p class="text-end text-sm text-gray-700">
                ${{ (cartItem.qty * cartItem.price).toFixed(2) }}
              </p>
              <div v-if="cartItem.addons && cartItem.addons.length > 0" class="col-span-2 mt-2 ml-4">
                <div v-for="addon in cartItem.addons" :key="addon.addon_id"
                  class="grid grid-cols-2 text-xs text-gray-600">
                  <span>+ {{ addon.quantity }} × Addon</span>
                  <span class="text-end">${{ (addon.quantity * (addon.price || 0)).toFixed(2) }}</span>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-2 mt-4">
              <span class="font-bold text-start text-black text-lg">Total</span>
              <span class="font-bold text-end text-black text-lg">${{ total.toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="w-full mt-16 bg-white p-10 rounded-lg">
      <h1 class="text-black font-bold text-3xl">More events from organizer</h1>
      <Carousel class="mt-5">
        <CarouselPrevious class="absolute left-[calc(100%-100px)] top-[-40px] -translate-y-1/2 z-10" />
        <CarouselNext class="absolute -right-[-20px] top-[-40px] -translate-y-1/2 z-10" />
        <CarouselContent>
          <CarouselItem v-for="event in otherEvents" :key="event.id"
            class="basis-full sm:basis-1/2 md:basis-1/2 lg:basis-1/2 xl:basis-1/4">
            <EventCard :event="event" />
          </CarouselItem>
        </CarouselContent>
      </Carousel>
    </div>

    <Dialog :open="errorDialog.open" @update:open="(open) => (errorDialog.open = open)">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ errorDialog.title }}</DialogTitle>
        </DialogHeader>
        {{ errorDialog.description }}
      </DialogContent>
    </Dialog>
  </AuthenticatedLayout>
</template>