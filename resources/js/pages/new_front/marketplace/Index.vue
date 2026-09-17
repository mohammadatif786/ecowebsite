<template>
  <div class="fade pb-20">
    <!-- Header -->
    <div class="rounded-3xl overflow-hidden mb-5" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)">
      <div class="p-5 text-white">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <span class="h-11 w-11 rounded-2xl bg-white/15 grid place-items-center shrink-0">
              <i data-lucide="shopping-bag" class="w-6 h-6"></i>
            </span>
            <div>
              <h1 class="text-2xl font-black">Marketplace</h1>
              <p class="text-white/80 text-sm font-bold">Multi-seller commerce · escrow-protected</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <button @click="openAffiliateHub"
              class="btn bg-white/15 hover:bg-white/25 text-white px-4 py-2.5 flex items-center gap-2 text-sm transition">
              <i data-lucide="handshake" class="w-4 h-4"></i>Promote &amp; Earn
            </button>
            <button @click="openSellerHub"
              class="btn bg-white/15 hover:bg-white/25 text-white px-4 py-2.5 text-sm transition">Seller</button>
            <button @click="showCart = true"
              class="btn bg-white/15 hover:bg-white/25 text-white px-4 py-2.5 flex items-center gap-2 text-sm transition">
              <i data-lucide="shopping-cart" class="w-4 h-4"></i>Cart ({{ cart.length }})
            </button>
            <button @click="showCreate = true"
              class="btn bg-white text-violet-700 font-black px-4 py-2.5 flex items-center gap-2 text-sm shadow hover:shadow-md transition">
              <i data-lucide="plus" class="w-4 h-4"></i>Create
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Seller Modal -->
    <div v-if="sellerModal"
      class="fixed inset-0 z-[150] bg-black/40 backdrop-blur-sm flex items-center justify-center p-4"
      @click.self="sellerModal = null">
      <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl p-6 relative">
        <button @click="sellerModal = null"
          class="absolute right-4 top-4 p-2 rounded-full bg-white/60 shadow text-rose-600 hover:text-rose-700"
          aria-label="Close" style="color:#f43f5e;">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-1 flex items-center justify-center">
            <div class="w-44 h-44 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden">
              <img v-if="sellerModal.avatar" :src="sellerModal.avatar" class="w-full h-full object-contain" />
              <div v-else class="text-slate-400">No image</div>
            </div>
          </div>
          <div class="md:col-span-2">
            <h3 class="font-black text-lg">{{ sellerModal.name }}</h3>
            <div class="grid grid-cols-2 gap-3 mt-3">
              <div class="rounded-lg bg-slate-50 p-3">
                <div class="text-xs text-slate-500">OWNER</div>
                <div class="font-black">{{ sellerModal.name }}</div>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <div class="text-xs text-slate-500">LOCATION</div>
                <div class="font-black">{{ sellerModal.city }}{{ sellerModal.country ? ', ' + sellerModal.country : ''
                  }}</div>
              </div>
            </div>

            <div class="mt-4">
              <div class="text-xs font-black text-amber-700 mb-2">PICKUP LOCATIONS</div>
              <ul class="list-disc list-inside text-sm text-slate-700">
                <template v-if="sellerModal.pickup_locations && sellerModal.pickup_locations.length">
                  <li v-for="loc in sellerModal.pickup_locations" :key="loc">{{ loc }}</li>
                </template>
                <template v-else>
                  <li>No pickup locations configured.</li>
                </template>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Status badges -->
    <div class="flex flex-wrap gap-2 mb-3">
      <span class="text-xs font-black text-green-600 bg-green-50 border border-green-100 px-3 py-1.5 rounded-full">✅
        Availability defaults to ALL</span>
      <span class="text-xs font-black text-amber-600 bg-amber-50 border border-amber-100 px-3 py-1.5 rounded-full">📦
        Pickup
        inherits from Store / Group</span>
      <span class="text-xs font-black text-slate-600 bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-full">🔒
        Payouts held until Delivered</span>
    </div>

    <!-- Category filter -->
    <div class="flex gap-2 overflow-x-auto hide-scroll pb-1 mb-5">
      <button v-for="c in CATS" :key="c" @click="activeCat = c"
        :class="['chip transition', activeCat === c ? 'on' : '']">{{
          c }}</button>
    </div>

    <!-- Product grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div v-for="p in filteredProducts" :key="p.id"
        class="card overflow-hidden group hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <div class="relative h-52 overflow-hidden bg-white">
          <img :src="p.image" :alt="p.title"
            class="w-full h-full object-contain p-2 group-hover:scale-[1.02] transition-transform duration-300" />
          <span
            class="absolute top-2 left-2 bg-lkblue text-white text-[11px] font-black px-2 py-0.5 rounded-full shadow-sm">{{
              p.seller_type }}</span>
          <span
            class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm text-[11px] font-black px-2 py-0.5 rounded-full shadow-sm">{{
              p.category }}</span>
          <span
            class="absolute bottom-2 left-2 bg-black/55 text-white text-[11px] font-bold px-2 py-0.5 rounded-full">{{
              p.fulfil }}</span>
          <span
            :class="['absolute bottom-2 right-2 text-white text-[11px] font-black px-2 py-0.5 rounded-full shadow-sm', p.stock > 0 ? 'bg-green-600' : 'bg-red-600']">
            {{ p.stock > 0 ? p.stock + ' In Stock' : 'Sold Out' }}
          </span>
        </div>
        <div class="p-4">
          <h3 class="font-black leading-tight" style="min-height:42px">{{ p.title }}</h3>
          <p class="text-xs text-slate-500 mt-1">by {{ p.seller }}</p>
          <div class="flex items-center justify-between mt-3">
            <span class="text-xl font-black">{{ money(p.price) }}</span>
            <div v-if="p.stock > 0" class="flex gap-2">
              <button @click="viewProduct(p)" class="btn btn-ghost px-3 py-2 text-sm">View</button>
              <button @click="addToCart(p)" class="btn btn-primary px-3 py-2 text-sm flex items-center gap-1">
                <i data-lucide="plus" class="w-4 h-4"></i>Add
              </button>
            </div>
            <button v-else class="btn bg-slate-200 text-slate-500 px-4 py-2 text-sm cursor-not-allowed">Sold</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ---- CART OVERLAY ---- -->
    <div v-if="showCart"
      class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center"
      @click.self="showCart = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md max-h-[80vh] overflow-y-auto shadow-2xl">
        <div
          class="sticky top-0 bg-white border-b border-slate-100 px-5 py-4 flex items-center justify-between rounded-t-3xl">
          <h3 class="text-lg font-black flex items-center gap-2"><i data-lucide="shopping-cart" class="w-5 h-5"></i>Cart
            ({{
              cart.length }})</h3>
          <button @click="showCart = false" class="text-slate-400 hover:text-slate-600 transition"><i data-lucide="x"
              class="w-5 h-5"></i></button>
        </div>
        <div class="p-5">
          <div v-if="!cart.length" class="text-center py-10 text-slate-400">
            <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <p class="font-bold">Your cart is empty</p>
          </div>
          <div v-else class="space-y-3">
            <div v-for="(item, i) in cart" :key="item.id"
              class="flex items-center gap-3 border border-slate-100 rounded-2xl p-3">
              <img :src="item.image" class="w-16 h-16 rounded-xl object-cover shrink-0" />
              <div class="flex-1 min-w-0">
                <p class="font-black text-sm leading-tight">{{ item.title }}</p>
                <p class="text-xs text-slate-500 mt-0.5">x{{ item.qty }}</p>
                <p class="font-black text-sm mt-1">{{ money(item.price * item.qty) }}</p>
              </div>
              <button @click="removeFromCart(i)" class="text-rose-500 hover:text-rose-700 transition p-1"><i
                  data-lucide="trash-2" class="w-4 h-4"></i></button>
            </div>
            <div class="border-t border-slate-100 pt-3 mt-3">
              <div class="flex justify-between font-black text-lg">
                <span>Subtotal</span>
                <span class="text-emerald-600">{{ money(cartTotal) }}</span>
              </div>
            </div>
            <button @click="openCheckout"
              class="btn btn-primary w-full py-3.5 font-black text-sm shadow hover:shadow-md hover:-translate-y-0.5 transition">
              Checkout →
            </button>
            <button @click="cart = []" class="btn btn-ghost w-full py-2.5 text-sm">Clear Cart</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ---- CHECKOUT MODAL ---- -->
    <div v-if="showCheckout"
      class="fixed inset-0 z-[160] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center p-4"
      @click.self="showCheckout = false">
      <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between z-10">
          <h3 class="text-xl font-black flex items-center gap-2"><i data-lucide="credit-card"
              class="w-6 h-6"></i>Checkout
          </h3>
          <button @click="showCheckout = false" class="text-slate-400 hover:text-slate-600 transition"><i
              data-lucide="x" class="w-6 h-6"></i></button>
        </div>

        <div class="p-6">
          <div class="grid md:grid-cols-2 gap-8">
            <!-- Left: Info & Shipping -->
            <div class="space-y-6">
              <div>
                <h4 class="font-black text-lg mb-3">Buyer Information</h4>
                <div class="grid gap-3">
                  <div>
                    <label class="text-xs font-black text-slate-500 uppercase tracking-wider">Full Name</label>
                    <input v-model="checkoutForm.name"
                      class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue" />
                    <p v-if="checkoutErrors.name" class="text-rose-500 text-[10px] mt-1 font-bold">{{
                      checkoutErrors.name }}
                    </p>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="text-xs font-black text-slate-500 uppercase tracking-wider">Email</label>
                      <input v-model="checkoutForm.email"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue" />
                      <p v-if="checkoutErrors.email" class="text-rose-500 text-[10px] mt-1 font-bold">{{
                        checkoutErrors.email }}</p>
                    </div>
                    <div>
                      <label class="text-xs font-black text-slate-500 uppercase tracking-wider">Phone</label>
                      <input v-model="checkoutForm.phone"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue" />
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h4 class="font-black text-lg mb-3">Delivery / Pickup</h4>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                  <input type="checkbox" id="usePickup" v-model="checkoutForm.usePickup"
                    class="w-5 h-5 rounded-lg text-lkblue focus:ring-lkblue border-slate-300" />
                  <label for="usePickup" class="text-sm font-bold text-slate-700">Pickup instead of delivery</label>
                </div>

                <div v-show="!checkoutForm.usePickup" class="mt-4 space-y-3">
                  <div>
                    <label class="text-xs font-black text-slate-500 uppercase tracking-wider">Shipping Address</label>
                    <input v-model="checkoutForm.ship.address1" placeholder="Street address, house number"
                      class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue" />
                    <p v-if="checkoutErrors.address1" class="text-rose-500 text-[10px] mt-1 font-bold">{{
                      checkoutErrors.address1 }}</p>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <input v-model="checkoutForm.ship.city" placeholder="City"
                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue" />
                    <input v-model="checkoutForm.ship.state" placeholder="State"
                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue" />
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <input v-model="checkoutForm.ship.country" placeholder="Country"
                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue" />
                    <input v-model="checkoutForm.ship.postal" placeholder="Postal / ZIP"
                      class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue" />
                  </div>
                </div>

                <div v-show="checkoutForm.usePickup" class="mt-4">
                  <label class="text-xs font-black text-slate-500 uppercase tracking-wider">Select Pickup
                    Location</label>
                  <select v-model="checkoutForm.pickupLoc"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-lkblue bg-white">
                    <option value="">Choose a location...</option>
                    <option v-for="loc in ['LinkUp HQ Pickup', 'Nassau Warehouse', 'City Center']" :key="loc">{{ loc }}
                    </option>
                  </select>
                  <p v-if="checkoutErrors.address" class="text-rose-500 text-[10px] mt-1 font-bold">{{
                    checkoutErrors.address }}</p>
                </div>
              </div>

              <div>
                <h4 class="font-black text-lg mb-3">Payment Method</h4>
                <div class="grid gap-2">
                  <button @click="checkoutForm.payMethod = 'wallet'"
                    :class="['flex items-center justify-between p-4 rounded-2xl border-2 transition', checkoutForm.payMethod === 'wallet' ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-100 bg-white']">
                    <div class="flex items-center gap-3">
                      <i data-lucide="wallet" class="w-6 h-6 text-emerald-600"></i>
                      <div class="text-left">
                        <p class="font-black text-sm">LinkUp Wallet</p>
                        <p class="text-[10px] text-slate-500">Instant payout from your balance</p>
                      </div>
                    </div>
                    <div v-if="checkoutForm.payMethod === 'wallet'"
                      class="h-6 w-6 rounded-full bg-emerald-500 grid place-items-center"><i data-lucide="check"
                        class="w-4 h-4 text-white"></i></div>
                  </button>

                  <button @click="checkoutForm.payMethod = 'card'"
                    :class="['flex items-center justify-between p-4 rounded-2xl border-2 transition', checkoutForm.payMethod === 'card' ? 'border-sky-500 bg-sky-50/50' : 'border-slate-100 bg-white']">
                    <div class="flex items-center gap-3">
                      <i data-lucide="credit-card" class="w-6 h-6 text-sky-600"></i>
                      <div class="text-left">
                        <p class="font-black text-sm">Credit / Debit Card</p>
                        <p class="text-[10px] text-slate-500">Pay via secure Stripe checkout</p>
                      </div>
                    </div>
                    <div v-if="checkoutForm.payMethod === 'card'"
                      class="h-6 w-6 rounded-full bg-sky-500 grid place-items-center"><i data-lucide="check"
                        class="w-4 h-4 text-white"></i></div>
                  </button>
                </div>
              </div>
            </div>

            <!-- Right: Summary -->
            <div class="bg-slate-50 rounded-3xl p-6 border border-slate-100 flex flex-col">
              <h4 class="font-black text-lg mb-4">Order Summary</h4>
              <div class="flex-1 space-y-4">
                <div v-for="item in cart" :key="item.id" class="flex items-center gap-3">
                  <img :src="item.image" class="w-12 h-12 rounded-xl object-cover border border-white" />
                  <div class="flex-1 min-w-0">
                    <p class="font-black text-sm truncate leading-tight">{{ item.title }}</p>
                    <p class="text-xs text-slate-500">Qty {{ item.qty }}</p>
                  </div>
                  <p class="font-black text-sm">{{ money(item.price * item.qty) }}</p>
                </div>
              </div>

              <div class="mt-6 pt-6 border-t border-slate-200 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-slate-500 font-bold">Subtotal</span>
                  <span class="font-black">{{ money(cartTotal) }}</span>
                </div>
                <div v-if="feeAmount > 0" class="flex justify-between text-sm text-sky-700">
                  <span class="font-bold">{{ props.shopFee?.label || 'Marketplace Fee' }}</span>
                  <span class="font-black">+ {{ money(feeAmount) }}</span>
                </div>
                <div v-if="pricingQuote && pricingQuote.tax > 0" class="flex justify-between text-sm text-emerald-700">
                  <span class="font-bold">Taxes</span>
                  <span class="font-black">+ {{ money(pricingQuote.tax) }}</span>
                </div>
                <p v-else class="text-[11px] font-medium text-emerald-700">Taxes, when applicable, are calculated from
                  the
                  checkout address.</p>
                <div class="flex justify-between text-xl pt-4 border-t border-slate-200">
                  <span class="font-black">Total</span>
                  <span class="font-black text-lkblue">{{ money(pricingQuote?.net ?? finalTotal) }}</span>
                </div>
              </div>

              <button @click="handleCheckout"
                class="btn btn-primary w-full mt-6 py-4 font-black text-base shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition">
                Place Order → Pay {{ money(finalTotal) }}
              </button>
              <p class="text-[10px] text-center text-slate-400 mt-4 px-6">
                {{ props.shopFee?.disclaimer || 'Secure checkout powered by LinkUp Escrow.' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ---- CREATE MODAL ---- -->
    <div v-if="showCreate"
      class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center p-4"
      @click.self="showCreate = false; activeCreateTab = ''">
      <div
        :class="['bg-white rounded-3xl w-full shadow-2xl overflow-hidden flex flex-col transition-all duration-300', activeCreateTab ? 'max-w-3xl max-h-[90vh]' : 'max-w-2xl']">
        <!-- Header -->
        <div class="p-4 text-white flex items-center justify-between shrink-0"
          style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%)">
          <div class="flex items-center gap-3">
            <span class="h-11 w-11 rounded-xl grid place-items-center bg-white/20">
              <i :data-lucide="activeCreateTab === 'store' ? 'store' : activeCreateTab === 'group' ? 'users' : activeCreateTab === 'product' ? 'package-plus' : 'plus'"
                class="w-6 h-6"></i>
            </span>
            <div>
              <h3 class="text-xl font-black leading-tight">
                {{ activeCreateTab === 'store' ? 'Create Store' : activeCreateTab === 'group' ? 'Create Carnival Group / Band' : activeCreateTab === 'product' ? 'Add Product / Costume' : 'Create on LinkUp' }}
              </h3>
              <p v-if="activeCreateTab" class="text-white/80 text-[11px] font-bold">
                {{ activeCreateTab === 'store' ? 'Stores offer pickup and/or delivery (inherited by products).' :
                  activeCreateTab === 'group' ? 'Organize costumes, sections and pickup for your band.' : activeCreateTab
                    ===
                    'product' ? 'List individual items, store stock, or carnival costumes.' : '' }}
              </p>
            </div>
          </div>
          <button @click="showCreate = false; activeCreateTab = ''"
            class="h-9 w-9 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="p-4 overflow-y-auto hide-scroll">
          <!-- Card List -->
          <div class="space-y-3" v-if="!activeCreateTab">
            <CreateCard icon="store" title="Create Store" desc="Sell products with pickup & delivery options"
              @click="openCreateStore" />
            <CreateCard icon="users" title="Create Carnival Group / Band"
              desc="Sell costumes & sections (frontline, backline…)" @click="openCreateGroup" />
            <CreateCard icon="package-plus" title="Add Product / Costume"
              desc="List an item for sale in the Marketplace" @click="openAddProduct" />
            <CreateCard icon="handshake" title="Promote & Earn (Affiliate)"
              desc="No products? Promote any store & earn commission" @click="openAffiliateHub" />
            <CreateCard icon="bar-chart-2" title="Seller Hub" desc="Manage your stores, products & orders"
              @click="openSellerHub" />
          </div>

          <!-- Tabs (Shown when activeCreateTab is set) -->
          <div v-else class="space-y-4">
            <StoreTab :activeTab="activeCreateTab" :merchants="props.merchants" @back="activeCreateTab = ''"
              @refresh="refresh" />
            <StoreCarnival :activeTab="activeCreateTab" :merchants="props.merchants" @back="activeCreateTab = ''"
              @refresh="refresh" />
            <ProductTab :activeTab="activeCreateTab" :merchants="props.merchants" :categories="props.categories"
              :userProducts="props.userProducts" @back="activeCreateTab = ''" @refresh="refresh"
              @close="showCreate = false; activeCreateTab = ''" />
          </div>
        </div>
      </div>
    </div>

    <!-- ---- ADD PRODUCT MODAL ---- -->
    <div v-if="showAddProduct"
      class="fixed inset-0 z-[130] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center"
      @click.self="showAddProduct = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="brandgrad text-white p-4 flex items-center justify-between sticky top-0 z-10">
          <div class="flex items-center gap-2">
            <span class="h-9 w-9 rounded-xl grid place-items-center bg-white/20"><i data-lucide="package-plus"
                class="w-5 h-5"></i></span>
            <div>
              <h3 class="text-lg font-black">Add Product / Costume</h3>
              <p class="text-white/80 text-[11px]">Image + category + seller.</p>
            </div>
          </div>
          <button @click="showAddProduct = false"
            class="bg-white/20 rounded-full px-3 py-1.5 text-xs font-black">✕</button>
        </div>
        <div class="p-4 space-y-3">
          <div class="grid grid-cols-2 gap-2">
            <div><label class="text-xs font-black text-slate-500">Product / Costume Name</label><input
                v-model="productForm.name" placeholder="e.g. Frontline Costume — Fire"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue" />
            </div>
            <div><label class="text-xs font-black text-slate-500">Price (USD)</label><input v-model="productForm.price"
                type="number" placeholder="450.00"
                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue" />
            </div>
          </div>
          <div><label class="text-xs font-black text-slate-500">Stock / Quantity</label><input
              v-model="productForm.stock" type="number" placeholder="10"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="text-xs font-black text-slate-500">Category</label>
              <select v-model="productForm.category"
                class="mt-1 w-full rounded-xl border border-slate-200 px-2 py-2 text-sm bg-white outline-none">
                <option v-for="c in SHOP_CATS" :key="c">{{ c }}</option>
              </select>
            </div>
            <div>
              <label class="text-xs font-black text-slate-500">Listing Type</label>
              <select v-model="productForm.type"
                class="mt-1 w-full rounded-xl border border-slate-200 px-2 py-2 text-sm bg-white outline-none">
                <option>Individual</option>
                <option>Store</option>
                <option>Group</option>
              </select>
            </div>
          </div>
          <div class="rounded-xl bg-blue-50 border border-blue-100 p-2.5">
            <div class="flex items-center justify-between">
              <label class="text-xs font-black text-blue-800">🤝 Affiliate commission %</label>
              <input v-model="productForm.commission" type="number" value="10" min="0" max="50"
                class="w-20 rounded-lg border border-blue-200 px-2 py-1.5 text-sm font-black text-center outline-none focus:border-blue-400" />
            </div>
            <p class="text-[10px] text-blue-600 mt-1">Creators who promote &amp; sell this on their live/vibes earn this
              %.
              Higher = more creators push it.</p>
          </div>
          <label class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 flex items-start gap-3 cursor-pointer">
            <input v-model="productForm.collectTax" type="checkbox" class="mt-0.5 h-4 w-4 accent-emerald-600" />
            <span><span class="block text-sm font-black text-emerald-900">Collect applicable tax</span><span
                class="block text-[11px] text-emerald-700 mt-0.5">Tax is calculated by LinkUp from the buyer delivery
                address or seller pickup location.</span></span>
          </label>
          <div><label class="text-xs font-black text-slate-500">Image URL (optional)</label><input
              v-model="productForm.imageUrl" placeholder="Leave blank for a placeholder"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue" />
          </div>
          <div><label class="text-xs font-black text-slate-500">Description</label><textarea v-model="productForm.desc"
              rows="2" placeholder="Short description…"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue"></textarea>
          </div>
          <div class="flex gap-2 pt-1">
            <button @click="saveProduct"
              class="flex-1 rounded-2xl py-3 font-black text-white shadow hover:shadow-md transition"
              style="background:linear-gradient(135deg,#3b82f6,#10b981)">✓ Publish</button>
            <button @click="showAddProduct = false; showCreate = true"
              class="rounded-2xl py-3 px-5 font-black border-2 border-slate-200 hover:bg-slate-50 transition">←
              Back</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ---- AFFILIATE HUB MODAL ---- -->
    <div v-if="showAffiliate"
      class="fixed inset-0 z-[130] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center"
      @click.self="showAffiliate = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="text-white p-3 sticky top-0 z-10"
          style="background:linear-gradient(135deg,#239df0 0%,#315bea 55%,#6d54ef 100%)">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="h-9 w-9 rounded-xl grid place-items-center bg-white/20"><i data-lucide="handshake"
                  class="w-5 h-5"></i></span>
              <div>
                <h3 class="text-lg font-black">Promote &amp; Earn</h3>
                <p class="text-white/80 text-[11px]">Sell for any store — earn on every sale you drive</p>
              </div>
            </div>
            <button @click="showAffiliate = false"
              class="bg-white/20 rounded-full px-3 py-1.5 text-xs font-black">✕</button>
          </div>
          <div class="flex gap-2 mt-3">
            <button v-for="t in affiliateTabs" :key="t.id" @click="affTab = t.id"
              :class="['shrink-0 rounded-full px-4 py-1.5 text-sm font-black transition', affTab === t.id ? 'bg-white text-black' : 'bg-white/20 text-white']">{{
                t.label }}</button>
          </div>
        </div>
        <div class="p-4">
          <!-- BROWSE tab -->
          <template v-if="affTab === 'browse'">
            <p class="text-[12px] text-slate-500 mb-3">Tag any of these on your Live or Vibes — you earn what the
              seller/organizer is offering, on every sale you drive. No inventory, no risk.</p>
            <div class="space-y-2">
              <div v-for="p in affiliateProducts" :key="p.id"
                class="flex items-center gap-3 rounded-2xl border border-slate-100 p-2 hover:border-slate-200 transition">
                <div class="relative shrink-0">
                  <img :src="p.image" class="h-14 w-14 rounded-xl object-cover" />
                  <span
                    class="absolute -top-1 -left-1 text-white text-[9px] font-black px-1 py-0.5 rounded-full bg-violet-600">🛍️</span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-black text-sm truncate">{{ p.title }}</p>
                  <p class="text-[11px] text-slate-500 truncate">{{ p.seller }} · {{ money(p.price) }}</p>
                  <p class="text-[11px] font-black text-emerald-600 mt-0.5">{{ p.commission || 10 }}% commission · earn
                    {{ money((p.price * (p.commission || 10)) / 100) }} per sale</p>
                </div>
                <button @click="togglePromote(p)"
                  :class="['btn px-3 py-2 text-xs shrink-0 transition', isPromoting(p.id) ? 'btn-ghost text-emerald-600' : 'btn-primary']">
                  {{ isPromoting(p.id) ? 'Promoting ✓' : 'Promote' }}
                </button>
              </div>
            </div>
          </template>

          <!-- MY PROMOTIONS tab -->
          <template v-else-if="affTab === 'promos'">
            <div v-if="promos.length" class="space-y-2">
              <div v-for="p in promos" :key="p.id"
                class="flex items-center gap-3 rounded-2xl border border-slate-100 p-2">
                <img :src="p.image" class="h-14 w-14 rounded-xl object-cover" />
                <div class="flex-1 min-w-0">
                  <p class="font-black text-sm truncate">{{ p.title }}</p>
                  <p class="text-[11px] text-emerald-600 font-black">{{ p.commission }}% · earn {{ money((p.price *
                    p.commission) / 100) }} per sale</p>
                </div>
                <div class="flex flex-col gap-1.5 shrink-0">
                  <button @click="tagOnLive(p)" class="btn btn-primary px-3 py-2 text-xs">Tag on Live</button>
                  <button @click="removePromo(p.id)"
                    class="btn btn-ghost px-3 py-2 text-xs text-rose-600">Remove</button>
                </div>
              </div>
            </div>
            <div v-else class="card p-8 text-center text-slate-400 font-bold">No promotions yet — go to Promote and pick
              products to earn on.</div>
          </template>

          <!-- EARNINGS tab -->
          <template v-else>
            <div class="grid grid-cols-3 gap-2">
              <div class="card p-3 text-center">
                <p class="text-xl font-black">{{ affSales.length }}</p>
                <p class="text-[11px] text-slate-400">Sales driven</p>
              </div>
              <div class="card p-3 text-center">
                <p class="text-xl font-black text-emerald-600">{{ money(affEarningsTotal) }}</p>
                <p class="text-[11px] text-slate-400">Commission</p>
              </div>
              <div class="card p-3 text-center">
                <p class="text-xl font-black">0</p>
                <p class="text-[11px] text-slate-400">Taps</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-2">
              <div class="rounded-2xl bg-amber-50 border border-amber-200 p-3">
                <p class="text-[11px] font-black text-amber-700">Pending (escrow)</p>
                <p class="text-2xl font-black text-amber-700">{{ money(affPending) }}</p>
                <p class="text-[10px] text-amber-600">Released when buyers receive orders</p>
              </div>
              <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-3">
                <p class="text-[11px] font-black text-emerald-700">Available</p>
                <p class="text-2xl font-black text-emerald-700">{{ money(affAvailable) }}</p>
              </div>
            </div>
            <div class="flex gap-2 mt-2">
              <button v-if="affPending > 0" @click="releaseAffEarnings"
                class="btn btn-ghost flex-1 py-3 text-sm">Simulate deliveries → release</button>
              <button @click="transferAffToWallet" class="btn btn-primary flex-1 py-3 text-sm">Transfer {{
                money(affAvailable) }} to Wallet</button>
            </div>
            <p class="text-[11px] text-slate-400 text-center mt-2">LinkUp keeps 5% · already paid out {{ money(affPaid)
              }}</p>
            <p class="font-black mt-3 mb-1">Recent commissions</p>
            <div class="space-y-1.5 max-h-40 overflow-y-auto">
              <p v-if="!affSales.length" class="text-center text-slate-400 text-sm py-4">No commissions yet — tag a
                product on a Live stream and make a sale.</p>
              <div v-for="s in affSales" :key="s.title"
                class="flex items-center justify-between border border-slate-100 rounded-xl p-2 text-sm">
                <div class="min-w-0">
                  <p class="font-bold truncate">{{ s.title }}</p>
                  <p class="text-[11px] text-slate-400">{{ money(s.amount) }} · {{ s.rate }}%</p>
                </div>
                <span class="font-black text-emerald-600 shrink-0">+{{ money(s.commission) }}</span>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- ---- SELLER HUB MODAL ---- -->
    <div v-if="showSellerHub"
      class="fixed inset-0 z-[130] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center"
      @click.self="showSellerHub = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-5xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="brandgrad text-white p-4 sticky top-0 z-10">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-black">Seller Dashboard</h3>
            <button @click="showSellerHub = false"
              class="bg-white/20 rounded-full px-3 py-1.5 text-xs font-black">✕</button>
          </div>
          <div class="flex gap-2 mt-3 overflow-x-auto pb-2 pr-1" style="scrollbar-width:thin">
            <button v-for="t in sellerTabs" :key="t.id" @click="sellerTab = t.id"
              :class="['shrink-0 rounded-full px-4 py-2 text-sm font-black transition', sellerTab === t.id ? 'bg-white text-[#2863e8]' : 'bg-white/20 text-white']">{{
                t.label }}</button>
          </div>
        </div>
        <div class="p-4">
          <!-- OVERVIEW -->
          <template v-if="sellerTab === 'overview'">
            <div class="rounded-3xl px-5 py-4 text-white mb-3 relative overflow-hidden"
              style="background:linear-gradient(135deg,#5634df,#7c3ff1)">
              <span class="absolute -right-4 -top-7 h-28 w-28 rounded-full bg-white/10"></span>
              <div class="relative flex items-start justify-between">
                <div>
                  <p class="text-[10px] font-black text-white/75 uppercase">Total sales</p>
                  <p class="text-4xl font-black mt-1">{{ money(sellerRevenue) }}</p>
                  <p class="text-[11px] font-bold text-white/75 mt-2">↗ 18% &nbsp; vs last period · {{ sellerOrderCount
                    }} orders all-time</p>
                </div><span class="rounded-xl bg-white/15 p-3 text-xl">↗</span>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
              <div v-for="stat in sellerOverviewStats" :key="stat.label" class="card p-3 min-h-[110px]"><span
                  :class="['inline-grid h-8 w-8 place-items-center rounded-xl text-sm', stat.tint]">{{ stat.icon
                  }}</span>
                <p class="text-[10px] font-black text-slate-400 uppercase mt-2">{{ stat.label }}</p>
                <p class="text-lg font-black mt-0.5">{{ stat.value }}</p>
                <p class="text-[10px] text-slate-400">{{ stat.note }}</p>
              </div>
            </div>
            <div class="card p-4 mt-3">
              <div class="flex items-center justify-between mb-1">
                <p class="font-black">Sales · last 13 days</p>
                <span class="text-emerald-600 font-black text-sm">▲ 18%</span>
              </div>
              <svg viewBox="0 0 300 90" class="w-full" style="height:90px">
                <defs>
                  <linearGradient id="sellerChartFill" x1="0" x2="0" y1="0" y2="1">
                    <stop stop-color="#3b82f6" stop-opacity=".34" />
                    <stop offset="1" stop-color="#3b82f6" stop-opacity=".02" />
                  </linearGradient>
                </defs>
                <polygon :points="`0,90 ${chartPoints} 300,90`" fill="url(#sellerChartFill)" />
                <polyline fill="none" stroke="#3b82f6" stroke-width="2.5" :points="chartPoints" />
              </svg>
            </div>
            <div class="card p-4 mt-3">
              <p class="font-black mb-3">Top Products</p>
              <div v-if="sellerInventory.length" class="space-y-3">
                <div v-for="(p, index) in sellerInventory.slice(0, 3)" :key="p.id" class="flex items-center gap-3">
                  <span
                    class="h-6 w-6 rounded-full bg-slate-100 text-slate-500 text-[11px] font-black grid place-items-center">{{
                      index + 1 }}</span>
                  <img :src="p.image" class="h-9 w-9 rounded-lg object-cover" />
                  <div class="min-w-0 flex-1">
                    <p class="font-bold text-sm truncate">{{ p.title }}</p>
                    <div class="h-1.5 mt-1 rounded-full bg-slate-100 overflow-hidden"><span
                        class="block h-full rounded-full bg-[#5b42ed]"
                        :style="{ width: `${Math.max(18, 100 - (index * 24))}%` }"></span></div>
                  </div>
                  <b class="text-xs">{{ money(p.price) }}</b>
                </div>
              </div>
              <p v-else class="text-sm text-slate-400 text-center py-3">No products listed yet.</p>
            </div>
            <div class="card p-4 mt-3">
              <div class="flex justify-between items-center mb-2">
                <p class="font-black">Recent Orders</p><button @click="sellerTab = 'orders'"
                  class="text-xs font-black text-[#2f75eb]">View all →</button>
              </div>
              <div v-if="SELLER_ORDERS.length" class="space-y-2">
                <div v-for="o in SELLER_ORDERS.slice(0, 3)" :key="o.id"
                  class="flex justify-between gap-2 border-b border-slate-50 pb-2 last:border-0">
                  <div class="min-w-0">
                    <p class="font-bold text-sm truncate">{{ o.item }}</p>
                    <p class="text-[10px] text-slate-400">{{ o.id }} · {{ o.buyer }}</p>
                  </div>
                  <div class="text-right"><b class="text-sm">{{ money(o.total) }}</b>
                    <p class="text-[10px] font-black text-emerald-600">{{ o.status }}</p>
                  </div>
                </div>
              </div>
              <p v-else class="text-sm text-slate-400 text-center py-3">No orders yet.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-3">
              <div class="rounded-2xl p-4 text-white" style="background:linear-gradient(135deg,#059669,#14b8a6)">
                <p class="text-emerald-50 text-[10px] font-black uppercase">Available</p>
                <p class="text-2xl font-black">{{ money(sellerEarningsData.available) }}</p><button
                  @click="sellerTab = 'earnings'"
                  class="w-full mt-2 rounded-xl bg-white/90 py-2 text-xs font-black text-emerald-700">Transfer
                  →</button>
              </div>
              <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-amber-700 text-[10px] font-black uppercase">Pending</p>
                <p class="text-2xl font-black text-amber-700">{{ money(sellerEarningsData.pending) }}</p>
                <p class="text-[10px] text-amber-600 mt-2">Held until delivered</p>
              </div>
            </div>
            <div class="rounded-2xl p-4 mt-3 text-white flex items-center justify-between"
              style="background:linear-gradient(135deg,#059669,#14b8a6)">
              <div>
                <p class="text-emerald-50 text-xs font-black">Available to transfer</p>
                <p class="text-2xl font-black">{{ money(sellerEarningsData.available) }}</p>
              </div>
              <button @click="sellerTab = 'earnings'"
                class="btn bg-white/90 text-emerald-700 px-4 py-2.5 font-bold text-sm">Transfer →</button>
            </div>
          </template>

          <!-- INVENTORY -->
          <template v-else-if="sellerTab === 'inventory'">
            <p class="font-black mb-2">Inventory ({{ sellerInventory.length }})</p>
            <div class="space-y-2 max-h-80 overflow-y-auto">
              <div v-for="p in sellerInventory" :key="p.id"
                class="flex items-center gap-3 border border-slate-100 rounded-2xl p-2">
                <img :src="p.image" class="h-12 w-12 rounded-xl object-cover" />
                <div class="flex-1 min-w-0">
                  <p class="font-black text-sm truncate">{{ p.title }}</p>
                  <p class="text-[11px] text-slate-500">{{ money(p.price) }} · {{ p.category }}</p>
                </div>
                <span
                  :class="['rounded-full px-2.5 py-1 text-xs font-black shrink-0', (p.stock || 0) > 0 ? 'bg-green-50 text-green-600' : 'bg-rose-50 text-rose-600']">
                  {{ (p.stock || 0) > 0 ? p.stock + ' left' : 'Sold out' }}
                </span>
              </div>
            </div>
            <button @click="openAddProduct"
              class="btn btn-primary w-full mt-3 py-3 text-sm font-black">+ Add product</button>
          </template>

          <!-- ORDERS -->
          <template v-else-if="sellerTab === 'orders'">
            <p class="font-black mb-2">Orders ({{ SELLER_ORDERS.length }})</p>
            <div class="space-y-2 max-h-80 overflow-y-auto">
              <div v-for="o in SELLER_ORDERS" :key="o.id" class="border border-slate-100 rounded-2xl p-3">
                <div class="flex items-center justify-between">
                  <p class="font-black text-sm">{{ o.item }}</p>
                  <span
                    :class="['rounded-full px-2 py-0.5 text-[10px] font-black', o.status === 'Delivered' ? 'bg-emerald-100 text-emerald-700' : o.status === 'Shipped' ? 'bg-sky-100 text-sky-700' : o.status === 'Paid' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700']">{{
                      o.status }}</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ o.id }} · {{ o.buyer }} · {{ o.units }} unit{{ o.units >
                  1 ? 's' : '' }}</p>
                <p class="font-black text-sm mt-1">{{ money(o.total) }}</p>
              </div>
            </div>
          </template>

          <!-- REPORTS -->
          <template v-else-if="sellerTab === 'reports'">
            <p class="text-xs font-medium text-slate-400 mb-2">Sales Tax — flat 10% rate, set at signup</p>
            <div class="grid grid-cols-3 gap-2">
              <div v-for="stat in sellerReportSummary" :key="stat.label" class="card p-3">
                <p class="text-[10px] font-black text-slate-400 uppercase">{{ stat.label }}</p>
                <p :class="['text-lg font-black mt-1', stat.color]">{{ stat.value }}</p>
                <p class="text-[10px] text-slate-400">{{ stat.note }}</p>
              </div>
            </div>
            <div class="rounded-2xl p-4 text-white mt-3" style="background:linear-gradient(135deg,#5234df,#813df2)">
              <p class="text-[10px] font-black text-white/70 uppercase">Net revenue — after commission</p>
              <p class="text-3xl font-black mt-1">{{ money(sellerNetRevenue) }}</p>
              <p class="text-[11px] font-bold text-white/75 mt-2">{{ money(sellerRevenue) }} gross sales − {{
                money(sellerCommission) }} commission</p>
            </div>
            <div class="card p-4 mt-3">
              <p class="font-black">Sales by Category</p>
              <div class="mt-3 space-y-3">
                <div v-for="category in sellerCategoryReport" :key="category.name">
                  <div class="flex justify-between text-sm font-bold"><span>{{ category.name }}</span><span>{{
                    money(category.value) }}</span></div>
                  <div class="h-1.5 mt-1 rounded-full bg-slate-100"><span class="block h-full rounded-full bg-[#6a3df0]"
                      :style="{ width: `${category.percent}%` }"></span></div>
                </div>
              </div>
            </div>
            <div class="card p-4 mt-3">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-black">Commission Report</p>
                  <p class="text-[10px] text-slate-400">Affiliate paid out per product</p>
                </div>
                <div class="flex gap-1"><button @click="printSellerReport" class="report-action">⎙ Print</button><button
                    @click="downloadSellerCsv('commission')" class="report-action">⇩ CSV</button></div>
              </div>
              <div class="grid grid-cols-4 gap-2 mt-3">
                <div v-for="stat in sellerCommissionStats" :key="stat.label" class="rounded-xl bg-slate-50 p-2">
                  <p class="text-[9px] font-black text-slate-400">{{ stat.label }}</p><b :class="stat.color">{{
                    stat.value }}</b>
                </div>
              </div>
              <p class="text-[10px] font-black text-slate-400 uppercase mt-4">By product</p>
              <div class="mt-2 space-y-2 max-h-72 overflow-y-auto pr-1">
                <div v-for="row in sellerReportRows" :key="row.id" class="rounded-2xl border border-slate-100 p-3">
                  <div class="flex justify-between gap-3">
                    <div class="min-w-0">
                      <p class="font-black text-sm truncate">{{ row.product }}</p>
                      <p class="text-[10px] text-slate-400">{{ row.category }} · {{ money(row.sales) }} in sales</p>
                    </div>
                    <div class="text-right shrink-0"><b class="text-violet-600">{{ money(row.commission) }}</b>
                      <p class="text-[10px] text-slate-400">commission</p>
                    </div>
                  </div>
                  <p class="text-[10px] text-emerald-600 font-bold mt-2">Net to you: {{ money(row.net) }}</p>
                  <div class="border-t border-slate-100 mt-2 pt-2 flex justify-between text-[11px]"><span
                      class="font-bold text-slate-500">@affiliate · {{ row.units }} sale{{ row.units === 1 ? '' : 's'
                      }}</span><b :class="row.status === 'Paid' ? 'text-emerald-600' : 'text-amber-600'">{{
                        money(row.commission) }} ({{ row.status.toLowerCase() }})</b></div>
                </div>
                <p v-if="!sellerReportRows.length" class="text-sm text-slate-400 text-center py-4">No commission
                  activity yet.</p>
              </div>
            </div>
            <div class="card p-4 mt-3">
              <div class="flex justify-between">
                <p class="font-black">Commission Payouts — By Order</p>
                <div class="flex gap-1"><button @click="printSellerReport" class="report-action">⎙ Print</button><button
                    @click="downloadSellerCsv('commission')" class="report-action">⇩ CSV</button></div>
              </div>
              <div class="max-h-64 overflow-auto mt-3">
                <table class="w-full min-w-[560px] text-xs">
                  <thead class="sticky top-0 bg-white text-left text-slate-400 uppercase text-[9px]">
                    <tr>
                      <th class="pb-2">Order</th>
                      <th class="pb-2">Product</th>
                      <th class="pb-2">Paid to</th>
                      <th class="pb-2">Rate</th>
                      <th class="pb-2">Commission</th>
                      <th class="pb-2">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="row in sellerReportRows" :key="row.id" class="border-t border-slate-100">
                      <td class="py-2 font-black">{{ row.id }}</td>
                      <td class="py-2">{{ row.product }}</td>
                      <td class="py-2 text-blue-600 font-bold">@affiliate</td>
                      <td class="py-2">{{ sellerCommissionRate * 100 }}%</td>
                      <td class="py-2 text-violet-600 font-bold">{{ money(row.commission) }}</td>
                      <td class="py-2"><span
                          :class="['rounded-full px-2 py-0.5 font-black', row.status === 'Paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700']">{{
                            row.status }}</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card p-4 mt-3">
              <div class="flex justify-between">
                <p class="font-black">Detailed Sales Report</p>
                <div class="flex gap-1"><button @click="printSellerReport" class="report-action">⎙ Print</button><button
                    @click="downloadSellerCsv('sales')" class="report-action">⇩ CSV</button></div>
              </div>
              <div class="max-h-64 overflow-auto mt-3">
                <table class="w-full min-w-[440px] text-xs">
                  <thead class="sticky top-0 bg-white text-left text-slate-400 uppercase text-[9px]">
                    <tr>
                      <th class="pb-2">Order</th>
                      <th class="pb-2">Product</th>
                      <th class="pb-2">Units</th>
                      <th class="pb-2">Sales</th>
                      <th class="pb-2">Commission</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in SELLER_ORDERS" :key="order.id" class="border-t border-slate-100">
                      <td class="py-2 font-black">{{ order.id }}</td>
                      <td class="py-2">{{ order.item }}</td>
                      <td class="py-2">{{ order.units }}</td>
                      <td class="py-2">{{ money(order.total) }}</td>
                      <td class="py-2 text-violet-600 font-bold">{{ money(order.total * sellerCommissionRate) }}</td>
                    </tr>
                    <tr v-if="!SELLER_ORDERS.length">
                      <td colspan="5" class="py-4 text-center text-slate-400">No sales yet.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card p-4 mt-3">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-black">Sales Tax Report</p>
                  <p class="text-[10px] text-slate-400">For filing with your local tax agency · flat 10% rate</p>
                </div>
                <div class="flex gap-1"><button @click="printSellerReport" class="report-action">⎙ Print</button><button
                    @click="downloadSellerCsv('tax')" class="report-action">⇩ CSV</button></div>
              </div>
              <div class="flex items-center justify-between rounded-xl bg-amber-50 border border-amber-100 p-3 mt-3">
                <span class="font-black text-amber-700 text-sm">Total Sales Tax collected</span><b
                  class="text-xl text-amber-700">{{ money(sellerTaxCollected) }}</b>
              </div>
            </div>
          </template>

          <!-- EARNINGS -->
          <template v-else>
            <div class="rounded-2xl bg-white border border-slate-100 p-5 text-center">
              <p class="text-[11px] font-black uppercase text-lkblue2">All-time revenue</p>
              <p class="text-4xl font-black mt-1">{{ money(sellerRevenue) }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-3">
              <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                <p class="text-[11px] font-black text-emerald-700">Available for payout</p>
                <p class="text-2xl font-black text-emerald-700">{{ money(sellerEarningsData.available) }}</p>
                <p class="text-[10px] text-emerald-600">From delivered orders</p>
              </div>
              <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4">
                <p class="text-[11px] font-black text-amber-700">Pending</p>
                <p class="text-2xl font-black text-amber-700">{{ money(sellerEarningsData.pending) }}</p>
                <p class="text-[10px] text-amber-600">Held until delivered</p>
              </div>
            </div>
            <div class="card p-4 mt-3">
              <p class="font-black mb-2">Transfer earnings to Wallet</p>
              <div class="grid grid-cols-3 gap-2 mb-2">
                <button @click="cashOutAmt = Math.min(50, sellerEarningsData.available)"
                  class="rounded-xl border-2 border-emerald-100 py-2 font-black text-emerald-700 text-sm hover:bg-emerald-50 transition">{{
                    money(Math.min(50, sellerEarningsData.available)) }}</button>
                <button @click="cashOutAmt = Math.min(100, sellerEarningsData.available)"
                  class="rounded-xl border-2 border-emerald-100 py-2 font-black text-emerald-700 text-sm hover:bg-emerald-50 transition">{{
                    money(Math.min(100, sellerEarningsData.available)) }}</button>
                <button @click="cashOutAmt = sellerEarningsData.available"
                  class="rounded-xl border-2 border-emerald-100 py-2 font-black text-emerald-700 text-sm hover:bg-emerald-50 transition">All</button>
              </div>
              <input v-model="cashOutAmt" type="number" placeholder="Amount (USD)"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-black text-lg outline-none focus:border-emerald-400" />
              <button @click="sellerCashOut"
                class="btn w-full mt-2 py-3 text-white text-sm font-black flex items-center justify-center gap-2 shadow hover:shadow-md hover:-translate-y-0.5 transition"
                style="background:linear-gradient(135deg,#059669,#14b8a6)">
                <i data-lucide="wallet" class="w-4 h-4"></i>Transfer to Wallet
              </button>
              <p class="text-[11px] text-slate-400 mt-2 text-center">Last payout: {{ money(sellerEarningsData.paidOut)
                }} · completed</p>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Product Detail modal (two-column layout) -->
    <div v-if="viewingProduct" class="fixed inset-0 z-[140] bg-black/50 backdrop-blur-sm grid place-items-center p-4">
      <div class="bg-white rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-auto shadow-2xl">
        <div class="flex flex-col lg:flex-row">
          <!-- Left: image -->
          <div class="lg:w-1/2 w-full bg-white flex items-center justify-center p-6 lg:p-8">
            <div class="w-full max-w-[520px] flex items-center justify-center">
              <img :src="viewingProduct.image" alt="Product image"
                class="max-h-[520px] w-auto max-w-full object-contain rounded-lg shadow-sm bg-white" />
            </div>
          </div>

          <!-- Right: details -->
          <div class="lg:w-1/2 w-full p-6 lg:p-8">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h2 class="text-xl lg:text-2xl font-black leading-tight mb-1">{{ viewingProduct.title }}</h2>
                <div class="text-sm text-slate-500 mb-3">{{ viewingProduct.category }} · {{ money(viewingProduct.price)
                  }} ·
                  {{ viewingProduct.seller_type }}</div>
              </div>
              <button @click="viewingProduct = null"
                class="ml-auto p-2 rounded-full bg-white/60 hover:bg-white/80 shadow text-rose-600 hover:text-rose-700"
                aria-label="Close" style="color: #f43f5e;">
                <i data-lucide="x" class="w-5 h-5"></i>
              </button>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-1">
              <div>
                <h3 class="font-black text-sm mb-2">Description</h3>
                <p class="text-slate-700 text-sm">
                  {{ viewingProduct.desc || viewingProduct.description || 'No description available.' }}
                </p>
              </div>

              <div class="flex gap-2 items-center">
                <div class="flex-1">
                  <div class="flex flex-wrap gap-2 mb-3">
                    <span class="chip">SELLER</span>
                    <span class="chip">{{ viewingProduct.seller }}</span>
                    <span class="chip">LISTING TYPE</span>
                    <span class="chip">{{ viewingProduct.seller_type }}</span>
                  </div>

                  <div class="flex gap-2 mb-4">
                    <button v-if="viewingProduct.stock > 0" @click="addToCart(viewingProduct); viewingProduct = null"
                      class="btn bg-blue-600 text-white px-4 py-2 rounded-lg font-black">+ Add to Cart</button>
                    <button @click="() => followSeller(viewingProduct)" class="btn btn-ghost px-4 py-2 rounded-lg">
                      {{ localFavoriteSellerIds.includes(Number(viewingProduct.user_id || viewingProduct.seller_id)) ?
                        'Following' : 'Follow seller' }}
                    </button>
                    <button @click="() => viewSeller(viewingProduct)"
                      class="btn btn-ghost px-4 py-2 rounded-lg">Seller</button>
                  </div>
                </div>
              </div>

              <div class="rounded-lg border border-amber-100 bg-amber-50 p-4">
                <div class="font-black text-amber-700 mb-2">PICKUP LOCATIONS (INHERITED)</div>
                <ul class="list-disc list-inside text-sm text-amber-800">
                  <template v-if="viewingProduct.pickup_locations && viewingProduct.pickup_locations.length">
                    <li v-for="loc in viewingProduct.pickup_locations" :key="loc">{{ loc }}</li>
                  </template>
                  <template v-else>
                    <li>No pickup locations configured.</li>
                  </template>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, nextTick, onMounted, onUpdated, h, watch } from 'vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import { DB, PIC } from '../../../components/new_frontend/MockDataStore';
