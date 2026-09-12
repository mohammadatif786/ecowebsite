<template>
    <AuthenticatedLayout>

        <Head :title="`Browse ${userdata.name}`" />

        <div class="profile-wrap">
            <div class="shell">

                <header class="top-header">
                    <div class="user-identity">
                        <div class="mini-avatar">
                            <img :src="getPhotoUrl(allPhotos[currentIdx])" alt="Profile" />
                        </div>
                        <div class="id-text">
                            <div class="name-row">
                                <h1 class="profile-name">{{ userdata.name }}, {{ userdata.age }}</h1>
                                <span class="status-pill"><span class="status-dot"></span> Active</span>
                            </div>
                            <p class="sub-info">
                                <span class="country-pulse">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 22s7-4.5 7-11a7 7 0 10-14 0c0 6.5 7 11 7 11z" stroke="currentColor"
                                            stroke-width="2" />
                                        <circle cx="12" cy="11" r="2.5" stroke="currentColor" stroke-width="2" />
                                    </svg>
                                    <span>{{ userdata.country }}</span>
                                </span>
                                <span v-if="userdata.distance"> • 📍 {{ userdata.distance }} km away</span>
                            </p>
                        </div>
                    </div>

                    <div class="header-actions">
                        <!-- Show friend status only if neither user has blocked the other -->
                        <template v-if="!isBlocked && !userdata.is_blocking_current_user">
                            <div v-if="isFriends" class="btn-linked">Linked Up ✓</div>
                            <div v-else-if="isFriendRequestSent" class="btn-sent">Request Sent</div>
                            <FriendRequestButton v-else :userId="userdata.uid" />
                        </template>

                        <!-- Show blocked status if user is blocked -->
                        <template v-else-if="isBlocked">
                            <div class="btn-blocked">Blocked</div>
                        </template>

                        <!-- Show blocked status if current user is blocked by profile owner -->
                        <template v-else-if="userdata.is_blocking_current_user">
                            <div class="btn-blocked">Cannot Send Request</div>
                        </template>

                        <!-- Block/Unblock Button -->
                        <button @click="isBlocked ? unblockUser() : openBlockModal()" :disabled="isBlocking"
                            class="btn-block" style="margin-left: 10px;">
                            {{ isBlocking ? 'Loading...' : (isBlocked ? 'Unblock' : 'Block') }}
                        </button>

                        <!-- Report Button -->
                        <button @click="openReportModal" class="btn-report" style="margin-left: 10px;">
                            Report
                        </button>

                        <div class="menu-wrap">
                            <div v-if="isMenuOpen" class="custom-dropdown fadeIn">
                                <!-- Hide Profile Option -->
                                <div class="menu-item">
                                    <div class="flex items-start justify-between w-full text-left">
                                        <div>
                                            <div class="title font-bold">Hide Profile</div>
                                            <div class="desc text-xs opacity-70">Not interested</div>
                                        </div>
                                        <div class="flex flex-row flex-center justify-end ml-auto">
                                            <button @click="hideProfileFromUser"
                                                class="w-11 h-6 bg-gray-200 rounded-full relative transition-all duration-300"
                                                :class="!hideProfile ? 'bg-[#D5DB2B]' : 'bg-gray-200'">
                                                <div class="absolute top-[2px] w-5 h-5 bg-white rounded-full border transition-all duration-300"
                                                    :class="!hideProfile ? 'translate-x-0' : 'translate-x-full'"></div>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 my-1"></div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="profile-content">
                    <section class="photo-section">
                        <div class="hero-container">
                            <img :src="getPhotoUrl(allPhotos[currentIdx])" class="main-hero-img fadeIn"
                                :key="currentIdx" />
                            <div class="hero-overlay-top">
                                <div class="verified-tag">✓ Verified Photos</div>
                                <div class="nav-controls">
                                    <button class="nav-arrow" @click="prevPhoto">◀</button>
                                    <button class="nav-arrow" @click="nextPhoto">▶</button>
                                </div>
                            </div>
                            <div class="hero-overlay-bottom">
                                <div class="photo-dots">
                                    <span v-for="(_, i) in allPhotos" :key="i" class="dot"
                                        :class="{ active: i === currentIdx }"></span>
                                </div>
                                <div class="photo-counter">{{ currentIdx + 1 }} / {{ allPhotos.length }}</div>
                            </div>
                        </div>
                        <div class="thumbnail-grid">
                            <div v-for="(img, i) in allPhotos" :key="i" class="thumb-box"
                                :class="{ active: i === currentIdx }" @click="currentIdx = i">
                                <img :src="getPhotoUrl(img)" />
                            </div>
                        </div>

                        <!-- Swipe-like Action buttons for cohesiveness with Home/All Matches -->
                        <div class="action-buttons-container mt-6">
                            <!-- Pass ✕ -->
                            <button class="action-btn-circle pass-btn" title="Pass" @click.prevent.stop="handlePass">
                                <svg class="h-[20px] w-[20px]" fill="none" stroke="currentColor" stroke-width="2.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Gift 🎁 -->
                            <button class="action-btn-circle gift-btn" title="Send Gift"
                                @click.prevent.stop="handleGift">
                                <svg class="h-[24px] w-[24px] fill-white" viewBox="0 0 24 24">
                                    <path
                                        d="M20 6h-2.18c.07-.31.18-.6.18-.93C18 3.37 16.63 2 14.93 2c-.97 0-1.76.42-2.35 1.09L12 3.77l-.58-.68C10.83 2.42 10.04 2 9.07 2 7.37 2 6 3.37 6 5.07c0 .33.11.62.18.93H4c-1.11 0-2 .89-2 2v3c0 .55.45 1 1 1h1v7c0 1.1.89 2 2 2h12c1.11 0 2-.9 2-2v-7h1c.55 0 1-.45 1-1V8c0-1.11-.89-2-2-2zm-7 0h-2V5.07C11 4.48 11.48 4 12.07 4h-.07C12.59 4 13 4.41 13 4.93V6zm-4 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm8 14H7v-7h10v7z" />
                                </svg>
                            </button>

                            <!-- Like ♥ -->
                            <button class="action-btn-circle like-btn" title="Like" @click.prevent.stop="handleLike">
                                <svg class="h-[20px] w-[20px] fill-rose-500" viewBox="0 0 24 24">
                                    <path
                                        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                </svg>
                            </button>
                        </div>
                    </section>

                    <section class="details-stack">
                        <div class="info-card fadeIn">
                            <div class="card-header"><span>About Me</span><span class="hint">Bio</span></div>
                            <p class="bio-text">{{ userdata.about_me || 'No bio available' }}</p>
                        </div>

                        <div class="info-card fadeIn">
                            <div class="card-header"><span>Interests</span><span class="hint">Lifestyle</span></div>
                            <div class="chip-container">
                                <span v-for="interest in userdata.interests" :key="interest" class="interest-chip">✨ {{
                                    interest
                                    }}</span>
                            </div>
                        </div>

                        <div class="info-card fadeIn">
                            <div class="card-header"><span>Looking for</span><span class="hint">Intent</span></div>
                            <div class="chip-container">
                                <span class="interest-chip primary-brand-chip">
                                    <span class="chip-icon-box">📍</span>
                                    {{ userdata.whyare || 'Here to Link Up' }}
                                </span>
                            </div>
                        </div>

                        <div class="info-card fadeIn">
                            <div class="card-header"><span>Basics</span><span class="hint">Details</span></div>
                            <div class="kv-grid">
                                <div v-if="userdata.job" class="label">Work</div>
                                <div v-if="userdata.job" class="value">{{ userdata.job }}</div>
                                <div class="label">University</div>
                                <div class="value"><span class="uni-badge-pulse">
                                        {{ userdata.university || 'NotSpecified' }}</span>
                                </div>
                                <div class="label">Languages</div>
                                <div class="value">{{ userdata.language }}</div>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>

        <div v-if="showBlockModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeBlockModal">
            <div class="absolute inset-0 bg-black/55 backdrop-blur-sm fadeIn"></div>

            <div class="relative min-h-screen w-full flex items-end sm:items-center justify-center p-3 sm:p-6">
                <div
                    class="w-full max-w-lg max-h-[90vh] flex flex-col bg-white/78 border border-white/48 shadow-[0_26px_60px_rgba(2,6,23,0.18)] backdrop-blur-[18px] saturate-[1.6] rounded-[28px] popIn overflow-hidden">

                    <!-- Header -->
                    <div class="p-5 border-b border-white/30 shrink-0">
                        <div class="flex items-start gap-4">
                            <div class="relative w-12 h-12 rounded-3xl grid place-items-center" style="background: radial-gradient(circle at 30% 30%, rgba(14,165,233,0.22), rgba(223,255,0,0.14));
                                       border:1px solid rgba(255,255,255,0.40);
                                       box-shadow: 0 14px 28px rgba(2,6,23,0.12);">
                                <UserX class="w-6 h-6" style="color:#0ea5e9"></UserX>
                                <div
                                    class="absolute -right-1 -bottom-1 w-7 h-7 rounded-full grid place-items-center bg-white/90 border border-slate-200">
                                    <Ban class="w-4 h-4" style="color:#ef4444"></Ban>
                                </div>
                            </div>

                            <div class="flex-1">
                                <div class="text-[12px] text-slate-600 font-extrabold uppercase tracking-wide">SAFETY
                                    ACTION
                                </div>
                                <h2 class="text-[22px] leading-tight font-black tracking-tight mt-1">
                                    Block <span class="text-slate-900">{{ userdata.name }}</span>
                                </h2>
                                <p class="text-[14px] font-semibold text-slate-700 mt-1">
                                    Please tell us why you're blocking <span class="font-black">@{{ userdata.linkup_id
                                        ||
                                        'acchcuu' }}</span>.
                                    <span class="text-slate-500">We won't tell them.</span>
                                </p>
                            </div>

                            <button @click="closeBlockModal"
                                class="bg-white/80 border border-slate-200/30 rounded-full px-3 py-2 text-[13px] font-black hover:brightness-105 transition-all">
                                <X class="w-4 h-4"></X>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-5 overflow-y-auto flex-1">
                        <!-- Reasons -->
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-[12px] text-slate-600 font-extrabold uppercase tracking-wide">Reason
                                </div>
                                <div class="text-[13px] text-slate-700 font-semibold">Pick one (optional).</div>
                            </div>
                            <span class="text-[12px] text-slate-500 font-bold">LinkUp Safety</span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <button v-for="reason in blockReasons" :key="reason.id" type="button"
                                @click="selectedReason = selectedReason === reason.id ? null : reason.id"
                                class="rounded-full border border-slate-200/50 bg-white/78 shadow-[0_10px_20px_rgba(2,6,23,0.06)] font-black text-[13px] px-[14px] py-[10px] text-slate-900/92 transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99] flex items-center gap-2"
                                :class="selectedReason === reason.id ? 'border-blue-500/50 bg-blue-100 shadow-[0_14px_28px_rgba(14,165,233,0.12)]' : ''">
                                <component :is="reason.icon" class="w-4 h-4"
                                    :style="selectedReason === reason.id ? 'color:#0ea5e9' : 'color:#0ea5e9'">
                                </component>
                                <span :class="selectedReason === reason.id ? 'text-blue-600' : 'text-slate-900'">{{
                                    reason.label
                                    }}</span>
                            </button>
                        </div>

                        <!-- Note -->
                        <div class="mt-3">
                            <textarea v-model="blockNote" rows="3"
                                class="w-full rounded-[20px] border border-slate-200/70 bg-white/85 px-4 py-3 text-[14px] font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:shadow-[0_0_0_4px_rgba(14,165,233,0.18)] focus:border-blue-500/55 transition-all"
                                placeholder="Optional: add a short note (this is private)."
                                @input="updateBlockNoteCount"></textarea>

                            <div
                                class="mt-2 flex items-center justify-between text-[12px] text-slate-500 font-semibold">
                                <div class="flex items-center gap-2">
                                    <Lock class="w-4 h-4"></Lock>
                                    <span>Private—only used for moderation and safety.</span>
                                </div>
                                <span>{{ blockNote.length }}/280</span>
                            </div>
                        </div>

                        <!-- What happens -->
                        <div
                            class="mt-4 bg-white/62 border border-white/40 shadow-[0_14px_30px_rgba(2,6,23,0.12)] backdrop-blur-[16px] saturate-[1.6] rounded-[22px] p-4">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-2xl grid place-items-center"
                                    style="background: rgba(14,165,233,0.12); border:1px solid rgba(14,165,233,0.25);">
                                    <Info class="w-4.5 h-4.5" style="color:#0ea5e9"></Info>
                                </div>
                                <div class="font-black text-slate-900">What happens when you block</div>
                            </div>

                            <div class="mt-3 space-y-3 text-[13px] text-slate-700 font-semibold">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-[2px] w-9 h-9 rounded-2xl grid place-items-center border border-slate-200/60 bg-white/70">
                                        <SearchX class="w-4.5 h-4.5"></SearchX>
                                    </div>
                                    <div>They won't be able to find your profile or message you.</div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-[2px] w-9 h-9 rounded-2xl grid place-items-center border border-slate-200/60 bg-white/70">
                                        <BellOff class="w-4.5 h-4.5"></BellOff>
                                    </div>
                                    <div>They won't be notified that you blocked them.</div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-[2px] w-9 h-9 rounded-2xl grid place-items-center border border-slate-200/60 bg-white/70">
                                        <Settings class="w-4.5 h-4.5"></Settings>
                                    </div>
                                    <div>You can unblock them anytime in Settings.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <button @click="closeBlockModal"
                                class="bg-white/80 border border-slate-200/30 rounded-full px-4 py-3 text-[15px] font-black hover:brightness-105 transition-all">
                                Cancel
                            </button>
                            <button @click="confirmBlock"
                                class="bg-gradient-to-r from-pink-400/98 to-red-500/92 border border-white/22 text-white rounded-full px-4 py-3 text-[15px] font-black shadow-[0_16px_34px_rgba(239,68,68,0.22)] hover:brightness-105 transition-all">
                                Yes, Block
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Report User Modal -->
        <div v-if="showReportModal" class="fixed inset-0 z-50" @click.self="closeReportModal">
            <div class="absolute inset-0 bg-black/55 backdrop-blur-sm fadeIn"></div>

            <div class="relative h-full w-full flex items-end sm:items-center justify-center p-3 sm:p-6">
                <div
                    class="w-full max-w-lg bg-white/78 border border-white/48 shadow-[0_26px_60px_rgba(2,6,23,0.18)] backdrop-blur-[18px] saturate-[1.6] rounded-[28px] popIn overflow-hidden">

                    <!-- Header -->
                    <div class="p-5 border-b border-white/30">
                        <div class="flex items-start gap-4">
                            <div class="relative w-12 h-12 rounded-3xl grid place-items-center" style="background: radial-gradient(circle at 30% 30%, rgba(14,165,233,0.22), rgba(223,255,0,0.14));
                                       border:1px solid rgba(255,255,255,0.40);
                                       box-shadow: 0 14px 28px rgba(2,6,23,0.12);">
                                <Flag class="w-6 h-6" style="color:#0ea5e9"></Flag>
                                <div
                                    class="absolute -right-1 -bottom-1 w-7 h-7 rounded-full grid place-items-center bg-white/90 border border-slate-200">
                                    <TriangleAlert class="w-4 h-4" style="color:#f59e0b"></TriangleAlert>
                                </div>
                            </div>

                            <div class="flex-1">
                                <div class="text-[12px] text-slate-600 font-extrabold uppercase tracking-wide">Report
                                </div>
                                <h2 class="text-[24px] leading-tight font-black tracking-tight mt-1">
                                    Reporting <span class="text-slate-900">{{ userdata.name }}</span>
                                </h2>
                                <p class="text-[14px] font-semibold text-slate-700 mt-1">
                                    Tell us what happened. We won't tell them.
                                </p>
                            </div>

                            <button @click="closeReportModal"
                                class="bg-white/80 border border-slate-200/30 rounded-full px-3 py-2 text-[13px] font-black hover:brightness-105 transition-all">
                                <X class="w-4 h-4"></X>
                            </button>
                        </div>

                        <div class="mt-4 text-[18px] font-black tracking-tight text-slate-900">
                            Why are you reporting this user?
                        </div>

                        <!-- Context chips -->
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span
                                class="rounded-full border border-slate-200/50 bg-white/78 shadow-[0_10px_20px_rgba(2,6,23,0.06)] font-black text-[13px] px-[14px] py-[10px] text-slate-900/92 transition-all flex items-center gap-2">
                                <Layers class="w-4 h-4" style="color:#0ea5e9"></Layers>
                                Any part of LinkUp
                            </span>
                            <span
                                class="rounded-full border border-slate-200/50 bg-white/78 shadow-[0_10px_20px_rgba(2,6,23,0.06)] font-black text-[13px] px-[14px] py-[10px] text-slate-900/92 transition-all flex items-center gap-2">
                                <ShieldAlert class="w-4 h-4" style="color:#22c55e"></ShieldAlert>
                                Safety review
                            </span>
                        </div>
                    </div>

                    <!-- Step 1 -->
                    <div v-if="reportStep === 1" class="p-5">
                        <div class="space-y-2.5">
                            <button v-for="reason in reportReasons" :key="reason.id" type="button"
                                @click="selectReportReason(reason.id)"
                                class="w-full rounded-[18px] border border-slate-200/70 bg-white/76 shadow-[0_10px_18px_rgba(2,6,23,0.06)] px-4 py-3 flex items-center gap-3 text-left transition-all hover:brightness-105 active:translate-y-px active:scale-[0.995]"
                                :class="selectedReportReason === reason.id ? 'border-blue-500/55 bg-gradient-to-b from-blue-500/12 to-lime/8 shadow-[0_16px_26px_rgba(14,165,233,0.12)]' : ''">

                                <!-- Radio dot -->
                                <div
                                    class="w-[22px] h-[22px] rounded-full border-[3px] border-blue-500/95 grid place-items-center bg-white/80 shadow-[0_10px_18px_rgba(2,6,23,0.08)] flex-shrink-0">
                                    <span
                                        class="w-[10px] h-[10px] rounded-full bg-blue-500/95 shadow-[0_0_0_6px_rgba(14,165,233,0.14)] transition-transform"
                                        :style="selectedReportReason === reason.id ? 'transform: scale(1)' : 'transform: scale(0)'"></span>
                                </div>

                                <!-- Icon -->
                                <div class="w-9 h-9 rounded-2xl grid place-items-center"
                                    style="background: rgba(14,165,233,0.10); border:1px solid rgba(14,165,233,0.22); box-shadow: 0 10px 18px rgba(2,6,23,0.06);">
                                    <component :is="reason.icon" class="w-4.5 h-4.5" style="color:#0ea5e9"></component>
                                </div>

                                <!-- Text -->
                                <div class="flex-1 min-w-0">
                                    <div class="text-[16px] font-black text-slate-900">{{ reason.label }}</div>
                                    <div class="text-[12px] text-slate-500 font-semibold mt-0.5 truncate">
                                        {{ reason.context.slice(0, 3).join(' • ') }}
                                        {{ reason.context.length > 3 ? ' •…' : '' }}
                                    </div>
                                </div>

                                <!-- Chevron -->
                                <div class="opacity-70">
                                    <ChevronRight class="w-5 h-5"></ChevronRight>
                                </div>
                            </button>
                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            <button @click="closeReportModal"
                                class="rounded-full border border-slate-200/50 bg-white/82 shadow-[0_10px_22px_rgba(2,6,23,0.10)] font-black text-[15px] px-4 py-3 flex-1 transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]">
                                Cancel
                            </button>
                            <button @click="goToReportStep2" :disabled="!selectedReportReason"
                                class="rounded-full bg-gradient-to-br from-blue-500/96 to-blue-600/92 border border-white/22 text-white font-black text-[15px] px-4 py-3 flex-[1.2] shadow-[0_16px_34px_rgba(14,165,233,0.22)] transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]"
                                :style="selectedReportReason ? 'opacity: 1; cursor: pointer;' : 'opacity: 0.55; cursor: not-allowed;'">
                                Continue
                            </button>
                        </div>

                        <p class="mt-3 text-[12px] text-slate-500 font-semibold">
                            Tip: Keep it simple—choose the closest category. You can add details next.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div v-if="reportStep === 2" class="p-5">
                        <div
                            class="bg-white/62 border border-white/40 shadow-[0_14px_30px_rgba(2,6,23,0.12)] backdrop-blur-[16px] saturate-[1.6] rounded-[22px] p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-[12px] text-slate-600 font-extrabold uppercase tracking-wide">
                                        Selected
                                    </div>
                                    <div class="text-[18px] font-black text-slate-900 mt-1">
                                        {{reportReasons.find(r => r.id === selectedReportReason)?.label || '—'}}
                                    </div>
                                    <div class="text-[13px] text-slate-700 font-semibold mt-1">Optional: tell us where
                                        this
                                        happened.</div>
                                </div>
                                <button @click="backToReportStep1"
                                    class="rounded-full border border-slate-200/50 bg-white/82 shadow-[0_10px_22px_rgba(2,6,23,0.10)] font-black text-[13px] px-3 py-2 transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]">
                                    <ArrowLeft class="w-4 h-4"></ArrowLeft>
                                </button>
                            </div>

                            <!-- Context chips -->
                            <div v-if="reportReasons.find(r => r.id === selectedReportReason)?.context?.length"
                                class="mt-3 flex flex-wrap gap-2">
                                <button v-for="area in reportReasons.find(r => r.id === selectedReportReason)?.context"
                                    :key="area" type="button" @click="toggleContextArea(area)"
                                    class="rounded-full border border-slate-200/50 bg-white/72 shadow-[0_10px_18px_rgba(2,6,23,0.06)] font-black text-[13px] px-[10px] py-[10px] text-slate-900/92 transition-all flex items-center gap-2"
                                    :class="selectedContextAreas.includes(area) ? 'border-blue-500/55 bg-gradient-to-b from-blue-500/12 to-lime/8 shadow-[0_16px_26px_rgba(14,165,233,0.12)]' : ''">
                                    <Check class="w-4 h-4" style="color:#0ea5e9"></Check>
                                    {{ area }}
                                </button>
                            </div>
                            <div v-else class="mt-3 text-[13px] text-slate-600 font-semibold">
                                No additional context needed.
                            </div>

                            <textarea v-model="reportDetails" rows="3"
                                class="mt-3 w-full rounded-[18px] border border-slate-200/70 bg-white/85 px-4 py-3 text-[14px] font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:shadow-[0_0_0_4px_rgba(14,165,233,0.18)] focus:border-blue-500/55 transition-all"
                                placeholder="Add details (optional): what happened, order/ticket ID, transaction amount, message text, etc."
                                @input="updateReportDetailsCount"></textarea>

                            <div
                                class="mt-2 flex items-center justify-between text-[12px] text-slate-500 font-semibold">
                                <span class="flex items-center gap-2">
                                    <Lock class="w-4 h-4"></Lock>
                                    Private—used for safety + fraud review.
                                </span>
                                <span>{{ reportDetails.length }}/500</span>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            <button @click="closeReportModal"
                                class="rounded-full border border-slate-200/50 bg-white/82 shadow-[0_10px_22px_rgba(2,6,23,0.10)] font-black text-[15px] px-4 py-3 flex-1 transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]">
                                Cancel
                            </button>
                            <button @click="submitReport"
                                class="rounded-full bg-gradient-to-br from-blue-500/96 to-blue-600/92 border border-white/22 text-white font-black text-[15px] px-4 py-3 flex-[1.2] shadow-[0_16px_34px_rgba(14,165,233,0.22)] transition-all hover:brightness-105 active:translate-y-px active:scale-[0.99]">
                                Submit
                            </button>
                        </div>

                        <p class="mt-3 text-[12px] text-slate-500 font-semibold">
                            If this involves payments, LinkUp may request receipts, screenshots, or transaction IDs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Gift Dialog -->
        <GiftDialog ref="giftDialogRef" :user="selectedUser" :balance="user?.coins || 0" />
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import FriendRequestButton from '@/components/admin/FriendRequestButton.vue';
import GenralDialog from '@/components/front/GenralDialog.vue';
import GiftDialog from '@/components/front/GiftDialog.vue';
import { route } from 'ziggy-js';
import {
    UserX,
    Ban,
    X,
    ShieldAlert,
    AlertCircle,
    AlertTriangle,
    Bell,
    HelpCircle,
    Lock,
    Info,
    SearchX,
    BellOff,
    Settings,
    Flag,
    TriangleAlert,
    MessageCircleWarning,
    UserRoundX,
    Siren,
    PackageX,
    FileWarning,
    ChevronRight,
    ArrowLeft,
    Check,
    Layers
} from 'lucide-vue-next';

