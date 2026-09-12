<template>
  <div class="fade pb-20">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-black">Wallet</h1>
        <p class="text-slate-500 font-bold">Pay, get paid, send, save & top up</p>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2 space-y-5">

        <!-- Main Wallet Card -->
        <div class="rounded-3xl bg-slate-900 text-white p-6 relative overflow-hidden">
          <div class="absolute -right-10 -top-10 opacity-10 blur-xl">
            <span class="w-64 h-64 bg-lkblue rounded-full block"></span>
          </div>
          <p class="text-white/60 text-xs font-black tracking-wide flex items-center gap-2 relative z-10"><i data-lucide="wallet" class="w-4 h-4"></i>LINK UP DIGITAL WALLET</p>
          <p class="text-4xl font-black mt-2 relative z-10">{{ money(WALLET.balance) }}</p>
          <div class="flex flex-wrap gap-2 mt-5 relative z-10">
            <button @click="showScanPay = true" class="btn bg-gradient-to-r from-teal-400 to-cyan-500 text-white px-4 py-2.5 flex items-center gap-2"><i data-lucide="scan-line" class="w-4 h-4"></i>Scan to Pay</button>
            <button @click="showMyQR = true" class="btn bg-white/15 hover:bg-white/25 text-white px-4 py-2.5 flex items-center gap-2 transition"><i data-lucide="qr-code" class="w-4 h-4"></i>My QR</button>
            <button @click="openTopUp('cash')" class="btn btn-primary px-4 py-2.5 flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i>Top Up</button>
            <button @click="showTransfer = true" class="btn bg-white/15 hover:bg-white/25 text-white px-4 py-2.5 flex items-center gap-2 transition"><i data-lucide="repeat" class="w-4 h-4"></i>Transfer</button>
          </div>
        </div>

        <!-- Quick Link Ups -->
        <div class="card p-4">
          <h3 class="font-black mb-3">Quick Link Ups</h3>
          <div class="flex gap-3 pb-2">
            <button @click="openAddContact" class="shrink-0 text-center hover:opacity-80 transition">
              <div class="h-12 w-12 rounded-full border-2 border-dashed border-slate-300 grid place-items-center text-slate-400 mx-auto">
                <i data-lucide="plus" class="w-5 h-5"></i>
              </div>
              <p class="text-[10px] font-bold mt-1">New</p>
            </button>
            <div v-for="c in cs" :key="c.contactId || c.id" class="relative shrink-0 w-20 text-center group">
              <button @click="openSend(c.tag)" class="w-full hover:opacity-80 transition">
              <img :src="c.img" class="h-12 w-12 rounded-full object-cover mx-auto"/>
              <p class="text-[10px] font-bold mt-1 w-20 truncate">{{ c.name }}</p>
              <p class="text-[10px] font-light w-20 truncate">{{ c.tag }}</p>
              </button>
              <button @click.stop="removeQuickPayContact(c)" class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-slate-700 text-white grid place-items-center opacity-0 group-hover:opacity-100 focus:opacity-100 transition" title="Remove contact" :aria-label="`Remove ${c.name}`">
                <i data-lucide="x" class="w-3 h-3"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Action Grid -->
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
          <button v-for="a in actions" :key="a.name" @click="a.fn()" class="card p-4 flex flex-col items-center gap-2 hover:-translate-y-0.5 transition shadow-sm hover:shadow-md">
            <span class="w-11 h-11 rounded-xl grid place-items-center text-white" :style="{ background: a.color }">
              <i :data-lucide="a.icon" class="w-5 h-5"></i>
            </span>
            <span class="text-[11px] font-black !text-slate-600 text-center leading-tight">{{ a.name }}</span>
          </button>
        </div>

        <!-- LinkUp Save -->
        <div class="rounded-3xl p-5 text-white relative overflow-hidden shadow-lg" style="background:linear-gradient(135deg,#059669,#14b8a6)">
          <div class="absolute right-0 bottom-0 opacity-10">
            <i data-lucide="piggy-bank" class="w-48 h-48 -mr-10 -mb-10"></i>
          </div>
          <div class="relative z-10">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <span class="font-black flex items-center gap-2"><i data-lucide="piggy-bank" class="w-5 h-5"></i> LinkUp Save</span>
              <div class="flex items-center gap-2">
                <button @click="showSaveHow = true" class="h-7 w-7 rounded-full bg-white/20 hover:bg-white/30 grid place-items-center font-black transition">?</button>
                <span class="rounded-full bg-white/20 px-2.5 py-1 text-[10px] font-black">Powered by Scotiabank</span>
              </div>
            </div>
            <p class="text-emerald-50 text-xs mt-4">Available Savings Balance</p>
            <h3 class="text-3xl font-black drop-shadow-md">{{ money(saveData.balance) }}</h3>
            <div class="flex flex-wrap gap-4 mt-2 text-xs font-bold bg-black/10 rounded-xl px-3 py-2 inline-flex">
              <span>Tier <b class="text-emerald-100">{{ saveTierInfo.name }}</b></span>
              <span>APY <b class="text-emerald-100">{{ saveTierInfo.apy.toFixed(1) }}%</b></span>
              <span>Earned this month <b class="text-emerald-100">{{ money(saveData.earnedMonth) }}</b></span>
            </div>

            <div v-if="saveNextTier" class="mt-4">
              <div class="flex justify-between text-[10px] font-bold text-emerald-50 mb-1">
                <span>{{ saveTierInfo.name }}</span>
                <span>{{ saveNextTier.name }} at {{ money(saveNextTier.min) }}</span>
              </div>
              <div class="h-1.5 rounded-full bg-white/25 overflow-hidden">
                <div class="h-1.5 rounded-full bg-white transition-all duration-1000" :style="{ width: Math.min(100, Math.round(saveData.balance / saveNextTier.min * 100)) + '%' }"></div>
              </div>
            </div>
            <p v-else class="text-[10px] text-emerald-50 mt-3 font-bold">🏆 Top tier — Elite APY.</p>

            <div class="flex gap-2 mt-5">
              <button @click="showSaveDeposit = true" class="flex-1 rounded-2xl bg-white text-emerald-700 hover:bg-emerald-50 py-2.5 font-black text-sm transition">Deposit</button>
              <button @click="showSaveWithdraw = true" class="flex-1 rounded-2xl bg-white/20 hover:bg-white/30 py-2.5 font-black text-sm transition">Withdraw</button>
              <button @click="showSaveHistory = true" class="rounded-2xl bg-white/20 hover:bg-white/30 px-3 py-2.5 font-black transition"><i data-lucide="history" class="w-4 h-4"></i></button>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between bg-white/15 rounded-2xl px-3 py-2">
              <div class="text-xs">
                <p class="font-black">Auto-Save</p>
                <p class="text-emerald-50 text-[10px] font-bold">Sweep {{ saveData.autoSavePct }}% of each payout into savings</p>
              </div>
              <button @click="showSaveAuto = true" class="rounded-full bg-white/20 hover:bg-white/30 px-3 py-1 text-xs font-black transition">Edit</button>
            </div>
          </div>
        </div>

        <LinkUpCoinsCard :coins="WALLET.coins" @buy-more="openTopUp('coins')" />

      </div>

      <!-- Right Column: Activity -->
      <div class="self-start sticky top-20 space-y-5">
        <div class="card p-5 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-black text-lg">Recent activity</h3>
            <button @click="openActivity" class="text-lkblue2 text-sm font-bold hover:underline">See all</button>
          </div>
          <div class="divide-y divide-slate-100">
            <div v-if="!txns.length" class="text-center text-slate-400 py-6 font-bold text-sm">No activity yet.</div>
            <div v-for="(t, i) in txns.slice(0,8)" :key="i" class="flex items-center justify-between py-3">
              <div class="min-w-0 pr-3">
                <p class="font-bold text-sm truncate">{{ t.title }}</p>
                <p class="text-xs text-slate-400">{{ t.dateLabel }}</p>
              </div>
              <span :class="['font-black shrink-0', t.isPositive ? 'text-green-600' : 'text-slate-700']">
                {{ t.isPositive ? '+' : '-' }}{{ money(t.amount) }}
              </span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ================== MODALS ================== -->
    <!-- (Using standard fixed overlay pattern from prototype) -->

    <!-- Scan to Pay -->
    <div v-if="showScanPay" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showScanPay = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-5 text-center shadow-2xl">
        <div class="flex items-center justify-between mb-2"><h3 class="text-xl font-black">Scan to Pay</h3><button @click="showScanPay = false" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-5 h-5"></i></button></div>
        <div class="rounded-2xl bg-slate-900 text-white/70 aspect-square grid place-items-center mb-3"><i data-lucide="scan-line" class="w-16 h-16"></i></div>
        <p class="text-sm text-slate-500">Point your camera at a LinkUp QR to pay.</p>
        <button @click="showScanPay = false; showMerchant = true" class="btn btn-primary w-full mt-4 py-3 shadow">Enter amount manually</button>
      </div>
    </div>

    <!-- My QR -->
    <div v-if="showMyQR" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showMyQR = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-5 text-center shadow-2xl">
        <div class="flex items-center justify-between mb-2"><h3 class="text-xl font-black">My QR</h3><button @click="showMyQR = false" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-5 h-5"></i></button></div>
        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=LinkUp-' + USER.handle" class="mx-auto rounded-2xl border border-slate-100 shadow-sm mb-3 w-56 h-56"/>
        <p class="font-black text-lg">{{ USER.name }}</p>
        <p class="text-sm text-slate-500 font-bold">{{ USER.handle }}</p>
        <p class="text-[12px] text-slate-400 mt-2">Show this to get paid instantly.</p>
      </div>
    </div>

    <!-- Top Up -->
    <WalletTopUpModal ref="topUpModalRef" :coin-packs="coinPacks" />

    <!-- Transfer Menu -->
    <div v-if="showTransfer" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showTransfer = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-5 shadow-2xl">
        <div class="flex items-center justify-between mb-4"><h3 class="text-xl font-black">Transfer</h3><button @click="showTransfer = false" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-5 h-5"></i></button></div>
        <div class="grid grid-cols-3 gap-3 text-center text-xs font-black">
          <button @click="showTransfer = false; showTransferBank = true" class="rounded-2xl border-2 border-slate-100 py-4 hover:border-lkblue hover:bg-blue-50 transition"><i data-lucide="building-2" class="w-6 h-6 mx-auto mb-2 text-lkblue"></i><p>To Bank</p></button>
          <button @click="showTransfer = false; showSaveDeposit = true" class="rounded-2xl border-2 border-slate-100 py-4 hover:border-emerald-400 hover:bg-emerald-50 transition"><i data-lucide="piggy-bank" class="w-6 h-6 mx-auto mb-2 text-emerald-500"></i><p>To Save</p></button>
          <button @click="showTransfer = false; openTopUp('coins')" class="rounded-2xl border-2 border-slate-100 py-4 hover:border-amber-400 hover:bg-amber-50 transition"><i data-lucide="gem" class="w-6 h-6 mx-auto mb-2 text-amber-500"></i><p>To Coins</p></button>
        </div>
      </div>
    </div>

    <!-- Transfer to Bank -->
    <div v-if="showTransferBank" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showTransferBank = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-5 shadow-2xl">
        <div class="flex items-center justify-between mb-4"><h3 class="text-xl font-black">Transfer to Bank</h3><button @click="showTransferBank = false" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-5 h-5"></i></button></div>
        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 mb-3 border border-slate-100"><span class="font-black text-slate-500">Available</span><span class="font-black text-lg">{{ money(WALLET.balance) }}</span></div>
        <input v-model="tbAmt" type="number" placeholder="Amount (USD)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-black text-lg outline-none focus:border-lkblue transition-colors mb-2"/>
        <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none bg-white focus:border-lkblue transition-colors mb-3">
          <option>Scotiabank ••4471</option>
          <option>RBC ••8830</option>
        </select>
        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-3 text-amber-800 mb-3">
          <p class="font-bold text-sm">5% bank processing fee applies.</p>
          <p class="text-[12px] mt-1 font-bold opacity-80">You withdraw {{ money(tbAmt || 0) }} • Fee {{ money((tbAmt || 0) * 0.05) }} • You receive {{ money((tbAmt || 0) * 0.95) }}</p>
        </div>
        <button @click="doWithdraw" class="btn w-full py-3.5 text-white shadow" style="background:#0b2942">Confirm Withdrawal</button>
      </div>
    </div>

    <SendMoneyModal
      ref="sendMoneyModalRef"
      :balance="WALLET.balance"
      :contacts="cs"
      :users="quickPayUsers"
      @money-sent="handleMoneySent"
    />

    <!-- Request Money -->
    <RequestMoneyModal
      ref="requestMoneyModalRef"
      :contacts="cs"
      :users="quickPayUsers"
      @money-requested="handleMoneyRequested"
    />

    <!-- Requested Money -->
    <RequestedMoneyModal
      ref="requestedMoneyModalRef"
      :requests="walletMoneyRequests"
      @request-paid="handleRequestedMoneyPaid"
      @request-updated="handleRequestedMoneyUpdated"
    />

    <!-- Digital ASUE -->
    <div v-if="showAsue" class="fixed inset-0 z-[130] bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" @click.self="showAsue = false">
      <AsueDetailModal
        v-if="props.activeAsue"
        :asue="props.activeAsue"
        :current-user="currentUserForAsue"
        @close="showAsue = false"
        @refresh="refreshAsue"
      />
      <AsueModal
        v-else
        :users="props.users"
        @close="showAsue = false"
      />
    </div>

    <!-- Pay Bills -->
    <div v-if="showPayBills" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showPayBills = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
        <div v-if="!billToPay" class="fade">
          <div class="p-5 flex items-center justify-between"><h3 class="text-xl font-black">Pay Bills</h3><button @click="showPayBills = false" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-5 h-5"></i></button></div>
          <div class="p-4 pt-0 space-y-3 max-h-[70vh] overflow-y-auto">
            <div v-for="b in BILLERS" :key="b.id" class="flex items-center gap-3 border border-slate-100 rounded-2xl p-3 shadow-sm hover:border-sky-200 transition">
              <span class="h-10 w-10 rounded-full bg-sky-50 grid place-items-center text-sky-500 shrink-0"><i :data-lucide="b.icon" class="w-5 h-5"></i></span>
              <div class="flex-1 min-w-0">
                <p class="font-black text-sm">{{ b.name }}</p>
                <p :class="['text-[11px] font-bold mt-0.5', billPaid[b.id] ? 'text-emerald-600' : 'text-slate-400']">{{ billPaid[b.id] ? '✅ Paid ' + money(billPaid[b.id]) : b.due }}</p>
              </div>
              <span v-if="billPaid[b.id]" class="rounded-full bg-emerald-50 text-emerald-600 px-3 py-1.5 text-xs font-black shrink-0">✓ Paid</span>
              <button v-else @click="billToPay = b" class="btn bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 text-xs shadow-sm transition">Pay</button>
            </div>
          </div>
        </div>
        <div v-else class="fade p-5">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-black flex items-center gap-2">
              <button @click="billToPay = null" class="mr-1 hover:bg-slate-100 p-1 rounded"><i data-lucide="arrow-left" class="w-4 h-4"></i></button>
              {{ billToPay.name }}
            </h3>
            <button @click="showPayBills = false" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-5 h-5"></i></button>
          </div>
          <input v-model="billAmt" type="number" placeholder="Amount (USD)" class="w-full rounded-2xl border border-slate-200 px-4 py-4 font-black text-xl outline-none focus:border-lkblue transition-colors"/>
          <button @click="payBill" class="btn btn-primary w-full mt-4 py-3.5 shadow">Pay {{ billToPay.name }}</button>
        </div>
      </div>
    </div>

    <!-- Merchant Pay -->
    <div v-if="showMerchant" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showMerchant = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-5 shadow-2xl">
        <div class="flex items-center justify-between mb-4"><h3 class="text-xl font-black">Merchant Pay</h3><button @click="showMerchant = false" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-5 h-5"></i></button></div>
        <div class="rounded-2xl bg-slate-900 text-white/70 p-6 text-center mb-4">
          <i data-lucide="scan-line" class="w-12 h-12 mx-auto text-lkblue"></i><p class="font-bold text-sm mt-3">Scan merchant QR to pay</p>
        </div>
        <input v-model="merchName" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mb-3 font-bold outline-none focus:border-lkblue transition-colors"/>
        <input v-model="merchAmt" type="number" placeholder="Amount (USD)" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-black text-lg outline-none focus:border-lkblue transition-colors"/>
        <button @click="payMerchant" class="btn btn-primary w-full mt-4 py-3.5 shadow hover:shadow-md transition">Pay Merchant</button>
      </div>
    </div>

    <!-- Cards -->
    <div v-if="showCards" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showCards = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-5 shadow-2xl">
        <div class="flex items-center justify-between mb-5"><h3 class="text-xl font-black">My Cards</h3><button @click="showCards = false" class="hover:bg-slate-100 p-1 rounded"><i data-lucide="x" class="w-5 h-5"></i></button></div>
        <div class="rounded-3xl p-6 text-white shadow-lg relative overflow-hidden" style="background:linear-gradient(135deg,#0b2942,#2f9bef)">
          <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
          <div class="flex justify-between items-start relative z-10">
            <span class="logo text-2xl drop-shadow-md">Link<span class="text-lkyellow">üp</span></span>
            <i data-lucide="wifi" class="w-5 h-5 opacity-80"></i>
          </div>
          <p class="tracking-[0.2em] text-xl mt-8 font-mono relative z-10">•••• •••• •••• {{ cardInfo.last4 || '4471' }}</p>
          <div class="flex justify-between mt-4 text-sm font-bold relative z-10">
            <span class="uppercase tracking-wide">{{ USER.name }}</span>
            <span>{{ cardInfo.exp || '08/28' }}</span>
          </div>
        </div>
        <button @click="toast('❄ Card frozen')" class="btn btn-ghost w-full mt-5 py-3 hover:bg-slate-50 transition">Freeze card</button>
        <button @click="toast('➕ Virtual card created')" class="btn btn-primary w-full mt-3 py-3 shadow transition">Create virtual card</button>
      </div>
    </div>

    <WalletActivityModal
      ref="activityModalRef"
      :activities="txns"
      :money-requests="props.moneyRequests"
      :subscriptions="props.subscriptions"
      :bank-withdrawals="props.bankWithdrawals"
    />
    <QuickPayContactModal
      ref="quickPayContactModalRef"
      :users="quickPayUsers"
      :contacts="cs"
      @contact-added="addQuickPayContact"
    />

    <!-- LinkUp Save: Deposit -->
    <div v-if="showSaveDeposit" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showSaveDeposit = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden">
        <div class="p-5 text-white" style="background:linear-gradient(135deg,#059669,#14b8a6)">
          <div class="flex items-center justify-between"><h3 class="text-xl font-black">Deposit to Savings</h3><button @click="showSaveDeposit = false" class="hover:bg-white/20 p-1 rounded transition"><i data-lucide="x" class="w-5 h-5"></i></button></div>
          <p class="text-emerald-50 text-xs mt-1 font-bold">Wallet available: {{ money(WALLET.balance) }} · min {{ money(savingsConfig.topUpMin) }}</p>
        </div>
        <div class="p-5 space-y-4">
          <div class="grid grid-cols-3 gap-2">
            <button @click="depAmt = 100" class="rounded-xl border-2 border-emerald-100 hover:bg-emerald-50 py-2.5 font-black text-emerald-700 text-sm transition">$100</button>
            <button @click="depAmt = 500" class="rounded-xl border-2 border-emerald-100 hover:bg-emerald-50 py-2.5 font-black text-emerald-700 text-sm transition">$500</button>
            <button @click="depAmt = 1000" class="rounded-xl border-2 border-emerald-100 hover:bg-emerald-50 py-2.5 font-black text-emerald-700 text-sm transition">$1000</button>
          </div>
          <input v-model="depAmt" type="number" placeholder="Amount (USD)" class="w-full rounded-2xl border border-slate-200 px-4 py-4 font-black text-xl outline-none focus:border-emerald-500 transition-colors"/>
          <div class="rounded-2xl bg-emerald-50 text-emerald-800 text-xs font-bold p-3 border border-emerald-100 shadow-sm">Moves Wallet → LinkUp Save. Interest accrues daily, posts monthly.</div>
          <button @click="applyDeposit" class="btn w-full py-3.5 text-white shadow hover:shadow-md transition" style="background:linear-gradient(135deg,#059669,#14b8a6)">Deposit</button>
        </div>
      </div>
    </div>

    <!-- LinkUp Save: Withdraw -->
    <div v-if="showSaveWithdraw" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showSaveWithdraw = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden">
        <div class="p-5 text-white" style="background:linear-gradient(135deg,#059669,#14b8a6)">
          <div class="flex items-center justify-between"><h3 class="text-xl font-black">Withdraw Savings</h3><button @click="showSaveWithdraw = false" class="hover:bg-white/20 p-1 rounded transition"><i data-lucide="x" class="w-5 h-5"></i></button></div>
          <p class="text-emerald-50 text-xs mt-1 font-bold">Savings balance: {{ money(saveData.balance) }}</p>
        </div>
        <div class="p-5 space-y-4">
          <input v-model="wdAmt" type="number" placeholder="Amount (USD)" class="w-full rounded-2xl border border-slate-200 px-4 py-4 font-black text-xl outline-none focus:border-emerald-500 transition-colors"/>
          <div class="rounded-2xl bg-amber-50 text-amber-800 text-xs font-bold p-4 border border-amber-200 shadow-sm">
            ⚠️ Funds locked {{ saveTierInfo.lock || savingsConfig.lockDays }} days. Early withdrawal incurs {{ savingsConfig.earlyPenalty }}% penalty.
          </div>
          <button @click="applyWithdraw" class="btn w-full py-3.5 text-white shadow hover:shadow-md transition" style="background:linear-gradient(135deg,#059669,#14b8a6)">Withdraw to Wallet</button>
        </div>
      </div>
    </div>

    <!-- LinkUp Save: Auto-Save -->
    <div v-if="showSaveAuto" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showSaveAuto = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden">
        <div class="p-5 text-white" style="background:linear-gradient(135deg,#059669,#14b8a6)">
          <div class="flex items-center justify-between"><h3 class="text-xl font-black">Auto-Save</h3><button @click="showSaveAuto = false" class="hover:bg-white/20 p-1 rounded transition"><i data-lucide="x" class="w-5 h-5"></i></button></div>
          <p class="text-emerald-50 text-xs mt-1 font-bold">Sweep a % of each payout into savings.</p>
        </div>
        <div class="p-5 space-y-4">
          <div class="grid grid-cols-4 gap-2">
            <button @click="asPct = 0" class="rounded-xl border-2 border-emerald-100 hover:bg-emerald-50 py-2.5 font-black text-emerald-700 text-sm transition">0%</button>
            <button @click="asPct = 5" class="rounded-xl border-2 border-emerald-100 hover:bg-emerald-50 py-2.5 font-black text-emerald-700 text-sm transition">5%</button>
            <button @click="asPct = 10" class="rounded-xl border-2 border-emerald-100 hover:bg-emerald-50 py-2.5 font-black text-emerald-700 text-sm transition">10%</button>
            <button @click="asPct = 25" class="rounded-xl border-2 border-emerald-100 hover:bg-emerald-50 py-2.5 font-black text-emerald-700 text-sm transition">25%</button>
          </div>
          <input v-model="asPct" type="number" class="w-full rounded-2xl border border-slate-200 px-4 py-4 font-black text-2xl outline-none focus:border-emerald-500 transition-colors text-center"/>
          <button @click="applyAuto" class="btn w-full py-3.5 text-white shadow hover:shadow-md transition" style="background:linear-gradient(135deg,#059669,#14b8a6)">Save Rule</button>
        </div>
      </div>
    </div>

    <!-- LinkUp Save: History -->
    <div v-if="showSaveHistory" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showSaveHistory = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-5 text-white" style="background:linear-gradient(135deg,#059669,#14b8a6)">
          <div class="flex items-center justify-between"><h3 class="text-xl font-black">Savings History</h3><button @click="showSaveHistory = false" class="hover:bg-white/20 p-1 rounded transition"><i data-lucide="x" class="w-5 h-5"></i></button></div>
        </div>
        <div class="p-4 space-y-2 max-h-[70vh] overflow-y-auto bg-slate-50/50">
          <div v-for="(t, i) in saveHistory" :key="i" class="flex items-center justify-between bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:border-slate-200 transition">
            <div class="pr-3">
              <p class="font-black text-sm">{{ t[0] }}</p>
              <p class="text-[11px] text-slate-400 font-bold mt-0.5">{{ t[2] }}</p>
            </div>
            <span :class="['font-black text-lg shrink-0', t[1] < 0 ? 'text-rose-600' : 'text-emerald-600']">{{ t[1] < 0 ? '−' : '+' }}{{ money(Math.abs(t[1])) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- LinkUp Save: How it Works -->
    <div v-if="showSaveHow" class="fixed inset-0 z-[120] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="showSaveHow = false">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-5 text-white" style="background:linear-gradient(135deg,#059669,#14b8a6)">
          <div class="flex items-center justify-between"><h3 class="text-xl font-black">How LinkUp Save works</h3><button @click="showSaveHow = false" class="hover:bg-white/20 p-1 rounded transition"><i data-lucide="x" class="w-5 h-5"></i></button></div>
          <p class="text-emerald-50 text-xs mt-1 font-bold">Powered by Scotiabank</p>
        </div>
        <div class="p-5 space-y-4">
          <div class="space-y-2">
            <div v-for="t in savingsConfig.tiers" :key="t.name" class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 border border-emerald-100">
              <span class="font-black text-sm text-emerald-800">{{ t.name }} <span class="text-emerald-600 ml-1">{{ t.apy.toFixed(2) }}% APY</span></span>
              <span class="text-[11px] font-bold text-emerald-700/60">{{ money(t.min) }}+ · {{ t.lock }}-day lock</span>
            </div>
          </div>
          <ul class="space-y-3 text-sm text-slate-600 font-bold mt-4">
            <li class="flex gap-3"><span class="text-emerald-600">●</span>Sweep idle wallet balance into savings.</li>
            <li class="flex gap-3"><span class="text-emerald-600">●</span>Scotiabank custodies deposits & pays the wholesale rate.</li>
            <li class="flex gap-3"><span class="text-emerald-600">●</span>Interest accrues daily, posts monthly.</li>
            <li class="flex gap-3"><span class="text-emerald-600">●</span>Opening min {{ money(savingsConfig.minBalance) }} · Top-up min {{ money(savingsConfig.topUpMin) }}.</li>
            <li class="flex gap-3"><span class="text-emerald-600">●</span>Early withdrawal penalty {{ savingsConfig.earlyPenalty }}% · KYC-verified only.</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, reactive, nextTick, onMounted, watch, onUpdated } from 'vue';
import axios from 'axios';
import { router, usePage } from '@inertiajs/vue3';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import { DB, AV, getUser } from '../../../components/new_frontend/MockDataStore';
import WalletTopUpModal from './TopUpModal.vue';
import QuickPayContactModal from './QuickPayContactModal.vue';
import SendMoneyModal from './SendMoneyModal.vue';
import RequestMoneyModal from './RequestMoneyModal.vue';
import RequestedMoneyModal from './RequestedMoneyModal.vue';
import WalletActivityModal from './WalletActivityModal.vue';
import LinkUpCoinsCard from './LinkUpCoinsCard.vue';
import AsueModal from '../../User/UserWallet/components/AsueModal.vue';
import AsueDetailModal from '../../User/UserWallet/components/AsueDetailModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  wallet: { type: Object, default: () => ({ balance: 0, coins: 0 }) },
  contacts: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  moneyRequests: { type: Array, default: () => [] },
  activity: { type: Array, default: () => [] },
  subscriptions: { type: Array, default: () => [] },
  bankWithdrawals: { type: Array, default: () => [] },
  activeAsue: { type: Object, default: null },
});