import axios from 'axios';
import { useForm, usePage, router } from '@inertiajs/vue3';
import StoreTab from '../../User/LinkUpShop/Products/Components/tabs/StoreTab.vue';
import StoreCarnival from '../../User/LinkUpShop/Products/Components/tabs/StoreCarnival.vue';
import ProductTab from '../../User/LinkUpShop/Products/Components/tabs/Product.vue';

defineOptions({ layout: MainLayout });

// ---------- DATA ----------
const props = defineProps({
  products: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  merchants: { type: Array, default: () => [] },
  userProducts: { type: Array, default: () => [] },
  favoriteSellerIds: { type: Array, default: () => [] },
  promotionProductIds: { type: Array, default: () => [] },
  affiliatePromotions: { type: Array, default: () => [] },
  shopFee: { type: Object, default: () => ({}) },
  sellerHub: { type: Object, default: () => ({ earnings: {}, orders: [] }) },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const CATS = computed(() => {
  const base = props.categories && props.categories.length
    ? ['All', ...props.categories.map(c => c.name || c)]
    : ['All', 'Home', 'Accessories', 'Outdoors', 'Collectibles', 'Carnival'];
  return [...new Set(base)];
});
const SHOP_CATS = ['Home', 'Accessories', 'Outdoors', 'Collectibles', 'Carnival', 'Fashion', 'Electronics', 'Food', 'Beauty', 'Other'];

// ---------- STATE ----------
const activeCat = ref('All');
const cart = ref(DB.get('lk_cart', []));
const showCart = ref(false);
const showCheckout = ref(false);

watch(cart, (newCart) => {
  DB.set('lk_cart', newCart);
}, { deep: true });
const showCreate = ref(false);
const activeCreateTab = ref('');
const showAddProduct = ref(false);
const showAffiliate = ref(false);
const showSellerHub = ref(false);
const viewingProduct = ref(null);
const sellerModal = ref(null);
const localFavoriteSellerIds = ref([...(props.favoriteSellerIds || [])].map(Number));

const affTab = ref('browse');
const sellerTab = ref('overview');
const affiliateTabs = [{ id: 'browse', label: 'Promote' }, { id: 'promos', label: 'My Promotions' }, { id: 'earnings', label: 'Earnings' }];
const sellerTabs = [{ id: 'overview', label: 'Overview' }, { id: 'inventory', label: 'Inventory' }, { id: 'orders', label: 'Orders' }, { id: 'reports', label: 'Reports' }, { id: 'earnings', label: 'Earnings' }];

const productForm = reactive({ name: '', price: '', stock: '10', category: 'Home', type: 'Individual', commission: '10', collectTax: false, imageUrl: '', desc: '' });
const cashOutAmt = ref(0);

// Checkout State
const checkoutForm = reactive({
  name: '', email: '', phone: '', notes: '',
  usePickup: false, pickupLoc: '', payMethod: 'wallet',
  ship: { address1: '', address2: '', city: '', state: '', country: '', postal: '' }
});
const checkoutErrors = ref({});
const pricingQuote = ref(null);

// ---------- COMPUTED ----------
const allProducts = computed(() => {
  return props.products;
});

const filteredProducts = computed(() => {
  return activeCat.value === 'All' ? allProducts.value : allProducts.value.filter(p => p.category === activeCat.value);
});

const cartTotal = computed(() => cart.value.reduce((s, i) => s + i.price * i.qty, 0));

// Fees
const feeAmount = computed(() => {
  const fee = props.shopFee;
  if (!fee || !fee.enabled) return 0;
  let amount = 0;
  const sub = cartTotal.value;
  if (fee.fee_type === 'percent') amount = sub * (Number(fee.percent) / 100);
  else if (fee.fee_type === 'fixed') amount = Number(fee.fixed);
  else if (fee.fee_type === 'both') amount = (sub * (Number(fee.percent) / 100)) + Number(fee.fixed);
  if (Number(fee.min_fee) > 0 && amount < Number(fee.min_fee)) amount = Number(fee.min_fee);
  if (Number(fee.max_fee) > 0 && amount > Number(fee.max_fee)) amount = Number(fee.max_fee);
  return amount;
});

const finalTotal = computed(() => cartTotal.value + feeAmount.value);

// Seller
const sellerRevenue = computed(() => props.sellerHub?.revenue || 0);
const sellerUnitsSold = computed(() => props.sellerHub?.unitsSold || 0);
const sellerInventory = computed(() => {
  if (props.userProducts?.length) return props.userProducts.map(p => ({
    id: p.id,
    title: p.name,
    stock: p.qty,
    price: p.price,
    category: p.category?.name || 'Other',
    image: p.cover_image || p.image_url || PIC('pr1', 80, 80)
  }));
  return [];
});
const sellerInventoryCount = computed(() => props.sellerHub?.inventoryCount || 0);
const liveCashOutBalance = ref({ available: null, pending: null });
const latestSellerPayout = ref(null);
const sellerEarningsData = computed(() => ({
  available: liveCashOutBalance.value.available ?? props.sellerHub?.earnings?.available ?? 0,
  pending: liveCashOutBalance.value.pending ?? props.sellerHub?.earnings?.pending ?? 0,
  paidOut: latestSellerPayout.value?.amount ?? props.sellerHub?.earnings?.paidOut ?? 0,
}));

const SELLER_ORDERS = computed(() => props.sellerHub?.orders || []);
const sellerOrderCount = computed(() => Number(props.sellerHub?.orderCount || SELLER_ORDERS.value.length));
const sellerCommissionRate = 0.08;
const sellerCommission = computed(() => sellerRevenue.value * sellerCommissionRate);
const sellerCommissionPending = computed(() => Math.max(0, sellerCommission.value - sellerEarningsData.value.paidOut));
const sellerNetRevenue = computed(() => Math.max(0, sellerRevenue.value - sellerCommission.value));
const sellerTaxCollected = computed(() => sellerRevenue.value * 0.10);
const sellerAverageOrder = computed(() => sellerOrderCount.value ? sellerRevenue.value / sellerOrderCount.value : 0);
const sellerOverviewStats = computed(() => [
  { label: 'Commission paid out', value: money(sellerCommission.value), note: 'affiliate earnings', icon: '🤝', tint: 'bg-violet-100' },
  { label: 'Taxes', value: money(sellerTaxCollected.value), note: '10% flat rate', icon: '🏛', tint: 'bg-amber-100' },
  { label: 'Net revenue', value: money(sellerNetRevenue.value), note: 'after commission', icon: '💵', tint: 'bg-emerald-100' },
  { label: 'Units sold', value: sellerUnitsSold.value, note: 'items', icon: '📦', tint: 'bg-blue-100' },
  { label: 'Orders', value: sellerOrderCount.value, note: 'total', icon: '🛍', tint: 'bg-orange-100' },
  { label: 'Avg order', value: money(sellerAverageOrder.value), note: 'per order', icon: '🧮', tint: 'bg-cyan-100' },
]);
const sellerReportSummary = computed(() => [
  { label: 'Sales subtotal', value: money(sellerRevenue.value), note: 'excl. tax', color: '' },
  { label: 'Sales tax collected', value: money(sellerTaxCollected.value), note: '10% rate', color: 'text-amber-600' },
  { label: 'Total collected', value: money(sellerRevenue.value + sellerTaxCollected.value), note: 'from buyers', color: '' },
  { label: 'Orders', value: sellerOrderCount.value, note: 'all time', color: '' },
  { label: 'Units sold', value: sellerUnitsSold.value, note: 'items', color: '' },
  { label: 'Avg order', value: money(sellerAverageOrder.value + (sellerAverageOrder.value * .10)), note: 'incl. tax', color: '' },
]);
const sellerCommissionStats = computed(() => [
  { label: 'TOTAL', value: money(sellerCommission.value), color: 'text-violet-600' },
  { label: 'PAID OUT', value: money(sellerEarningsData.value.paidOut), color: 'text-emerald-600' },
  { label: 'PENDING', value: money(sellerCommissionPending.value), color: 'text-amber-600' },
  { label: 'ORDERS', value: sellerOrderCount.value, color: 'text-slate-800' },
]);
const sellerCategoryReport = computed(() => {
  const totals = sellerInventory.value.reduce((all, product) => {
    all[product.category] = (all[product.category] || 0) + Number(product.price || 0);
    return all;
  }, {});
  const max = Math.max(...Object.values(totals), 1);
  return Object.entries(totals).map(([name, value]) => ({ name, value, percent: Math.max(8, Math.round((value / max) * 100)) }));
});
const sellerReportRows = computed(() => SELLER_ORDERS.value.map((order) => ({
  id: order.id,
  product: order.item,
  category: sellerInventory.value.find((item) => item.title === order.item)?.category || 'Marketplace',
  units: Number(order.units || 0),
  sales: Number(order.total || 0),
  commission: Number(order.total || 0) * sellerCommissionRate,
  net: Number(order.total || 0) * (1 - sellerCommissionRate),
  status: ['Delivered', 'Paid'].includes(order.status) ? 'Paid' : 'Pending',
})));

// Affiliate
const promos = ref([...props.affiliatePromotions]);
const affiliateProducts = computed(() => allProducts.value.filter(p => (p.stock == null || p.stock > 0)));
const affPending = computed(() => 0);
const affAvailable = computed(() => 0);
const affPaid = computed(() => 0);
const affSales = computed(() => []);
const affEarningsTotal = computed(() => affPending.value + affAvailable.value + affPaid.value);

// SVG chart points for seller overview
const chartPoints = computed(() => {
  const d = [20, 35, 28, 50, 45, 62, 58, 80, 72, 95, 88, 70, 84];
  return d.map((p, i) => `${((i / 12) * 300).toFixed(0)},${(90 - (p / 100) * 90).toFixed(0)}`).join(' ');
});

// ---------- METHODS ----------
const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const printSellerReport = (event) => {
  const section = event?.currentTarget?.closest('.card')?.innerText || '';
  const reportType = section.includes('Sales Tax Report') ? 'tax' : section.includes('Commission Report') || section.includes('Commission Payouts') ? 'commission' : 'sales';
  const title = reportType === 'tax' ? 'Sales Tax Report' : reportType === 'commission' ? 'Commission Report' : 'Sales Report';
  const date = new Date().toLocaleDateString();
  const columns = reportType === 'tax'
    ? ['Order', 'Item', 'Taxable Amount', 'Sales Tax Collected']
    : reportType === 'commission'
      ? ['Order', 'Product', 'Paid To', 'Rate', 'Commission', 'Status']
      : ['Order', 'Item', 'Qty', 'Subtotal', 'Sales Tax', 'Commission', 'Net To You', 'Total'];
  const rows = sellerReportRows.value.map((row) => reportType === 'tax'
    ? [row.id, row.product, money(row.sales), money(row.sales * .10)]
    : reportType === 'commission'
      ? [row.id, row.product, '@affiliate', `${sellerCommissionRate * 100}%`, money(row.commission), row.status]
      : [row.id, row.product, row.units, money(row.sales), money(row.sales * .10), money(row.commission), money(row.net), money(row.sales * 1.10)]);
  const total = reportType === 'tax' ? money(sellerTaxCollected.value) : reportType === 'commission' ? money(sellerCommission.value) : money(sellerRevenue.value);
  const html = `<!doctype html><html><head><title>${title}</title><style>body{font-family:Arial,sans-serif;margin:28px;color:#0f172a}h1{font-size:30px;margin:0 0 4px}.sub{color:#64748b;margin:0 0 22px}table{width:100%;border-collapse:collapse;font-size:13px}th{text-align:left;color:#94a3b8;font-size:10px;text-transform:uppercase;padding:10px;border-bottom:1px solid #cbd5e1}td{padding:9px 10px;border-bottom:1px solid #e2e8f0}tfoot td{font-weight:700;border-top:2px solid #0f172a}</style></head><body><h1>${title}</h1><p class="sub">Seller report · Generated ${date}</p><table><thead><tr>${columns.map((column) => `<th>${column}</th>`).join('')}</tr></thead><tbody>${rows.map((row) => `<tr>${row.map((cell) => `<td>${cell}</td>`).join('')}</tr>`).join('')}</tbody><tfoot><tr><td colspan="${columns.length - 1}">Total</td><td>${total}</td></tr></tfoot></table></body></html>`;
  const reportWindow = window.open('', '_blank');
  if (!reportWindow) return window.toast?.('Please allow popups to print reports.');
  reportWindow.document.open();
  reportWindow.document.write(html);
  reportWindow.document.close();
  reportWindow.focus();
  setTimeout(() => reportWindow.print(), 250);
};
const downloadSellerCsv = (type) => {
  const headers = type === 'tax'
    ? ['Order ID', 'Item', 'Taxable Amount', 'Tax Rate %', 'Sales Tax Collected']
    : type === 'commission'
      ? ['Order ID', 'Product', 'Paid To', 'Rate %', 'Commission', 'Status']
      : ['Order ID', 'Item', 'Qty', 'Subtotal', 'Sales Tax', 'Commission', 'Net To You', 'Total', 'Status'];
  const rows = sellerReportRows.value.map((row) => type === 'tax'
    ? [row.id, row.product, row.sales, 10, row.sales * .10]
    : type === 'commission'
      ? [row.id, row.product, '@affiliate', sellerCommissionRate * 100, row.commission, row.status]
      : [row.id, row.product, row.units, row.sales, row.sales * .10, row.commission, row.net, row.sales * 1.10, row.status]);
  const csv = [headers, ...rows].map((row) => row.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\n');
  const link = document.createElement('a');
  link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
  link.download = `seller-${type}-report.csv`;
  link.click();
  URL.revokeObjectURL(link.href);
};

const addToCart = (p) => {
  const existing = cart.value.find(i => i.id === p.id);
  if (existing) { existing.qty++; }
  else { cart.value.push({ id: p.id, title: p.title, price: p.price, image: p.image, qty: 1 }); }
  if (window.toast) window.toast('🛍️ Added to cart');
};
const removeFromCart = (i) => { cart.value.splice(i, 1); };

const openCheckout = () => {
  if (!cart.value.length) return;
  showCart.value = false;
  showCheckout.value = true;
};

const handleCheckout = () => {
  checkoutErrors.value = {};
  if (!checkoutForm.name) checkoutErrors.value.name = 'Full name is required.';
  if (!checkoutForm.email) checkoutErrors.value.email = 'Email is required.';
  if (!checkoutForm.usePickup) {
    if (!checkoutForm.ship.address1) checkoutErrors.value.address1 = 'Address line 1 is required.';
    if (!checkoutForm.ship.city) checkoutErrors.value.city = 'City is required.';
  }
  if (Object.keys(checkoutErrors.value).length) return;

  const form = useForm({
    payment_method: checkoutForm.payMethod,
    shipping_method: checkoutForm.usePickup ? 'pickup' : 'standard',
    phone: checkoutForm.phone,
    address: checkoutForm.usePickup
      ? (checkoutForm.pickupLoc || 'Pickup Location')
      : (checkoutForm.ship.address1 + (checkoutForm.ship.address2 ? (', ' + checkoutForm.ship.address2) : '')),
    city: checkoutForm.ship.city,
    state: checkoutForm.ship.state,
    country: checkoutForm.ship.country,
    zip: checkoutForm.ship.postal,
    items: cart.value.map(i => ({ id: i.id, qty: i.qty })),
    notes: checkoutForm.notes
  });

  form.post(route('frontend.checkout.store'), {
    onSuccess: () => {
      cart.value = [];
      showCheckout.value = false;
      if (window.toast) window.toast('✅ Order placed successfully!');
    },
    onError: (errs) => {
      checkoutErrors.value = errs;
      if (errs.balance && window.toast) window.toast(errs.balance);
    },
  });
};

watch(showCheckout, (v) => {
  if (v && user.value) {
    checkoutForm.name = user.value.name || '';
    checkoutForm.email = user.value.email || '';
    checkoutForm.phone = user.value.phone_number || '';
    checkoutForm.ship.city = user.value.new_city || user.value.city || '';
    checkoutForm.ship.state = user.value.new_state || user.value.state || '';
    checkoutForm.ship.country = user.value.new_country || user.value.country || '';
  }
});

const refreshPricingQuote = async () => {
  if (!showCheckout.value || !cart.value.length) return;
  try {
    const { data } = await axios.post(route('frontend.checkout.quote'), {
      items: cart.value.map(i => ({ id: i.id, qty: i.qty })),
      country: checkoutForm.ship.country, state: checkoutForm.ship.state,
      city: checkoutForm.ship.city, zip: checkoutForm.ship.postal,
      shipping_method: checkoutForm.usePickup ? 'pickup' : 'standard',
    });
    pricingQuote.value = data;
  } catch (_) { pricingQuote.value = null; }
};

watch(() => [showCheckout.value, checkoutForm.usePickup, checkoutForm.ship.country, checkoutForm.ship.state, checkoutForm.ship.city, checkoutForm.ship.postal, cart.value.map(i => `${i.id}:${i.qty}`).join(',')], refreshPricingQuote);

const viewProduct = (p) => { viewingProduct.value = p; };

const followSeller = async (p) => {
  const rawId = p.user_id || p.seller_id || p.sellerId || (p.seller_obj && p.seller_obj.id);

  if (!rawId) {
    if (window.toast) window.toast('Seller ID not available');
    return;
  }

  const sellerId = Number(rawId);

  try {
    const { data } = await axios.post(
      route('frontend.products.toggle-favorite', { seller_id: sellerId })
    );

    if (data.status === false) {
      if (window.toast) window.toast(data.message);
      return;
    }

    // Real-time local update with type-safe toggle
    if (localFavoriteSellerIds.value.includes(sellerId)) {
      localFavoriteSellerIds.value = localFavoriteSellerIds.value.filter(id => id !== sellerId);
    } else {
      localFavoriteSellerIds.value = [...localFavoriteSellerIds.value, sellerId];
    }

    if (window.toast) window.toast('✅ ' + data.message);
  } catch (error) {
    const message = error.response?.data?.message || 'Could not follow seller';
    if (window.toast) window.toast(message);
    console.error('follow seller error:', error);
  }
};

const viewSeller = (p) => {
  // Use embedded seller object when available
  const seller = p.merchant || p.seller_obj || (p.seller && { name: p.seller });
  sellerModal.value = seller || { name: 'Seller' };
};

const openAffiliateHub = () => { showCreate.value = false; activeCreateTab.value = ''; affTab.value = 'browse'; showAffiliate.value = true; };
const openSellerHub = () => { showCreate.value = false; activeCreateTab.value = ''; sellerTab.value = 'overview'; showSellerHub.value = true; };
const openCreateStore = () => { showSellerHub.value = false; showAffiliate.value = false; showCreate.value = true; activeCreateTab.value = 'store'; };
const openCreateGroup = () => { showSellerHub.value = false; showAffiliate.value = false; showCreate.value = true; activeCreateTab.value = 'group'; };
const openAddProduct = () => {   showSellerHub.value = false; showAffiliate.value = false;  showCreate.value = true; activeCreateTab.value = 'product'; };

const refresh = () => {
  router.reload({
    only: ['products', 'merchants', 'userProducts'],
    preserveScroll: true,
    preserveState: true
  });
};

const saveProduct = () => {
  // Product records must be created through the authenticated Inertia product form,
  // never through this legacy browser-only modal.
  showAddProduct.value = false;
  showCreate.value = true;
  activeCreateTab.value = 'product';
  return;
  if (!productForm.name.trim()) { if (window.toast) window.toast('Enter a product name'); return; }
  const prod = {
    id: 'u' + Date.now(),
    seller_type: productForm.type,
    seller: 'Individual Seller · ' + (DB.get('lk_user', {}).city || 'Nassau'),
    title: productForm.name,
    price: parseFloat(productForm.price) || 0,
    category: productForm.category,
    stock: parseInt(productForm.stock) || 1,
    fulfil: productForm.type !== 'Individual' ? 'Pickup available' : 'Delivery only',
    image: productForm.imageUrl || PIC('u' + Date.now(), 700, 500),
    desc: productForm.desc,
    commission: Math.max(0, Math.min(50, parseInt(productForm.commission) || 10)),
    collect_tax: !!productForm.collectTax,
  };
  const list = DB.get('lk_products', []);
  list.unshift(prod);
  DB.set('lk_products', list);
  if (window.toast) window.toast(`✅ "${prod.title}" published to Marketplace`);
  showAddProduct.value = false;
  activeCat.value = prod.category === 'Carnival' ? 'Carnival' : 'All';
};

const isPromoting = (id) => promos.value.some(p => Number(p.id) === Number(id));
const togglePromote = async (p) => {
  const { data } = await axios.post(route('new_frontend.marketplace.affiliate.promotions.toggle', { product: p.id }));
  promos.value = data.promoting
    ? [...promos.value.filter(item => Number(item.id) !== Number(p.id)), p]
    : promos.value.filter(item => Number(item.id) !== Number(p.id));
  return;
  if (isPromoting(p.id)) {
    DB.set('lk_affiliate_promos', promos.value.filter(x => String(x.id) !== String(p.id)));
    if (window.toast) window.toast('Removed from promotions');
  } else {
    const list = DB.get('lk_affiliate_promos', []);
    list.unshift({ id: p.id, title: p.title, price: p.price, image: p.image, commission: p.commission || 10, kind: 'product' });
    DB.set('lk_affiliate_promos', list);
    if (window.toast) window.toast(`🤝 Promoting ${p.title}`);
  }
};
const removePromo = (id) => {
  const product = allProducts.value.find(p => Number(p.id) === Number(id));
  if (product) return togglePromote(product);
  DB.set('lk_affiliate_promos', promos.value.filter(p => String(p.id) !== String(id)));
};
const tagOnLive = (p) => {
  if (window.toast) window.toast('🔴 ' + p.title + ' tagged — go live to start selling!');
};
const releaseAffEarnings = () => {
  if (window.toast) window.toast('Affiliate earnings are released from delivered, attributed orders only.');
  return;
  const e = DB.get('lk_affiliate_earnings', { pending: 0, available: 0, paid: 0, clicks: 0, sales: [] });
  e.available = +(e.available + e.pending).toFixed(2);
  (e.sales || []).forEach(s => { if (s.status === 'pending') s.status = 'released'; });
  e.pending = 0;
  DB.set('lk_affiliate_earnings', e);
  if (window.toast) window.toast('✅ Commission released');
};
const transferAffToWallet = () => {
  if (window.toast) window.toast('Affiliate wallet transfers require a server-side attributed earnings record.');
  return;
  const e = DB.get('lk_affiliate_earnings', { pending: 0, available: 0, paid: 0, clicks: 0, sales: [] });
  if (e.available <= 0) { if (window.toast) window.toast('Nothing available yet'); return; }
  const amt = e.available;
  e.paid = +(e.paid + amt).toFixed(2);
  e.available = 0;
  DB.set('lk_affiliate_earnings', e);
  const wallet = DB.get('lk_wallet', { balance: 0 });
  wallet.balance = +(wallet.balance + amt).toFixed(2);
  DB.set('lk_wallet', wallet);
  if (window.toast) window.toast(`💸 ${money(amt)} commission → Wallet`);
};
const loadLiveCashOutBalance = async () => {
  try {
    const { data } = await axios.get('/seller/cash-out/available');
    liveCashOutBalance.value = {
      available: Number(data.available || 0),
      pending: Number(data.pending || 0),
    };
  } catch (_) {
    // Keep the server-rendered dashboard values if the balance endpoint is unavailable.
  }
};
const loadLatestSellerPayout = async () => {
  try {
    const { data } = await axios.get('/seller/cash-out');
    latestSellerPayout.value = (data.requests || []).find((request) => request.status === 'completed') || null;
  } catch (_) {
    latestSellerPayout.value = null;
  }
};
const sellerCashOut = async () => {
  const v = parseFloat(cashOutAmt.value);
  if (!v || v <= 0) { if (window.toast) window.toast('Enter an amount'); return; }
  if (v > sellerEarningsData.value.available) { if (window.toast) window.toast('Amount exceeds available payout'); return; }

  try {
    const { data: balance } = await axios.get('/seller/cash-out/available');
    const available = Number(balance.available || 0);
    if (v > available) {
      window.toast?.(`Insufficient earnings. Your available earnings are ${money(available)}`);
      return;
    }

    await axios.post('/seller/cash-out', { amount: v });
    await loadLiveCashOutBalance();
    await loadLatestSellerPayout();
    showSellerHub.value = false;
    cashOutAmt.value = 0;
    window.toast?.(`${money(v)} transferred to your Wallet`);
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    const firstError = (value) => Array.isArray(value) ? value[0] : value;
    const message = firstError(errors.rate_limit) || firstError(errors.amount) || error.response?.data?.message || 'Unable to transfer earnings right now.';
    window.toast?.(String(message));
  }
  return;

  router.post(route('frontend.seller.cash-out.store'), { amount: v }, {
    preserveScroll: true,
    onSuccess: () => {
      showSellerHub.value = false;
      cashOutAmt.value = 0;
      if (window.toast) window.toast(`🏦 ${money(v)} transferred to your Wallet`);
    },
    onError: (errs) => {
      const firstError = (value) => Array.isArray(value) ? value[0] : value;
      const message = firstError(errs.rate_limit) || firstError(errs.amount) || 'Unable to transfer earnings right now.';
      window.toast?.(String(message));
    }
  });
};

watch(showSellerHub, (isOpen) => {
  if (isOpen) {
    loadLiveCashOutBalance();
    loadLatestSellerPayout();
  }
});

const refreshIcons = () => {
  nextTick(() => {
    if (window.lucide) {
      window.lucide.createIcons();
    } else {
      // Retry in case Lucide is still loading from CDN
      setTimeout(refreshIcons, 300);
    }
  });
};

// Watch for any UI state changes that might reveal new icons
watch(
  [
    showCart, showCheckout, showCreate, activeCreateTab,
    showAddProduct, showAffiliate, showSellerHub,
    viewingProduct, sellerModal, activeCat, cart,
    () => props.products, () => props.userProducts, () => props.sellerHub
  ],
  () => {
    refreshIcons();
  },
  { deep: true }
);

onMounted(() => {
  refreshIcons();
});

onUpdated(() => {
  refreshIcons();
});

// ---------- INLINE COMPONENTS ----------
const CreateCard = (props, { emit }) => {
  return h('button', {
    onClick: () => emit('click'),
    class: 'w-full text-left rounded-2xl border border-slate-100 p-4 hover:border-blue-200 hover:bg-slate-50/50 flex items-center gap-4 transition group'
  }, [
    h('span', {
      class: 'h-14 w-14 rounded-2xl grid place-items-center text-white shrink-0 shadow-sm transition group-hover:scale-105',
      style: 'background:linear-gradient(135deg, #3b82f6 0%, #10b981 100%)'
    }, [h('i', { 'data-lucide': props.icon, class: 'w-6 h-6' })]),
    h('div', { class: 'flex-1 min-w-0' }, [
      h('p', { class: 'text-base font-black text-slate-800' }, props.title),
      h('p', { class: 'text-xs font-bold text-slate-400 mt-0.5' }, props.desc)
    ])
  ]);
};
CreateCard.props = ['icon', 'title', 'desc'];
CreateCard.emits = ['click'];
</script>

<style scoped>
.report-action {
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  padding: 6px 10px;
  font-size: 12px;
  font-weight: 800;
  color: #334155;
  background: #fff;
}

.report-action:hover {
  background: #f8fafc;
  border-color: #93c5fd;
  color: #2563eb;
}
</style>