const props = defineProps<{
    userdata: any,
    isFriends: boolean,
    isFriendRequestSent: boolean
}>();

const allPhotos = computed(() => {
    const photos = [];
    if (props.userdata.avatar) photos.push(props.userdata.avatar);
    if (Array.isArray(props.userdata.more_photos)) photos.push(...props.userdata.more_photos);
    return photos.length > 0 ? photos : ['default-avatar.png'];
});

const currentIdx = ref(0);
const isMenuOpen = ref(false);
const DialogCloseEdit = ref(false);

const giftDialogRef = ref();
const selectedUser = ref();
const interactForm = useForm({});

const handleLike = () => {
    interactForm.post(route('frontend.profile.like', props.userdata.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`You liked ${props.userdata.name}! ❤️`);
            router.visit(route('frontend.find.matches'));
        },
        onError: (err) => {
            console.error(err);
            toast.error("Failed to like user");
        }
    });
};

const handlePass = () => {
    interactForm.post(route('frontend.profile.dislike', props.userdata.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`You passed on ${props.userdata.name} 👎`);
            router.visit(route('frontend.find.matches'));
        },
        onError: (err) => {
            console.error(err);
            toast.error("Failed to pass user");
        }
    });
};

const handleGift = () => {
    selectedUser.value = props.userdata;
    giftDialogRef.value.open();
};

