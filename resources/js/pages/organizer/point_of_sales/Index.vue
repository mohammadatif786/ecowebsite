<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/organizer/AppLayout.vue'
import {
  CreditCard,
  ShoppingCart,
  Users,
  Search,
  Plus,
  Minus,
  Trash2,
  MoreVertical,
  Edit,
  Power,
  Check,
  Receipt,
  Loader2,
  X
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'

interface PointOfSaleAccount {
  id: number
  name: string
  username: string
  creationDate: string
  lastLogin: string
  eventsCount: number
  status: 'enabled' | 'disabled'
}

interface EventTicket {
  id: number
  name: string
  price: number
}

interface PosEvent {
  id: number
  title: string
  image: string
  price: number
  tickets: EventTicket[]
}

interface CartItem {
  id: number // ticket id
  eventTitle: string
  ticketName: string
  price: number
  qty: number
}

interface Transaction {
  id: number
  event: string
  amount: number
  qty: number
  method: string
  time: string
  buyer?: string
}

const props = defineProps<{
  pointsOfSale: PointOfSaleAccount[]
  events: PosEvent[]
  todaySales: number
  todayTransactions: Transaction[]
}>()

// Tabs: 'terminal' or 'accounts'
const activeTab = ref('terminal')

// --- Terminal State ---
const cart = ref<CartItem[]>([])
const paymentMethod = ref('Cash')
const buyer = ref({ name: '', email: '' })
const taxEnabled = ref(false)
const isCharging = ref(false)
const showReceiptModal = ref(false)
const lastOrderId = ref('')
const lastReceiptLines = ref<any[]>([])
const lastTotal = ref(0)
const lastMethod = ref('')
const lastTime = ref('')
const lastBuyer = ref('')
const lastBuyerEmail = ref('')
const lastFees = ref({ platform: 0, processing: 0, tax: 0, subtotal: 0 })
const localTodaySales = ref(0)
const localTransactions = ref<Transaction[]>([])

// --- Accounts State ---
const searchQuery = ref('')
const activeDropdown = ref<number | null>(null)
const showAddAccountModal = ref(false)
const showEditAccountModal = ref(false)
const editingAccount = ref<PointOfSaleAccount | null>(null)

const addAccountForm = ref({
  name: '',
  username: '',
  password: '',
  password_confirmation: ''
})

const editAccountForm = ref({
  name: '',
  username: '',
  password: '',
  password_confirmation: ''
})

// --- Terminal Logic ---
const subtotal = computed(() => {
  return cart.value.reduce((acc, item) => acc + (item.price * item.qty), 0)
})

const totalQty = computed(() => {
  return cart.value.reduce((acc, item) => acc + item.qty, 0)
})

const platformFee = computed(() => Number((subtotal.value * 0.077).toFixed(2)))
const processingFee = computed(() => paymentMethod.value === 'Credit' ? 0.30 : 0)
const tax = computed(() => taxEnabled.value ? Number((subtotal.value * 0.10).toFixed(2)) : 0)
const buyerTotal = computed(() => Number((subtotal.value + platformFee.value + processingFee.value + tax.value).toFixed(2)))

const availableTickets = computed(() => props.events.flatMap(event =>
  event.tickets.map(ticket => ({ ...ticket, eventId: event.id, eventTitle: event.title, eventImage: event.image }))
))

const addToCart = (ticket: EventTicket & { eventTitle: string }) => {
  const existing = cart.value.find(i => i.id === ticket.id)
  if (existing) {
    existing.qty++
  } else {
    cart.value.push({
      id: ticket.id,
      eventTitle: ticket.eventTitle,
      ticketName: ticket.name,
      price: ticket.price,
      qty: 1
    })
  }
}

const changeQty = (id: number, delta: number) => {
  const item = cart.value.find(i => i.id === id)
  if (!item) return
  item.qty += delta
  if (item.qty <= 0) {
    cart.value = cart.value.filter(i => i.id !== id)
  }
}

const clearCart = () => {
  cart.value = []
  buyer.value = { name: '', email: '' }
  taxEnabled.value = false
  paymentMethod.value = 'Cash'
}

const handleCharge = () => {
  if (cart.value.length === 0) return

  isCharging.value = true
  const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  const receiptLines = cart.value.map(item => ({ ...item }))
  const orderId = `POS-${String(Date.now()).slice(-6)}`
  const customer = buyer.value.name.trim() || 'Walk-in Customer'

  lastOrderId.value = orderId
  lastReceiptLines.value = receiptLines
  lastTotal.value = buyerTotal.value
  lastMethod.value = paymentMethod.value
  lastTime.value = time
  lastBuyer.value = customer
  lastBuyerEmail.value = buyer.value.email.trim()
  lastFees.value = { platform: platformFee.value, processing: processingFee.value, tax: tax.value, subtotal: subtotal.value }
  localTodaySales.value = Number((localTodaySales.value + buyerTotal.value).toFixed(2))
  localTransactions.value.unshift(...receiptLines.map((item, index) => ({
    id: Number(`${Date.now()}${index}`), event: item.eventTitle, amount: Number((item.price * item.qty).toFixed(2)),
    qty: item.qty, method: paymentMethod.value, time, buyer: customer,
  })))
  cart.value = []
  buyer.value = { name: '', email: '' }
  taxEnabled.value = false
  paymentMethod.value = 'Cash'
  showReceiptModal.value = true
  isCharging.value = false
  toast.success('Demo sale completed. No ticket or payment was created.')
}

const printReceipt = () => {
  const receiptWindow = window.open('', '_blank')
  if (!receiptWindow) {
    toast.error('Please allow pop-ups to print the receipt.')
    return
  }
  const lines = lastReceiptLines.value.map(line => `<tr><td>${line.eventTitle} ×${line.qty}</td><td style="text-align:right">${formatCurrency(line.price * line.qty)}</td></tr>`).join('')
  receiptWindow.document.write(`<html><head><title>POS Receipt</title><style>body{font-family:Arial,sans-serif;padding:28px;color:#0f172a}table{width:100%;border-collapse:collapse}td{padding:8px 0;border-bottom:1px solid #e2e8f0}.total{font-size:20px;font-weight:bold}</style></head><body><h2>POS Ticket Receipt</h2><p>${lastOrderId.value} · ${lastTime.value} · ${lastMethod.value}</p><p><b>Buyer:</b> ${lastBuyer.value}</p><table>${lines}</table><p>Subtotal: ${formatCurrency(lastFees.value.subtotal)}</p><p>Platform fee: ${formatCurrency(lastFees.value.platform)}</p><p>Processing fee: ${formatCurrency(lastFees.value.processing)}</p><p>Tax: ${formatCurrency(lastFees.value.tax)}</p><p class="total">Total: ${formatCurrency(lastTotal.value)}</p></body></html>`)
  receiptWindow.document.close()
  receiptWindow.focus()
  receiptWindow.print()
}

// --- Accounts Logic ---
const filteredAccounts = computed(() => {
  if (!searchQuery.value) return props.pointsOfSale
  const q = searchQuery.value.toLowerCase()
  return props.pointsOfSale.filter(a =>
    a.name.toLowerCase().includes(q) || a.username.toLowerCase().includes(q)
  )
})

const openAddAccount = () => {
  addAccountForm.value = { name: '', username: '', password: '', password_confirmation: '' }
  showAddAccountModal.value = true
}

const openEditAccount = (account: PointOfSaleAccount) => {
  editingAccount.value = account
  editAccountForm.value = { name: account.name, username: account.username, password: '', password_confirmation: '' }
  showEditAccountModal.value = true
  activeDropdown.value = null
}

const submitAddAccount = () => {
  router.post(route('organizer.pos.store'), addAccountForm.value, {
    onSuccess: () => {
      showAddAccountModal.value = false
      toast.success('Point of Sale created')
    }
  })
}

const submitEditAccount = () => {
  if (!editingAccount.value) return
  router.put(route('organizer.pos.update', editingAccount.value.id), editAccountForm.value, {
    onSuccess: () => {
      showEditAccountModal.value = false
      toast.success('Point of Sale updated')
    }
  })
}

const toggleAccountStatus = (id: number) => {
  router.patch(route('organizer.pos.toggle', id), {}, {
    onSuccess: () => {
      activeDropdown.value = null
      toast.success('Status updated')
    }
  })
}

const deleteAccount = (id: number) => {
  if (confirm('Are you sure you want to delete this POS account?')) {
    router.delete(route('organizer.pos.destroy', id), {
      onSuccess: () => {
        activeDropdown.value = null
        toast.success('Account deleted')
      }
    })
  }
}

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val)
}
</script>