// --- UTILS ---
const money = n => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const USER = getUser();
const page = usePage();
const currentUserForAsue = computed(() => {
  const user = page.props.auth?.user || {};

  return {
    id: user.id,
    linkup_id: user.linkup_id,
    name: user.name,
    tag: user.tag,
  };
});

// --- DATA CONSTANTS ---
const MY_CCY = 'USD';
const CCY = [
  {code:'USD',sym:'$',mode:'pegged',rate:1,bps:0},
  {code:'BSD',sym:'B$',mode:'pegged',rate:1,bps:0},
  {code:'XCD',sym:'EC$',mode:'pegged',rate:2.70,bps:0},
  {code:'BBD',sym:'Bds$',mode:'pegged',rate:2.00,bps:0},
  {code:'TTD',sym:'TT$',mode:'floating',rate:6.78,bps:150},
  {code:'JMD',sym:'J$',mode:'floating',rate:157,bps:150},
  {code:'COP',sym:'COL$',mode:'floating',rate:4000,bps:200}
];
const BILLERS = [
  {id:'bpl', name:'BPL Electricity', due:'Due Jun 18', icon:'zap'},
  {id:'cable', name:'Cable & Internet', due:'Due Jun 22', icon:'wifi'},
  {id:'water', name:'Water & Sewerage', due:'Due Jun 25', icon:'droplet'},
  {id:'btc', name:'BTC Mobile', due:'Due Jun 28', icon:'smartphone'}
];
const savingsConfig = {minBalance:1000, topUpMin:100, earlyPenalty:1.5, lockDays:90, tiers:[{name:'Standard',min:1000,apy:3.0,lock:30},{name:'Premium',min:5000,apy:4.0,lock:90},{name:'Elite',min:25000,apy:4.5,lock:180}]};