const page = usePage();
const user = (page.props as any)?.auth?.user;
const profile = user?.hide_profile;
const hideProfile = ref(false); // This will be for user-specific hiding

const hideProfileFromUser = () => {
    const confirmed = confirm(`Are you sure you want to hide ${props.userdata.name}?`);

    if (!confirmed) {
        return; // User clicked "No", do nothing
    }

    console.log('Hide profile confirmed, calling backend...');

    // Call backend to hide profile
    const hideForm = useForm({
        user_id: props.userdata.id,
        target_user_id: props.userdata.id
    });

    hideForm.post('/profile/hide-from-user', {
        onSuccess: (response: any) => {
            console.log('Hide profile success response:', response);
            hideProfile.value = true;
            // Show success message
            alert('Profile hidden successfully!');
            // Force button state update
            setTimeout(() => {
                // This will trigger Vue's reactivity
                console.log('hideProfile.value after timeout:', hideProfile.value);
            }, 100);
        },
        onError: (errors: any) => {
            console.log('Hide profile error:', errors);
            alert('Error hiding profile. Please try again.');
        }
    });
};

// Check if current user has hidden this profile
const isProfileHidden = ref(false);

// Block user functionality
const isBlocked = ref(false);
const isBlocking = ref(false); // Loading state for blocking/unblocking