<template>
  <Head title="Point of Sale" />

  <AppLayout>
    <!-- Sync Section Header (Line 4685 Reference) -->
    <div class="rounded-3xl overflow-hidden mb-6" :style="{ background: activeTab === 'terminal' ? 'linear-gradient(120deg,#059669,#14b8a6)' : 'linear-gradient(120deg,#6366f1,#8b5cf6)' }">
      <div class="p-5 flex items-center justify-between flex-wrap gap-3 text-white">
        <div class="flex items-center gap-4">
          <span class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center shrink-0">
            <component :is="activeTab === 'terminal' ? CreditCard : Users" class="w-6 h-6" />
          </span>
          <div>
            <p class="text-xl font-black leading-tight">{{ activeTab === 'terminal' ? 'Point of Sale' : 'POS Accounts' }}</p>
            <p class="text-white/80 text-[13px] font-bold mt-0.5">
              {{ activeTab === 'terminal' ? 'Sell tickets at the door' : 'Manage who can sell tickets on your behalf' }}
            </p>
          </div>
        </div>

        <!-- Tab Switcher -->
        <div class="flex p-1 bg-black/10 rounded-2xl border border-white/10 shrink-0">
          <button @click="activeTab = 'terminal'"
            class="px-4 py-1.5 rounded-xl text-xs font-black transition-all"
            :class="activeTab === 'terminal' ? 'bg-white text-emerald-600 shadow-sm' : 'text-white/70 hover:text-white'">
            Terminal
          </button>
          <button @click="activeTab = 'accounts'"
            class="px-4 py-1.5 rounded-xl text-xs font-black transition-all"
            :class="activeTab === 'accounts' ? 'bg-white text-indigo-600 shadow-sm' : 'text-white/70 hover:text-white'">
            Accounts
          </button>
        </div>
      </div>
    </div>

    <!-- TERMINAL TAB -->
    <div v-if="activeTab === 'terminal'" class="space-y-6 animate-in fade-in duration-300">
      <div class="flex items-center justify-end mb-4">
        <div class="rounded-2xl px-6 py-4 text-white text-right shadow-lg shadow-emerald-500/20"
             style="background:linear-gradient(135deg,#059669,#14b8a6)">
          <p class="text-[11px] font-black opacity-80 uppercase tracking-wider">Today's Sales</p>
          <p class="text-3xl font-black">{{ formatCurrency(localTodaySales) }}</p>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content (Events & Transactions) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Event Grid -->
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <button v-for="ticket in availableTickets" :key="ticket.id"
              @click="addToCart(ticket)"
              class="card overflow-hidden text-left hover:shadow-xl transition-all hover:-translate-y-1 bg-white border border-slate-100 rounded-[20px] group">
              <div class="relative h-28 overflow-hidden">
                <img :src="ticket.eventImage" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors"></div>
              </div>
              <div class="p-3">
                <p class="font-black text-sm truncate text-slate-800">{{ ticket.name }}</p>
                <p class="text-[11px] text-slate-400 font-bold truncate mt-0.5">{{ ticket.eventTitle }}</p>
                <p class="text-emerald-600 font-black text-sm mt-1">{{ ticket.price ? formatCurrency(ticket.price) : 'Free' }}</p>
              </div>
            </button>
            <div v-if="!availableTickets.length" class="col-span-full py-12 text-center bg-white rounded-[20px] border border-dashed border-slate-200">
               <p class="text-slate-400 font-bold">No tickets available for active events.</p>
            </div>
          </div>

          <!-- Today's Transactions -->
          <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-sm">
            <p class="font-black text-lg mb-4 text-slate-800">Today's Transactions</p>
            <div class="space-y-2 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
              <div v-for="t in localTransactions" :key="t.id"
                class="flex items-center justify-between text-sm border-b border-slate-50 last:border-0 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-xl bg-slate-50 grid place-items-center text-slate-400">
                    <Receipt class="w-5 h-5" />
                  </div>
                  <div>
                    <p class="font-black text-slate-800">{{ t.event }}</p>
                    <p class="text-[11px] text-slate-400 font-bold">{{ t.time }} · {{ t.method }} · ×{{ t.qty }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <b class="text-emerald-600 text-lg block">{{ formatCurrency(t.amount) }}</b>
                </div>
              </div>
              <div v-if="!localTransactions.length" class="text-slate-400 text-sm text-center py-12">
                No sales yet today. Start selling!
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar (Cart) -->
        <div class="space-y-6">
          <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-xl sticky top-24">
            <div class="flex items-center justify-between mb-4">
               <p class="font-black text-lg text-slate-800 flex items-center gap-2">
                 <ShoppingCart class="w-5 h-5 text-emerald-500" />
                 Cart
               </p>
               <span class="bg-emerald-50 text-emerald-600 px-2.5 py-0.5 rounded-full text-xs font-black">{{ totalQty }}</span>
            </div>

            <div class="min-h-[200px] max-h-[40vh] overflow-y-auto mb-4 pr-1 custom-scrollbar">
              <div v-for="item in cart" :key="item.id" class="flex items-center justify-between mb-4 gap-3">
                <div class="min-w-0 flex-1">
                  <p class="font-black text-sm truncate text-slate-800">{{ item.eventTitle }}</p>
                  <p class="text-[11px] text-slate-400 font-bold uppercase tracking-tighter">{{ item.ticketName }} · {{ formatCurrency(item.price) }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <button @click="changeQty(item.id, -1)" class="h-8 w-8 rounded-xl border border-slate-100 bg-slate-50 grid place-items-center hover:bg-slate-100 transition shadow-sm">
                    <Minus class="w-3 h-3 font-black" />
                  </button>
                  <span class="w-6 text-center font-black text-sm text-slate-700">{{ item.qty }}</span>
                  <button @click="changeQty(item.id, 1)" class="h-8 w-8 rounded-xl border border-slate-100 bg-slate-50 grid place-items-center hover:bg-slate-100 transition shadow-sm">
                    <Plus class="w-3 h-3 font-black" />
                  </button>
                </div>
              </div>
              <div v-if="!cart.length" class="text-slate-400 text-sm text-center py-16 px-4">
                <div class="h-16 w-16 bg-slate-50 rounded-full grid place-items-center mx-auto mb-4">
                  <Plus class="w-8 h-8 text-slate-200" />
                </div>
                <p class="font-bold">Tap an event card<br/>to add to cart.</p>
              </div>
            </div>

            <div class="border-t border-slate-100 pt-5 space-y-3">
              <div class="flex items-center justify-between text-sm"><span class="text-slate-500">Subtotal</span><b>{{ formatCurrency(subtotal) }}</b></div>
              <div class="flex items-center justify-between text-sm"><span class="text-slate-500">Platform fee <span class="text-slate-400 text-xs">(7.7%)</span></span><b>{{ formatCurrency(platformFee) }}</b></div>
              <div v-if="paymentMethod === 'Credit'" class="flex items-center justify-between text-sm"><span class="text-slate-500">Processing fee</span><b>{{ formatCurrency(processingFee) }}</b></div>
              <label class="flex items-center justify-between gap-2 text-sm cursor-pointer"><span class="text-slate-500">Tax <span class="text-slate-400 text-xs">(10%)</span></span><span class="flex items-center gap-2"><b>{{ formatCurrency(tax) }}</b><input v-model="taxEnabled" type="checkbox" class="accent-emerald-600" /></span></label>
              <div class="border-t border-slate-100 pt-4 flex items-center justify-between font-black text-xl text-slate-800">
                <span>Total</span><span>{{ formatCurrency(buyerTotal) }}</span>
              </div>
              <p class="text-[11px] text-slate-400 leading-relaxed">Fees are display-only in this static terminal; no payment or ticket is created.</p>
              <div class="space-y-2">
                <input v-model="buyer.name" type="text" placeholder="Buyer name" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none bg-slate-50 focus:border-emerald-400 transition" />
                <input v-model="buyer.email" type="email" placeholder="Buyer email (for e-ticket)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none bg-slate-50 focus:border-emerald-400 transition" />
              </div>
              <div class="space-y-1.5">
                <label class="text-[11px] font-black text-slate-400 uppercase">Payment Method</label>
                <select v-model="paymentMethod" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none bg-slate-50 focus:border-emerald-400 transition">
                  <option>Cash</option><option>Credit</option>
                </select>
              </div>

              <button @click="handleCharge" :disabled="!cart.length || isCharging"
                class="w-full py-4 rounded-2xl font-black text-white transition hover:scale-[1.01] active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/25"
                style="background:linear-gradient(135deg,#059669,#14b8a6)">
                <Loader2 v-if="isCharging" class="w-5 h-5 animate-spin" />
                <span v-else>Charge & Issue Ticket{{ totalQty > 1 ? 's' : '' }}</span>
              </button>

              <button v-if="cart.length" @click="clearCart"
                class="w-full py-2.5 rounded-2xl text-slate-400 hover:text-rose-500 text-sm font-black transition-colors flex items-center justify-center gap-1.5">
                <Trash2 class="w-4 h-4" />
                Clear Cart
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ACCOUNTS TAB -->
    <div v-else class="space-y-6 animate-in fade-in duration-300">
      <div class="card overflow-visible bg-white rounded-[20px] border border-slate-100 shadow-sm">
        <!-- Header with Results & Controls -->
        <div class="flex items-center justify-between px-5 py-4 flex-wrap gap-2">
            <div class="flex items-center gap-3">
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input v-model="searchQuery" placeholder="Search accounts..."
                  class="pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm outline-none focus:border-indigo-400 transition w-64"/>
              </div>
              <p class="text-slate-400 font-bold text-xs uppercase">{{ filteredAccounts.length }} accounts found</p>
            </div>

            <button @click="openAddAccount"
                class="h-10 px-5 rounded-full text-white flex items-center gap-2 font-black shadow-lg hover:scale-105 transition active:scale-95"
                style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                <Plus class="w-4 h-4" />
                Add POS Account
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-black text-slate-400 uppercase border-b border-slate-100">
                        <th class="px-5 py-3 text-center w-12">#</th>
                        <th class="px-4 py-3">Name / Username</th>
                        <th class="px-4 py-3">Creation Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="(account, index) in filteredAccounts" :key="account.id" class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-5 py-4 text-center text-slate-300 font-black">{{ index + 1 }}</td>
                        <td class="px-4 py-4">
                          <p class="font-black text-slate-700">{{ account.name }}</p>
                          <p class="text-xs text-slate-400 font-medium">{{ account.username }}</p>
                        </td>
                        <td class="px-4 py-4 text-slate-500 font-bold text-xs">{{ account.creationDate }}</td>
                        <td class="px-4 py-4">
                            <span
                                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                                :class="account.status === 'enabled' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500'"
                            >
                                {{ account.status }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right relative">
                            <button @click.stop="activeDropdown = activeDropdown === account.id ? null : account.id"
                                class="h-8 w-8 rounded-lg grid place-items-center hover:bg-slate-100 ml-auto transition border border-transparent group-hover:border-slate-100">
                                <MoreVertical class="w-4 h-4 text-slate-400" />
                            </button>

                            <div v-if="activeDropdown === account.id"
                                 class="absolute right-4 top-10 z-30 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 text-left animate-in fade-in slide-in-from-top-2 duration-200"
                                 @click.stop>
                                <button @click="openEditAccount(account)" class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                    <Edit class="w-4 h-4 text-slate-400" /> Edit Account
                                </button>
                                <button @click="toggleAccountStatus(account.id)" class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 transition">
                                    <Power class="w-4 h-4 text-slate-400" /> {{ account.status === 'enabled' ? 'Disable' : 'Enable' }}
                                </button>
                                <div class="h-px bg-slate-50 my-1"></div>
                                <button @click="deleteAccount(account.id)" class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-rose-50 text-rose-600 transition">
                                    <Trash2 class="w-4 h-4" /> Delete Account
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!filteredAccounts.length">
                        <td colspan="5" class="px-5 py-16 text-center text-slate-400 font-bold">
                           <div class="h-16 w-16 bg-slate-50 rounded-full grid place-items-center mx-auto mb-4">
                             <Users class="w-8 h-8 text-slate-200" />
                           </div>
                           No POS accounts found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>
    </div>

    <!-- Receipt Modal (Sync with line 4752 Reference) -->
    <div v-if="showReceiptModal" class="fixed inset-0 bg-slate-900/45 backdrop-blur-sm z-[10000] flex items-center justify-center p-4">
       <div class="bg-white rounded-[20px] w-full max-w-lg overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
         <div class="p-6 text-center">
            <div class="h-14 w-14 rounded-full bg-emerald-50 text-emerald-500 grid place-items-center mx-auto mb-3">
               <Check class="w-7 h-7" />
            </div>
            <p class="font-black text-lg text-slate-950 mb-1">Sale Complete</p>
            <p class="text-slate-400 text-sm mb-1">{{ lastOrderId }} · {{ lastTime }} · {{ lastMethod }}</p>

            <p class="text-slate-400 text-sm mb-4">{{ lastBuyer }}<template v-if="lastBuyerEmail"> · {{ lastBuyerEmail }}</template></p>
            <div class="rounded-2xl border border-slate-100 p-4 text-left mb-4">
              <div v-for="l in lastReceiptLines" :key="l.id" class="flex justify-between text-sm mb-3">
                <span class="text-slate-600 font-medium">{{ l.eventTitle }} ×{{ l.qty }}</span>
                <b class="text-slate-800">{{ formatCurrency(l.price * l.qty) }}</b>
              </div>
              <div class="border-t border-slate-100 mt-2 pt-2 space-y-0 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><b>{{ formatCurrency(lastFees.subtotal) }}</b></div>
                <div class="flex justify-between"><span class="text-slate-500">Platform fee</span><b>{{ formatCurrency(lastFees.platform) }}</b></div>
                <div v-if="lastFees.processing" class="flex justify-between"><span class="text-slate-500">Processing fee</span><b>{{ formatCurrency(lastFees.processing) }}</b></div>
                <div v-if="lastFees.tax" class="flex justify-between"><span class="text-slate-500">Tax</span><b>{{ formatCurrency(lastFees.tax) }}</b></div>
              </div>
              <div class="border-t border-slate-100 mt-1 pt-2 flex justify-between font-black text-slate-900">
                <span>Total</span>
                <span>{{ formatCurrency(lastTotal) }}</span>
              </div>
            </div>

            <p class="text-[11px] text-slate-400 mb-2">Fee shown for reference — settled with LinkUp at your next payout, not deducted now.</p>
            <p v-if="lastBuyerEmail" class="text-emerald-600 text-xs font-bold mb-4">E-ticket emailed to {{ lastBuyerEmail }}</p>
            <p v-else class="text-slate-400 text-xs mb-4">No email entered — print the ticket instead.</p>
            <div class="flex gap-2">
              <button @click="printReceipt" class="btn btn-ghost flex-1 py-3 font-black">Print Ticket</button>
              <button @click="showReceiptModal = false" class="btn btn-primary flex-1 py-3 font-black">New Sale</button>
            </div>
         </div>
       </div>
    </div>

    <!-- Add Account Modal -->
    <div v-if="showAddAccountModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4" @click.self="showAddAccountModal = false">
      <div class="bg-white rounded-[24px] w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-6 flex items-center justify-between border-b border-slate-50">
          <h3 class="text-xl font-black text-slate-800">Add POS Account</h3>
          <button @click="showAddAccountModal = false" class="h-8 w-8 rounded-full hover:bg-slate-100 grid place-items-center transition"><X class="w-4 h-4 text-slate-400" /></button>
        </div>
        <form @submit.prevent="submitAddAccount" class="p-6 space-y-4">
          <div class="space-y-1">
            <label class="text-[11px] font-black text-slate-400 uppercase ml-1">Staff Name</label>
            <input v-model="addAccountForm.name" required placeholder="Full name" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition font-bold" />
          </div>
          <div class="space-y-1">
            <label class="text-[11px] font-black text-slate-400 uppercase ml-1">Username / Email</label>
            <input v-model="addAccountForm.username" required placeholder="staff.name@linkup.com" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition font-bold" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="text-[11px] font-black text-slate-400 uppercase ml-1">Password</label>
              <input v-model="addAccountForm.password" type="password" required placeholder="••••••••" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition" />
            </div>
            <div class="space-y-1">
              <label class="text-[11px] font-black text-slate-400 uppercase ml-1">Confirm</label>
              <input v-model="addAccountForm.password_confirmation" type="password" required placeholder="••••••••" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition" />
            </div>
          </div>
          <button type="submit" class="w-full py-4 rounded-2xl font-black text-white transition hover:brightness-105 active:scale-[0.98] shadow-lg shadow-indigo-500/20 mt-2" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
            Create POS Account
          </button>
        </form>
      </div>
    </div>

    <!-- Edit Account Modal -->
    <div v-if="showEditAccountModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4" @click.self="showEditAccountModal = false">
      <div class="bg-white rounded-[24px] w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-6 flex items-center justify-between border-b border-slate-50">
          <h3 class="text-xl font-black text-slate-800">Edit Account</h3>
          <button @click="showEditAccountModal = false" class="h-8 w-8 rounded-full hover:bg-slate-100 grid place-items-center transition"><X class="w-4 h-4 text-slate-400" /></button>
        </div>
        <form @submit.prevent="submitEditAccount" class="p-6 space-y-4">
          <div class="space-y-1">
            <label class="text-[11px] font-black text-slate-400 uppercase ml-1">Staff Name</label>
            <input v-model="editAccountForm.name" required placeholder="Full name" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition font-bold" />
          </div>
          <div class="space-y-1">
            <label class="text-[11px] font-black text-slate-400 uppercase ml-1">Username / Email</label>
            <input v-model="editAccountForm.username" required placeholder="staff.name@linkup.com" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition font-bold" />
          </div>
          <div class="bg-blue-50 text-blue-600 p-3 rounded-xl text-[10px] font-bold flex gap-2 items-start">
             <span class="bg-blue-600 text-white rounded-full h-4 w-4 grid place-items-center shrink-0">i</span>
             Leave password fields empty to keep the existing password.
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="text-[11px] font-black text-slate-400 uppercase ml-1">New Password</label>
              <input v-model="editAccountForm.password" type="password" placeholder="••••••••" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition" />
            </div>
            <div class="space-y-1">
              <label class="text-[11px] font-black text-slate-400 uppercase ml-1">Confirm</label>
              <input v-model="editAccountForm.password_confirmation" type="password" placeholder="••••••••" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition" />
            </div>
          </div>
          <button type="submit" class="w-full py-4 rounded-2xl font-black text-white transition hover:brightness-105 active:scale-[0.98] shadow-lg shadow-indigo-500/20 mt-2" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
            Save Changes
          </button>
        </form>
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.zoom-in-95 {
    animation-name: zoomIn95;
}
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes zoomIn95 {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}
</style>