// --- STATE ---
const WALLET = reactive({
  balance: Number(props.wallet?.balance ?? 0),
  coins: Number(props.wallet?.coins ?? 0),
});
const normalizeActivity = (item, index = 0) => ({
  id: item.id || 'activity-' + index,
  title: item.title || 'Wallet activity',
  amount: Math.abs(Number(item.amount || 0)),
  isPositive: Boolean(item.isPositive),
  status: item.status || 'success',
  type: item.type || 'wallet',
  counterparty: item.counterparty || '',
  note: item.note || '',
  runningBalance: item.runningBalance ?? null,
  date: item.date || null,
  dateLabel: item.dateLabel || 'Today',
});

const txns = ref([]);
const hydrateActivity = () => {
  txns.value = props.activity.map(normalizeActivity);
};

hydrateActivity();

watch(() => props.activity, hydrateActivity, { deep: true });
const countryCurrency = (country) => {
  const key = String(country || '').trim().toLowerCase();

  return ({
    bs: 'BSD',
    bahamas: 'BSD',
    bb: 'BBD',
    barbados: 'BBD',
    jm: 'JMD',
    jamaica: 'JMD',
    lc: 'XCD',
    'st. lucia': 'XCD',
    'saint lucia': 'XCD',
    tt: 'TTD',
    'trinidad & tobago': 'TTD',
    'trinidad and tobago': 'TTD',
    co: 'COP',
    colombia: 'COP',
    do: 'USD',
    'dominican republic': 'USD',
    us: 'USD',
    'united states': 'USD',
  })[key] || 'USD';
};

