<template>
    <GuestLayout>

        <Head title="LinkUp — Secure Auth (Sign Up + Sign In)" />

        <div class="min-h-screen bg-linear-to-b from-[#f8fbff] to-[#f6f8fc]">
            <div
                class="mx-auto max-w-300 grid grid-cols-1 lg:grid-cols-[1.08fr_1fr] gap-5 sm:gap-8 lg:gap-10 p-4 sm:p-6 lg:p-10 items-center min-h-screen">
                <!-- LEFT: Features Panel -->
                <section class="order-2 lg:order-1">
                    <h1
                        class="text-[32px] sm:text-[42px] lg:text-[54px] leading-[1.06] lg:leading-[1.02] mb-3 tracking-[-0.02em]">
                        <span class="text-[#2fa4e6] font-black">LinkUp</span><br />
                        Create. Connect. Pay.
                    </h1>

                    <div
                        class="inline-flex max-w-full flex-wrap items-center gap-2.5 my-2.5 px-3 py-2 rounded-full border border-[rgba(47,164,230,.20)] bg-[rgba(47,164,230,.07)] text-[#0b3b57] font-black text-xs tracking-[0.02em]">
                        Create events • Go live • Meet people • Wallet • News
                    </div>

                    <p class="mb-4 text-[#64748b] text-sm sm:text-base max-w-135">
                        One secure platform where you create events, connect with people, manage money, and stay
                        informed — all under one verified LinkUp ID.
                    </p>

                    <ul class="mt-2.5 space-y-2.5 max-w-140 list-none p-0">
                        <li
                            class="flex gap-3 items-start p-3 rounded-2xl border border-[rgba(230,237,247,.9)] bg-linear-to-b from-white to-[#f8fbff] shadow-[0_10px_22px_rgba(15,23,42,.05)]">
                            <div
                                class="w-8.5 h-8.5 rounded-xl flex items-center justify-center bg-[rgba(47,164,230,.10)] border border-[rgba(47,164,230,.16)] text-lg shrink-0">
                                🎉</div>
                            <div>
                                <div class="font-black text-[13.5px] leading-[1.2] m-0">Linkup Events (Create Events)
                                </div>
                                <div class="mt-1 text-[#64748b] text-[12.8px] leading-[1.35]">Host events, sell tickets,
                                    and become your own event organizer.</div>
                            </div>
                        </li>

                        <li
                            class="flex gap-3 items-start p-3 rounded-2xl border border-[rgba(230,237,247,.9)] bg-linear-to-b from-white to-[#f8fbff] shadow-[0_10px_22px_rgba(15,23,42,.05)]">
                            <div
                                class="w-8.5 h-8.5 rounded-xl flex items-center justify-center bg-[rgba(47,164,230,.10)] border border-[rgba(47,164,230,.16)] text-lg shrink-0">
                                📺</div>
                            <div>
                                <div class="font-black text-[13.5px] leading-[1.2] m-0">LinkUp Live</div>
                                <div class="mt-1 text-[#64748b] text-[12.8px] leading-[1.35]">Go live, stream events,
                                    and connect with your audience in real time.</div>
                            </div>
                        </li>

                        <li
                            class="flex gap-3 items-start p-3 rounded-2xl border border-[rgba(230,237,247,.9)] bg-linear-to-b from-white to-[#f8fbff] shadow-[0_10px_22px_rgba(15,23,42,.05)]">
                            <div
                                class="w-8.5 h-8.5 rounded-xl flex items-center justify-center bg-[rgba(47,164,230,.10)] border border-[rgba(47,164,230,.16)] text-lg shrink-0">
                                💬</div>
                            <div>
                                <div class="font-black text-[13.5px] leading-[1.2] m-0">Find Matches & Friends</div>
                                <div class="mt-1 text-[#64748b] text-[12.8px] leading-[1.35]">Meet new people, make
                                    friends, and build real connections.</div>
                            </div>
                        </li>

                        <li
                            class="flex gap-3 items-start p-3 rounded-2xl border border-[rgba(230,237,247,.9)] bg-linear-to-b from-white to-[#f8fbff] shadow-[0_10px_22px_rgba(15,23,42,.05)]">
                            <div
                                class="w-8.5 h-8.5 rounded-xl flex items-center justify-center bg-[rgba(47,164,230,.10)] border border-[rgba(47,164,230,.16)] text-lg shrink-0">
                                🛍️</div>
                            <div>
                                <div class="font-black text-[13.5px] leading-[1.2] m-0">LinkUp Marketplace</div>
                                <div class="mt-1 text-[#64748b] text-[12.8px] leading-[1.35]">Buy and sell products
                                    directly on the platform.</div>
                            </div>
                        </li>

                        <li
                            class="flex gap-3 items-start p-3 rounded-2xl border border-[rgba(230,237,247,.9)] bg-linear-to-b from-white to-[#f8fbff] shadow-[0_10px_22px_rgba(15,23,42,.05)]">
                            <div
                                class="w-8.5 h-8.5 rounded-xl flex items-center justify-center bg-[rgba(47,164,230,.10)] border border-[rgba(47,164,230,.16)] text-lg shrink-0">
                                💳</div>
                            <div>
                                <div class="font-black text-[13.5px] leading-[1.2] m-0">LinkUp Wallet</div>
                                <div class="mt-1 text-[#64748b] text-[12.8px] leading-[1.35]">Send/receive money, buy
                                    cryptocurrency, and pay for tickets, drinks, and products.</div>
                            </div>
                        </li>

                        <li
                            class="flex gap-3 items-start p-3 rounded-2xl border border-[rgba(230,237,247,.9)] bg-linear-to-b from-white to-[#f8fbff] shadow-[0_10px_22px_rgba(15,23,42,.05)]">
                            <div
                                class="w-8.5 h-8.5 rounded-xl flex items-center justify-center bg-[rgba(47,164,230,.10)] border border-[rgba(47,164,230,.16)] text-lg shrink-0">
                                📰</div>
                            <div>
                                <div class="font-black text-[13.5px] leading-[1.2] m-0">LinkUp 360 News</div>
                                <div class="mt-1 text-[#64748b] text-[12.8px] leading-[1.35]">Stay updated with local,
                                    regional, and global news.</div>
                            </div>
                        </li>
                    </ul>

                    <div
                        class="mt-3.5 border border-[rgba(37,99,235,.20)] bg-[rgba(37,99,235,.06)] text-[#1e3a8a] rounded-2xl p-2.5 text-xs flex gap-2.5 items-start max-w-140">
                        <div>🔐</div>
                        <div>
                            <b class="text-[#1e40af]">Built with security in mind:</b>
                            reserve your LinkUp ID, protect your account, and enable 2-step verification.
                            <ul class="mt-2 ml-4 text-xs leading-[1.4] text-[#1e3a8a]">
                                <li>2-step verification (OTP / authenticator)</li>
                                <li>Device checks + login alerts</li>
                                <li>Rate limiting + lockouts after repeated attempts</li>
                                <li>Never share OTP/PIN — LinkUp support will never ask</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- RIGHT: Auth Card -->
                <section class="order-1 lg:order-2">
                    <div
                        class="bg-white border border-[#e6edf7] rounded-[18px] shadow-[0_22px_60px_rgba(15,23,42,.10)] p-4 sm:p-5.5">
                        <!-- Tabs -->
                        <div
                            class="flex gap-2.5 mb-3.5 p-1.5 border border-[#e6edf7] rounded-2xl bg-linear-to-b from-white to-[#f8fbff]">
                            <button type="button"
                                class="flex-1 border-0 bg-transparent px-2 py-2.5 rounded-xl cursor-pointer font-black text-[13px] sm:text-[15px] transition-all duration-150"
                                :class="activeTab === 'signup' ? 'text-[#0f172a] bg-white shadow-[0_10px_18px_rgba(15,23,42,.06)] border border-[#e6edf7]' : 'text-[#64748b]'"
                                @click="activeTab = 'signup'">
                                Create account
                            </button>
                            <button type="button"
                                class="flex-1 border-0 bg-transparent px-2 py-2.5 rounded-xl cursor-pointer font-black text-[13px] sm:text-[15px] transition-all duration-150"
                                :class="activeTab === 'signin' ? 'text-[#0f172a] bg-white shadow-[0_10px_18px_rgba(15,23,42,.06)] border border-[#e6edf7]' : 'text-[#64748b]'"
                                @click="activeTab = 'signin'">
                                Sign in
                            </button>
                        </div>

                        <!-- SIGN UP VIEW -->
                        <div v-if="activeTab === 'signup'">
                            <h2 class="m-0 mb-3.5 text-[22px] sm:text-[26px] tracking-[-0.01em]">Create your account
                            </h2>

                            <form @submit.prevent="submit" class="space-y-3.5" novalidate>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-xs text-[#64748b] mb-1.5 tracking-[0.02em]"
                                            for="firstName">First name</label>
                                        <input v-model="form.first_name" id="firstName" name="first_name" type="text"
                                            autocomplete="given-name" placeholder="Aaliyah"
                                            class="w-full px-3 py-3 rounded-xl border border-[#e6edf7] bg-white outline-none text-[15px] transition-all duration-150 focus:border-[rgba(47,164,230,.55)] focus:shadow-[0_0_0_4px_rgba(47,164,230,.14)]"
                                            @input="generateHandleFromName" />
                                        <InputError class="mt-1 text-xs text-red-600"
                                            :message="form.errors.first_name" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-[#64748b] mb-1.5 tracking-[0.02em]"
                                            for="lastName">Last name</label>
                                        <input v-model="form.last_name" id="lastName" name="last_name" type="text"
                                            autocomplete="family-name" placeholder="Brown"
                                            class="w-full px-3 py-3 rounded-xl border border-[#e6edf7] bg-white outline-none text-[15px] transition-all duration-150 focus:border-[rgba(47,164,230,.55)] focus:shadow-[0_0_0_4px_rgba(47,164,230,.14)]"
                                            @input="generateHandleFromName" />
                                        <InputError class="mt-1 text-xs text-red-600"
                                            :message="form.errors.last_name" />
                                    </div>
                                </div>

                                <!-- LinkUp ID Field -->
                                <div>
                                    <label class="block text-xs text-[#64748b] mb-1.5 tracking-[0.02em]"
                                        for="handle">LinkUp ID</label>
                                    <div class="flex flex-col sm:flex-row gap-2.5 items-stretch sm:items-start">
                                        <input v-model="form.linkup_id" id="handle" name="linkup_id" type="text"
                                            inputmode="text" autocapitalize="none" autocomplete="off"
                                            placeholder="@aaliyahbrown"
                                            class="flex-1 font-mono px-3 py-3 rounded-xl border border-[#e6edf7] bg-white outline-none text-[15px] transition-all duration-150 focus:border-[rgba(47,164,230,.55)] focus:shadow-[0_0_0_4px_rgba(47,164,230,.14)]"
                                            @input="onHandleInput" />
                                        <button type="button"
                                            class="border border-[#e6edf7] bg-white rounded-xl px-2.5 py-2.5 cursor-pointer font-black text-[13px] text-[#0f172a] min-w-28 transition-all duration-150 hover:-translate-y-px hover:shadow-[0_10px_18px_rgba(15,23,42,.06)] whitespace-nowrap disabled:opacity-55 disabled:cursor-not-allowed disabled:transform-none w-full sm:w-auto"
                                            @click="generateHandleFromName">
                                            Regenerate
                                        </button>
                                    </div>

                                    <!-- Status Indicator -->
                                    <div class="mt-2 text-xs flex items-center gap-2 flex-wrap text-[#64748b]">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.25 rounded-full border font-black text-xs"
                                            :class="{
                                                'border-[rgba(245,158,11,.25)] bg-[rgba(245,158,11,.10)] text-[#f59e0b]': handleStatus === 'waiting',
                                                'border-[rgba(22,163,74,.25)] bg-[rgba(22,163,74,.08)] text-[#16a34a]': handleStatus === 'available',
                                                'border-[rgba(239,68,68,.25)] bg-[rgba(239,68,68,.08)] text-[#ef4444]': handleStatus === 'taken',
                                                'border-[rgba(37,99,235,.25)] bg-[rgba(37,99,235,.08)] text-[#2563eb]': handleStatus === 'checking'
                                            }">
                                            {{ handleStatusText }}
                                        </span>
                                        <span>{{ handleStatusMessage }}</span>
                                    </div>

                                    <!-- Suggestions -->
                                    <div v-if="suggestions.length > 0" class="mt-2.5">
                                        <div class="text-[#64748b] text-xs mb-2">Suggestions (tap to use)</div>
                                        <div class="flex flex-wrap gap-2">
                                            <button v-for="suggestion in suggestions" :key="suggestion" type="button"
                                                class="border border-[#e6edf7] bg-white rounded-full px-2.5 py-1.75 cursor-pointer font-black text-xs transition-all duration-150 hover:-translate-y-px hover:shadow-[0_10px_18px_rgba(15,23,42,.06)] font-mono"
                                                @click="selectSuggestion(suggestion)">
                                                @{{ suggestion }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs text-[#64748b] mb-1.5 tracking-[0.02em]"
                                        for="signupEmail">Email</label>
                                    <input v-model="form.email" id="signupEmail" name="email" type="email"
                                        autocomplete="email" placeholder="you@example.com"
                                        class="w-full px-3 py-3 rounded-xl border border-[#e6edf7] bg-white outline-none text-[15px] transition-all duration-150 focus:border-[rgba(47,164,230,.55)] focus:shadow-[0_0_0_4px_rgba(47,164,230,.14)]" />
                                    <InputError class="mt-1 text-xs text-red-600" :message="form.errors.email" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div class="relative">
                                        <label class="block text-xs text-[#64748b] mb-1.5 tracking-[0.02em]"
                                            for="signupPassword">Password</label>
                                        <div class="relative">
                                            <input v-model="form.password" id="signupPassword" name="password"
                                                :type="showPassword ? 'text' : 'password'" autocomplete="new-password"
                                                placeholder="••••••••"
                                                class="w-full px-3 py-3 pr-11 rounded-xl border border-[#e6edf7] bg-white outline-none text-[15px] transition-all duration-150 focus:border-[rgba(47,164,230,.55)] focus:shadow-[0_0_0_4px_rgba(47,164,230,.14)]"
                                                @input="updatePwUI" />
                                            <button type="button"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 border-0 bg-transparent cursor-pointer p-1.5 text-[#64748b] hover:text-[#0f172a]"
                                                @click="togglePassword">
                                                <svg v-if="showPassword === true" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                </svg>

                                            </button>
                                        </div>
                                        <div class="mt-1.5">
                                            <div class="h-1 w-full bg-[#e6edf7] rounded-full overflow-hidden mb-1">
                                                <div class="h-full transition-all duration-300"
                                                    :class="{
                                                        'bg-red-400': passwordStrength <= 2,
                                                        'bg-amber-400': passwordStrength === 3 || passwordStrength === 4,
                                                        'bg-emerald-400': passwordStrength === 5
                                                    }"
                                                    :style="{ width: passwordStrengthWidth + '%' }"></div>
                                            </div>
                                            <p class="text-[10px] text-[#64748b] leading-tight">
                                                Use 8+ characters with a mix of uppercase, lowercase, numbers & symbols.
                                            </p>
                                        </div>
                                        <InputError class="mt-1 text-xs text-red-600" :message="form.errors.password" />
                                    </div>

                                    <div class="relative">
                                        <label class="block text-xs text-[#64748b] mb-1.5 tracking-[0.02em]"
                                            for="signupConfirm">Confirm password</label>
                                        <div class="relative">
                                            <input v-model="form.password_confirmation" id="signupConfirm"
                                                name="password_confirmation"
                                                :type="showConfirmPassword ? 'text' : 'password'"
                                                autocomplete="new-password" placeholder="••••••••"
                                                class="w-full px-3 py-3 pr-11 rounded-xl border border-[#e6edf7] bg-white outline-none text-[15px] transition-all duration-150 focus:border-[rgba(47,164,230,.55)] focus:shadow-[0_0_0_4px_rgba(47,164,230,.14)]" />
                                            <button type="button"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 border-0 bg-transparent cursor-pointer p-1.5 text-[#64748b] hover:text-[#0f172a]"
                                                @click="toggleConfirmPassword">
                                                <svg v-if="showConfirmPassword === true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                </svg>


                                            </button>
                                        </div>
                                        <InputError class="mt-1 text-xs text-red-600"
                                            :message="form.errors.password_confirmation" />
                                    </div>
                                </div>
                                <div class="relative my-4">
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="w-full h-px bg-[#e6edf7]"></div>
                                    </div>
                                    <div
                                        class="relative flex justify-center text-xs uppercase text-[#94a3b8] tracking-[0.06em]">
                                        <span class="bg-white px-2.5">OR CONTINUE WITH</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button type="button"
                                        class="border border-[#e6edf7] bg-white rounded-xl px-3 py-3 cursor-pointer font-black flex items-center justify-center gap-2.5 transition-all duration-150 hover:-translate-y-px hover:shadow-[0_10px_18px_rgba(15,23,42,.06)]"
                                        @click="socialAuth('google')">
                                        <span>🟡</span> Google
                                    </button>
                                    <button type="button"
                                        class="border border-[#e6edf7] bg-white rounded-xl px-3 py-3 cursor-pointer font-black flex items-center justify-center gap-2.5 transition-all duration-150 hover:-translate-y-px hover:shadow-[0_10px_18px_rgba(15,23,42,.06)]"
                                        @click="socialAuth('facebook')">
                                        <span>🔵</span> Facebook
                                    </button>
                                </div>

                                <button type="submit"
                                    class="mt-3.5 w-full border-0 rounded-xl px-3.5 py-3.5 font-black text-[15.5px] cursor-pointer text-white bg-linear-to-r from-[#0ea5e9] to-[#62c8ff] shadow-[0_18px_34px_rgba(14,165,233,.25)] transition-all duration-150 hover:saturate-[1.05] hover:-translate-y-px disabled:opacity-55 disabled:cursor-not-allowed disabled:transform-none"
                                    :disabled="form.processing || !isFormValid">
                                    <span v-if="!form.processing">Create account</span>
                                    <span v-else>Processing...</span>
                                </button>

                                <div
                                    class="flex items-center justify-between gap-3 flex-wrap mt-1.5 text-xs text-[#64748b]">
                                    <span>Already have an account?</span>
                                    <button type="button" class="text-[#0f172a] font-black hover:underline"
                                        @click="activeTab = 'signin'">
                                        Sign in
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- SIGN IN VIEW -->
                        <div v-if="activeTab === 'signin'">
                            <h2 class="m-0 mb-3.5 text-[22px] sm:text-[26px] tracking-[-0.01em]">Sign in</h2>

                            <form @submit.prevent="signinSubmit" class="space-y-3.5" novalidate>
                                <div>
                                    <label class="block text-xs text-[#64748b] mb-1.5 tracking-[0.02em]"
                                        for="signinId">Email or LinkUp ID</label>
                                    <input v-model="signinForm.email" id="signinId" name="email" type="text"
                                        autocomplete="username" placeholder="you@example.com or @aaliyahbrown"
                                        class="w-full px-3 py-3 rounded-xl border border-[#e6edf7] bg-white outline-none text-[15px] transition-all duration-150 focus:border-[rgba(47,164,230,.55)] focus:shadow-[0_0_0_4px_rgba(47,164,230,.14)]" />
                                    <InputError class="mt-1 text-xs text-red-600" :message="signinForm.errors.email" />
                                </div>

                                <div class="relative">
                                    <label class="block text-xs text-[#64748b] mb-1.5 tracking-[0.02em]"
                                        for="signinPassword">Password</label>
                                    <div class="relative">
                                        <input v-model="signinForm.password" id="signinPassword" name="password"
                                            :type="showSigninPassword ? 'text' : 'password'"
                                            autocomplete="current-password" placeholder="••••••••"
                                            class="w-full px-3 py-3 pr-11 rounded-xl border border-[#e6edf7] bg-white outline-none text-[15px] transition-all duration-150 focus:border-[rgba(47,164,230,.55)] focus:shadow-[0_0_0_4px_rgba(47,164,230,.14)]" />
                                        <button type="button"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 border-0 bg-transparent cursor-pointer p-1.5 text-[#64748b] hover:text-[#0f172a]"
                                            @click="toggleSigninPassword">
                                            👁️
                                        </button>
                                    </div>
                                    <InputError class="mt-1 text-xs text-red-600"
                                        :message="signinForm.errors.password" />
                                </div>

                                <div
                                    class="flex items-center justify-between gap-3 flex-wrap mt-1.5 text-xs text-[#64748b]">
                                    <label class="flex items-center gap-2.5 m-0">
                                        <input type="checkbox" id="rememberMe" v-model="signinForm.remember" />
                                        <span>Remember this device</span>
                                    </label>
                                    <button type="button" class="text-[#0f172a] font-black hover:underline"
                                        @click="toggleForgot(true)">
                                        Forgot password?
                                    </button>
                                </div>

                                <div
                                    class="border border-[rgba(37,99,235,.20)] bg-[rgba(37,99,235,.06)] text-[#1e3a8a] rounded-2xl p-2.5 text-xs flex gap-2.5 items-start">
                                    <div>🔐</div>
                                    <div>
                                        <b class="text-[#1e40af]">Strong security recommendation:</b>
                                        Enable 2-step verification for all wallet accounts.
                                        We also recommend login alerts + authenticator apps.
                                    </div>
                                </div>

                                <button type="submit"
                                    class="mt-3.5 w-full border-0 rounded-xl px-3.5 py-3.5 font-black text-[15.5px] cursor-pointer text-white bg-linear-to-r from-[#0ea5e9] to-[#62c8ff] shadow-[0_18px_34px_rgba(14,165,233,.25)] transition-all duration-150 hover:saturate-[1.05] hover:-translate-y-px disabled:opacity-55 disabled:cursor-not-allowed disabled:transform-none"
                                    :disabled="signinForm.processing">
                                    <span v-if="!signinForm.processing">Continue</span>
                                    <span v-else>Processing...</span>
                                </button>

                                <div class="relative my-4">
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="w-full h-px bg-[#e6edf7]"></div>
                                    </div>
                                    <div
                                        class="relative flex justify-center text-xs uppercase text-[#94a3b8] tracking-[0.06em]">
                                        <span class="bg-white px-2.5">OR CONTINUE WITH</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button type="button"
                                        class="border border-[#e6edf7] bg-white rounded-xl px-3 py-3 cursor-pointer font-black flex items-center justify-center gap-2.5 transition-all duration-150 hover:-translate-y-px hover:shadow-[0_10px_18px_rgba(15,23,42,.06)]"
                                        @click="socialAuth('google')">
                                        <span>🟡</span> Google
                                    </button>
                                    <button type="button"
                                        class="border border-[#e6edf7] bg-white rounded-xl px-3 py-3 cursor-pointer font-black flex items-center justify-center gap-2.5 transition-all duration-150 hover:-translate-y-px hover:shadow-[0_10px_18px_rgba(15,23,42,.06)]"
                                        @click="socialAuth('facebook')">
                                        <span>🔵</span> Facebook
                                    </button>
                                </div>

                                <div
                                    class="flex items-center justify-between gap-3 flex-wrap mt-1.5 text-xs text-[#64748b]">
                                    <span>New to LinkUp?</span>
                                    <button type="button" class="text-[#0f172a] font-black hover:underline"
                                        @click="activeTab = 'signup'">
                                        Create account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Forgot Password Modal -->
            <div v-if="showForgot" id="forgotOverlay" class="fixed inset-0 bg-black/50 z-50"
                @click="toggleForgot(false)"></div>
            <div v-if="showForgot" id="forgotModal" class="fixed inset-0 z-50 grid place-items-center p-4" role="dialog"
                aria-modal="true">
                <div class="bg-white rounded-2xl shadow-lg w-full max-w-md overflow-hidden">
                    <div class="flex items-center justify-between border-b px-6 pt-5 pb-3">
                        <h2 class="text-xl font-semibold text-slate-800">Reset your password</h2>
                        <button class="rounded-lg p-2 hover:bg-slate-100" aria-label="Close"
                            @click="toggleForgot(false)">
                            <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3 10.6 10.6 16.9 4.3z" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="forgotSubmit" class="space-y-4 px-6 pt-5 pb-6" novalidate>
                        <div v-if="!forgotSuccess" class="space-y-4">
                            <p class="text-sm text-slate-600">Enter your account email.</p>

                            <div>
                                <label class="text-sm text-slate-600" for="fpEmail">Email</label>
                                <input v-model="forgotForm.email" id="fpEmail" type="email" autocomplete="email"
                                    placeholder="you@example.com"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-3 outline-none focus:ring-4 focus:ring-linkup-blue/20 focus:border-linkup-blue" />
                                <InputError class="mt-1 text-xs text-red-600" :message="forgotForm.errors.email" />
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="button"
                                    class="rounded-xl border border-slate-200 px-4 py-2 hover:bg-slate-50"
                                    @click="toggleForgot(false)">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="bg-linkup-blue rounded-xl px-5 py-3 font-semibold text-white hover:brightness-95 disabled:opacity-60 disabled:cursor-not-allowed"
                                    :disabled="forgotForm.processing">
                                    <span v-if="!forgotForm.processing">Send reset</span>
                                    <span v-else>Processing...</span>
                                </button>
                            </div>
                        </div>

                        <div v-if="forgotSuccess" class="text-center">
                            <h3 class="text-lg font-semibold text-slate-800">Check your email</h3>
                            <p class="mt-1 text-sm text-slate-600">If the email exists, we've sent instructions.</p>
                            <button type="button"
                                class="bg-linkup-lime mt-4 rounded-xl px-5 py-2.5 font-semibold text-slate-900 hover:brightness-95"
                                @click="toggleForgot(false)">
                                Done
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <Footer1 />
    </GuestLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/admin/InputError.vue';
import Footer1 from '@/components/footer1.vue';
import GuestLayout from '@/layouts/admin/GuestLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';

// Password policy
const PW_REGEX = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/;
const pwScore = (v: string) => {
    let s = 0;
    if (v.length >= 8) s++;
    if (/[a-z]/.test(v)) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/\d/.test(v)) s++;
    if (/[^\w\s]/.test(v)) s++;
    return Math.min(s, 5);
};

// Signup form
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    linkup_id: '',
    password: '',
    password_confirmation: '',
});

// Signin form
const signinForm = useForm({
    email: '',
    password: '',
    remember: false,
});

// Forgot password form
const forgotForm = useForm({
    email: '',
});

// State
const activeTab = ref<'signup' | 'signin'>('signup');
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const showSigninPassword = ref(false);
const showForgot = ref(false);
const forgotSuccess = ref(false);
const passwordStrength = ref(0);
const handleStatus = ref<'waiting' | 'checking' | 'available' | 'taken'>('waiting');
const handleStatusMessage = ref('Type your first and last name to generate an ID.');
const suggestions = ref<string[]>([]);
const checkingHandle = ref(false);
let handleCheckTimeout: ReturnType<typeof setTimeout> | null = null;

// Computed
const passwordStrengthWidth = computed(() => [0, 20, 40, 70, 90, 100][passwordStrength.value]);

const handleStatusText = computed(() => {
    switch (handleStatus.value) {
        case 'available': return 'Available';
        case 'taken': return 'Taken';
        case 'checking': return 'Checking';
        case 'waiting': return 'Waiting';
    }
});

const isFormValid = computed(() => {
    const linkupIdWithoutAt = fromAtHandle(form.linkup_id);
    return (
        form.first_name.trim().length >= 1 &&
        form.last_name.trim().length >= 1 &&
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim()) &&
        PW_REGEX.test(form.password) &&
        form.password === form.password_confirmation &&
        linkupIdWithoutAt.length >= 3 &&
        handleStatus.value === 'available'
    );
});

// Methods
const normalizeHandle = (str: string): string => {
    return (str || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9_]+/g, '')
        .trim();
};