// Block modal state
const showBlockModal = ref(false);
const selectedReason = ref<string | null>(null);
const blockNote = ref('');
const blockReasons = [
    { id: 'spam', label: 'Spam / Scam', icon: ShieldAlert },
    { id: 'harassment', label: 'Harassment', icon: AlertCircle },
    { id: 'fake', label: 'Fake profile', icon: UserX },
    { id: 'inappropriate', label: 'Inappropriate', icon: AlertTriangle },
    { id: 'threats', label: 'Threats', icon: Bell },
    { id: 'other', label: 'Other', icon: HelpCircle },
];

// Report functionality
const showReportModal = ref(false);
const reportStep = ref(1);
const selectedReportReason = ref<string | null>(null);
const selectedContextAreas = ref<string[]>([]);
const reportDetails = ref('');
const reportReasons = [
    {
        id: "scam_fraud",
        label: "Scam / Fraud / Suspicious Payments",
        icon: ShieldAlert,
        context: ["Wallet", "Marketplace", "Tickets & Events", "Subscriptions", "Crypto/Transfers"]
    },
    {
        id: "harassment",
        label: "Harassment / Bullying / Hate",
        icon: MessageCircleWarning,
        context: ["Chat", "Comments", "Live", "Profile"]
    },
    {
        id: "explicit",
        label: "Sexual Content / Unwanted Advances",
        icon: TriangleAlert,
        context: ["Chat", "Profile", "Live", "Images/Videos"]
    },
    {
        id: "impersonation",
        label: "Fake Profile / Impersonation",
        icon: UserRoundX,
        context: ["Profile", "Verification", "Photos"]
    },
    {
        id: "threats",
        label: "Threats / Violence / Extortion",
        icon: Siren,
        context: ["Chat", "Calls", "Live", "Off-platform threats"]
    },
    {
        id: "privacy",
        label: "Privacy / Doxxing / Blackmail",
        icon: Lock,
        context: ["Shared personal info", "Screenshots", "Address/Phone", "Nudes/Leaks"]
    },
    {
        id: "illegal_goods",
        label: "Prohibited or Illegal Goods",
        icon: PackageX,
        context: ["Marketplace", "Listings", "Drop-off/Meetups"]
    },
    {
        id: "terms",
        label: "Other Policy Violation",
        icon: FileWarning,
        context: ["Spam", "Content rules", "Event rules", "Multiple accounts"]
    }
];