const normalizeQuickPayUser = (user, index = 0) => {
  const tag = user.tag || user.display_tag || (user.linkup_id ? user.linkup_id : '@user-' + user.id);
  const country = user.country || user.new_country || '';

  return {
    id: user.id,
    name: user.name || user.email || 'LinkUp User',
    tag,
    img: user.img || user.avatar || AV((index % 54) + 1),
    ccy: user.ccy || countryCurrency(user.country_code || country),
    country,
    email: user.email || '',
    type: user.type || 'user',
  };
};

const normalizeContact = (contact, index = 0) => {
  const contactUser = contact.contact_user || contact.contactUser || contact;
  const normalized = normalizeQuickPayUser(contactUser, index);

  return {
    ...normalized,
    name: contact.name || normalized.name,
    contactId: contact.id || contact.contact_id || null,
  };
};

const cs = ref([]);
const hydrateContacts = () => {
  cs.value = props.contacts.map(normalizeContact);
};

hydrateContacts();

watch(() => props.contacts, hydrateContacts, { deep: true });

const quickPayUsers = computed(() => props.users.map(normalizeQuickPayUser));

// Modals
const showScanPay = ref(false);
const showMyQR = ref(false);
const topUpModalRef = ref(null);
const quickPayContactModalRef = ref(null);
const sendMoneyModalRef = ref(null);
const requestMoneyModalRef = ref(null);
const requestedMoneyModalRef = ref(null);
const activityModalRef = ref(null);
const showTransfer = ref(false);
const showTransferBank = ref(false);
const showPayBills = ref(false);
const showMerchant = ref(false);
const showCards = ref(false);

