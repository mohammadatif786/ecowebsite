<style scoped>
body {
    background-color: #379ce8;
}

/* ── Gift Receiver Overlay ──────────────────────────────── */
.rxOverlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .38);
    backdrop-filter: blur(14px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    z-index: 200;
}

.rxOverlay.show {
    display: flex;
}

.rxCard {
    width: min(760px, 94vw);
    border-radius: 28px;
    background: rgba(255, 255, 255, .84);
    border: 1px solid rgba(148, 163, 184, .28);
    box-shadow: 0 24px 80px rgba(2, 6, 23, .18);
    overflow: hidden;
    position: relative;
}

.rxTop {
    padding: 18px 18px 14px;
    background: linear-gradient(135deg, rgba(236, 72, 153, .14), rgba(14, 165, 233, .14), rgba(223, 255, 0, .10));
    border-bottom: 1px solid rgba(148, 163, 184, .22);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.rxTitle {
    font-weight: 1100;
    font-size: 22px;
    line-height: 1.1;
}

.rxClose {
    width: 44px;
    height: 44px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, .28);
    background: rgba(255, 255, 255, .68);
    cursor: pointer;
    font-weight: 1000;
    color: #64748b;
    flex-shrink: 0;
}

.rxBody {
    padding: 18px;
    text-align: center;
    background: rgba(255, 255, 255, .72);
}

.rxBigEmoji {
    font-size: 78px;
    filter: drop-shadow(0 18px 22px rgba(0, 0, 0, .18));
    animation: popIn 520ms ease both;
}

.rxHeart {
    display: inline-block;
    margin-top: 10px;
    font-size: 26px;
    animation: heartPulse 900ms ease-in-out infinite;
}

.rxMsg {
    margin-top: 12px;
    font-weight: 1000;
    font-size: 18px;
}

.rxSub {
    margin-top: 8px;
    color: #64748b;
    font-weight: 900;
    font-size: 13px;
    line-height: 1.35;
}

.rxFrom {
    margin-top: 12px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 999px;
    background: rgba(14, 165, 233, .10);
    border: 1px solid rgba(14, 165, 233, .18);
    font-weight: 1000;
}

.rxActions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 16px;
}

.btn {
    border: none;
    cursor: pointer;
    border-radius: 999px;
    padding: 12px 14px;
    font-weight: 1100;
    min-height: 44px;
}

.btn.primary {
    color: #000000;
    background: linear-gradient(135deg, rgba(14, 165, 233, .96), rgba(14, 165, 233, .72));
    box-shadow: 0 18px 44px rgba(14, 165, 233, .20);
}

.btn.ghost {
    background: rgba(255, 255, 255, .68);
    border: 1px solid rgba(148, 163, 184, .28);
    color: #64748b;
}

.confetti {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.confetti i {
    position: absolute;
    top: -12px;
    width: 10px;
    height: 18px;
    border-radius: 3px;
    opacity: .9;
    animation: fall 1200ms linear forwards;
}

@keyframes fall {
    to {
        transform: translateY(110vh) rotate(320deg);
        opacity: 1;
    }
}

@keyframes popIn {
    0% {
        opacity: 0;
        transform: translateY(8px) scale(.82);
    }

    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes heartPulse {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.18);
    }
}

/* Quick Reply Composer */
.rxComposer {
    margin-top: 16px;
    text-align: left;
    border-top: 1px solid rgba(148, 163, 184, .22);
    padding-top: 14px;
}

.rxComposerLabel {
    font-weight: 1100;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.pillMini {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 999px;
    background: rgba(223, 255, 0, .22);
    border: 1px solid rgba(223, 255, 0, .42);
    font-weight: 1100;
    color: #0f172a;
    font-size: 12px;
    white-space: nowrap;
}

.rxBubble {
    background: rgba(14, 165, 233, .10);
    border: 1px solid rgba(14, 165, 233, .18);
    border-radius: 18px;
    padding: 10px 12px;
    box-shadow: 0 12px 26px rgba(2, 6, 23, .06);
}

#rxReplyText {
    width: 100%;
    border: none;
    outline: none;
    resize: vertical;
    background: transparent;
    font-weight: 900;
    color: #0f172a;
    font-size: 14px;
    line-height: 1.35;
    font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
}