// Initialize block status immediately in setup
isProfileHidden.value = props.userdata.is_hidden_by_current_user || false;
isBlocked.value = props.userdata.is_blocked_by_current_user || false;

// Check if this profile is hidden by current user
onMounted(() => {
    // Add click listener for menu
    window.addEventListener('click', () => isMenuOpen.value = false);
});

const toggleMenu = () => isMenuOpen.value = !isMenuOpen.value;

function confirmToast(message: string, onYes: () => void) {
    toast(message, {
        action: {
            label: 'Yes',
            onClick: () => {
                onYes();
            }
        },
        cancel: {
            label: 'No'
        },
        duration: Infinity
    });
}

// Block modal functions
const openBlockModal = async () => {
    showBlockModal.value = true;
    selectedReason.value = null;
    blockNote.value = '';
    document.body.style.overflow = 'hidden';
};

const closeBlockModal = () => {
    showBlockModal.value = false;
    document.body.style.overflow = '';
};

// Report modal functions
const openReportModal = () => {
    showReportModal.value = true;
    reportStep.value = 1;
    selectedReportReason.value = null;
    selectedContextAreas.value = [];
    reportDetails.value = '';
    document.body.style.overflow = 'hidden';
};

const closeReportModal = () => {
    showReportModal.value = false;
    document.body.style.overflow = '';
};