const showSaveDeposit = ref(false);
const showSaveWithdraw = ref(false);
const showSaveAuto = ref(false);
const showSaveHistory = ref(false);
const showSaveHow = ref(false);
const showAsue = ref(false);

const refreshAsue = () => {
  router.reload({ only: ['activeAsue', 'wallet', 'activity'] });
};

// Forms & Inputs
const coinPacks = [[100,1],[550,5],[1200,10],[3000,25]].map(p => ({luc: p[0], usd: p[1]}));
const tbAmt = ref('');
const merchName = ref('The Stop & Shop');
const merchAmt = ref('');
const cardInfo = DB.get('lk_my_card', {last4:'4471', exp:'08/28'});

// Pay Bills
const billPaid = ref(DB.get('lk_bill_paid', {}));
const billToPay = ref(null);
const billAmt = ref('');

// LinkUp Save
const saveData = reactive(DB.get('lk_save', { balance: 2450, earnedMonth: 8.17, autoSavePct: 10 }));
const saveHistory = [['Interest posted',8.17,'Apr 30'],['Auto-Save sweep (10%)',120,'Apr 28'],['Deposit from Wallet',500,'Apr 15'],['Interest posted',7.40,'Mar 31'],['Withdrawal to Wallet',-200,'Mar 12']];
const depAmt = ref('');
const wdAmt = ref('');
const asPct = ref(saveData.autoSavePct);