const clampHandle = (h: string): string => {
    if (!h) return '';
    if (h.length < 3) return '';
    return h.slice(0, 20);
};

const fromAtHandle = (h: string): string => {
    return (h || '').replace(/^@+/, '');
};

const baseCandidates = (first: string, last: string): string[] => {
    const f = normalizeHandle(first);
    const l = normalizeHandle(last);
    if (!f || !l) return [];

    const cands = [
        `${f}${l}`,
        `${f}_${l}`,
        `${f}${l[0]}`,
        `${f[0]}${l}`,
    ];

    return [...new Set(cands.map(clampHandle).filter(Boolean))];
};

const randomSuffix = (len: number = 3): string => {
    const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    let out = '';
    for (let i = 0; i < len; i++) {
        out += chars[Math.floor(Math.random() * chars.length)];
    }
    return out;
};

const checkHandleAvailability = async (handle: string): Promise<{ available: boolean; reason?: string }> => {
    if (!handle || handle.length < 3) {
        return { available: false, reason: 'Enter a valid ID (3–20 chars).' };
    }

    try {
        const response = await axios.post(route('check.linkup.id'), {
            linkup_id: handle,
        });

        return { available: response.data.available, reason: response.data.reason };
    } catch (error: any) {
        console.error('Error checking handle:', error);
        return { available: false, reason: error.response?.data?.reason || 'Error checking availability.' };
    }
};