const selectReportReason = (reasonId: string) => {
    selectedReportReason.value = reasonId;
};

const toggleContextArea = (area: string) => {
    const index = selectedContextAreas.value.indexOf(area);
    if (index > -1) {
        selectedContextAreas.value.splice(index, 1);
    } else {
        selectedContextAreas.value.push(area);
    }
};

const goToReportStep2 = () => {
    if (selectedReportReason.value) {
        reportStep.value = 2;
    }
};

const backToReportStep1 = () => {
    reportStep.value = 1;
};

const submitReport = () => {
    const selectedReason = reportReasons.find(r => r.id === selectedReportReason.value);

    const formData = {
        reported_user_id: props.userdata.id,
        reason: selectedReason?.label || '',
        selected_reason: JSON.stringify(selectedContextAreas.value),
        message: reportDetails.value,
    };

    router.post(route('frontend.flag.user'), formData, {
        onSuccess: () => {
            showReportModal.value = false;
            window.location.reload();
        },
        onError: (errors) => {
            console.error('Failed to report user:', errors);
            alert('ERROR: Failed to report user - ' + JSON.stringify(errors));
        }
    });
};

const updateReportDetailsCount = () => {
    const max = 500;
    if (reportDetails.value.length > max) {
        reportDetails.value = reportDetails.value.slice(0, max);
    }
};