const saveTierInfo = computed(() => {
  const bal = saveData.balance;
  if (bal < savingsConfig.minBalance) return { name: 'Below Min', apy: 0, lock: 0 };
  let t = { name: 'Standard', apy: 0, lock: 0 };
  savingsConfig.tiers.slice().sort((a, b) => a.min - b.min).forEach(x => {
    if (bal >= x.min) t = { name: x.name, apy: x.apy, lock: x.lock };
  });
  return t;
});
const saveNextTier = computed(() => savingsConfig.tiers.slice().sort((a, b) => a.min - b.min).find(x => x.min > saveData.balance));

// Pending / Routing
const normalizeMoneyRequest = (request, index = 0) => ({
  ...request,
  amount: Number(request.amount || 0),
  person: {
    ...(request.person || {}),
    img: request.person?.img || AV(((index + 20) % 54) + 1),
  },
});

const walletMoneyRequests = ref([]);
const hydrateMoneyRequests = () => {
  walletMoneyRequests.value = props.moneyRequests.map(normalizeMoneyRequest);
};

hydrateMoneyRequests();

watch(() => props.moneyRequests, hydrateMoneyRequests, { deep: true });

const actions = [
  { name: 'Send Money', icon: 'send', color: '#2f9bef', fn: () => openSend() },
  { name: 'Request', icon: 'hand-coins', color: '#f59e0b', fn: () => openRequest() },
  { name: 'Requested', icon: 'inbox', color: '#8b5cf6', fn: () => openRequestedMoney() },
  { name: 'Pay Bills', icon: 'receipt', color: '#22c55e', fn: () => { showPayBills.value = true; } },
  { name: 'Merchant', icon: 'store', color: '#a855f7', fn: () => { showMerchant.value = true; } },
  { name: 'Transfer', icon: 'repeat', color: '#0b2942', fn: () => { showTransfer.value = true; } },
  { name: 'Top Up', icon: 'plus', color: '#0ea5e9', fn: () => openTopUp('cash') },
  { name: 'Cards', icon: 'credit-card', color: '#ec4899', fn: () => { showCards.value = true; } },
  { name: 'Activity', icon: 'history', color: '#64748b', fn: () => openActivity() },
  { name: 'ASUE', icon: 'users-round', color: '#0f766e', fn: () => { showAsue.value = true; } }
];