.rxComposerActions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
    margin-top: 10px;
}

.rxTinyHint {
    margin-top: 8px;
    font-size: 12px;
    color: #64748b;
    font-weight: 850;
}

/* ── Friend Request Modal ──────────────────────────────── */
.frOverlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .38);
    backdrop-filter: blur(14px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    z-index: 260;
}

.frOverlay.show {
    display: flex;
}

.frCard {
    width: min(680px, 94vw);
    border-radius: 26px;
    background: rgba(255, 255, 255, .86);
    border: 1px solid rgba(148, 163, 184, .28);
    box-shadow: 0 24px 80px rgba(2, 6, 23, .18);
    overflow: hidden;
}

.frTop {
    padding: 16px 18px 14px;
    background: linear-gradient(135deg, rgba(223, 255, 0, .32), rgba(223, 255, 0, .18));
    border-bottom: 1px solid rgba(148, 163, 184, .22);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.frTitle {
    font-weight: 1100;
    font-size: 18px;
    line-height: 1.1;
}

.frClose {
    width: 44px;
    height: 44px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, .28);
    background: rgba(255, 255, 255, .68);
    cursor: pointer;
    font-weight: 1000;
    color: #64748b;
    flex-shrink: 0;
}

.frBody {
    padding: 16px 18px 18px;
    background: rgba(255, 255, 255, .74);
}

.frRow {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.frToPill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 999px;
    background: rgba(14, 165, 233, .10);
    border: 1px solid rgba(14, 165, 233, .18);
    font-weight: 1100;
}

.frBadge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 999px;
    background: rgba(223, 255, 0, .22);
    border: 1px solid rgba(223, 255, 0, .42);
    font-weight: 1100;
    color: #0f172a;
    font-size: 12px;
    white-space: nowrap;
}

.frField {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.frLabel {
    font-weight: 1000;
    font-size: 12px;
    color: #64748b;
}

.frInput {
    border: 1px solid rgba(148, 163, 184, .28);
    background: rgba(255, 255, 255, .82);
    border-radius: 16px;
    padding: 12px 12px;
    font-weight: 900;
    outline: none;
    font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
}

.frInput:focus {
    border-color: rgba(14, 165, 233, .45);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .14);
}

.frActions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
    margin-top: 12px;
}


/* ══════════════════════════════════════════════════════════
   PROFILE DROPDOWN (avatar → hide-profile toggle + logout)
   ══════════════════════════════════════════════════════════ */
.profileMenu {
    text-align: left;
}

.profileMenuRow {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 16px;
    border-bottom: 1px solid rgba(148, 163, 184, .16);
}

.profileMenuLogout {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 10px;
    padding: 13px 16px;
    font-size: 14px;
    font-weight: 700;
    color: #e11d48;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background 120ms ease;
}

.profileMenuLogout:hover {
    background: rgba(225, 29, 72, .06);
}

/* ══════════════════════════════════════════════════════════
   MOBILE BOTTOM NAVIGATION BAR
   ══════════════════════════════════════════════════════════ */
.mobileBottomNav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 100;
    background: #ffffff;
    border-top: 1px solid rgba(148, 163, 184, .16);
    box-shadow: 0 -6px 28px rgba(2, 6, 23, .10);
    display: none;
    align-items: flex-end;
    justify-content: space-around;
    padding: 6px 4px;
    padding-bottom: max(6px, env(safe-area-inset-bottom));
}

.mobileNavItem {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    padding: 6px 8px;
    border-radius: 14px;
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
    font-family: inherit;
    min-width: 48px;
    min-height: 44px;
    -webkit-tap-highlight-color: transparent;
    text-decoration: none;
    transition: color 120ms ease, background 120ms ease;
    position: relative;
}

.mobileNavItem i {
    font-size: 22px;
    line-height: 1;
}

.mobileNavLabel {
    font-size: 9px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: 0.02em;
}

.premiumNavBtn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    padding: 0 6px;
    border: none;
    background: transparent;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    font-family: inherit;
}