const updateBlockNoteCount = () => {
    const max = 280;
    if (blockNote.value.length > max) {
        blockNote.value = blockNote.value.slice(0, max);
    }
};

const confirmBlock = () => {
    if (isBlocking.value) return;

    isBlocking.value = true;

    router.post(route('frontend.profile.block.user'), {
        blocked_user_id: props.userdata.id,
        reason: selectedReason.value || 'unspecified',
        note: blockNote.value.trim()
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isBlocked.value = true;
            isBlocking.value = false;
            closeBlockModal();
            toast.dismiss();
            toast.success("User blocked successfully!");
        },
        onError: (errors) => {
            console.error('Failed to block user:', errors);
            isBlocking.value = false;
            toast.error("Failed to block user");
        }
    });
};

const unblockUser = () => {
    confirmToast(`Do you want to unblock ${props.userdata.name}?`, () => {
        if (isBlocking.value) return;

        isBlocking.value = true;

        router.post(route('frontend.profile.unblock.user'), {
            blocked_user_id: props.userdata.id
        }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                isBlocked.value = false;
                isBlocking.value = false;
                toast.dismiss();
                toast.success("User unblocked successfully!");
            },
            onError: (errors) => {
                console.error('Failed to unblock user:', errors);
                isBlocking.value = false;
                toast.error("Failed to unblock user");
            }
        });
    });
};

// Initialize Lucide icons after component mount
onMounted(() => {
    window.addEventListener('click', () => isMenuOpen.value = false);
});

// Watch for modal open/close to reinitialize icons
watch(showBlockModal, () => {
    // Icons are now Vue components, no need to initialize
});

// Watch and emit change to backend - for user-specific hiding
watch(hideProfile, (newValue) => {
    console.log('Hide profile toggled for user:', props.userdata.id, newValue);

    router.post(route("profile.hide.from.user"), {
        hidden_user_id: props.userdata.id,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            console.log('Profile hide status updated successfully');
            // Update local state
            isProfileHidden.value = newValue;
        },
        onError: (errors) => {
            console.error('Failed to update profile hide status:', errors);
            // Revert the toggle state if there's an error
            hideProfile.value = !newValue;
        }
    });
});

// Listen for changes from other components
onMounted(() => {
    window.addEventListener('hideProfileChanged', (event: any) => {
        hideProfile.value = event.detail.hideProfile;
    });

    // Initial sync from localStorage
    const stored = localStorage.getItem('hide_profile_state');
    if (stored !== null) {
        hideProfile.value = stored === 'true';
    }

    // Add click listener for menu
    window.addEventListener('click', () => isMenuOpen.value = false);
});

const nextPhoto = () => currentIdx.value = (currentIdx.value + 1) % allPhotos.value.length;
const prevPhoto = () => currentIdx.value = (currentIdx.value - 1 + allPhotos.value.length) % allPhotos.value.length;

function getPhotoUrl(photo: string) {
    if (!photo) return '';
    return photo.startsWith('http') ? photo : `/storage/${photo}`;
}
</script>