// --- LOGIC ---
const syncDB = () => { DB.set('lk_wallet', WALLET); DB.set('lk_save', saveData); };
const pushTx = (title, amount) => {
  txns.value.unshift({
    id: 'local-' + Date.now(),
    title,
    amount: Math.abs(Number(amount || 0)),
    isPositive: Number(amount || 0) >= 0,
    status: 'success',
    type: 'local',
    counterparty: '',
    note: '',
    runningBalance: WALLET.balance,
    date: new Date().toISOString(),
    dateLabel: 'Today',
  });

  if (window.toast) window.toast((amount >= 0 ? 'Received: ' : 'Paid: ') + money(Math.abs(amount)));
};

const openTopUp = (tab) => { topUpModalRef.value?.open(tab); };

const doWithdraw = () => {
  const v = parseFloat(tbAmt.value);
  if (!v || v <= 0) { if (window.toast) window.toast('Enter an amount'); return; }
  if (v > WALLET.balance) { if (window.toast) window.toast('Exceeds balance'); return; }
  const fee = v * 0.05;
  WALLET.balance = +(WALLET.balance - v).toFixed(2);
  pushTx('Transfer to bank', -v);
  syncDB();
  showTransferBank.value = false;
  if (window.toast) window.toast(`🏦 Withdrew ${money(v)} (−${money(fee)} fee)`);
};

const openAddContact = () => { quickPayContactModalRef.value?.open(); };
const addQuickPayContact = (user) => {
  if (cs.value.some(c => Number(c.id) === Number(user.id))) return;
  cs.value.push({ ...user });
};

