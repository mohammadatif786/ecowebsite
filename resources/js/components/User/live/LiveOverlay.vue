<template>
  <div v-if="isOpen" class="fixed inset-0 z-[150] bg-[#0b0f2d] text-white font-sans">
    <!-- HOST MODE (STUDIO VIEW) -->
    <template v-if="session.mode === 'host'">
      <div class="flex h-full w-full overflow-hidden">
        <!-- LEFT SIDEBAR -->
        <aside class="w-64 border-r border-white/5 flex flex-col p-4 shrink-0 bg-[#0b0f2d]/50">
          <div class="flex items-center gap-3 mb-8 px-2">
            <img :src="props.user?.avatar || 'https://i.pravatar.cc/60?img=33'" class="w-10 h-10 rounded-full object-cover border-2 border-lkblue" />
            <div class="min-w-0">
              <p class="font-black text-sm truncate">{{ props.user?.name || 'Cassius' }}</p>
              <p class="text-[10px] text-slate-400 font-bold flex items-center gap-1">
                <i data-lucide="users" class="w-2.5 h-2.5"></i> 0 Followers
              </p>
            </div>
          </div>

          <nav class="flex-1 space-y-1">
            <button v-for="nav in STUDIO_NAV" :key="nav.id"
              @click="activeStudioTab = nav.id"
              :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-black transition',
                        activeStudioTab === nav.id ? 'bg-gradient-to-r from-blue-600 to-blue-400 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-white/5']"
            >
              <i :data-lucide="nav.icon" class="w-4 h-4"></i>
              {{ nav.label }}
            </button>
          </nav>

          <!-- Session Revenue Card -->
          <div class="mt-auto p-4 rounded-2xl bg-white/5 border border-white/10">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Session Revenue</p>
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-1.5">
                <i data-lucide="gem" class="w-4 h-4 text-amber-400"></i>
                <span class="text-sm font-black text-amber-400">{{ num(session.coins) }}</span>
              </div>
              <span class="text-base font-black text-emerald-400">${{ (session.coins * 0.01).toFixed(2) }}</span>
            </div>
            <button @click="showToast('💰 Transferring to wallet...')" class="w-full py-2.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/30 rounded-xl text-[11px] font-black transition">
              Transfer to Wallet
            </button>
          </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 flex flex-col overflow-hidden relative">
          <!-- Top Global Header -->
          <header class="h-16 border-b border-white/5 flex items-center justify-between px-6 shrink-0 bg-[#0b0f2d]">
            <div class="flex items-center gap-4">
              <button @click="close" class="text-slate-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
              </button>
              <div class="flex items-center gap-2">
                <svg viewBox="0 0 120 120" width="24" height="24">
                  <ellipse cx="42" cy="20" rx="13" ry="16" fill="#9EDB2F"/>
                  <ellipse cx="80" cy="20" rx="13" ry="16" fill="#2f9bef"/>
                  <path d="M34 44 v34 a16 16 0 0 0 32 0 v-34" fill="none" stroke="#2f9bef" stroke-width="18" stroke-linecap="round"/>
                  <path d="M86 44 v50" fill="none" stroke="#9EDB2F" stroke-width="18" stroke-linecap="round"/>
                </svg>
                <span class="logo text-xl text-white font-black leading-none">Link<span style="color:#F5C518">üp</span></span>
              </div>

              <!-- Live Indicator -->
              <div class="flex items-center gap-1 bg-red-500 rounded-full px-3 py-1 ml-2 border border-red-400/50 shadow-lg shadow-red-500/20">
                <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                <span class="text-[11px] font-black uppercase tracking-tighter">LIVE · {{ hms(timer) }}</span>
              </div>
            </div>

            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2 bg-white/5 px-3 py-1.5 rounded-full border border-white/5">
                <i data-lucide="gem" class="w-4 h-4 text-amber-400"></i>
                <span class="text-xs font-black text-slate-200">{{ num(walletCoins) }}</span>
              </div>
              <div class="flex items-center gap-2 bg-emerald-500/10 px-3 py-1.5 rounded-full border border-emerald-500/20">
                <span class="text-xs font-black text-emerald-400">${{ walletBalance.toFixed(2) }}</span>
              </div>
            </div>
          </header>

          <!-- STUDIO VIEW -->
          <div v-if="activeStudioTab === 'studio'" class="flex-1 flex overflow-hidden p-6 gap-6 bg-gradient-to-br from-[#0b0f2d] via-[#111a45] to-[#1e3a5f]">
            <div class="flex-1 flex flex-col gap-4 min-w-0 relative">

              <!-- Stream Info Overlay Header -->
              <div class="flex items-center justify-between gap-4 z-10 relative">
                <div class="flex items-center gap-3">
                  <div class="bg-black/40 backdrop-blur-md border border-white/10 rounded-full px-4 py-2 flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                      <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                      <span class="text-xs font-black uppercase tracking-tight">Live Stream</span>
                    </div>
                    <span class="w-px h-3 bg-white/10"></span>
                    <span class="text-[11px] font-black text-slate-300 uppercase">{{ session.category || 'Just Chatting' }}</span>
                  </div>
                  <div class="bg-black/40 backdrop-blur-md border border-white/10 rounded-full px-4 py-2 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-400"></i>
                    <span class="text-[11px] font-black text-slate-300">{{ session.loc || 'The Valley, Anguilla' }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2 bg-black/40 backdrop-blur-md border border-white/10 rounded-full px-4 py-2">
                   <div class="flex items-center gap-1.5 text-[11px] font-black text-slate-300">
                     <i data-lucide="users" class="w-3.5 h-3.5"></i> {{ num(session.viewers) }}
                   </div>
                   <span class="w-px h-3 bg-white/10 mx-1"></span>
                   <div class="flex items-center gap-1.5 text-[11px] font-black text-slate-300">
                     <i data-lucide="heart" class="w-3.5 h-3.5 text-rose-500"></i> {{ num(session.hearts) }}
                   </div>
                   <span class="w-px h-3 bg-white/10 mx-1"></span>
                   <div class="flex items-center gap-1.5 text-[11px] font-black text-slate-300">
                     <i data-lucide="gift" class="w-3.5 h-3.5 text-amber-400"></i> {{ num(session.gifts) }}
                   </div>
                </div>
              </div>

              <!-- Main Viewport -->
              <div class="flex-1 relative rounded-[32px] overflow-hidden bg-slate-950/40 border border-white/5 shadow-2xl">
                <div class="absolute inset-0 grid place-items-center text-white/20">
                  <div class="text-center">
                    <i data-lucide="video" class="w-20 h-20 mx-auto mb-4 opacity-50"></i>
                    <p class="font-black text-xl tracking-tight uppercase opacity-50">Your camera preview</p>
                  </div>
                </div>

                <!-- Floating Shop Banner -->
                <div v-if="featuredProduct" @click="openLiveShop" class="absolute top-6 left-6 z-20 flex max-w-[320px] cursor-pointer items-center rounded-full bg-white p-1.5 shadow-2xl animate-in fade-in slide-in-from-left-4 duration-500">
                  <img :src="featuredProduct.image || featuredProduct.cover_image" class="w-11 h-11 rounded-full object-cover shrink-0 shadow-sm" />
                  <div class="min-w-0 flex-1 px-3">
                    <p class="text-[12px] font-black text-slate-900 truncate leading-tight">{{ featuredProduct.title }}</p>
                    <p class="text-[11px] font-black text-blue-600 leading-tight mt-0.5">${{ Number(featuredProduct.price).toFixed(2) }}</p>
                  </div>
                  <button @click.stop="openLiveShop" class="bg-blue-500 text-white text-[11px] font-black px-4 py-2 rounded-full hover:bg-blue-600 shrink-0 transition shadow-md mr-1">
                    Shop {{ featuredProducts.length > 1 ? featuredProducts.length : '' }}
                  </button>
                </div>

                <!-- Stage Feed Layout -->
                <div class="absolute top-24 left-6 flex items-start gap-4 z-10">
                  <!-- Host Feed -->
                  <div class="w-16 h-20 rounded-2xl border-2 border-emerald-500 bg-slate-900 shadow-xl overflow-hidden relative shrink-0">
                    <img :src="props.user?.avatar" class="w-full h-full object-cover opacity-60" />
                    <div class="absolute bottom-1 left-1 bg-emerald-500 text-white text-[7px] font-black px-1 rounded uppercase">Host</div>
                    <div class="absolute inset-0 flex items-center justify-center text-[10px] font-black pointer-events-none text-white/80">You</div>
                  </div>

                  <!-- Guest Feeds -->
                  <div v-for="g in invitedGuests" :key="g.id"
                    :class="['w-16 h-20 rounded-2xl border-2 shadow-xl overflow-hidden relative shrink-0 transition-all duration-500',
                             g.status === 'live' ? 'border-rose-500' : 'border-amber-400']"
                  >
                    <div :class="['absolute inset-0 flex items-center justify-center text-[11px] font-black text-white', g.color]">
                       {{ g.name.substring(0,2).toUpperCase() }}
                    </div>

                    <!-- Remove button -->
                    <button @click="removeGuest(g.id)" class="absolute top-0.5 right-0.5 w-4 h-4 bg-black/50 text-white rounded-full flex items-center justify-center hover:bg-black transition z-20">
                      <i data-lucide="x" class="w-2.5 h-2.5"></i>
                    </button>

                    <!-- Status Badges -->
                    <div v-if="g.status === 'live'" class="absolute bottom-1 left-1 bg-rose-500 text-white text-[7px] font-black px-1 rounded uppercase flex items-center gap-0.5">
                      <span class="w-1 h-1 bg-white rounded-full animate-pulse"></span> LIVE
                    </div>
                    <div v-else class="absolute bottom-1 left-1 bg-amber-400 text-[#0b0f2d] text-[8px] font-black px-1 rounded flex items-center leading-none h-3">
                       ...
                    </div>
                  </div>
                </div>

                <!-- Stage Controls (Bottom Left) -->
                <div class="absolute bottom-6 left-6 flex items-center gap-3">
                  <button @click="openInviteModal" class="relative w-12 h-12 rounded-2xl bg-black/50 backdrop-blur-xl border border-white/10 grid place-items-center hover:bg-white/10 transition">
                    <i data-lucide="user-plus" class="w-5 h-5 text-white"></i>
                    <span v-if="invitedGuests.length > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 text-white text-[8px] font-black rounded-full flex items-center justify-center border border-[#0b0f2d]">
                       {{ invitedGuests.length }}
                    </span>
                  </button>
                  <button @click="openTagPicker" class="w-12 h-12 rounded-2xl bg-black/50 backdrop-blur-xl border border-white/10 grid place-items-center hover:bg-white/10 transition">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-white"></i>
                  </button>
                  <button @click="isMuted = !isMuted" :class="['w-12 h-12 rounded-2xl backdrop-blur-xl border grid place-items-center transition', isMuted ? 'bg-rose-500/20 border-rose-500/50 text-rose-500' : 'bg-black/50 border-white/10 text-white']">
                    <i :data-lucide="isMuted ? 'mic-off' : 'mic'" class="w-5 h-5"></i>
                  </button>
                  <button @click="showToast('📷 Camera toggled')" class="w-12 h-12 rounded-2xl bg-black/50 backdrop-blur-xl border border-white/10 grid place-items-center hover:bg-white/10 transition">
                    <i data-lucide="camera" class="w-5 h-5 text-white"></i>
                  </button>
                </div>

                <!-- End Broadcast Button -->
                <button @click="endBroadcast" class="absolute bottom-6 right-6 px-8 py-4 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl shadow-2xl shadow-rose-950/40 transition transform active:scale-95 text-base">
                  End Live
                </button>
              </div>
            </div>

            <!-- RIGHT PANEL (ENGAGEMENT) -->
            <aside class="w-80 flex flex-col rounded-[32px] bg-black/40 backdrop-blur-3xl border border-white/5 overflow-hidden shrink-0 shadow-2xl">
              <!-- Tabs -->
              <div class="flex p-2 gap-1 bg-white/5 border-b border-white/5 shrink-0">
                <button v-for="tab in STUDIO_PANEL_TABS" :key="tab.id"
                  @click="activePanelTab = tab.id"
                  :class="['flex-1 py-3 rounded-2xl text-xs font-black transition',
                           activePanelTab === tab.id ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200']"
                >
                  {{ tab.label }}
                </button>
              </div>

              <!-- Messages -->
              <div v-if="activePanelTab === 'chat'" ref="chatBody" class="flex-1 overflow-y-auto p-4 space-y-4 hide-scroll">
                <div v-for="(msg, i) in chat" :key="i" class="flex gap-3">
                   <div v-if="msg.who !== 'LinkUp' && msg.who !== 'You'" class="w-8 h-8 rounded-full bg-slate-800 shrink-0 text-[10px] grid place-items-center font-black text-white">
                     {{ msg.who.charAt(0) }}
                   </div>
                   <div v-else-if="msg.who === 'LinkUp'" class="w-8 h-8 rounded-full bg-lkblue shrink-0 flex items-center justify-center">
                     <i data-lucide="info" class="w-4 h-4 text-white"></i>
                   </div>
                   <div class="min-w-0 flex-1">
                     <p :class="['text-[11px] font-black mb-1', msg.who === 'LinkUp' ? 'text-lkyellow' : 'text-slate-400']">{{ msg.who }}</p>
                     <p class="text-sm font-semibold text-slate-100 leading-relaxed">{{ msg.msg }}</p>
                   </div>
                </div>
              </div>

              <!-- Q&A Section -->
              <div v-if="activePanelTab === 'qa'" class="flex-1 overflow-y-auto p-4 space-y-3 hide-scroll">
                <div v-for="q in questions" :key="q.id" class="p-4 rounded-2xl bg-white/5 border border-white/10 transition hover:bg-white/[0.08]">
                  <div class="flex items-center gap-2 mb-2">
                    <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black text-white', q.color || 'bg-blue-500']">
                      {{ q.who.substring(0,2).toUpperCase() }}
                    </div>
                    <span class="text-xs font-black text-white">{{ q.who }}</span>
                  </div>
                  <p class="text-sm font-semibold text-slate-200 leading-snug mb-3">{{ q.text }}</p>
                  <button @click="markAnswered(q.id)" class="text-[11px] font-black text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                    Mark as answered <i data-lucide="arrow-right" class="w-3 h-3"></i>
                  </button>
                </div>

                <div v-if="questions.length === 0" class="h-full flex flex-col items-center justify-center p-8 text-center opacity-40">
                   <i data-lucide="help-circle" class="w-12 h-12 mb-3 text-slate-400"></i>
                   <p class="text-sm font-bold text-slate-400">No active questions</p>
                </div>
              </div>

              <!-- Polls Section -->
              <div v-if="activePanelTab === 'polls'" class="flex-1 overflow-y-auto p-4 space-y-4 hide-scroll text-slate-900">
                <div class="flex items-center gap-2 mb-2">
                  <i data-lucide="bar-chart-2" class="w-4 h-4 text-slate-400"></i>
                  <span class="text-sm font-black uppercase text-white">Create a poll</span>
                </div>

                <textarea v-model="pollForm.question" placeholder="Ask a question..." class="w-full bg-white rounded-2xl p-4 text-slate-900 font-bold placeholder:text-slate-400 focus:ring-0 border-none min-h-[80px] resize-none"></textarea>

                <div v-for="(opt, idx) in pollForm.options" :key="idx" class="space-y-1">
                  <input v-model="pollForm.options[idx]" :placeholder="'Option ' + (idx + 1)" class="w-full bg-white rounded-2xl px-4 py-3.5 text-slate-900 font-bold placeholder:text-slate-400 focus:ring-0 border-none" />
                </div>

                <button @click="startPoll" class="w-full py-4 bg-gradient-to-r from-[#8b5cf6] to-[#a855f7] text-white font-black rounded-2xl shadow-lg transition transform active:scale-95 mt-2">
                  Start Poll
                </button>
              </div>

              <!-- Input -->
              <div v-if="activePanelTab === 'chat'" class="p-4 border-t border-white/5 bg-white/[0.02] shrink-0">
                <div class="relative">
                  <input v-model="chatInput" @keyup.enter="sendChat" placeholder="Type a message..." class="w-full bg-white/5 border border-white/10 rounded-2xl pl-5 pr-14 py-4 text-sm font-semibold outline-none focus:bg-white/10 focus:border-blue-500/50 transition text-white" />
                  <button @click="sendChat" :disabled="!chatInput.trim()" class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-blue-600 text-white rounded-xl grid place-items-center hover:bg-blue-500 disabled:opacity-50 transition shadow-lg">
                    <i data-lucide="send" class="w-4 h-4"></i>
                  </button>
                </div>
              </div>
            </aside>
          </div>

          <!-- ANALYTICS VIEW -->
          <div v-if="activeStudioTab === 'analytics'" class="flex-1 p-8 space-y-6 overflow-y-auto bg-gradient-to-br from-[#0b0f2d] via-[#111a45] to-[#1e3a5f]">
            <div class="flex items-center gap-4 mb-8">
              <div class="w-12 h-12 rounded-2xl bg-blue-500/20 flex items-center justify-center border border-blue-500/30">
                <i data-lucide="bar-chart-3" class="w-6 h-6 text-blue-400"></i>
              </div>
              <div>
                <h2 class="text-2xl font-black text-white">Analytics & Earnings</h2>
                <p class="text-sm text-slate-400 font-bold">How this live is performing</p>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-4 max-w-2xl text-white">
              <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Viewers</p>
                <p class="text-3xl font-black">{{ session.viewers }}</p>
              </div>
              <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Hearts</p>
                <p class="text-3xl font-black">{{ session.hearts }}</p>
              </div>
              <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gifts</p>
                <p class="text-3xl font-black">{{ session.gifts }}</p>
              </div>
            </div>

            <div class="max-w-2xl space-y-4">
              <div class="p-8 rounded-3xl bg-blue-600/10 border border-blue-600/20 text-center">
                <p class="text-[11px] font-black text-blue-400 uppercase tracking-[0.2em] mb-3">Gross Collected (All-Time)</p>
                <p class="text-5xl font-black mb-2 text-white">${{ (session.coins * 0.01 + 268.25).toFixed(2) }}</p>
                <p class="text-xs text-blue-300/60 font-bold">From live gifts · coins → cash</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="p-6 rounded-3xl bg-emerald-500/10 border border-emerald-500/20">
                  <p class="text-[11px] font-black text-emerald-400 mb-2">Your 50% share</p>
                  <p class="text-3xl font-black text-emerald-400">${{ ((session.coins * 0.01 + 268.25) * 0.5).toFixed(2) }}</p>
                </div>
                <div class="p-6 rounded-3xl bg-white/5 border border-white/10">
                  <p class="text-[11px] font-black text-slate-400 mb-2">LinkUp fee (50%)</p>
                  <p class="text-3xl font-black text-white">${{ ((session.coins * 0.01 + 268.25) * 0.5).toFixed(2) }}</p>
                </div>
              </div>

              <div class="p-6 rounded-3xl bg-white/5 border border-white/10 flex items-center justify-between text-white">
                <div>
                  <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Eligible to transfer</p>
                  <p class="text-3xl font-black">$0.00</p>
                </div>
                <button @click="showToast('💰 No funds eligible for transfer yet')" class="px-10 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-2xl shadow-xl shadow-emerald-950/20 transition transform active:scale-95">
                  Transfer
                </button>
              </div>
            </div>
          </div>

          <!-- GUEST LIST VIEW -->
          <div v-if="activeStudioTab === 'guests'" class="flex-1 p-8 overflow-y-auto bg-gradient-to-br from-[#0b0f2d] via-[#111a45] to-[#1e3a5f]">
            <div class="flex items-center gap-4 mb-8">
              <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30">
                <i data-lucide="users" class="w-6 h-6 text-emerald-400"></i>
              </div>
              <div>
                <h2 class="text-2xl font-black text-white">Guest List</h2>
                <p class="text-sm text-slate-400 font-bold">Invite up to 6 people to co-host this live</p>
              </div>
            </div>

            <div v-if="invitedGuests.length" class="mb-8 text-white">
              <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">On your live</p>
              <div class="max-w-md space-y-2">
                <div v-for="g in invitedGuests" :key="g.id" class="flex items-center gap-4 p-3 rounded-2xl bg-white/5 border border-white/5 shadow-sm">
                  <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-xs font-black shadow-lg text-white', g.color]">
                    {{ g.name.substring(0,2).toUpperCase() }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-black text-sm text-white truncate">@{{ g.name }}</p>
                    <p v-if="g.status === 'live'" class="text-[10px] text-rose-500 font-black flex items-center gap-1 uppercase">
                      <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span> Live now
                    </p>
                    <p v-else class="text-[10px] text-lkyellow font-black flex items-center gap-1 uppercase">
                      Invited — waiting to accept
                    </p>
                  </div>
                  <button @click="removeGuest(g.id)" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-rose-400 rounded-xl text-[11px] font-black transition">
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Suggested</p>

            <div class="max-w-md space-y-2">
              <div v-for="g in suggestedGuests" :key="g.id" class="flex items-center gap-4 p-3 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/[0.08] transition shadow-sm">
                <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-xs font-black shadow-lg text-white', g.color]">
                   {{ g.name.substring(0,2).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-black text-sm text-white truncate">@{{ g.name }}</p>
                  <p class="text-[10px] text-slate-400 font-bold flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-2.5 h-2.5 text-slate-400"></i> {{ g.loc }}
                  </p>
                </div>
                <button @click="inviteGuest(g)" class="px-5 py-2 bg-blue-600 hover:bg-blue-50 text-white rounded-full text-[11px] font-black transition transform active:scale-95 shadow-md">
                  Invite
                </button>
              </div>
            </div>
          </div>

          <!-- HELP VIEW -->
          <div v-if="activeStudioTab === 'help'" class="flex-1 p-8 overflow-y-auto bg-gradient-to-br from-[#0b0f2d] via-[#111a45] to-[#1e3a5f]">
            <div class="flex items-center gap-4 mb-8">
              <div class="w-12 h-12 rounded-2xl bg-orange-500/20 flex items-center justify-center border border-orange-500/30">
                <i data-lucide="help-circle" class="w-6 h-6 text-orange-400"></i>
              </div>
              <div>
                <h2 class="text-2xl font-black text-white">Help & Guide</h2>
                <p class="text-sm text-slate-400 font-bold">Quick tips for running a great LinkUp Live</p>
              </div>
            </div>

            <div class="max-w-xl space-y-3 text-white">
              <div v-for="(item, idx) in HELP_ITEMS" :key="idx" class="p-5 rounded-[24px] bg-white/5 border border-white/5 flex gap-5 items-start transition hover:bg-white/[0.08] shadow-sm">
                <div :class="['w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 shadow-lg', item.color]">
                   <i :data-lucide="item.icon" class="w-5 h-5 text-white"></i>
                </div>
                <div class="min-w-0">
                  <p class="font-black text-[15px] text-white mb-1">{{ item.title }}</p>
                  <p class="text-[13px] text-slate-400 font-semibold leading-relaxed">{{ item.desc }}</p>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </template>

    <!-- VIEWER MODE (DEDICATED VISITOR UI) -->
    <template v-else>
      <LiveViewerOverlay
        :session="session"
        :chat="chat"
        :featured-product="featuredProduct"
        @close="close"
        @send-chat="sendChat"
        @send-heart="sendHeart"
        @open-shop="openLiveShop"
        @open-gift="openGiftModal"
      />
    </template>

    <!-- MODALS -->
    <LiveShopModal
      ref="liveShopModalRef"
      :can-manage="session.mode === 'host'"
      @feature="onFeatureProduct"
      @remove="onRemoveProduct"
    />
    <CoHostInviteModal
      ref="coHostInviteModalRef"
      @join="onGuestAccept"
      @decline="onGuestDecline"
    />
    <InviteGuestsModal
      ref="inviteGuestsModalRef"
      @invite="inviteGuest"
      @remove="removeGuest"
    />
    <TagPickerModal
      ref="tagPickerModalRef"
      @pick="onTagPick"
    />
    <LiveGiftModal
      ref="liveGiftModalRef"
      :balance="walletCoins"
      @send="onSendGift"
    />
  </div>

  <LiveEndModal ref="liveEndModalRef" />
</template>

<script setup>
import { ref, onBeforeUnmount, nextTick, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { DB } from '../MockDataStore';
import LiveEndModal from '../modals/LiveEndModal.vue';
import LiveShopModal from '../modals/LiveShopModal.vue';
import CoHostInviteModal from '../modals/CoHostInviteModal.vue';
import InviteGuestsModal from '../modals/InviteGuestsModal.vue';
import TagPickerModal from '../modals/TagPickerModal.vue';
import LiveGiftModal from '../modals/LiveGiftModal.vue';
import LiveViewerOverlay from './LiveViewerOverlay.vue';

const page = usePage();
const props = defineProps({
  user: { type: Object, default: () => ({}) }
});

const isOpen = ref(false);
const session = ref({});
const chat = ref([]);
const chatInput = ref('');
const timer = ref(0);
const isMuted = ref(false);
const activeStudioTab = ref('studio');
const activePanelTab = ref('chat');
const chatBody = ref(null);
const questions = ref([
  { id: 1, who: 'Renee', text: 'Any Carnival fete tips this year?', color: 'bg-orange-500' },
  { id: 2, who: 'Simone', text: 'Where are you streaming from?', color: 'bg-indigo-500' },
  { id: 3, who: 'Amara', text: 'Any Carnival fete tips this year?', color: 'bg-pink-500' },
  { id: 4, who: 'Amara', text: 'How do I become a co-host?', color: 'bg-pink-500' },
  { id: 5, who: 'Simone', text: 'What camera are you using?', color: 'bg-indigo-500' },
  { id: 6, who: 'Simone', text: 'Any Carnival fete tips this year?', color: 'bg-indigo-500' },
]);
const pollForm = ref({
  question: '',
  options: ['', '', '', '']
});
const HELP_ITEMS = [
  { title: 'Going live', desc: 'Pick a title, category and location in Broadcast Settings, then hit Go Live. Your camera preview shows here once connected.', icon: 'radio', color: 'bg-rose-500' },
  { title: 'Co-hosting', desc: 'Invite up to 6 guests from the Guest List. They get a request and choose to accept before joining — you stay in control of who is on screen.', icon: 'users', color: 'bg-emerald-500' },
  { title: 'Selling live', desc: 'Tag Marketplace products or event tickets so viewers can buy without leaving the stream — you earn commission on every sale.', icon: 'shopping-bag', color: 'bg-blue-600' },
  { title: 'Earning from gifts', desc: 'Viewers send gifts that convert to coins. You keep 50% as cash, transferable to your Wallet any time from Session Revenue.', icon: 'gift', color: 'bg-orange-500' },
  { title: 'Q&A and Polls', desc: 'Use the Q&A tab to field viewer questions on stream, or start a Poll to get instant feedback with live results.', icon: 'bar-chart-2', color: 'bg-indigo-600' }
];

const suggestedGuests = ref([
  { id: 1, name: 'tanyab', loc: 'Trinidad', color: 'bg-rose-500' },
  { id: 2, name: 'islandvibez', loc: 'Jamaica', color: 'bg-amber-500' },
  { id: 3, name: 'reneeb', loc: 'Bahamas', color: 'bg-orange-500' },
  { id: 4, name: 'carlosmx', loc: 'Mexico', color: 'bg-rose-600' },
  { id: 5, name: 'gabis', loc: 'Brazil', color: 'bg-blue-600' },
  { id: 6, name: 'drech', loc: 'Guyana', color: 'bg-blue-500' },
  { id: 7, name: 'kemarb', loc: 'Jamaica', color: 'bg-rose-500' },
  { id: 8, name: 'aaliyahvibes', loc: 'Barbados', color: 'bg-blue-700' },
]);

const invitedGuests = ref([]);

let interval = null;

const liveEndModalRef = ref(null);
const liveShopModalRef = ref(null);
const coHostInviteModalRef = ref(null);
const inviteGuestsModalRef = ref(null);
const tagPickerModalRef = ref(null);
const liveGiftModalRef = ref(null);
const featuredProduct = ref(null);

const STUDIO_NAV = [
  { id: 'studio', label: 'Studio View', icon: 'monitor' },
  { id: 'analytics', label: 'Analytics', icon: 'bar-chart-3' },
  { id: 'guests', label: 'Guest List', icon: 'users' },
  { id: 'help', label: 'Help & Guide', icon: 'help-circle' }
];

const STUDIO_PANEL_TABS = [
  { id: 'chat', label: 'Chat' },
  { id: 'qa', label: 'Q&A' },
  { id: 'polls', label: 'Polls' }
];

const walletCoins = computed(() => Number(page.props.auth?.user?.coins || 37010));
const walletBalance = computed(() => Number(page.props.walletBalance || 29.35));

const featuredProducts = computed(() => {
  return (session.value.products || []).filter(p => p.featured === true);
});

const num = (n) => Number(n || 0).toLocaleString();
const hms = (s) => {
  const m = Math.floor(s / 60);
  const x = s % 60;
  return (m < 10 ? '0' : '') + m + ':' + (x < 10 ? '0' : '') + x;
};
const showToast = (msg) => { if (window.toast) window.toast(msg); };

const open = (liveSession) => {
  session.value = { ...liveSession };
  featuredProduct.value = (session.value.products || []).find((product) => product.featured !== false) || null;
  isOpen.value = true;
  timer.value = 0;
  activeStudioTab.value = 'studio';
  activePanelTab.value = 'chat';
  invitedGuests.value = [];

  if (session.value.mode === 'host') {
    chat.value = [{ who: 'LinkUp', msg: 'You are live! Say hi 👋' }];
    session.value.viewers = 99;
    session.value.hearts = 52;
    session.value.gifts = 24;
    session.value.coins = 1825;
  } else {
    chat.value = [
      { who: session.value.host || 'Host', msg: 'Welcome in everybody! 🔥' },
      { who: 'Renee', msg: 'Tuned in from Nassau 🇧🇸' },
      { who: 'Marcus', msg: '🔥🔥' }
    ];
  }

  clearInterval(interval);
  interval = setInterval(() => {
    timer.value++;
    if (session.value.mode === 'host' && activeStudioTab.value === 'studio') {
      if (Math.random() < 0.3) session.value.viewers++;
      if (Math.random() < 0.2) session.value.hearts += Math.floor(Math.random() * 3);
      if (Math.random() < 0.05) session.value.coins += 50;
    }

    if (Math.random() < 0.15) {
      const msgs = ['Yesss!', '🔥🔥', 'Let\'s go!', 'Love this', 'Hello from JA 🇯🇲', 'Vibes!!'];
      const users = ['Tanya', 'Dre', 'Carlos', 'Gabby', 'Anonymous'];
      chat.value.push({
        who: users[Math.floor(Math.random() * users.length)],
        msg: msgs[Math.floor(Math.random() * msgs.length)]
      });
      if (chat.value.length > 50) chat.value.shift();
      scrollToBottom();
    }
  }, 1000);

  nextTick(() => {
    if (window.lucide) window.lucide.createIcons();
    scrollToBottom();
  });
};

const scrollToBottom = () => {
  nextTick(() => {
    if (chatBody.value) {
      chatBody.value.scrollTop = chatBody.value.scrollHeight;
    }
  });
};

const close = () => {
  isOpen.value = false;
  clearInterval(interval);
};

const endBroadcast = () => {
  if (session.value.mode === 'host') {
    const coins = session.value.coins || 0;
    const gross = +(coins * 0.01).toFixed(2);
    const e = DB.get('lk_live_earnings', { collected: 224.00, transferred: 0 });
    e.collected = +(e.collected + gross).toFixed(2);
    DB.set('lk_live_earnings', e);

    const sold = (session.value.products || []).reduce((sum, p) => sum + (Number(p.sold) || 0), 0);
    const comm = (session.value.products || []).reduce((sum, p) => {
      const price = Number(p.price) || 0;
      const rate = Number(p.commission) || 10;
      return sum + ((price * rate) / 100) * (Number(p.sold) || 0);
    }, 0);

    if (window.showLiveSummary) {
      window.showLiveSummary({
        secs: timer.value,
        viewers: session.value.viewers,
        hearts: session.value.hearts,
        gifts: session.value.gifts,
        coins: coins,
        sold: sold,
        commissionEarned: comm
      });
    }
  }
  close();
};

const sendChat = (msgText) => {
  const text = typeof msgText === 'string' ? msgText : chatInput.value;
  if (!text || !text.trim()) return;
  chat.value.push({ who: 'You', msg: text });
  chatInput.value = '';
  if (chat.value.length > 50) chat.value.shift();
  scrollToBottom();
};

const sendHeart = () => {
  session.value.hearts = (session.value.hearts || 0) + 1;
  showToast('🌹 Heart sent');
};

const markAnswered = (id) => {
  questions.value = questions.value.filter(q => q.id !== id);
  showToast('✅ Question marked as answered');
};

const startPoll = () => {
  if (!pollForm.value.question.trim()) {
    showToast('❌ Please ask a question');
    return;
  }
  showToast('📊 Poll started!');
  pollForm.value = { question: '', options: ['', '', '', ''] };
};

const openLiveShop = () => {
  liveShopModalRef.value?.open(session.value.products || [], featuredProduct.value?.id);
};

const onFeatureProduct = (p) => {
  featuredProduct.value = p;
  showToast(`📣 Featuring: ${p.title}`);
};

const onRemoveProduct = (id) => {
  const p = session.value.products.find(item => item.id === id);
  if (p) p.featured = false;
  if (featuredProduct.value?.id === id) {
    featuredProduct.value = featuredProducts.value[0] || null;
  }
};

const openInviteModal = () => {
  inviteGuestsModalRef.value?.open(suggestedGuests.value, invitedGuests.value);
};

const openTagPicker = () => {
  tagPickerModalRef.value?.open();
};

const onTagPick = (item) => {
  if (!session.value.products.some((p) => p.id === item.id)) {
    const p = { ...item, sold: 0, left: 10, featured: true };
    session.value.products.push(p);
    if (window.toast) window.toast(item.title + ' tagged');
    if (!featuredProduct.value) featuredProduct.value = p;
  }
};

const openGiftModal = () => {
  liveGiftModalRef.value?.open();
};

const onSendGift = (gift) => {
  session.value.gifts = (session.value.gifts || 0) + 1;
  session.value.coins = (session.value.coins || 0) + gift.price;
  chat.value.push({ who: 'LinkUp', msg: `🎁 You sent ${gift.name}!` });
  showToast(`🎁 Sent ${gift.emoji} ${gift.name}`);
  scrollToBottom();
};

const inviteGuest = (guest) => {
  if (invitedGuests.value.length >= 6) {
    showToast('❌ Max 6 co-hosts allowed');
    return;
  }

  if (!invitedGuests.value.find(g => g.id === guest.id)) {
    invitedGuests.value.push({ ...guest, status: 'invited' });
    showToast(`📩 Invitation sent to @${guest.name}`);
    setTimeout(() => {
      coHostInviteModalRef.value?.open(guest);
    }, 1500);
  }
};

const onGuestAccept = (guest) => {
  const g = invitedGuests.value.find(item => item.id === guest.id);
  if (g) {
    g.status = 'live';
    showToast(`🎉 @${guest.name} joined the stream!`);
    nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
  }
};

const onGuestDecline = (guest) => {
  removeGuest(guest.id);
  showToast(`🚫 @${guest.name} declined the invitation`);
};

const removeGuest = (id) => {
  invitedGuests.value = invitedGuests.value.filter(g => g.id !== id);
  nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
};

defineExpose({ open, close });
</script>

<style scoped>
.hide-scroll::-webkit-scrollbar {
  display: none;
}
.hide-scroll {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