<style scoped>
.profile-wrap {
    margin-top: 3.4%;
    background: linear-gradient(180deg, #2fa4e6 0%, #2fa4e6 170px, #f5f7fb 170px);
    padding: 26px 20px 100px;
    min-height: 100vh;
}

.shell {
    max-width: 1280px;
    margin: 5% auto;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(12px);
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 18px 45px rgba(2, 6, 23, .10);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.top-header {
    position: sticky;
    top: 0;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: rgba(255, 255, 255, 0.8);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.user-identity {
    display: flex;
    align-items: center;
    gap: 15px;
}

.mini-avatar {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #ddd;
}

.mini-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-name {
    font-size: 24px;
    font-weight: 900;
    color: #334155;
}

.status-pill {
    font-size: 14px;
    font-weight: 800;
    background: #fff;
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #334155;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
}

.country-pulse {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    background: rgba(14, 165, 233, 0.1);
    color: #0369a1;
    font-weight: 800;
    animation: pulse 2s infinite;
}

.sub-info {
    color: #64748b;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 12px;
    position: relative;
    align-items: center;
}

.btn-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    border: 1px solid #ddd;
    background: white;
    display: grid;
    place-items: center;
    cursor: pointer;
}

.btn-linked {
    background: #22c55e;
    color: white;
    padding: 8px 16px;
    border-radius: 12px;
    font-weight: bold;
}

.btn-sent {
    background: #64748b;
    color: white;
    padding: 8px 16px;
    border-radius: 12px;
    font-weight: bold;
}

.btn-blocked {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
    padding: 8px 18px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 14px;
}

.btn-block {
    background-color: #dc2626;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-block:hover {
    background-color: #b91c1c;
}

.btn-block:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

.btn-report {
    background-color: #3b82f6;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-report:hover {
    background-color: #2563eb;
}

.profile-content {
    display: grid;
    grid-template-columns: 460px 1fr;
    gap: 24px;
    padding: 24px;
}

.hero-container {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    background: #000;
    height: 550px;
}

.main-hero-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-overlay-top {
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    display: flex;
    justify-content: space-between;
}

.verified-tag {
    background: rgba(255, 255, 255, 0.9);
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 14px;
    color: #334155;
}

.nav-arrow {
    background: rgba(0, 0, 0, 0.4);
    color: white;
    padding: 5px 12px;
    border-radius: 10px;
    cursor: pointer;
}

.hero-overlay-bottom {
    position: absolute;
    bottom: 15px;
    left: 15px;
    right: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.photo-dots {
    display: flex;
    gap: 6px;
    background: rgba(0, 0, 0, 0.2);
    padding: 8px;
    border-radius: 20px;
}

.dot {
    width: 6px;
    height: 6px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
}

.dot.active {
    width: 18px;
    background: white;
    border-radius: 10px;
}

.photo-counter {
    color: white;
    background: rgba(0, 0, 0, 0.4);
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 800;
}

.thumbnail-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-top: 15px;
}

.thumb-box {
    aspect-ratio: 1;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid transparent;
    cursor: pointer;
}

.thumb-box.active {
    border-color: #0ea5e9;
}

.thumb-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.details-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.info-card {
    background: white;
    padding: 20px;
    border-radius: 24px;
    border: 1px solid #eee;
    color: #334155;
}

.card-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-weight: 900;
    font-size: 18px;
    color: #334155;
}

.hint {
    color: #64748b;
    font-size: 14px;
}

.bio-text {
    line-height: 1.6;
    color: #334155;
    font-size: 17px;
}

.chip-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.interest-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    padding: 8px 16px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    font-weight: 700;
    font-size: 15px;
    margin: 0;
    color: #334155;
}

.primary-brand-chip {
    background: rgba(14, 165, 233, 0.08);
    border-color: rgba(14, 165, 233, 0.3);
    color: #075985;
}

.chip-icon-box {
    width: 22px;
    height: 22px;
    background: rgba(14, 165, 233, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

.kv-grid {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 15px;
}

.label {
    color: #64748b;
    font-weight: 800;
}

.value {
    font-weight: 900;
    color: #334155;
}

.uni-badge-pulse {
    display: inline-block;
    background: #7c3aed28;
    color: #0f172a;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 900;
    border: 1px solid #7c3aed;
    animation: uniBeat 1.5s infinite;
}

.custom-dropdown {
    position: absolute;
    top: 55px;
    right: 0;
    width: 240px;
    background: white;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #eee;
    overflow: hidden;
    padding: 8px;
}

.menu-item {
    display: block;
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    cursor: pointer;
}

.menu-item:hover {
    background: #f8fafc;
}

/* Light-themed action buttons matching the home cards layout */
.action-buttons-container {
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 16px;
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
}

.action-btn-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: transform 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    cursor: pointer;
    border: none;
}

.action-btn-circle:hover {
    transform: scale(1.1);
}

.action-btn-circle:active {
    transform: scale(0.9);
}

.pass-btn {
    width: 46px;
    height: 46px;
    background: #ffffff;
    color: #9ca3af;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.pass-btn:hover {
    color: #ef4444;
    border-color: #fca5a5;
    box-shadow: 0 6px 14px rgba(239, 68, 68, 0.15);
}

.like-btn {
    width: 46px;
    height: 46px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.like-btn:hover {
    box-shadow: 0 6px 14px rgba(244, 63, 94, 0.15);
    border-color: #fecdd3;
}

.gift-btn {
    width: 58px;
    height: 58px;
    background: linear-gradient(135deg, #a855f7, #7c3aed);
    box-shadow: 0 6px 18px rgba(124, 58, 237, 0.25);
    border: 3px solid #ffffff;
}

.gift-btn:hover {
    box-shadow: 0 8px 22px rgba(124, 58, 237, 0.4);
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }

    100% {
        transform: scale(1);
    }
}

@keyframes uniBeat {
    0% {
        transform: scale(1);
    }

    30% {
        transform: scale(1.03);
    }

    100% {
        transform: scale(1);
    }
}

.fadeIn {
    animation: fade 0.4s ease-out forwards;
}

@keyframes fade {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

.popIn {
    animation: popIn 0.18s ease-out;
    transform-origin: 50% 75%;
}

@keyframes popIn {
    from {
        opacity: 0;
        transform: translateY(10px) scale(0.985);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@media (max-width: 1024px) {
    .profile-content {
        grid-template-columns: 1fr;
    }

    .hero-container {
        height: 450px;
    }
}
</style>