const generateHandleFromName = async () => {
    const candidates = baseCandidates(form.first_name, form.last_name);
    if (!candidates.length) {
        form.linkup_id = '';
        handleStatus.value = 'waiting';
        handleStatusMessage.value = 'Type your first and last name to generate an ID.';
        suggestions.value = [];
        return;
    }

    handleStatus.value = 'checking';
    handleStatusMessage.value = 'Generating your LinkUp ID…';
    suggestions.value = [];

    // Shuffle candidates array to randomize selection
    const shuffledCandidates = [...candidates].sort(() => Math.random() - 0.5);

    // Try each candidate in random order
    let selectedCandidate = '';
    for (const candidate of shuffledCandidates) {
        const check = await checkHandleAvailability(candidate);
        if (check.available) {
            selectedCandidate = candidate;
            break;
        }
    }

    // If base candidates are taken, generate variations with random elements
    if (!selectedCandidate) {
        const baseCandidate = shuffledCandidates[0];
        const base = baseCandidate.slice(0, 14); // Leave room for suffix

        // Generate variations with random numbers and suffixes
        const variations: string[] = [];

        // Add variations with random 2-3 digit numbers
        for (let i = 0; i < 3; i++) {
            const num = Math.floor(Math.random() * 900) + 100; // 100-999
            variations.push(clampHandle(base + num));
        }

        // Add variations with random suffixes
        for (let i = 0; i < 5; i++) {
            variations.push(clampHandle(base + randomSuffix(3)));
        }

        // Add variations with underscore and random numbers
        for (let i = 0; i < 2; i++) {
            const num = Math.floor(Math.random() * 90) + 10; // 10-99
            variations.push(clampHandle(`${base}_${num}`));
        }

        // Check variations for availability
        const uniqueVariations = [...new Set(variations)].filter(Boolean);
        const available: string[] = [];

        for (const variation of uniqueVariations) {
            const chk = await checkHandleAvailability(variation);
            if (chk.available) {
                available.push(variation);
                if (!selectedCandidate) {
                    selectedCandidate = variation; // Use first available
                }
            }
        }

        if (selectedCandidate) {
            form.linkup_id = '@' + selectedCandidate;
            handleStatus.value = 'available';
            handleStatusMessage.value = 'This LinkUp ID is available.';
            suggestions.value = available.slice(1, 7); // Show other available options
        } else {
            form.linkup_id = '@' + baseCandidate;
            handleStatus.value = 'taken';
            handleStatusMessage.value = 'That LinkUp ID is taken. Choose a suggestion or edit your ID.';
            suggestions.value = available.slice(0, 6);
        }
    } else {
        form.linkup_id = '@' + selectedCandidate;
        handleStatus.value = 'available';
        handleStatusMessage.value = 'This LinkUp ID is available.';
        suggestions.value = [];
    }
};