const ccyOf = (code) => CCY.find(c => c.code === code) || null;
const appFx = (amt, from, to) => {
  if (from === to) return Math.round(amt * 100) / 100;
  const f = ccyOf(from), t = ccyOf(to);
  if (!f || !t) return null;
  return Math.round((amt / f.rate) * t.rate * (1 - t.bps / 10000) * 100) / 100;
};
const fmtCcy = (amt, code) => {
  if (amt == null) return '—';
  const sym = (ccyOf(code) || {}).sym || (code + ' ');
  return sym + Number(amt).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const walletResolve = (q) => {
  q = (q || '').trim(); if (!q) return null;
  const key = q.toLowerCase().replace(/^[@~]/, '');
  const hit = cs.value.find(c => c.tag.toLowerCase().replace(/^[@~]/, '') === key || c.name.toLowerCase() === q.toLowerCase());
  if (hit) return hit;
  const dirHit = quickPayUsers.value.find(u => u.tag.toLowerCase().replace(/^@/, '') === key || u.name.toLowerCase() === q.toLowerCase());
  if (dirHit) return dirHit;
  return { name: q.replace(/^[@~]/, ''), tag: '@' + key, img: AV(8), unknown: true };
};

const openSend = (tag) => { sendMoneyModalRef.value?.open(tag); };
const handleMoneySent = ({ recipient, amount }) => {
  WALLET.balance = +(WALLET.balance - amount).toFixed(2);
  pushTx('Sent to ' + recipient.name, -amount);
  syncDB();
  if (window.toast) window.toast('Sent ' + money(amount) + ' to ' + recipient.name);
};

const openRequest = (tag) => { requestMoneyModalRef.value?.open(tag); };
const handleMoneyRequested = ({ recipient, amount }) => {
  if (window.toast) window.toast('Requested ' + money(amount) + ' from ' + recipient.name);
};
const removeQuickPayContact = async (contact) => {
  if (!contact?.contactId) return;
  try {
    const { data } = await axios.delete(`/new_frontend/wallet/contact/${contact.contactId}`);
    if (data.success) {
      cs.value = cs.value.filter((item) => Number(item.contactId) !== Number(contact.contactId));
    } else {
      alert(data.message || 'Unable to remove this contact.');
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Unable to remove this contact.');
  }
};

const openRequestedMoney = () => { requestedMoneyModalRef.value?.open(); };
const handleRequestedMoneyPaid = (request) => {
  WALLET.balance = +(WALLET.balance - Number(request.amount || 0)).toFixed(2);
  pushTx('Paid ' + request.person.name, -Number(request.amount || 0));
  handleRequestedMoneyUpdated({ id: request.id, status: 'accepted' });
  syncDB();
  if (window.toast) window.toast('Paid ' + money(request.amount) + ' to ' + request.person.name);
};
const handleRequestedMoneyUpdated = ({ id, status }) => {
  walletMoneyRequests.value = walletMoneyRequests.value.map((request) => (
    Number(request.id) === Number(id) ? { ...request, status } : request
  ));
};

const openActivity = () => { activityModalRef.value?.open(); };

const payBill = () => {
  const v = parseFloat(billAmt.value);
  if (!v || v <= 0) { if (window.toast) window.toast('Enter an amount'); return; }
  if (WALLET.balance < v) { if (window.toast) window.toast('Insufficient balance'); return; }
  WALLET.balance = +(WALLET.balance - v).toFixed(2);
  billPaid.value[billToPay.value.id] = v;
  DB.set('lk_bill_paid', billPaid.value);
  pushTx('Bill · ' + billToPay.value.name, -v);
  syncDB();
  showPayBills.value = false;
  if (window.toast) window.toast(`🧾 Paid ${money(v)} to ${billToPay.value.name}`);
  billToPay.value = null; billAmt.value = '';
};

const payMerchant = () => {
  const v = parseFloat(merchAmt.value);
  if (!v || v <= 0) { if (window.toast) window.toast('Enter an amount'); return; }
  if (WALLET.balance < v) { if (window.toast) window.toast('Insufficient balance'); return; }
  WALLET.balance = +(WALLET.balance - v).toFixed(2);
  pushTx('Merchant · ' + merchName.value, -v);
  syncDB();
  showMerchant.value = false;
  if (window.toast) window.toast(`🏪 Paid ${money(v)} to ${merchName.value}`);
  merchAmt.value = '';
};

// --- SAVE ACTIONS ---
const applyDeposit = () => {
  const v = parseFloat(depAmt.value);
  if (!v || v < savingsConfig.topUpMin) { if (window.toast) window.toast('Minimum deposit is ' + money(savingsConfig.topUpMin)); return; }
  if (v > WALLET.balance) { if (window.toast) window.toast('Not enough wallet balance'); return; }
  saveData.balance += v; WALLET.balance = +(WALLET.balance - v).toFixed(2);
  pushTx('Deposit to LinkUp Save', -v); syncDB(); showSaveDeposit.value = false;
  if (window.toast) window.toast('🐷 ' + money(v) + ' moved to LinkUp Save');
  depAmt.value = '';
};
const applyWithdraw = () => {
  const v = parseFloat(wdAmt.value);
  if (!v || v <= 0) { if (window.toast) window.toast('Enter an amount'); return; }
  if (v > saveData.balance) { if (window.toast) window.toast('Exceeds savings balance'); return; }
  const pen = v * savingsConfig.earlyPenalty / 100;
  saveData.balance -= v; WALLET.balance = +(WALLET.balance + (v - pen)).toFixed(2);
  pushTx('Withdraw from Save', v - pen); syncDB(); showSaveWithdraw.value = false;
  if (window.toast) window.toast(`Withdrew ${money(v - pen)} (−${money(pen)} penalty)`);
  wdAmt.value = '';
};
const applyAuto = () => {
  const v = parseFloat(asPct.value) || 0;
  saveData.autoSavePct = Math.max(0, Math.min(100, v));
  syncDB(); showSaveAuto.value = false;
  if (window.toast) window.toast('Auto-Save set to ' + saveData.autoSavePct + '%');
};

onMounted(() => { nextTick(() => { if (window.lucide) window.lucide.createIcons(); }); });
onUpdated(() => { nextTick(() => { if (window.lucide) window.lucide.createIcons(); }); });
</script>