.premiumBtnCircle {
    width: 52px;
    height: 52px;
    margin-top: -18px;
    border-radius: 50%;
    background: linear-gradient(135deg, #379ce8, #1a6eb0);
    box-shadow: 0 6px 22px rgba(55, 156, 232, .44);
    border: 3px solid #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.premiumBtnCircle i {
    font-size: 20px;
}

.premiumBtnLabel {
    font-size: 9px;
    font-weight: 900;
    color: #379ce8;
    line-height: 1;
    margin-top: 1px;
}

.notifDot {
    position: absolute;
    top: 2px;
    right: 4px;
    min-width: 16px;
    height: 16px;
    padding: 0 3px;
    border-radius: 999px;
    background: #f43f5e;
    color: #fff;
    font-size: 9px;
    font-weight: 900;
    line-height: 16px;
    text-align: center;
}

.main-content-style {
    margin-left: -4%;
    margin-top: -3.5%;
}

/* ══════════════════════════════════════════════════════════
   MOBILE RESPONSIVE OVERRIDES  (≤ 1280 px)
   ══════════════════════════════════════════════════════════ */
@media (max-width: 1280px) {

    .main-content-style {
        margin-left: 0%;
        margin-top: 0%;
    }

    .mobileBottomNav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 100;
        background: #ffffff;
        border-top: 1px solid rgba(148, 163, 184, .16);
        box-shadow: 0 -6px 28px rgba(2, 6, 23, .10);
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        padding: 6px 4px;
        padding-bottom: max(6px, env(safe-area-inset-bottom));
    }

    /* Gift overlay → bottom sheet */
    .rxOverlay,
    .frOverlay {
        align-items: flex-end;
        padding: 0;
    }

    .rxCard,
    .frCard {
        width: 100%;
        max-width: 100%;
        border-radius: 22px 22px 0 0;
        max-height: 92dvh;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    .rxTop,
    .frTop {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .rxBigEmoji {
        font-size: 52px;
    }

    .rxTitle {
        font-size: 17px;
    }

    .rxMsg {
        font-size: 15px;
    }

    .rxSub {
        font-size: 12px;
    }

    .frTitle {
        font-size: 16px;
    }

    .rxActions,
    .rxComposerActions,
    .frActions {
        flex-direction: column;
        align-items: stretch;
    }

    .rxActions .btn,
    .rxComposerActions .btn,
    .frActions .btn {
        width: 100%;
        min-height: 48px;
        padding: 14px;
    }

    #rxReplyText,
    .frInput {
        font-size: 16px !important;
    }

    .rxFrom {
        flex-wrap: wrap;
        justify-content: center;
        text-align: center;
    }

    .frRow {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .frBadge {
        white-space: normal;
    }
}

/* Very small phones (≤ 380 px) */
@media (max-width: 380px) {
    .rxBigEmoji {
        font-size: 42px;
    }

    .rxBody {
        padding: 14px;
    }

    .frBody {
        padding: 14px 14px 16px;
    }
}
</style>

<template>
    <div class="min-h-screen bg-[#379ce8] font-sans pb-18 xl:pb-0">

        <GoogleTranslateSelect />

        <div style="background-color: royalblue;">
            <!-- ───────────────── NAVBAR ───────────────── -->
            <nav class="xl:px-5">
                <div class="mx-auto pt-4 xl:pt-12 xl:pb-8 xl:px-21.25">

                    <!-- ══════════════════════════════════════════════════
                         DESKTOP NAV — 3-column grid for true centering
                         LEFT: Logo | CENTER: Nav links | RIGHT: Actions
                         ══════════════════════════════════════════════════ -->
                    <div class="hidden xl:grid xl:grid-cols-3 xl:items-center w-full text-[18px] text-white">

                        <!-- ── LEFT: Logo ── -->
                        <div class="flex items-center">
                            <ApplicationLogo />
                        </div>

                        <!-- ── CENTER: Navigation links ── -->
                        <div class="flex items-center justify-center gap-6 xl:gap-8">
                            <Link :href="route('frontend.find.matches')"
                                class="rounded-md px-3 py-2 font-bold transition hover:bg-white hover:text-[#379ce8] whitespace-nowrap"
                                style="font-size: 80%;">
                                Find Matches
                            </Link>

                            <p class="cursor-pointer rounded-xl bg-white px-5 py-2 font-bold text-[#379ce8] transition hover:bg-[#2f85d0] hover:text-white whitespace-nowrap"
                                @click="handleSubClick" style="font-size: 80%;">
                                Go Premium
                            </p>

                            <Link v-if="isOrganizer"
                                :href="isOrganizer ? route('organizer.event.create') : organizerDashboardRoute"
                                class="rounded-md px-3 py-2 font-semibold transition hover:bg-white hover:text-[#379ce8] whitespace-nowrap"
                                style="font-size: 80%;">
                                Create Events
                            </Link>
                            <button v-else @click="openPopUp()"
                                class="rounded-md px-3 py-2 font-semibold transition hover:bg-white hover:text-[#379ce8] whitespace-nowrap"
                                style="font-size: 80%;">
                                Create Events
                            </button>

                            <Link v-if="isOrganizer" :href="route('organizer.ticket.index')"
                                class="rounded-md px-3 py-2 font-bold transition hover:bg-white hover:text-[#379ce8] whitespace-nowrap"
                                style="font-size: 80%;">
                                My Tickets
                            </Link>
                            <Link v-else :href="route('frontend.bookings.upcomming')"
                                class="rounded-md px-3 py-2 font-bold transition hover:bg-white hover:text-[#379ce8] whitespace-nowrap"
                                style="font-size: 80%;">
                                My Tickets
                            </Link>

                            <button v-if="isOrganizer" @click="handleBack"
                                class="rounded-md px-3 py-2 font-bold transition hover:bg-white hover:text-[#379ce8] whitespace-nowrap"
                                style="font-size: 80%;">
                                Back
                            </button>
                        </div>

                        <!-- ── RIGHT: Credit · Coins · Chat · Notifications · Profile ── -->
                        <div class="flex items-center justify-end gap-5 xl:gap-6">
                            <p style="font-size: 80%;"
                                class="cursor-pointer rounded-full border-2 border-white px-2 py-1 font-bold transition hover:bg-white hover:text-[#379ce8] whitespace-nowrap"
                                @click="handleWalletClick">
                                Credit &nbsp; ${{ walletBalance }}
                            </p>

                            <p style="font-size: 80%;"
                                class="cursor-pointer rounded-full border-2 border-white px-2 py-1 font-bold transition hover:bg-white hover:text-[#379ce8] inline-flex items-center whitespace-nowrap"
                                @click="handleWalletClick">
                                Coins&nbsp;
                                <Coins /> {{ user?.coins }}
                            </p>

                            <i class="ri-discuss-line cursor-pointer text-1xl transition hover:text-gray-200"
                                @click="handleChatClick"></i>

                            <button type="button" class="relative" @click="handleNotificationClick">
                                <i
                                    class="ri-notification-line cursor-pointer text-1xl transition hover:text-gray-200"></i>
                                <span v-if="unreadNotificationsCount > 0"
                                    class="absolute -top-1 -right-1 min-w-4.5 h-4.5 rounded-full bg-rose-500 text-white text-[10px] font-extrabold leading-4.5 text-center shadow">
                                    {{ unreadNotificationsCount }}
                                </span>
                            </button>

                            <!-- Profile avatar dropdown -->
                            <div class="relative" ref="profileMenuDesktopRef">
                                <button type="button" @click="showProfileMenu = !showProfileMenu" aria-haspopup="true"
                                    :aria-expanded="showProfileMenu" class="flex items-center gap-1 rounded-full">
                                    <span
                                        class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-white/15">
                                        <img v-if="avatarUrl" :src="avatarUrl" alt="Profile"
                                            class="h-full w-full object-cover" />
                                        <i v-else class="ri-user-3-fill text-lg text-white"></i>
                                    </span>
                                    <i class="ri-arrow-down-s-line text-base text-white transition-transform"
                                        :class="{ 'rotate-180': showProfileMenu }"></i>
                                </button>

                                <div v-if="showProfileMenu" role="menu"
                                    class="profileMenu absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-slate-100 bg-white text-slate-800 shadow-[0_18px_50px_rgba(2,6,23,.18)]">
                                    <div class="profileMenuRow">
                                        <span class="text-sm font-semibold">Hide my profile</span>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" v-model="hideProfile">
                                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer
                                                        peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                                        peer-checked:after:border-white after:content-[''] after:absolute
                                                        after:top-0.5 after:inset-0.5 after:bg-white after:border
                                                        after:rounded-full after:h-5 after:w-5 after:transition-all
                                                        peer-checked:bg-[#D5DB2B]">
                                            </div>
                                        </label>
                                    </div>
                                    <button type="button" role="menuitem" class="profileMenuLogout"
                                        @click="handleLogout">
                                        <i class="ri-logout-box-r-line text-base"></i>
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══════════════════════════════════════════════════
                         MOBILE NAV — Logo left · Actions right
                         ══════════════════════════════════════════════════ -->
                    <div class="flex xl:hidden items-center justify-between w-full text-[18px] text-white">
                        <ApplicationLogo />

                        <!-- Mobile Top-Right: credit · coins · notif · hide-toggle · hamburger -->
                        <div class="flex items-center gap-2">

                            <button @click="handleWalletClick"
                                class="flex items-center gap-1 rounded-full border border-white/70 px-3 py-1.5 text-white text-[12px] font-bold active:bg-white/20">
                                ${{ walletBalance }}
                            </button>

                            <button @click="handleWalletClick"
                                class="flex items-center gap-1 rounded-full border border-white/70 px-2.5 py-1.5 text-white text-[12px] font-bold active:bg-white/20">
                                <Coins class="w-3.5 h-3.5" />
                                <span>{{ user?.coins ?? 0 }}</span>
                            </button>

                            <button type="button" class="relative p-1.5 text-white" @click="handleNotificationClick">
                                <i class="ri-notification-3-line text-[22px]"></i>
                                <span v-if="unreadNotificationsCount > 0"
                                    class="absolute top-0 right-0 min-w-4 h-4 px-0.5 rounded-full bg-rose-500 text-white text-[9px] font-extrabold leading-4 text-center shadow">
                                    {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                                </span>
                            </button>

                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-white transition hover:bg-white hover:text-[#379ce8]">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Responsive Navigation Menu (hamburger drawer) -->
                    <div :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }" class="xl:hidden">
                        <div class="pt-2 pb-3">
                            <Sidebar />
                        </div>
                    </div>
                </div>
            </nav>

            <!-- ───────────────── PAGE CONTENT ───────────────── -->
            <main class="mx-auto mt-4 xl:mt-6 flex gap-6 xl:px-20">
                <div class="hidden xl:block">
                    <Sidebar />
                </div>

                <div v-if="isOrganizer" class="w-full overflow-hidden rounded-lg px-2 xl:px-8">
                    <slot />
                </div>
                <div v-else class="w-full overflow-hidden rounded-lg px-2 shadow-md xl:px-8 main-content-style">
                    <slot />
                </div>
            </main>

            <Footer1 class="mt-12" />
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         MOBILE BOTTOM NAVIGATION BAR
         ══════════════════════════════════════════════════════ -->
    <nav class="mobileBottomNav xl:hidden" aria-label="Main navigation">

        <Link :href="route('frontend.find.matches')" class="mobileNavItem">
            <i class="ri-user-search-line"></i>
            <span class="mobileNavLabel">Matches</span>
        </Link>

        <button class="mobileNavItem" @click="handleSubClick" aria-label="Go Premium">
            <i class="ri-vip-crown-line"></i>
            <span class="mobileNavLabel">Premium</span>
        </button>

        <Link v-if="isOrganizer" :href="route('organizer.event.create')" class="premiumNavBtn"
            aria-label="Create Events">
            <div class="premiumBtnCircle">
                <i class="calendar-next-enter-active"></i>
            </div>
        </Link>
        <button v-else class="premiumNavBtn" @click="openPopUp" aria-label="Create Events">
            <div class="premiumBtnCircle">
                <i class="ri-calendar-2-fill"></i>
            </div>
            <span class="premiumBtnLabel">Events</span>
        </button>

        <Link v-if="isOrganizer" :href="route('organizer.ticket.index')" class="mobileNavItem">
            <i class="ri-ticket-2-line"></i>
            <span class="mobileNavLabel">Tickets</span>
        </Link>
        <Link v-else :href="route('frontend.bookings.upcomming')" class="mobileNavItem">
            <i class="ri-ticket-2-line"></i>
            <span class="mobileNavLabel">Tickets</span>
        </Link>

        <button v-if="isOrganizer" class="mobileNavItem" @click="handleBack" aria-label="Back">
            <i class="ri-arrow-go-back-line"></i>
            <span class="mobileNavLabel">Back</span>
        </button>
        <button v-else class="mobileNavItem" @click="handleChatClick" aria-label="Chat">
            <i class="ri-discuss-line"></i>
            <span class="mobileNavLabel">Chat</span>
        </button>

    </nav>

    <!-- ══════════════════════════════════════════════════════
         OTHER COMPONENTS & MODALS
         ══════════════════════════════════════════════════════ -->
    <LinkUpOrganizerPopup v-model="showPopup" @continue="handleContinue" @cancel="handleCancel" />
    <Toaster position="top-center" />

    <GiftDialog ref="giftDialogRef" :user="giftReplyUser" :balance="user?.coins || 0" />

    <!-- ── Gift Receiver Overlay ── -->
    <div class="rxOverlay" :class="{ show: showReceiver }" aria-live="polite" role="dialog" aria-modal="true">
        <div class="rxCard">
            <div class="confetti" id="confetti"></div>

            <div class="rxTop">
                <div class="rxTitle">Someone likes your vibe 💘</div>
                <button class="rxClose" @click="hideReceiver" aria-label="Close">✕</button>
            </div>

            <div class="rxBody">
                <div class="rxBigEmoji">{{ receivedGift?.emoji || '🎁' }}</div>
                <div class="rxHeart">💖</div>
                <div class="rxMsg">You just got a gift!</div>
                <div class="rxSub">That's a clear sign they're interested. Want to reply?</div>
                <div class="rxFrom">From: <span>{{ receivedFrom }}</span></div>

                <div class="rxActions" v-if="!showComposer">
                    <button class="btn primary" @click="openQuickReply">Reply</button>
                    <button class="btn ghost" @click="hideReceiver">Later</button>
                </div>

                <div class="rxComposer" v-if="showComposer">
                    <div class="rxComposerLabel">
                        <span>Quick reply</span>
                        <span class="pillMini">Send Friend Request</span>
                    </div>

                    <div class="rxBubble">
                        <textarea id="rxReplyText" v-model="quickReplyText" rows="3" ref="quickReplyInput"></textarea>
                    </div>

                    <div class="rxComposerActions">
                        <button class="btn ghost" @click="openFriendRequest">Friend Request</button>
                        <button class="btn primary" @click="sendQuickReply">Send Reply</button>
                        <button class="btn ghost" @click="showComposer = false">Cancel</button>
                    </div>
                    <div class="rxTinyHint">Tip: send a message, or send a friend request from the same box.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Friend Request Modal ── -->
    <div class="frOverlay" :class="{ show: showFriendRequest }" aria-live="polite" role="dialog" aria-modal="true">
        <div class="frCard">
            <div class="frTop">
                <div class="frTitle">Send Friend Request</div>
                <button class="frClose" @click="showFriendRequest = false" aria-label="Close">✕</button>
            </div>

            <div class="frBody">
                <div class="frRow">
                    <div class="frToPill">To: <span>{{ receivedFrom }}</span></div>
                    <div class="frBadge">Become friends on LinkUp</div>
                </div>

                <div class="frField">
                    <div class="frLabel">Add a note (optional)</div>
                    <textarea class="frInput" v-model="friendRequestNote" rows="3"
                        placeholder="e.g., Hey! I like your vibe — let's be friends on LinkUp."
                        ref="friendRequestNoteInput"></textarea>
                </div>

                <div class="frActions">
                    <button class="btn ghost" @click="showFriendRequest = false">Cancel</button>
                    <button class="btn primary" @click="sendFriendRequest">Send Friend Request</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import Footer1 from '@/components/footer1.vue';
import Sidebar from '@/components/front/Sidebar.vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import useMessages from '@/composables/useMessages';
import { useGiftReceiver } from '@/composables/useGiftReceiver';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onBeforeUnmount, watchEffect } from 'vue';

const page = usePage();
const user = (usePage().props as any).auth?.user;

const unreadNotificationsCount = ref<number>((page.props as any)?.notifications?.unreadCount ?? 0);
const showProfileMenu = ref(false);
const profileMenuDesktopRef = ref<HTMLElement | null>(null);
const profileMenuMobileRef = ref<HTMLElement | null>(null);

function handleClickOutsideProfileMenu(e: MouseEvent) {
    const target = e.target as Node;
    const insideDesktop = profileMenuDesktopRef.value?.contains(target) ?? false;
    const insideMobile = profileMenuMobileRef.value?.contains(target) ?? false;
    if (!insideDesktop && !insideMobile) {
        showProfileMenu.value = false;
    }
}

const avatarUrl = computed(() => {
    const a = user?.avatar;
    if (!a) return null;
    return typeof a === 'string' && a.startsWith('http') ? a : `/storage/${a}`;
});

function handleLogout() {
    router.post(route('logout'));
}

import GoogleTranslateSelect from '@/components/front/GoogleTranslateSelect.vue';
import LanguageDropdown from '@/components/front/LanguageDropdown.vue';
import LinkUpOrganizerPopup from '@/components/LinkUpOrganizerPopup.vue';
import GiftDialog from '@/components/front/GiftDialog.vue';
import { Coins } from 'lucide-vue-next';
import axios from 'axios';

const showingNavigationDropdown = ref(false);

const handleChatClick = () => router.visit(route('frontend.user.chat'));
const handlePublicChatClick = () => router.visit(route('frontend.public.chat'));
const handleNotificationClick = () => router.visit(route('frontend.user.allnotifications'));
const handleWalletClick = () => router.visit(route('frontend.user.wallet'));
const handleSubClick = () => router.visit(route('frontend.subscription.plans'));
const showPopup = ref(false);
const giftDialogRef = ref<any>(null);
const giftReplyUser = ref<any>(null);
const showComposer = ref(false);
const quickReplyText = ref('');
const showFriendRequest = ref(false);
const friendRequestNote = ref('');
const quickReplyInput = ref<HTMLTextAreaElement | null>(null);
const friendRequestNoteInput = ref<HTMLTextAreaElement | null>(null);
const organizerCategories = computed(() => user?.organizer_profile?.categories ?? []);
const hasCookouts = computed(() => organizerCategories.value.includes('cookouts/food') || organizerCategories.value.includes('cookouts'));
const hasWellness = computed(() => organizerCategories.value.includes('Wellness and Spa'));
const organizerDashboardRoute = computed(() => {
    if (hasCookouts.value) return route('organizer.cookout.dashboard');
    if (hasWellness.value) return route('organizer.wallness-spa.dashboard');
    return route('organizer.dashboard');
});

let unreadPollTimer: any = null;

async function refreshUnreadNotificationsCount() {
    try {
        const res = await axios.get('/api/user/notifications/unread-count');
        const next = Number(res?.data?.unreadCount ?? 0);
        if (!Number.isNaN(next)) unreadNotificationsCount.value = next;
    } catch { /* ignore */ }
}

onMounted(() => {
    refreshUnreadNotificationsCount();
    unreadPollTimer = setInterval(refreshUnreadNotificationsCount, 15000);
    document.addEventListener('click', handleClickOutsideProfileMenu);

    const myId = (usePage().props as any)?.auth?.user?.id;
    if (myId && window.Echo) {
        window.Echo.private(`App.Models.User.${myId}`)
            .listen('.App\\Events\\MessageSent', (e: any) => {
                try {
                    if (!e || Number(e.to_user_id) !== Number(myId)) return;
                    unreadNotificationsCount.value = Math.max(0, Number(unreadNotificationsCount.value) + 1);
                    const senderName = e?.sender?.name || 'Someone';
                    const preview = typeof e?.content === 'string' && e.content.trim()
                        ? e.content.trim() : 'You received a new message';
                    toast.message(`New message from ${senderName}`, { description: preview });
                } catch { /* ignore */ }
            });
    }
});

onBeforeUnmount(() => {
    if (unreadPollTimer) { clearInterval(unreadPollTimer); unreadPollTimer = null; }
    const myId = (usePage().props as any)?.auth?.user?.id;
    if (myId && window.Echo) {
        window.Echo.private(`App.Models.User.${myId}`).stopListening('.App\\Events\\MessageSent');
    }
});

function handleContinue() {
    localStorage.removeItem('organizer_popup_shown');
    window.sessionStorage.setItem('isOrganizer', 'true');
    window.location.href = organizerDashboardRoute.value;
}

function handleBack() {
    window.sessionStorage.removeItem('isOrganizer');
    window.location.href = route('frontend.find.matches');
}

const isOrganizer = ref(window.sessionStorage.getItem('isOrganizer'));

function handleCancel() {
    window.sessionStorage.removeItem('isOrganizer');
    router.visit(route('frontend.myevents.create'));
}

useMessages();

const { showReceiver, receivedGift, receivedFrom, hideReceiver, replyToGift } = useGiftReceiver();

watch(showReceiver, (newVal) => {
    if (!newVal) showComposer.value = false;
});

const openQuickReply = () => {
    const fromName = receivedFrom.value || 'there';
    quickReplyText.value = receivedGift.value
        ? `Thank you, ${fromName}! That was really kind of you — I appreciate the sweet words. 😊`
        : '';
    showComposer.value = true;
    setTimeout(() => quickReplyInput.value?.focus(), 100);
};

const sendQuickReply = async () => {
    if (!quickReplyText.value.trim()) { toast.error('Type a message.'); return; }
    const targetUserId = receivedGift.value?.sender_id;
    if (!targetUserId) return;
    try {
        await axios.post('/api/user/gift-reply', { receiver_id: targetUserId, message: quickReplyText.value });
        toast.success('Reply sent ✅');
        showComposer.value = false;
        hideReceiver();
    } catch { toast.error('Failed to send reply.'); }
};

const openFriendRequest = () => {
    const to = receivedFrom.value || 'Someone';
    friendRequestNote.value = `Hey ${to}! I like your vibe — want to be friends on LinkUp?`;
    showFriendRequest.value = true;
    setTimeout(() => friendRequestNoteInput.value?.focus(), 100);
};

const sendFriendRequest = async () => {
    const targetUserId = receivedGift.value?.sender_id;
    if (!targetUserId) return;
    try {
        await axios.post(route('frontend.friend-request.send', { user: targetUserId }), { note: friendRequestNote.value });
        toast.success('Friend request sent ✅');
        showFriendRequest.value = false;
        if (!showComposer.value) hideReceiver();
    } catch { toast.error('Failed to send friend request.'); }
};

const { walletBalance } = usePage<{ walletBalance: number }>().props;
const profile = user?.hide_profile;
const hideProfile = ref(profile);

function openPopUp() {
    const hasOrganizerProfile = user?.organizer_profile !== null && user?.organizer_profile !== undefined;
    if (hasOrganizerProfile || window.sessionStorage.getItem('isOrganizer')) {
        window.sessionStorage.setItem('isOrganizer', 'true');
        window.location.href = organizerDashboardRoute.value;
    } else {
        showPopup.value = true;
    }
}

watch(hideProfile, (newValue) => {
    router.put(route('frontend.profile.hide'), { hide_profile: newValue }, {
        preserveScroll: true,
        preserveState: true,
    });
});

watchEffect(() => {
    const flash = page.props?.flash as any;
    if (flash?.success) toast.success(flash.success);
    else if (flash?.error) toast.error(flash.error);
    else if (flash?.message) toast(flash.message);
});
</script>