const onHandleInput = () => {
    if (handleCheckTimeout) {
        clearTimeout(handleCheckTimeout);
    }

    const cleaned = clampHandle(normalizeHandle(fromAtHandle(form.linkup_id)));

    if (!cleaned) {
        handleStatus.value = 'waiting';
        handleStatusMessage.value = 'Enter a valid ID (3–20 chars). Letters/numbers/underscore only.';
        suggestions.value = [];
        return;
    }

    form.linkup_id = '@' + cleaned;
    handleStatus.value = 'checking';
    handleStatusMessage.value = 'Checking availability…';
    suggestions.value = [];

    handleCheckTimeout = setTimeout(async () => {
        const check = await checkHandleAvailability(cleaned);

        if (check.available) {
            handleStatus.value = 'available';
            handleStatusMessage.value = 'This LinkUp ID is available.';
        } else {
            handleStatus.value = 'taken';
            handleStatusMessage.value = check.reason || 'That LinkUp ID is taken.';

            // Generate suggestions
            const base = cleaned.slice(0, 16);
            const suggestionList: string[] = [];

            for (let n = 1; n <= 5; n++) {
                suggestionList.push(clampHandle(base + n));
            }

            for (let i = 0; i < 4; i++) {
                suggestionList.push(clampHandle(base + randomSuffix(3)));
            }

            const unique = [...new Set(suggestionList)].filter(Boolean);
            const available: string[] = [];

            for (const s of unique) {
                const chk = await checkHandleAvailability(s);
                if (chk.available) {
                    available.push(s);
                }
            }

            suggestions.value = available.slice(0, 6);
        }
    }, 300);
};

