<template>
  <div v-if="isOpen" class="fixed inset-0 z-[150] overflow-y-auto" style="background:rgba(248,250,252,0.95)">
    <div class="bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between sticky top-0 z-10 shadow-sm">
      <h2 class="text-xl font-black truncate max-w-lg">{{ event?.title }}</h2>
      <button @click="close" class="text-slate-400 font-bold hover:text-slate-600 transition">Close</button>
    </div>

    <div class="p-6 grid lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
      <!-- MAIN TICKET & ADDONS CONFIG -->
      <div class="lg:col-span-2 space-y-5">
        <div class="rounded-2xl bg-blue-50 text-lkblue2 font-black px-4 py-3 flex items-center gap-2">🎫 Tickets</div>

        <!-- Multiple ticket types -->
        <div v-if="event?.tickets && event.tickets.length > 0" class="space-y-3">
          <div v-for="ticket in event.tickets" :key="ticket.id"
            class="rounded-2xl border-2 border-dashed border-blue-200 p-4 bg-white">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-black flex items-center gap-2">
                  {{ ticket.name }}
                  <span class="chip" style="padding:3px 10px;font-size:11px">Ticket</span>
                </p>
                <p class="text-sm text-slate-400 mt-1">{{ ticket.name }}</p>
              </div>
              <div class="flex flex-col items-end gap-1 shrink-0">
                <span v-if="isTicketSaleEnded(ticket)" class="chip bg-rose-500 text-white border-rose-500" style="padding:6px 10px;font-size:11px">No longer available</span>
                <template v-else>
                  <p v-if="getDiscountedTicketPrice(ticket) > 0" class="text-xl font-black">{{ fmtCcy(getDiscountedTicketPrice(ticket)) }}</p>
                  <p v-else class="text-xs font-black bg-emerald-500 text-white px-2 py-1 rounded-lg">FREE</p>
                  <span v-if="isTicketSoldOut(ticket)" class="chip bg-rose-50 text-rose-600 border-rose-100" style="padding:3px 10px;font-size:11px">Sold Out</span>
                </template>
              </div>
            </div>

            <div class="flex items-center justify-between mt-3 pt-3 border-t border-dashed border-blue-200 flex-wrap gap-2">
              <span class="chip">Type: {{ event?.category.name }} • Available: {{ ticketAvailableQty(ticket) }}</span>
              <div class="flex items-center gap-2">
                <button
                  @click="changeTicketQty(ticket.id, -1)"
                  :disabled="getTicketQty(ticket.id) <= 0"
                  class="h-8 w-8 rounded-lg border border-slate-200 font-black flex items-center justify-center hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                >−</button>
                <span class="w-6 text-center font-black">{{ getTicketQty(ticket.id) }}</span>
                <button
                  @click="changeTicketQty(ticket.id, 1)"
                  :disabled="isTicketUnavailable(ticket) || getTicketQty(ticket.id) >= ticketAvailableQty(ticket) || isTicketSoldOut(ticket)"
                  class="h-8 w-8 rounded-lg border border-slate-200 font-black flex items-center justify-center hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                >+</button>
              </div>
            </div>

            <div v-if="hasDrinkAddons(ticket) && getTicketQty(ticket.id) > 0" class="mt-3">
              <button
                type="button"
                @click="openDrinksModal(ticket.id)"
                class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-black text-lkblue2 hover:bg-blue-100 transition"
              >
                Add Drinks
              </button>
            </div>

            <div v-if="isPackageTicket(ticket)" class="mt-3">
              <button
                type="button"
                @click="openPackageModal(ticket.id)"
                class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-black text-violet-700 hover:bg-violet-100 transition"
              >
                View Package
              </button>
            </div>

            <!-- Additional ticket details -->
            <div class="mt-3 pt-3 border-t border-dashed border-blue-200 space-y-2 text-sm">
              <p class="text-slate-600">
                <span class="font-bold">Price:</span> {{ fmtCcy(getDiscountedTicketPrice(ticket)) }}
                <span v-if="getDiscountedTicketPrice(ticket) > 0" class="text-slate-400 text-xs ml-2">
                  Fees and tax are calculated in the order summary.
                </span>
              </p>
              <p v-if="ticketSalesEndValue(ticket)" class="text-slate-500 text-xs">
                <span class="font-bold">Sales end:</span> {{ formatDate(ticketSalesEndValue(ticket)) }}
              </p>
              <p v-if="ticket.description" class="text-slate-500 text-xs">
                {{ ticket.description }}
              </p>
            </div>

            <WellnessServices
              v-if="activeWellnessTicket?.id === ticket.id"
              :ticket-id="ticket.id"
              :ticket-name="ticket.name"
              :wellness-config="ticket.wellness"
              :slot-blocks="activeWellnessSlotBlocks"
              :currency="ccy"
              :format-price="fmtCcy"
              :has-ticket-in-cart="getTicketQty(ticket.id) > 0"
              v-model:selection="wellnessSelection"
              @update-service-mode="handleUpdateServiceMode"
              @update-contact-phone="handleUpdateContactPhone"
              @update-included-service="handleUpdateIncludedService"
              @select-slot="handleSelectSlot"
              @release-slot="handleReleaseSlot"
              @inc-service="handleIncService"
              @dec-service="handleDecService"
              @inc-manual-addon="handleIncManualAddon"
              @dec-manual-addon="handleDecManualAddon"
            />

            <div
              v-if="activeCookoutTicket?.id === ticket.id"
              class="mt-4 rounded-2xl border border-orange-100 bg-orange-50/40 p-4 space-y-4"
            >
              <div class="flex items-center justify-between flex-wrap gap-2">
                <p class="font-black flex items-center gap-2">Cookout Plate Order</p>
                <span class="chip border-orange-200 text-orange-700 bg-orange-100">Ticket: {{ ticket.name }}</span>
              </div>
              <p class="text-sm text-slate-500 -mt-2">Base includes one plate. Add-ons update totals automatically.</p>

              <div v-if="cookoutIncludedProteins(ticket).length > 0">
                <p class="font-black mb-1">Choose Included Plate (1)</p>
                <p class="text-xs text-slate-400 mb-2">Choose exactly one protein for your included plate.</p>
                <div class="space-y-2">
                  <label v-for="p in cookoutIncludedProteins(ticket)" :key="`included-${ticket.id}-${p}`" class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm bg-white cursor-pointer hover:border-orange-300 transition">
                    <input type="radio" :name="`included-protein-${ticket.id}`" :value="p" :checked="getCookoutIncludedProteinValue(ticket) === p" @change="setCookoutIncludedProtein(ticket, p)" class="accent-orange-500"/>
                    <b>{{ p }}</b>
                    <span class="text-slate-400 font-semibold ml-auto">{{ getCookoutIncludedProteinValue(ticket) === p ? 'Included plate' : '' }}</span>
                  </label>
                </div>
              </div>

              <div v-if="cookoutSides(ticket).length > 0">
                <p class="font-black mb-1">Included Sides (Grab &amp; Go)</p>
                <p class="text-sm text-slate-600">{{ cookoutSides(ticket).join(', ') }}</p>
              </div>

              <div v-if="cookoutIncludedDrinks(ticket).length > 0">
                <p class="font-black mb-1">Included Drinks</p>
                <p class="text-sm text-slate-600">{{ cookoutIncludedDrinks(ticket).join(', ') }}</p>
              </div>

              <div v-if="cookoutAddonDrinks(ticket).length > 0">
                <div class="flex items-center justify-between mb-3">
                  <p class="font-black">Add-on Drinks</p>
                  <span class="chip border-slate-200 bg-white" style="padding:2px 8px;font-size:10px">Paid</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div v-for="d in cookoutAddonDrinks(ticket)" :key="`drink-${ticket.id}-${d.name}`" class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm hover:border-orange-200 transition">
                    <p class="font-black text-sm">{{ d.name }} <span class="chip bg-orange-50 text-orange-600 border-orange-100" style="padding:2px 8px;font-size:10px">Add-on</span></p>
                    <p class="font-black text-lg mt-1 text-slate-700">{{ fmtCcy(d.price) }}</p>
                    <div class="flex items-center gap-2 mt-3 bg-slate-50 w-max rounded-lg border border-slate-100">
                      <button @click="decCookoutDrink(ticket, d.name)" class="h-8 w-8 font-black text-slate-500 hover:text-slate-700">-</button>
                      <span class="w-6 text-center font-black">{{ getCookoutDrinkQty(ticket, d.name) }}</span>
                      <button
                        @click="incCookoutDrink(ticket, d.name)"
                        :disabled="d.maxQty !== null && d.maxQty > 0 && getCookoutDrinkQty(ticket, d.name) >= d.maxQty"
                        class="h-8 w-8 font-black text-slate-500 hover:text-slate-700 disabled:opacity-40 disabled:cursor-not-allowed"
                      >+</button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="cookoutAddonProteins(ticket).length > 0">
                <div class="flex items-center justify-between mb-3">
                  <p class="font-black">Add-on Proteins</p>
                  <span class="chip border-slate-200 bg-white" style="padding:2px 8px;font-size:10px">Paid</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div v-for="p in cookoutAddonProteins(ticket)" :key="`protein-${ticket.id}-${p.name}`" class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm hover:border-orange-200 transition">
                    <p class="font-black text-sm">{{ p.name }} <span class="chip bg-orange-50 text-orange-600 border-orange-100" style="padding:2px 8px;font-size:10px">Add-on</span></p>
                    <div class="flex items-baseline gap-2 mt-1">
                      <p class="font-black text-lg text-slate-700">{{ fmtCcy(p.price) }}</p>
                      <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Avail: {{ p.maxQty === null ? '-' : p.maxQty }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-3 bg-slate-50 w-max rounded-lg border border-slate-100">
                      <button @click="decCookoutProtein(ticket, p.name)" class="h-8 w-8 font-black text-slate-500 hover:text-slate-700">-</button>
                      <span class="w-6 text-center font-black">{{ getCookoutProteinQty(ticket, p.name) }}</span>
                      <button
                        @click="incCookoutProtein(ticket, p.name)"
                        :disabled="p.maxQty !== null && p.maxQty > 0 && getCookoutProteinQty(ticket, p.name) >= p.maxQty"
                        class="h-8 w-8 font-black text-slate-500 hover:text-slate-700 disabled:opacity-40 disabled:cursor-not-allowed"
                      >+</button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="cookoutManualAddons(ticket).length > 0">
                <div class="flex items-center justify-between mb-3">
                  <p class="font-black">Extras</p>
                  <span class="chip border-slate-200 bg-white" style="padding:2px 8px;font-size:10px">Optional</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div v-for="a in cookoutManualAddons(ticket)" :key="`extra-${ticket.id}-${a.name}`" class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm hover:border-orange-200 transition">
                    <p class="font-black text-sm">{{ a.name }} <span class="chip bg-orange-50 text-orange-600 border-orange-100" style="padding:2px 8px;font-size:10px">Extra</span></p>
                    <div class="flex items-baseline gap-2 mt-1">
                      <p class="font-black text-lg text-slate-700">{{ fmtCcy(a.price) }}</p>
                      <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Avail: {{ a.maxQty === null ? '-' : a.maxQty }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-3 bg-slate-50 w-max rounded-lg border border-slate-100">
                      <button @click="decCookoutManualAddon(ticket, a.name)" class="h-8 w-8 font-black text-slate-500 hover:text-slate-700">-</button>
                      <span class="w-6 text-center font-black">{{ getCookoutManualAddonQty(ticket, a.name) }}</span>
                      <button
                        @click="incCookoutManualAddon(ticket, a.name)"
                        :disabled="a.maxQty !== null && a.maxQty > 0 && getCookoutManualAddonQty(ticket, a.name) >= a.maxQty"
                        class="h-8 w-8 font-black text-slate-500 hover:text-slate-700 disabled:opacity-40 disabled:cursor-not-allowed"
                      >+</button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="cookoutTotalForTicket(ticket) > 0" class="rounded-xl border border-orange-100 bg-white p-3 flex items-center justify-between">
                <span class="font-black text-sm text-slate-700">Cookout add-ons total</span>
                <span class="font-black text-slate-900">{{ fmtCcy(cookoutTotalForTicket(ticket)) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="card p-10 text-center font-bold text-slate-400 bg-white border-2 border-dashed border-slate-100">
           No tickets are currently available for this event.
        </div>
      </div>

      <!-- ORDER SUMMARY & CHECKOUT -->
      <div class="rounded-2xl border-2 border-blue-200 p-5 h-fit bg-white shadow-sm sticky top-[88px]">
        <img :src="event?.image_url" class="w-full h-40 object-cover rounded-xl mb-4 shadow-inner"/>
        <p class="font-black text-lg mb-4 flex items-center gap-2"><i data-lucide="receipt" class="w-5 h-5 text-slate-400"></i> Order Summary</p>

        <template v-if="cartItems.length === 0">
          <div class="bg-slate-50 rounded-xl border border-slate-100 py-10 text-center">
            <i data-lucide="shopping-cart" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
            <p class="text-slate-500 font-bold text-sm">No items in cart</p>
          </div>
        </template>
        <template v-else>
          <div class="text-sm space-y-2.5 mb-4">
            <template v-for="item in cartItems" :key="item.ticketId">
              <div class="flex justify-between items-start">
                <span class="font-bold text-slate-700">x{{ item.qty }} {{ item.name }}</span>
                <b class="text-slate-900">{{ fmtCcy(item.price * item.qty) }}</b>
              </div>
              <TicketPackageDetails
                v-if="packageTicketForItem(item)"
                :ticket="packageTicketForItem(item)"
                :format-price="fmtCcy"
                class="mt-2"
              />
            </template>

            <div
              v-for="drink in activeDrinkItems"
              :key="drink.sku"
              class="flex justify-between text-[11px] text-slate-600 pl-2 border-l-2 border-blue-100"
            >
              <span>{{ drink.name }} <span class="font-bold">x{{ drink.qty }}</span></span>
              <span class="font-bold text-slate-700">{{ fmtCcy(drink.total) }}</span>
            </div>

            <template v-if="activeWellnessTicket">
              <div class="flex justify-between text-[11px] text-slate-500 pl-2 border-l-2 border-slate-100">
                <span>Service mode</span><span>{{ wellnessSelection.serviceMode === 'mobile' ? 'Mobile' : 'In-house' }}</span>
              </div>
              <div v-if="wellnessSelection.selectedSlot" class="flex justify-between text-[11px] text-slate-500 pl-2 border-l-2 border-slate-100">
                <span>Time slot (HOLD)</span><span>{{ wellnessSelection.selectedSlot }}</span>
              </div>
              <div v-if="wellnessSelection.serviceMode === 'mobile' && wellnessSelection.contactPhone" class="flex justify-between text-[11px] text-slate-500 pl-2 border-l-2 border-slate-100">
                <span>Contact phone</span><span>{{ wellnessSelection.contactPhone }}</span>
              </div>
              <div v-if="wellnessSelection.includedService" class="flex justify-between text-[11px] text-slate-500 pl-2 border-l-2 border-slate-100">
                <span>{{ wellnessSelection.includedService }} (Included Service) x{{ activeWellnessQty }}</span><span>{{ fmtCcy(0) }}</span>
              </div>
              <template v-for="(qty, serviceName) in wellnessSelection.services" :key="serviceName">
                <div v-if="qty > 0" class="flex justify-between text-[11px] text-slate-500 pl-2 border-l-2 border-slate-100">
                  <span>{{ serviceName }} (Add-on) x{{ qty }}</span><span>{{ fmtCcy(wellnessServicePrice(serviceName) * qty) }}</span>
                </div>
              </template>
              <template v-for="(qty, addonName) in wellnessSelection.manualAddons" :key="addonName">
                <div v-if="qty > 0" class="flex justify-between text-[11px] text-slate-500 pl-2 border-l-2 border-slate-100">
                  <span>{{ addonName }} (Manual Add-on) x{{ qty }}</span><span>{{ fmtCcy(wellnessManualAddonPrice(addonName) * qty) }}</span>
                </div>
              </template>
              <div v-if="wellnessSelection.serviceMode === 'mobile' && activeWellnessMobileFee > 0" class="flex justify-between text-[11px] text-slate-500 pl-2 border-l-2 border-slate-100">
                <span>Mobile Service Fee</span><span>{{ fmtCcy(activeWellnessMobileFee) }}</span>
              </div>
            </template>

            <template v-if="activeCookoutTicket">
              <template v-for="line in getCookoutLinesForTicket(activeCookoutTicket)" :key="line.nameType">
                <div class="flex justify-between text-[11px] text-slate-500 pl-2 border-l-2 border-slate-100">
                  <span>{{ line.nameType }} <span v-if="line.qty">x{{ line.qty }}</span></span><span>{{ fmtCcy(line.total) }}</span>
                </div>
              </template>
              <div v-if="cookoutSides(activeCookoutTicket).length > 0" class="rounded-xl bg-slate-50 p-2.5 mt-2 border border-slate-100">
                <p class="font-black text-[11px] text-slate-700">Included Sides (Grab &amp; Go)</p>
                <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">{{ cookoutSides(activeCookoutTicket).join(', ') }}</p>
              </div>
              <div v-if="cookoutIncludedDrinks(activeCookoutTicket).length > 0" class="rounded-xl bg-slate-50 p-2.5 mt-2 border border-slate-100">
                <p class="font-black text-[11px] text-slate-700">Included Drinks</p>
                <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">{{ cookoutIncludedDrinks(activeCookoutTicket).join(', ') }}</p>
              </div>
            </template>
          </div>

          <div class="border-t border-slate-100 pt-3 space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Services Subtotal:</span>
              <b class="text-slate-700">{{ fmtCcy(subtotal) }}</b>
            </div>

            <!-- APPLIED COUPONS BREAKDOWN -->
            <template v-if="appliedCoupons.length">
              <div v-for="c in appliedCoupons" :key="c.id" class="flex justify-between text-emerald-600 font-medium">
                <span>Coupon Discount: </span>
                <b>-{{ fmtCcy(getIndividualCouponDiscount(c)) }}</b>
              </div>
            </template>

            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Ticket &amp; Processing Fees:</span>
              <b class="text-slate-700">{{ fmtCcy(ticketProcessingFees) }}</b>
            </div>
            <div v-if="orderFees.vipPackageFeePct > 0" class="flex justify-between">
              <span class="text-slate-500 font-semibold">VIP Package Fee:</span>
              <b class="text-slate-700">{{ fmtCcy(orderFees.vipPackageFeePct) }}</b>
            </div>
            <div v-if="orderFees.drinkFees > 0" class="flex justify-between">
              <span class="text-slate-500 font-semibold">Drink Fees:</span>
              <b class="text-slate-700">{{ fmtCcy(orderFees.drinkFees) }}</b>
            </div>
            <div v-if="orderFees.bottleFee > 0" class="flex justify-between">
              <span class="text-slate-500 font-semibold">Bottle Fees:</span>
              <b class="text-slate-700">{{ fmtCcy(orderFees.bottleFee) }}</b>
            </div>
            <div v-if="orderFees.mobileFee > 0" class="flex justify-between">
              <span class="text-slate-500 font-semibold">Mobile Fee:</span>
              <b class="text-slate-700">{{ fmtCcy(orderFees.mobileFee) }}</b>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Tax:</span>
              <b class="text-slate-700">{{ fmtCcy(totalTax) }}</b>
            </div>
          </div>

          <div class="border-t-2 border-slate-100 mt-3 pt-3 flex justify-between items-center bg-slate-50 -mx-5 px-5 pb-2">
            <span class="font-black text-lg">Total</span>
            <span class="font-black text-xl text-emerald-600">{{ fmtCcy(total) }}</span>
          </div>
        </template>

        <!-- COUPON INPUT -->
        <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-100">
          <p class="text-xs font-black text-slate-400 uppercase mb-2">Have a coupon?</p>
          <div class="flex gap-2">
            <input
              v-model="couponInput"
              placeholder="Enter code"
              class="flex-1 min-w-0 rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue bg-white"
              @keyup.enter="applyCoupon"
            />
            <button
              @click="applyCoupon"
              :disabled="!couponInput.trim()"
              class="btn bg-slate-900 text-white px-4 py-2 text-xs font-black disabled:opacity-50"
            >
              Apply
            </button>
          </div>
          <div v-if="appliedCoupons.length" class="mt-2 space-y-1">
            <div v-for="c in appliedCoupons" :key="c.id" class="flex items-center justify-between bg-emerald-50 text-emerald-700 px-2 py-1.5 rounded-lg border border-emerald-100">
              <span class="text-xs font-bold flex items-center gap-1">
                <i data-lucide="tag" class="w-3 h-3"></i>
                {{ c.code }} (-{{ c.discountType === 'percentage' ? c.discount + '%' : fmtCcy(c.discount) }})
              </span>
              <button @click="removeCoupon(c.id)" class="text-emerald-900/40 hover:text-emerald-900 transition">✕</button>
            </div>
          </div>
        </div>

        <div class="mt-4 space-y-2 pt-2">
          <button @click="clear" :disabled="cartItems.length === 0" class="btn btn-ghost w-full py-2.5 text-sm disabled:opacity-40 disabled:cursor-not-allowed transition">Clear Cart</button>
          <button @click="pay('wallet')" :disabled="cartItems.length === 0" class="btn w-full text-white font-black py-3.5 disabled:opacity-40 disabled:cursor-not-allowed transition shadow hover:shadow-md hover:-translate-y-0.5" style="background:#10b981">
            Pay with Wallet
          </button>
          <button @click="pay('card')" :disabled="cartItems.length === 0" class="btn w-full text-white font-black py-3.5 disabled:opacity-40 disabled:cursor-not-allowed transition shadow hover:shadow-md hover:-translate-y-0.5" style="background:#2563eb">
            Pay with Card
          </button>
          <button @click="pay('bank')" :disabled="cartItems.length === 0" class="btn w-full text-white font-black py-3.5 disabled:opacity-40 disabled:cursor-not-allowed transition shadow hover:shadow-md hover:-translate-y-0.5" style="background:#c41e3a">
            Pay with Bank
          </button>
        </div>
      </div>
    </div>

    <AddDrinksModal
      :open="showDrinksModal"
      :ticket="drinksTicket"
      :quantities="drinkQuantities"
      :format-price="fmtCcy"
      @close="closeDrinksModal"
      @inc="changeDrinkQty($event, 1)"
      @dec="changeDrinkQty($event, -1)"
    />
    <ViewPackageModal
      :open="showPackageModal"
      :ticket="packageTicket"
      :format-price="fmtCcy"
      @close="closePackageModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import WellnessServices from '../components/WellnessServices.vue';
import AddDrinksModal from '../tickets/AddDrinksModal.vue';
import TicketPackageDetails from '../tickets/TicketPackageDetails.vue';
import ViewPackageModal from '../tickets/ViewPackageModal.vue';
import {
  calculateCheckoutTotals,
  getDiscountedTicketPrice,
  getTicketRemaining,
} from '../tickets/ticketCheckoutMath';
import {
  addonIdForDrink,
  findDrinkForSku,
  getDrinkAvailable,
  getDrinkPrice,
  hasDrinkAddons,
  parseDrinkSku,
} from '../tickets/ticketDrinkAddons';
import { isPackageTicket } from '../tickets/ticketPackages';

const emit = defineEmits(['complete']);
const props = defineProps({
  eventFeeSettings: { type: Object, default: () => ({}) },
  taxRules: { type: Object, default: () => ({}) },
});

const isOpen = ref(false);
const event = ref(null);
const ticketQuantities = ref({}); // Object to store quantities for each ticket ID
const drinkQuantities = ref({});
const showDrinksModal = ref(false);
const drinksTicketId = ref(null);
const showPackageModal = ref(false);
const packageTicketId = ref(null);
const slot = ref(null);
const serviceMode = ref('inhouse');
const locationMode = ref('address');
const mobileAddress = ref('');
const mobilePinPos = ref(null);
const contactPhone = ref('');
const cookoutProtein = ref('');
const addons = ref({});
const couponInput = ref('');
const appliedCoupons = ref([]);
const holdTimer = ref(0);
const holdExpiresAt = ref(null);

// Wellness selections per ticket
const wellnessSelections = ref({});

// Single wellness selection for the modal (simplified)
const wellnessSelection = ref({
    includedService: '',
    services: {},
    manualAddons: {},
    selectedSlot: null,
    selectedSlotDate: null,
    holdExpiresAt: null,
    serviceMode: 'inhouse',
    contactPhone: ''
});

// Cookout selections per ticket
const cookoutSelections = ref({});

const ccy = computed(() => event.value?.currency || 'USD');
const fmtCcy = (val) => {
  const v = Number(val || 0);
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: ccy.value }).format(v);
};

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const startHoldTimer = () => {
  if (window.wellnessTimer) clearInterval(window.wellnessTimer);

  const updateTimer = () => {
    if (!holdExpiresAt.value) {
      holdTimer.value = 0;
      clearInterval(window.wellnessTimer);
      return;
    }

    const now = Date.now();
    const diff = Math.max(0, Math.floor((holdExpiresAt.value - now) / 1000));
    holdTimer.value = diff;

    if (diff <= 0) {
      slot.value = null;
      holdExpiresAt.value = null;
      clearInterval(window.wellnessTimer);
    }
  };

  updateTimer();
  window.wellnessTimer = setInterval(updateTimer, 1000);
};

const changeAddonQty = (addonKey, delta, maxQty = 99) => {
  const current = addons.value[addonKey] || 0;
  const newQty = Math.max(0, Math.min(current + delta, maxQty));
  addons.value[addonKey] = newQty;
};

// WellnessServices event handlers
const handleUpdateServiceMode = (mode) => {
  wellnessSelection.value.serviceMode = mode;
  serviceMode.value = mode;
};

const handleUpdateContactPhone = (phone) => {
  wellnessSelection.value.contactPhone = phone;
  contactPhone.value = phone;
};

const handleUpdateIncludedService = (service) => {
  wellnessSelection.value.includedService = service;
};

const handleSelectSlot = (slotKey, date) => {
  const expiresAt = Date.now() + 5 * 60 * 1000;
  wellnessSelection.value.selectedSlot = slotKey;
  wellnessSelection.value.selectedSlotDate = date;
  wellnessSelection.value.holdExpiresAt = expiresAt;
  slot.value = slotKey;
  holdExpiresAt.value = expiresAt;
  startHoldTimer();
};

const handleReleaseSlot = () => {
  wellnessSelection.value.selectedSlot = null;
  wellnessSelection.value.selectedSlotDate = null;
  wellnessSelection.value.holdExpiresAt = null;
  slot.value = null;
  holdExpiresAt.value = null;
  if (window.wellnessTimer) {
    clearInterval(window.wellnessTimer);
  }
};

const handleIncService = (name) => {
  const current = wellnessSelection.value.services[name] || 0;
  wellnessSelection.value.services = { ...wellnessSelection.value.services, [name]: current + 1 };
};

const handleDecService = (name) => {
  const current = wellnessSelection.value.services[name] || 0;
  if (current <= 0) return;
  const next = current - 1;
  if (next <= 0) {
    const newServices = { ...wellnessSelection.value.services };
    delete newServices[name];
    wellnessSelection.value.services = newServices;
  } else {
    wellnessSelection.value.services = { ...wellnessSelection.value.services, [name]: next };
  }
};

const handleIncManualAddon = (name) => {
  const current = wellnessSelection.value.manualAddons[name] || 0;
  wellnessSelection.value.manualAddons = { ...wellnessSelection.value.manualAddons, [name]: current + 1 };
};

const handleDecManualAddon = (name) => {
  const current = wellnessSelection.value.manualAddons[name] || 0;
  if (current <= 0) return;
  const next = current - 1;
  if (next <= 0) {
    const newAddons = { ...wellnessSelection.value.manualAddons };
    delete newAddons[name];
    wellnessSelection.value.manualAddons = newAddons;
  } else {
    wellnessSelection.value.manualAddons = { ...wellnessSelection.value.manualAddons, [name]: next };
  }
};

// Ticket quantity management
const getTicketQty = (ticketId) => {
  return ticketQuantities.value[ticketId] || 0;
};

const ticketAvailableQty = (ticket) => getTicketRemaining(ticket);

const isTicketSoldOut = (ticket) => {
  return ticketAvailableQty(ticket) <= 0;
};

const ticketSalesEndValue = (ticket) => ticket?.sales_end || ticket?.sale_end || null;

const isTicketSaleEnded = (ticket) => {
  const salesEnd = ticketSalesEndValue(ticket);
  if (!salesEnd) return false;

  const endDate = new Date(salesEnd);
  if (Number.isNaN(endDate.getTime())) return false;

  return endDate.getTime() < Date.now();
};

const isTicketUnavailable = (ticket) => {
  return isTicketSoldOut(ticket) || isTicketSaleEnded(ticket);
};

const changeTicketQty = (ticketId, delta) => {
  if (delta > 0) {
    const ticket = event.value?.tickets?.find(t => t.id === ticketId);
    if (!ticket || isTicketUnavailable(ticket)) return;
  }

  const current = getTicketQty(ticketId);
  const ticket = event.value?.tickets?.find(t => t.id === ticketId);
  const maxQty = ticketAvailableQty(ticket);
  const newQty = Math.max(0, Math.min(current + delta, maxQty));
  ticketQuantities.value[ticketId] = newQty;
  if (newQty === 0) {
    removeDrinksForTicket(ticketId);
    const ticket = eventTickets.value.find(item => item.id === ticketId);
    if (hasWellnessService(ticket)) {
      resetWellnessSelection();
    }
    const hasOtherCookoutTicket = eventTickets.value.some(item => item.id !== ticketId && getTicketQty(item.id) > 0 && hasCookoutFood(item));
    if (hasCookoutFood(ticket) && !hasOtherCookoutTicket) {
      resetCookoutSelection();
    }
  }
};

const eventTickets = computed(() => event.value?.tickets || []);

const hasWellnessService = (ticket) => {
  return ticket?.wellness?.includeService === 'yes';
};

const toCookoutItem = (item, defaultMode = 'included') => {
  if (typeof item === 'string') {
    return { name: item, mode: defaultMode, price: 0, maxQty: null };
  }

  return {
    ...item,
    name: String(item?.name || ''),
    mode: item?.mode || defaultMode,
    price: Number(item?.price || 0),
    maxQty: item?.qty !== undefined && item?.qty !== null ? Number(item.qty) : null,
  };
};

const getCookoutConfig = (ticket) => ticket?.cookout || null;

const isCookoutTicketEnabled = (ticket) => {
  const config = getCookoutConfig(ticket);
  return Boolean(config && config.includeFood === 'yes');
};

const hasCookoutFood = (ticket) => {
  return isCookoutTicketEnabled(ticket);
};

const cookoutIncludedProteins = (ticket) => {
  const config = getCookoutConfig(ticket);
  const proteins = Array.isArray(config?.proteins) ? config.proteins : [];
  const customProteins = Array.isArray(config?.customProteins) ? config.customProteins : [];
  const names = [...proteins, ...customProteins]
    .map(item => toCookoutItem(item))
    .filter(item => item.name && item.checked !== false && item.mode !== 'addon')
    .map(item => item.name);

  return [...new Set(names)];
};

const cookoutSides = (ticket) => {
  const sides = getCookoutConfig(ticket)?.sides;
  return Array.isArray(sides) ? sides.filter(Boolean).map(String) : [];
};

const cookoutIncludedDrinks = (ticket) => {
  const drinks = getCookoutConfig(ticket)?.drinks;
  if (!Array.isArray(drinks)) return [];

  const names = drinks
    .map(item => toCookoutItem(item))
    .filter(item => item.name && item.mode !== 'addon')
    .map(item => item.name);

  return [...new Set(names)];
};

const cookoutAddonDrinks = (ticket) => {
  const drinks = getCookoutConfig(ticket)?.drinks;
  if (!Array.isArray(drinks)) return [];

  return drinks
    .map(item => toCookoutItem(item))
    .filter(item => item.name && item.mode === 'addon');
};

const cookoutAddonProteins = (ticket) => {
  const config = getCookoutConfig(ticket);
  const proteins = Array.isArray(config?.proteins) ? config.proteins : [];
  const customProteins = Array.isArray(config?.customProteins) ? config.customProteins : [];

  return [...proteins, ...customProteins]
    .map(item => toCookoutItem(item))
    .filter(item => item.name && item.checked !== false && item.mode === 'addon');
};

const cookoutManualAddons = (ticket) => {
  const addons = getCookoutConfig(ticket)?.manualAddons;
  if (!Array.isArray(addons)) return [];

  return addons
    .map(item => toCookoutItem(item, 'addon'))
    .filter(item => item.name);
};

const cookoutSelectionKey = (ticket) => String(ticket?.id || 'fallback');

const ensureCookoutSelection = (ticket) => {
  const key = cookoutSelectionKey(ticket);
  if (!cookoutSelections.value[key]) {
    cookoutSelections.value[key] = { includedProtein: '', proteins: {}, manualAddons: {}, drinks: {} };
  }
  return cookoutSelections.value[key];
};

const getCookoutSelection = (ticket) => cookoutSelections.value[cookoutSelectionKey(ticket)] || null;

const setCookoutIncludedProtein = (ticket, name) => {
  if (!hasCookoutFood(ticket)) return;
  ensureCookoutSelection(ticket).includedProtein = name;
};

const getCookoutIncludedProteinValue = (ticket) => {
  return String(getCookoutSelection(ticket)?.includedProtein || '');
};

const getCookoutProteinQty = (ticket, name) => {
  return Number(getCookoutSelection(ticket)?.proteins?.[name] || 0);
};

const getCookoutDrinkQty = (ticket, name) => {
  return Number(getCookoutSelection(ticket)?.drinks?.[name] || 0);
};

const getCookoutManualAddonQty = (ticket, name) => {
  return Number(getCookoutSelection(ticket)?.manualAddons?.[name] || 0);
};

const changeCookoutSelectionQty = (ticket, group, name, delta, maxQty = null) => {
  if (!hasCookoutFood(ticket) || activeCookoutQty.value <= 0) return;

  const selection = ensureCookoutSelection(ticket);
  const current = Number(selection[group]?.[name] || 0);
  const cap = maxQty !== null && Number(maxQty) > 0 ? Number(maxQty) : 999;
  const nextQty = Math.max(0, Math.min(current + delta, cap));

  if (!selection[group]) selection[group] = {};
  if (nextQty > 0) {
    selection[group][name] = nextQty;
  } else {
    delete selection[group][name];
  }
};

const incCookoutProtein = (ticket, name) => {
  const config = cookoutAddonProteins(ticket).find(item => item.name === name);
  changeCookoutSelectionQty(ticket, 'proteins', name, 1, config?.maxQty ?? null);
};

const decCookoutProtein = (ticket, name) => {
  const config = cookoutAddonProteins(ticket).find(item => item.name === name);
  changeCookoutSelectionQty(ticket, 'proteins', name, -1, config?.maxQty ?? null);
};

const incCookoutDrink = (ticket, name) => {
  const config = cookoutAddonDrinks(ticket).find(item => item.name === name);
  changeCookoutSelectionQty(ticket, 'drinks', name, 1, config?.maxQty ?? null);
};

const decCookoutDrink = (ticket, name) => {
  const config = cookoutAddonDrinks(ticket).find(item => item.name === name);
  changeCookoutSelectionQty(ticket, 'drinks', name, -1, config?.maxQty ?? null);
};

const incCookoutManualAddon = (ticket, name) => {
  const config = cookoutManualAddons(ticket).find(item => item.name === name);
  changeCookoutSelectionQty(ticket, 'manualAddons', name, 1, config?.maxQty ?? null);
};

const decCookoutManualAddon = (ticket, name) => {
  const config = cookoutManualAddons(ticket).find(item => item.name === name);
  changeCookoutSelectionQty(ticket, 'manualAddons', name, -1, config?.maxQty ?? null);
};

const cookoutTotalForTicket = (ticket) => {
  if (!hasCookoutFood(ticket)) return 0;
  const selection = getCookoutSelection(ticket);
  if (!selection) return 0;

  let total = 0;
  cookoutAddonProteins(ticket).forEach(item => {
    total += Number(selection.proteins?.[item.name] || 0) * Number(item.price || 0);
  });
  cookoutAddonDrinks(ticket).forEach(item => {
    total += Number(selection.drinks?.[item.name] || 0) * Number(item.price || 0);
  });
  cookoutManualAddons(ticket).forEach(item => {
    total += Number(selection.manualAddons?.[item.name] || 0) * Number(item.price || 0);
  });

  return total;
};

const getCookoutLinesForTicket = (ticket) => {
  if (!hasCookoutFood(ticket) || activeCookoutQty.value <= 0) return [];
  const selection = getCookoutSelection(ticket);
  if (!selection) return [];

  const lines = [];
  if (selection.includedProtein) {
    lines.push({ nameType: `${selection.includedProtein} (Included Plate)`, qty: activeCookoutQty.value, total: 0 });
  }

  cookoutAddonProteins(ticket).forEach(item => {
    const qty = Number(selection.proteins?.[item.name] || 0);
    if (qty > 0) lines.push({ nameType: `${item.name} (Cookout Add-on)`, qty, total: Number(item.price || 0) * qty });
  });
  cookoutAddonDrinks(ticket).forEach(item => {
    const qty = Number(selection.drinks?.[item.name] || 0);
    if (qty > 0) lines.push({ nameType: `${item.name} (Cookout Drink)`, qty, total: Number(item.price || 0) * qty });
  });
  cookoutManualAddons(ticket).forEach(item => {
    const qty = Number(selection.manualAddons?.[item.name] || 0);
    if (qty > 0) lines.push({ nameType: `${item.name} (Cookout Extra)`, qty, total: Number(item.price || 0) * qty });
  });

  return lines;
};

const activeWellnessTicket = computed(() => {
  return cartItems.value
    .map(item => eventTickets.value.find(ticket => ticket.id === item.ticketId))
    .find(ticket => hasWellnessService(ticket)) || null;
});

const activeCookoutTicket = computed(() => {
  return cartItems.value
    .map(item => eventTickets.value.find(ticket => ticket.id === item.ticketId))
    .find(ticket => hasCookoutFood(ticket)) || null;
});

const activeCookoutQty = computed(() => {
  if (!activeCookoutTicket.value) return 0;
  return getTicketQty(activeCookoutTicket.value.id);
});

const activeWellnessQty = computed(() => {
  if (!activeWellnessTicket.value) return 0;
  return getTicketQty(activeWellnessTicket.value.id);
});

const activeWellnessMobileFee = computed(() => {
  return Number(activeWellnessTicket.value?.wellness?.booking?.mobileFee || 0);
});

const wellnessServicePrice = (serviceName) => {
  const services = Array.isArray(activeWellnessTicket.value?.wellness?.services)
    ? activeWellnessTicket.value.wellness.services
    : [];
  const service = services.find(item => item.name === serviceName);
  return Number(service?.price || 0);
};

const wellnessManualAddonPrice = (addonName) => {
  const addons = Array.isArray(activeWellnessTicket.value?.wellness?.manualAddons)
    ? activeWellnessTicket.value.wellness.manualAddons
    : [];
  const addon = addons.find(item => item.name === addonName);
  return Number(addon?.price || 0);
};

const activeWellnessSlotBlocks = computed(() => {
  const ticket = activeWellnessTicket.value;
  const directBlocks = Array.isArray(ticket?.wellness_slot_blocks) ? ticket.wellness_slot_blocks : [];
  if (directBlocks.length > 0) {
    return directBlocks.map(block => ({
      start_time: block.start_time || block.start || '',
      end_time: block.end_time || block.end || '',
      slot_date: block.slot_date || ticket?.wellness?.booking?.slotDate || '',
      count: Number(block.count ?? block.available ?? 0),
    }));
  }

  const booking = ticket?.wellness?.booking;
  const slots = Array.isArray(booking?.slots) ? booking.slots : [];
  return slots.map(slot => ({
    start_time: slot.start,
    end_time: slot.end,
    slot_date: booking?.slotDate || new Date().toISOString().split('T')[0],
    count: slot.disabled === '1' || slot.disabled === true ? 0 : Number(booking?.maxPerSlot || 1),
  }));
});

const resetWellnessSelection = () => {
  wellnessSelection.value = {
    includedService: '',
    services: {},
    manualAddons: {},
    selectedSlot: null,
    selectedSlotDate: null,
    holdExpiresAt: null,
    serviceMode: 'inhouse',
    contactPhone: ''
  };
  slot.value = null;
  holdExpiresAt.value = null;
};

const resetCookoutSelection = () => {
  cookoutSelections.value = {};
};

const drinksTicket = computed(() => {
  return eventTickets.value.find(ticket => ticket.id === drinksTicketId.value) || null;
});

const packageTicket = computed(() => {
  return eventTickets.value.find(ticket => ticket.id === packageTicketId.value) || null;
});

const packageTicketForItem = (item) => {
  const ticket = eventTickets.value.find(ticket => ticket.id === item.ticketId);
  return ticket && isPackageTicket(ticket) ? ticket : null;
};

const removeDrinksForTicket = (ticketId) => {
  const next = { ...drinkQuantities.value };
  Object.keys(next).forEach((sku) => {
    const parsed = parseDrinkSku(sku);
    if (parsed?.ticketId === Number(ticketId)) {
      delete next[sku];
    }
  });
  drinkQuantities.value = next;
};

const openDrinksModal = (ticketId) => {
  const ticket = eventTickets.value.find(item => item.id === ticketId);
  if (!ticket || !hasDrinkAddons(ticket)) return;

  if (getTicketQty(ticketId) <= 0) {
    if (window.toast) window.toast('Add a ticket before selecting drinks');
    return;
  }

  drinksTicketId.value = ticketId;
  showDrinksModal.value = true;
};

const closeDrinksModal = () => {
  showDrinksModal.value = false;
  drinksTicketId.value = null;
};

const openPackageModal = (ticketId) => {
  const ticket = eventTickets.value.find(item => item.id === ticketId);
  if (!ticket || !isPackageTicket(ticket)) return;

  packageTicketId.value = ticketId;
  showPackageModal.value = true;
};

const closePackageModal = () => {
  showPackageModal.value = false;
  packageTicketId.value = null;
};

const changeDrinkQty = (sku, delta) => {
  const found = findDrinkForSku(eventTickets.value, sku);
  if (!found || getTicketQty(found.ticket.id) <= 0) return;

  const current = drinkQuantities.value[sku] || 0;
  const maxQty = getDrinkAvailable(found.drink);
  const newQty = Math.max(0, Math.min(current + delta, maxQty));
  const next = { ...drinkQuantities.value };

  if (newQty > 0) {
    next[sku] = newQty;
  } else {
    delete next[sku];
  }

  drinkQuantities.value = next;
};

// Calculate selected tickets for display.
const cartItems = computed(() => {
  if (!event.value?.tickets) return [];

  return event.value.tickets.map(ticket => {
    const qty = getTicketQty(ticket.id);
    if (qty === 0) return null;

    const price = getDiscountedTicketPrice(ticket);

    return {
      ticketId: ticket.id,
      name: ticket.name,
      price: price,
      qty: qty,
      total: price * qty
    };
  }).filter(item => item !== null);
});

const cartRecord = computed(() => {
  const items = cartItems.value.reduce((acc, item) => {
    const ticket = eventTickets.value.find(t => t.id === item.ticketId);
    acc[String(item.ticketId)] = {
      sku: String(item.ticketId),
      type: ticket?.type || 'Ticket',
      quantity: item.qty,
      price: item.price,
      section: '-',
    };
    return acc;
  }, {});

  Object.entries(drinkQuantities.value).forEach(([sku, quantity]) => {
    const found = findDrinkForSku(eventTickets.value, sku);
    if (!found || quantity <= 0 || getTicketQty(found.ticket.id) <= 0) return;

    items[sku] = {
      sku,
      type: `${found.drink.name} (${found.group.label})`,
      quantity,
      price: getDrinkPrice(found.drink),
      section: '-',
    };
  });

  return items;
});

const activeDrinkItems = computed(() => {
  return Object.entries(drinkQuantities.value).map(([sku, quantity]) => {
    const found = findDrinkForSku(eventTickets.value, sku);
    if (!found || quantity <= 0 || getTicketQty(found.ticket.id) <= 0) return null;

    const price = getDrinkPrice(found.drink);
    return {
      sku,
      ticketId: found.ticket.id,
      name: `${found.drink.name} (${found.group.label})`,
      qty: quantity,
      price,
      total: price * quantity,
    };
  }).filter(Boolean);
});

const prepareCartForBackend = () => {
  const items = cartItems.value.map(item => ({
    ticketId: item.ticketId,
    qty: item.qty,
    addons: [],
  }));
  const byTicketId = new Map(items.map(item => [Number(item.ticketId), item]));

  Object.entries(drinkQuantities.value).forEach(([sku, quantity]) => {
    const found = findDrinkForSku(eventTickets.value, sku);
    if (!found || quantity <= 0 || getTicketQty(found.ticket.id) <= 0) return;

    const item = byTicketId.get(Number(found.ticket.id));
    if (!item) return;

    item.addons.push({
      addon_id: addonIdForDrink(found.ticket, found.group.key, found.drink),
      name: found.drink.name,
      quantity,
      category: found.group.key,
      section: '-',
      price: getDrinkPrice(found.drink),
    });
  });

  const wellnessTicket = activeWellnessTicket.value;
  if (wellnessTicket) {
    const item = byTicketId.get(Number(wellnessTicket.id));
    if (item) {
      const services = Object.entries(wellnessSelection.value.services || [])
        .map(([name, qty]) => ({ name, qty: Number(qty || 0) }))
        .filter(service => service.qty > 0);
      const manualAddons = Object.entries(wellnessSelection.value.manualAddons || [])
        .map(([name, qty]) => ({ name, qty: Number(qty || 0) }))
        .filter(addon => addon.qty > 0);

      item.wellness = {
        includedService: wellnessSelection.value.includedService || '',
        selectedSlot: wellnessSelection.value.selectedSlot || null,
        selectedSlotDate: wellnessSelection.value.selectedSlotDate || null,
        holdExpiresAt: wellnessSelection.value.holdExpiresAt || null,
        serviceMode: wellnessSelection.value.serviceMode || 'inhouse',
        contactPhone: wellnessSelection.value.contactPhone || '',
        services,
        manualAddons,
      };
    }
  }

  const cookoutTicket = activeCookoutTicket.value;
  if (cookoutTicket) {
    const item = byTicketId.get(Number(cookoutTicket.id));
    const selection = getCookoutSelection(cookoutTicket);
    if (item && selection) {
      const proteins = Object.entries(selection.proteins || {})
        .map(([name, qty]) => ({ name, qty: Number(qty || 0) }))
        .filter(entry => entry.qty > 0);
      const drinks = Object.entries(selection.drinks || {})
        .map(([name, qty]) => ({ name, qty: Number(qty || 0) }))
        .filter(entry => entry.qty > 0);
      const manualAddons = Object.entries(selection.manualAddons || {})
        .map(([name, qty]) => ({ name, qty: Number(qty || 0) }))
        .filter(entry => entry.qty > 0);

      item.cookout = {
        includedProtein: selection.includedProtein || '',
        proteins,
        drinks,
        manualAddons,
      };
    }
  }

  return items;
};

const wellnessAddonTotal = computed(() => {
  let total = 0;
  const wellnessConfig = activeWellnessTicket.value?.wellness;
  if (wellnessConfig?.services) {
    Object.keys(wellnessSelection.value.services).forEach(key => {
      const qty = wellnessSelection.value.services[key] || 0;
      const service = wellnessConfig.services.find(s => s.name === key);
      const price = Number(service?.price || 0);
      total += qty * price;
    });
  }

  Object.keys(wellnessSelection.value.manualAddons).forEach(key => {
    const qty = wellnessSelection.value.manualAddons[key] || 0;
    const addon = wellnessConfig?.manualAddons?.find(s => s.name === key);
    const price = Number(addon?.price || 0);
    total += qty * price;
  });

  return total;
});

const couponDiscount = computed(() => {
  let discount = 0;
  const base = Object.values(cartRecord.value).reduce((sum, item) => sum + (item.price * item.quantity), 0);
  appliedCoupons.value.forEach(coupon => {
    if (coupon.discountType === 'percentage') {
      discount += (Number(coupon.discount) / 100) * base;
    } else {
      discount += Number(coupon.discount);
    }
  });
  return Math.min(discount, base);
});

const wellnessSelectionsForMath = computed(() => {
  const ticket = activeWellnessTicket.value;
  return ticket ? { [ticket.id]: wellnessSelection.value } : {};
});

const applyCoupon = () => {
  const code = couponInput.value.trim().toUpperCase();
  if (!code) return;

  if (appliedCoupons.value.some(c => c.code === code)) {
    if (window.toast) window.toast('Coupon already applied');
    return;
  }

  // Validate coupon exists for this event
  const found = (event.value?.coupons || []).find(c => c.code.toUpperCase() === code);

  if (!found) {
    if (window.toast) window.toast('Invalid coupon code for this event');
    return;
  }

  // Check expiry
  if (found.expiry_date && new Date(found.expiry_date) < new Date()) {
    if (window.toast) window.toast('Coupon has expired');
    return;
  }

  appliedCoupons.value.push({
    id: found.id,
    code: found.code,
    discount: Number(found.discount),
    discountType: found.discount_type || 'amount'
  });

  couponInput.value = '';
  if (window.toast) window.toast('✅ Coupon applied!');
};

const removeCoupon = (id) => {
  appliedCoupons.value = appliedCoupons.value.filter(c => c.id !== id);
};

const getIndividualCouponDiscount = (coupon) => {
  const base = Object.values(cartRecord.value).reduce((sum, item) => sum + (item.price * item.quantity), 0);
  if (coupon.discountType === 'percentage') {
    return (Number(coupon.discount) / 100) * base;
  }
  return Number(coupon.discount);
};

const cookoutTicketIds = computed(() => {
  return new Set(eventTickets.value
    .filter(ticket => hasCookoutFood(ticket))
    .map(ticket => ticket.id));
});

const orderTotals = computed(() => calculateCheckoutTotals({
  event: event.value || {},
  tickets: eventTickets.value,
  cartItems: cartRecord.value,
  eventFeeSettings: props.eventFeeSettings || {},
  taxRules: props.taxRules || {},
  wellnessSelections: wellnessSelectionsForMath.value,
  cookoutTicketIds: cookoutTicketIds.value,
  discount: couponDiscount.value,
  cookoutAddonsTotal: cookoutAddonsTotal.value,
  wellnessAddonsTotal: wellnessAddonTotal.value,
}));

const orderFees = computed(() => orderTotals.value.fees);
const subtotal = computed(() => orderTotals.value.servicesSubtotal);
const ticketProcessingFees = computed(() => {
  return orderFees.value.serviceFeePercent +
    orderFees.value.serviceFeeFixed +
    orderFees.value.processingFeePercent +
    orderFees.value.processingFeeFixed;
});
const totalTax = computed(() => orderFees.value.taxRatePercent);
const total = computed(() => orderTotals.value.total);

// Totals logic
const cookoutAddonsTotal = computed(() => activeCookoutTicket.value ? cookoutTotalForTicket(activeCookoutTicket.value) : 0);

const clear = () => {
  ticketQuantities.value = {};
  drinkQuantities.value = {};
  showDrinksModal.value = false;
  drinksTicketId.value = null;
  showPackageModal.value = false;
  packageTicketId.value = null;
  slot.value = null;
  serviceMode.value = 'inhouse';
  locationMode.value = 'address';
  mobileAddress.value = '';
  mobilePinPos.value = null;
  contactPhone.value = '';
  resetCookoutSelection();
  appliedCoupons.value = [];
  holdTimer.value = 0;
  holdExpiresAt.value = null;
  wellnessSelections.value = {};
  wellnessSelection.value = {
    includedService: '',
    services: {},
    manualAddons: {},
    selectedSlot: null,
    selectedSlotDate: null,
    holdExpiresAt: null,
    serviceMode: 'inhouse',
    contactPhone: ''
  };
  cookoutSelections.value = {};

  if (window.wellnessTimer) {
    clearInterval(window.wellnessTimer);
    window.wellnessTimer = null;
  }
};

const pay = async (method) => {
  if (cartItems.value.length === 0) return;
  if (activeWellnessTicket.value && !wellnessSelection.value.selectedSlot) {
    if (window.toast) window.toast('Select a time slot first');
    return;
  }
  if (activeWellnessTicket.value && wellnessSelection.value.serviceMode === 'mobile' && !String(wellnessSelection.value.contactPhone || '').trim()) {
    if (window.toast) window.toast('Enter a contact phone for mobile service');
    return;
  }
  if (
    activeCookoutTicket.value &&
    cookoutIncludedProteins(activeCookoutTicket.value).length > 0 &&
    !getCookoutIncludedProteinValue(activeCookoutTicket.value)
  ) {
    if (window.toast) window.toast('Choose an included cookout plate first');
    return;
  }

  try {
    const items = prepareCartForBackend();

    const formData = {
      items: items,
      appliedCoupons: appliedCoupons.value,
      checkoutSummary: {
        servicesSubtotal: Number(subtotal.value || 0),
        ticketProcessingFees: Number(ticketProcessingFees.value || 0),
        tax: Number(totalTax.value || 0),
        total: Number(total.value || 0),
        fees: orderFees.value
      }
    };

    if (method === 'wallet') {
      await router.post(route('new_frontend.wallet.buy-ticket'), formData, {
        preserveScroll: true,
        onSuccess: () => {
          if (window.toast) window.toast('Ticket purchased successfully with wallet!');
          emit('complete', { method: 'wallet', amount: total.value });
          close();
        },
        onError: (errors) => {
          if (window.toast) {
            window.toast(errors.error || errors.wallet || errors.items || errors.cart || 'Failed to purchase ticket with wallet');
          }
        }
      });
    } else if (method === 'card') {
      // Use form submission for Stripe to avoid CORS issues
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = route('new_frontend.stripe.buy-ticket');

      // Add CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
      }

      // Add form data
      Object.keys(formData).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = JSON.stringify(formData[key]);
        form.appendChild(input);
      });

      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);
    } else if (method === 'bank') {
      if (window.toast) window.toast('Redirecting to Secure Bank Payment...');
      // Implement Bank redirect or logic here
      console.log('Bank payment initiated:', formData);
    }
  } catch (error) {
    console.error('Payment error:', error);
    if (window.toast) window.toast('Payment failed. Please try again.');
  }
};

const open = (e) => {
  event.value = e;
  clear();
  isOpen.value = true;
  document.body.style.overflow = 'hidden';
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};

const close = () => {
  isOpen.value = false;
  document.body.style.overflow = '';
};

defineExpose({ open, close });
</script>