const selectSuggestion = async (suggestion: string) => {
    form.linkup_id = '@' + suggestion;
    handleStatus.value = 'checking';
    handleStatusMessage.value = 'Checking availability…';
    suggestions.value = [];

    const check = await checkHandleAvailability(suggestion);
    if (check.available) {
        handleStatus.value = 'available';
        handleStatusMessage.value = 'This LinkUp ID is available.';
    } else {
        handleStatus.value = 'taken';
        handleStatusMessage.value = check.reason || 'That LinkUp ID is taken.';
    }
};

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const toggleConfirmPassword = () => {
    showConfirmPassword.value = !showConfirmPassword.value;
};

const toggleSigninPassword = () => {
    showSigninPassword.value = !showSigninPassword.value;
};

const updatePwUI = () => {
    passwordStrength.value = pwScore(form.password);
};

const toggleForgot = (on: boolean) => {
    showForgot.value = on;
    if (on) {
        document.body.classList.add('no-scroll');
        forgotSuccess.value = false;
        setTimeout(() => document.querySelector('#fpEmail')?.focus(), 100);
    } else {
        document.body.classList.remove('no-scroll');
        forgotForm.reset();
    }
};

const submit = async () => {
    // Remove @ from linkup_id before submitting
    const linkupId = form.linkup_id.replace(/^@+/, '');
    form.transform((data) => ({
        ...data,
        linkup_id: linkupId,
    })).post(route('register'), {});
};

const signinSubmit = async () => {
    signinForm.post(route('login'), {
        onFinish: () => {
            signinForm.reset('password');
        },
    });
};

const forgotSubmit = async () => {
    forgotForm.post(route('password.email'), {
        onSuccess: () => {
            forgotSuccess.value = true;
        },
    });
};

const socialAuth = (provider: string) => {
    // Placeholder for social auth
    console.log('Social auth:', provider);
};

// Watch for name changes to auto-generate handle
watch([() => form.first_name, () => form.last_name], () => {
    if (form.first_name && form.last_name && !form.linkup_id) {
        generateHandleFromName();
    }
}, { debounce: 300 });
</script>

<style scoped>
.no-scroll {
    overflow: hidden;
}
</style>
