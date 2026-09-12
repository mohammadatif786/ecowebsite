<template>
    <div class="app-layout">
        <!-- HEADER -->
        <header class="glass-panel">
            <button class="back-btn" @click="goHome">
                <ArrowLeftIcon class="w-6 h-6" />
            </button>
            <div class="brand">Link<span>Up</span></div>
            <div class="header-center" :class="{ active: joined }">
                🔴 <span>{{ liveTimer }}</span>
            </div>
            <div class="header-right">
                <div class="wallet-pill coin-pill">
                    <span style="color:#ffd700">
                        <Coins />
                    </span>
                    <span>{{ state.coins.toLocaleString() }}</span>
                    <button class="btn-buy" title="Buy Coins" @click="openModal('wallet')">+</button>
                </div>
                <div class="wallet-pill">
                    <span style="color:#00cc00">💵</span>
                    <span>${{ state.cash?.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) || '0.00' }}</span>
                </div>
            </div>
        </header>

        <!-- LEFT SIDEBAR -->
        <aside class="sidebar-left glass-panel" @click="showNetworkModal = false">
            <div class="profile-card">
                <img :src="(joined && !isHost && liveUserData) ? liveUserData.avatar : user?.avatar" class="avatar"
                    :alt="(joined && !isHost && liveUserData) ? liveUserData.name : user?.name" />

                <div class="profile-info">
                    <h4>{{ (joined && !isHost && liveUserData) ? liveUserData.name : user?.name }}</h4>

                    <span class="follow-badge">
                        👥 <span>{{ (joined && !isHost && liveUserData) ? (liveUserData.followers || 0).toLocaleString()
                            : state.followers.toLocaleString() }}</span> Followers
                    </span>

                    <button v-if="joined && !isHost" :class="['btn-follow', { following: isFollowing }]"
                        @click="() => toggleFollow()">
                        {{ isFollowing ? 'Following' : 'Follow' }}
                    </button>
                </div>
            </div>

            <nav>
                <button class="nav-btn active">🎥 Studio View</button>
                <button class="nav-btn" @click.stop="showNetworkModal = true">🔴 Live Network</button>

                <button class="nav-btn" @click="showAnalyticsModal = true">📊 Analytics</button>
                <button v-if="joined && isHost" class="nav-btn" @click="showGuestsModal = true">👥 Guest List</button>
                <button v-if="!joined" class="nav-btn" @click="showSettingsModal = true">⚙️ Settings</button>

                <button class="nav-btn" @click="showHelpModal = true">❓ Help & Guide</button>
            </nav>

            <div v-if="joined && isHost" class="earnings-box">
                <div class="earnings-label">Session Revenue</div>
                <div class="earnings-row">
                    <span>Coins</span>
                    <span style="color:#ffd700; font-weight:700;">🪙 <span>{{ sessionCoins.toLocaleString()
                            }}</span></span>
                </div>
                <div class="earnings-row">
                    <span>Cash</span>
                    <span class="val-cash">$ <span>{{ sessionCash.toFixed(2) }}</span></span>
                </div>
                <button class="btn-transfer" :class="{ ready: sessionCash >= 50 }" @click="openTransferModal">
                    Transfer to Wallet
                </button>
            </div>
        </aside>

        <!-- CENTER STAGE -->
        <main class="stage">
            <!-- Help Modal -->
            <div class="modal-overlay" :class="{ active: showHelpModal }">
                <div class="modal-header">
                    <h2>LinkUp Studio Guide</h2>
                    <button class="close-btn" @click="showHelpModal = false">×</button>
                </div>
                <div style="max-width:600px;">
                    <ul class="help-list">
                        <li>
                            <strong>1. Going Live 🎥</strong>
                            <p>Click the main button at the bottom to start your stream. The button will turn Red and
                                pulsate to indicate you are On Air.</p>
                        </li>
                        <li>
                            <strong>2. Private Broadcasts & Subscriptions 🔒</strong>
                            <p>In Settings, you can switch visibility to "Private". This allows you to set a monthly
                                subscription fee.</p>
                        </li>
                        <li>
                            <strong>3. Earnings & Transfers 💸</strong>
                            <p>Gifts received during the show accumulate as "Session Revenue". Click "Transfer to
                                Wallet" to move funds.</p>
                        </li>
                        <li>
                            <strong>4. Managing Guests 👥</strong>
                            <p>Open the Guest List to invite users. Once they accept, click "Call" to bring them onto
                                the stage.</p>
                        </li>
                        <li>
                            <strong>5. Interaction Tools 💬</strong>
                            <p>Use the Right Sidebar to chat, answer Q&A questions, and create Polls to engage your
                                audience.</p>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Settings Modal -->
            <div class="modal-overlay" :class="{ active: showSettingsModal }">
                <div class="modal-header">
                    <h2>Broadcast Settings</h2>
                    <button class="close-btn" @click="showSettingsModal = false">×</button>
                </div>
                <div style="max-width:500px;">
                    <div class="form-group">
                        <label>Heading (Title)</label>
                        <input type="text" v-model="liveTitle" placeholder="e.g. Carnival Warmup! 🎭">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select v-model="broadcastType">
                            <option v-if="!liveCategories.length" value="Just Chatting">Just Chatting</option>
                            <option v-for="cat in liveCategories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" v-model="geoLocation">
                    </div>
                    <div class="form-group">
                        <label>Visibility</label>
                        <div class="visibility-toggle">
                            <button :class="['v-btn', { active: visibility === 'public' }]"
                                @click="visibility = 'public'">Public</button>
                            <button :class="['v-btn', { active: visibility === 'private' }]"
                                @click="visibility = 'private'">Private</button>
                        </div>
                        <p v-if="visibility === 'private'" class="text-[11px] text-[#28a4ff] mt-2 leading-tight">
                            🔒 Private broadcasts are restricted to subscribers. Set a fee that viewers must pay to join your stream.
                        </p>
                        <div class="private-settings" v-if="visibility === 'private'">
                            <label style="color:#28a4ff">Sub Fee ($)</label>
                            <input type="number" v-model="subscriptionRate" placeholder="5.00" min="1">
                            <div style="margin-top:10px; font-size:12px; color:#aaa;">
                                You earn (50%): <span style="color:#fff;">${{ (Number(subscriptionRate) * 0.5).toFixed(2)
                                    }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cover Image</label>
                        <label class="file-upload-box" :class="{ 'has-preview': coverImagePreview }">
                            <div v-if="coverImagePreview" class="preview-container">
                                <img :src="coverImagePreview" class="cover-preview" alt="Cover Preview" />
                                <button type="button" class="remove-preview"
                                    @click.prevent="removeCoverImage">×</button>
                            </div>
                            <div v-else class="upload-placeholder">
                                <span style="font-size:24px;">📷</span>
                                <span>Click to upload</span>
                            </div>
                            <input type="file" ref="coverImageInput" accept="image/png, image/jpeg, image/webp"
                                @change="handleCoverImageChange" hidden>
                        </label>
                    </div>

                    <div class="form-group">
                        <label
                            style="color:var(--brand-yellow); border-bottom:1px solid var(--glass-border); padding-bottom:5px; margin-bottom:15px;">Video
                            Settings</label>
                        <div class="video-settings-grid">
                            <div>
                                <span class="mini-label">Base (Canvas) Resolution</span>
                                <select v-model="videoSettings.baseResolution">
                                    <option value="1920x1080">1920x1080</option>
                                    <option value="1280x720">1280x720</option>
                                </select>
                            </div>
                            <div>
                                <span class="mini-label">Output (Scaled) Resolution</span>
                                <select v-model="videoSettings.outputResolution">
                                    <option value="1920x1080">1920x1080</option>
                                    <option value="1280x720">1280x720</option>
                                    <option value="854x480">854x480</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:10px;">
                            <span class="mini-label">Downscale Filter</span>
                            <select v-model="videoSettings.downscaleFilter">
                                <option value="bicubic">Bicubic (Sharpened scaling, 16 samples)</option>
                                <option value="lanczos">Lanczos (Sharpened scaling, 36 samples)</option>
                                <option value="bilinear">Bilinear (Fastest, but blurry)</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn-pill" style="width:100%;" @click="saveSettings" :disabled="isSavingSettings">
                        {{ isSavingSettings ? 'Saving...' : 'Save & Update' }}
                    </button>
                </div>
            </div>

            <!-- Guests Modal -->
            <div class="modal-overlay" :class="{ active: showGuestsModal }">
                <div class="modal-header">
                    <h2>Guest Management</h2>
                    <button class="close-btn" @click="showGuestsModal = false">×</button>
                </div>

                <!-- Tabs -->
                <div style="display:flex; border-bottom:1px solid rgba(255,255,255,0.1); margin-bottom:15px;">
                    <button @click="guestModalTab = 'guests'"
                        :style="{ padding: '10px 20px', borderBottom: guestModalTab === 'guests' ? '2px solid var(--brand-yellow)' : 'none', background: 'transparent', color: guestModalTab === 'guests' ? 'var(--brand-yellow)' : '#fff', cursor: 'pointer' }">
                        Guests ({{ state.guests.length }})
                    </button>
                    <button @click="guestModalTab = 'viewers'"
                        :style="{ padding: '10px 20px', borderBottom: guestModalTab === 'viewers' ? '2px solid var(--brand-yellow)' : 'none', background: 'transparent', color: guestModalTab === 'viewers' ? 'var(--brand-yellow)' : '#fff', cursor: 'pointer' }">
                        Viewers ({{ state.session?.viewer_count || 0 }})
                    </button>
                </div>

                <div v-if="guestModalTab === 'guests'">
                    <div style="display:flex; gap:10px; margin-bottom:20px;" class="relative">
                        <input type="text" ref="guestQueryInput" v-model="guestInput" placeholder="Username..."
                            style="flex:1;" @input="handleGuestInput">
                        <div v-if="showSuggestions && filteredUsers.length > 0"
                            class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-lg z-10 max-h-48 overflow-y-auto mt-1">
                            <div v-for="user in filteredUsers" :key="user.id"
                                class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-b-0"
                                @click="selectUser(user)">
                                <div class="font-semibold text-slate-700">{{ user.name }}</div>
                                <div class="text-sm text-slate-500">{{ user.linkup_id }}</div>
                            </div>
                        </div>
                        <button @click="inviteGuest()"
                            style="padding:10px 20px; background:var(--brand-yellow); color:#000; font-weight:700; border:none; border-radius:8px;">Invite</button>
                    </div>
                    <div v-for="guest in state.guests" :key="guest.id"
                        style="background:rgba(255,255,255,0.05); padding:10px; border-radius:8px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div
                                style="width:30px; height:30px; background:#444; border-radius:50%; display:grid; place-items:center; font-size:12px; font-weight:bold; overflow:hidden;">
                                <img v-if="guest.avatar" :src="guest.avatar" style="width:100%; height:100%; object-fit:cover;" />
                                <span v-else>{{ (guest.name || String(guest.id)).slice(0, 2).toUpperCase() }}</span>
                            </div>
                            <span>{{ guest.name || guest.id }}</span>
                            <span v-if="guest.status === 'pending'" style="font-size:10px; color:var(--brand-yellow); text-transform:uppercase;">Pending</span>
                        </div>
                        <div style="display:flex; gap:5px;">
                            <button v-if="guest.status === 'joined'" @click="addGuestToStage(guest.id)"
                                style="padding:6px 12px; background:var(--success); color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:12px;">Call</button>
                            <button @click="removeGuest(guest.id)"
                                style="padding:6px 12px; background:var(--danger); color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:12px;">Remove</button>
                        </div>
                    </div>
                </div>

                <div v-else>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding:0 5px;">
                        <span style="font-size:12px; color:rgba(255,255,255,0.6);">Active viewers who can be invited</span>
                        <button @click="fetchActiveViewers" :disabled="isLoadingViewers"
                            style="background:rgba(255,255,255,0.1); border:none; border-radius:4px; color:#fff; padding:4px 8px; cursor:pointer; font-size:11px; display:flex; align-items:center; gap:5px;">
                            <span v-if="isLoadingViewers">⏳</span>
                            <span v-else>🔄</span>
                            Refresh
                        </button>
                    </div>
                    <div v-if="isLoadingViewers" style="text-align:center; padding:20px;">Loading viewers...</div>
                    <div v-else-if="!activeViewers.length" style="text-align:center; padding:20px; color:rgba(255,255,255,0.5);">No active viewers to invite.</div>
                    <div v-else v-for="viewer in activeViewers" :key="viewer.id"
                        style="background:rgba(255,255,255,0.05); padding:10px; border-radius:8px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div
                                style="width:30px; height:30px; background:#444; border-radius:50%; display:grid; place-items:center; font-size:12px; font-weight:bold; overflow:hidden;">
                                <img v-if="viewer.avatar" :src="viewer.avatar" style="width:100%; height:100%; object-fit:cover;" />
                                <span v-else>{{ (viewer.name || viewer.linkup_id || 'U').slice(0, 2).toUpperCase() }}</span>
                            </div>
                            <div style="display:flex; flex-direction:column;">
                                <span>{{ viewer.name }}</span>
                                <span style="font-size:10px; color:rgba(255,255,255,0.5);">{{ viewer.linkup_id }}</span>
                            </div>
                        </div>
                        <button @click="inviteGuest(viewer)"
                            style="padding:6px 12px; background:var(--brand-yellow); color:#000; border:none; border-radius:6px; cursor:pointer; font-size:12px; font-weight:bold;">Invite</button>
                    </div>
                </div>
            </div>

            <!-- Network Modal -->
            <div class="modal-overlay network-modal" :class="{ active: showNetworkModal }">
                <div class="network-shell" role="dialog" aria-label="Live Network">
                    <div class="network-topbar">
                        <div class="network-topbar-left">
                            <span class="network-dot" aria-hidden="true"></span>
                            <span>Live Network</span>
                        </div>
                        <button class="network-close-btn" aria-label="Close"
                            @click="showNetworkModal = false">×</button>
                    </div>

                    <div class="network-topics-wrap">
                        <div class="network-topics">
                            <button v-for="topic in networkTopics" :key="topic" class="network-topic-btn"
                                :class="{ active: topic === activeNetworkTopic }" @click="activeNetworkTopic = topic">
                                {{ topic }}
                            </button>
                        </div>
                    </div>

                    <div class="network-content">
                        <div v-if="!filteredLiveStreams.length" class="network-empty">
                            No live streams in <b>{{ activeNetworkTopic }}</b> right now.
                        </div>

                        <div v-else class="network-grid">
                            <article v-for="stream in filteredLiveStreams" :key="stream.id" class="network-live-card"
                                @click.stop="fetchLiveStreamDetail(stream);">
                                <div class="network-thumb-wrap">
                                    <span class="network-live-badge">LIVE</span>

                                    <span v-if="stream.is_subscribed && String(stream?.visibility) === 'private'"
                                        :style="{
                                            position: 'absolute',
                                            top: '10px',
                                            right: '10px',
                                            padding: '8px 10px',
                                            borderRadius: '12px',
                                            background: 'rgba(34, 197, 94, 0.85)',
                                            border: '1px solid rgba(255,255,255,0.22)',
                                            backdropFilter: 'blur(10px)',
                                            color: '#fff',
                                            fontWeight: 900,
                                            fontSize: '12px',
                                            letterSpacing: '.06em',
                                            display: 'inline-flex',
                                            alignItems: 'center',
                                            gap: '8px',
                                            boxShadow: '0 12px 26px rgba(0,0,0,.18)'
                                        }">
                                        ✅ SUBSCRIBED
                                        <button @click.stop="handleUnsubscribe(stream)" :style="{
                                            background: 'rgba(255,255,255,0.2)',
                                            border: '1px solid rgba(255,255,255,0.3)',
                                            borderRadius: '6px',
                                            padding: '2px 6px',
                                            fontSize: '10px',
                                            cursor: 'pointer',
                                            marginLeft: '4px'
                                        }" title="Unsubscribe from this creator">
                                            ✕
                                        </button>
                                    </span>

                                    <span v-else-if="String(stream?.visibility) === 'private'" :style="{
                                        position: 'absolute',
                                        top: '10px',
                                        right: '10px',
                                        padding: '8px 10px',
                                        borderRadius: '12px',
                                        background: 'rgba(255,255,255,0.10)',
                                        border: '1px solid rgba(255,255,255,0.22)',
                                        backdropFilter: 'blur(10px)',
                                        color: '#eaf2ff',
                                        fontWeight: 900,
                                        fontSize: '12px',
                                        letterSpacing: '.06em',
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: '8px',
                                        boxShadow: '0 12px 26px rgba(0,0,0,.18)'
                                    }">
                                        🔒 PRIVATE
                                        <b :style="{ color: '#dfff00', textShadow: '0 0 10px rgba(223,255,0,.18)' }">
                                            {{ `$${Number(parseFloat(stream?.subscription_rate ?? 0) || 0).toFixed(2)}`
                                            }}
                                        </b>
                                    </span>
                                    <img class="network-thumb" :src="stream?.image_url" loading="lazy"
                                        :alt="(stream.title || 'Live Stream') + ' thumbnail'" />
                                </div>
                                <div class="network-card-body">
                                    <div class="network-title">{{ stream.title || 'Live Stream' }}</div>
                                    <div class="network-sub">{{ stream.broadcast_type || 'Entertainment' }}</div>
                                    <div class="network-stats">
                                        <div class="network-stat"><span class="network-icon">👤</span><span
                                                class="network-num">{{ stream.viewer_count || 0 }}</span></div>
                                        <div class="network-stat"><span class="network-icon">❤️</span><span
                                                class="network-num">{{ stream.like_count || 0 }}</span></div>
                                        <div class="network-stat"><span class="network-icon">🎁</span><span
                                                class="network-num">{{ stream.gift_count || 0 }}</span></div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Modal -->
            <AnalyticsModal :visible="showAnalyticsModal" @close="showAnalyticsModal = false" />

            <!-- VIDEO GRID -->
            <div class="video-grid" id="video-grid">
                <div class="video-slot">

                    <div class="stats-overlay">
                        <div class="stat-badge">👤 <span>{{ state.session?.viewer_count ?? 0 }}</span></div>
                        <div class="stat-badge">❤️ <span>{{ state.session?.like_count ?? 0 }}</span></div>
                        <div class="stat-badge">🎁 <span>{{ state.session?.gift_count ?? 0 }}</span></div>
                    </div>

                    <div class="stream-info-layer">
                        <div class="stream-heading">{{ liveStramData?.title || liveTitle || 'Heading Not Set' }}</div>
                        <div class="stream-category">{{ broadcastType }}</div>
                        <div class="stream-location">📍 {{ geoLocation }}</div>
                    </div>

                    <!-- Cover Layer - shown when not joined OR when camera is off -->
                    <div class="cover-layer"
                        :class="{ hidden: joined && (isHost ? (liveStreamComponent?.camOn && !liveStreamComponent?.cameraUnavailable) : liveStreamComponent?.hasRemoteVideo) }">
                        <div v-if="coverImagePreview" class="cover-image-full">
                            <img :src="coverImagePreview" alt="Stream Cover" />
                            <div v-if="joined && (isHost ? (!liveStreamComponent?.camOn || liveStreamComponent?.cameraUnavailable) : !liveStreamComponent?.hasRemoteVideo)"
                                class="cover-overlay-badge">
                                <span>{{ (isHost && liveStreamComponent?.cameraUnavailable) ? '📷 Camera Unavailable' :
                                    '📷 Camera Off' }}</span>
                            </div>
                        </div>
                        <div v-else class="cover-placeholder">
                            <div style="font-size:30px">🖼️</div>
                            <div>{{ joined && (isHost ? (!liveStreamComponent?.camOn || liveStreamComponent?.cameraUnavailable) : !liveStreamComponent?.hasRemoteVideo) ?
                                ((isHost && liveStreamComponent?.cameraUnavailable) ? 'Camera Unavailable'
                                    : 'Camera Off') : 'No Cover Image' }}</div>
                        </div>
                    </div>

                    <div class="reaction-dock" v-if="!isHost && joined">
                        <button class="reaction-btn" @click="sendReaction('bottle')">🍾</button>
                        <button class="reaction-btn" @click="sendReaction('heart')">❤️</button>
                    </div>

                    <!-- Live Stream Component -->
                    <LiveStreamComponent ref="liveStreamComponent"
                        :stream-id="liveStramData?.id ?? state.session?.id ?? null" :is-host="isHost"
                        :playback-url="playback" :stream-settings="{
                            title: liveTitle,
                            broadcastType: broadcastType,
                            visibility: visibility,
                            location: geoLocation,
                            baseResolution: videoSettings.baseResolution,
                            outputResolution: videoSettings.outputResolution,
                            downscaleFilter: videoSettings.downscaleFilter,
                            coverImage: coverImageFile,
                            subscriptionRate: subscriptionRate
                        }" :channel-name="channelName" :show-controls="false"
                        :is-stream-live="liveStramData?.status === 'live' || state.session?.status === 'live'"
                        :owner-id="liveStramData?.user_id || state.session?.user_id" @joined="handleStreamJoined"
                        @left="handleStreamLeft" @stream-ended="handleStreamEnded"
                        @stream-data-loaded="handleStreamDataLoaded" @stream-state-loaded="handleStreamStateLoaded"
                        @guest-published="handleGuestPublished" @guest-unpublished="handleGuestUnpublished"
                        @local-guest-published="handleLocalGuestPublished" />
                </div>

                <!-- Guest Video Slots -->
                <div v-for="guest in state.guests.filter(g => g.status === 'joined')" :key="guest.id"
                    class="video-slot">
                    <div :id="'guest-player-' + guest.id" class="video-player"
                        :class="{ 'video-hidden': String(guest.id) === String(user?.id) && !liveStreamComponent?.camOn }"
                        style="background:#111; display:grid; place-items:center;">
                        <span style="font-size:24px;">{{ guest.name?.charAt(0) || guest.id }}</span>
                    </div>
                    <div class="slot-label">{{ guest.name || guest.id }}</div>
                    <button v-if="isHost" class="btn-kick" @click="removeGuest(guest.id)">✕</button>
                </div>
            </div>

            <!-- Floating Reactions -->
            <div v-for="floater in floaters" :key="floater.id" class="floater"
                :style="{ left: floater.left + '%', bottom: '100px' }">
                {{ floater.emoji }}
            </div>

            <!-- CONTROLS DOCK -->
            <div class="controls-dock">
                <button v-if="joined && !isHost" class="btn-leave-overlay" @click="leaveStream">
                    Leave Stream 🚪
                </button>
                <button v-else class="btn-pill" :class="{ danger: joined }" @click="toggleLive"
                    :disabled="isEndingStream">
                    {{ isEndingStream ? 'Ending...' : (joined ? 'End Stream' : 'Go Live') }}
                </button>
                <div class="dock-divider"></div>
                <button class="btn-circle" v-if="joined && (isHost || isGuestOnStage)"
                    :class="{ muted: !liveStreamComponent?.micOn }" @click="liveStreamComponent?.toggleMic" title="Mic">{{
                        liveStreamComponent?.micOn ? '🎤' :
                            '🚫' }}</button>
                <button class="btn-circle" :class="{ muted: !liveStreamComponent?.camOn }"
                    v-if="joined && (isHost || isGuestOnStage)" @click="liveStreamComponent?.toggleCam" title="Cam">{{
                        liveStreamComponent?.camOn ? '📷' :
                            '🚫' }}</button>
                <!-- <button class="btn-circle" @click="showSettingsModal = true" title="Settings" v-if="!joined">⚙️</button> -->
            </div>
        </main>

        <!-- RIGHT SIDEBAR -->
        <aside class="sidebar-right glass-panel">
            <div class="tabs">
                <button :class="['tab', { active: activeTab === 'chat' }]" @click="activeTab = 'chat'">Chat</button>
                <button :class="['tab', { active: activeTab === 'qna' }]" @click="activeTab = 'qna'">Q&A</button>
                <button :class="['tab', { active: activeTab === 'polls' }]" @click="activeTab = 'polls'">Polls</button>
            </div>

            <!-- Chat Tab -->
            <div class="tab-body" :class="{ active: activeTab === 'chat' }">
                <ChatComponent :stream-id="currentStreamId" :is-host="isHost"
                    v-if="currentStreamId && !showSubscriptionModal" />
            </div>

            <!-- Q&A Tab -->
            <div class="tab-body" :class="{ active: activeTab === 'qna' }">
                <QnaComponent :stream-id="currentStreamId" :is-host="isHost"
                    v-if="currentStreamId && !showSubscriptionModal" />
            </div>

            <!-- Polls Tab -->
            <div class="tab-body" :class="{ active: activeTab === 'polls' }">
                <PollComponent :stream-id="currentStreamId" :is-host="isHost"
                    v-if="currentStreamId && !showSubscriptionModal" />
            </div>

            <!-- Gift Grid -->
            <div class="gift-section" v-if="!isHost && joined">
                <div class="gift-grid">
                    <div v-for="gift in giftItems" :key="gift.id" class="gift-item" @click="sendGiftItem(gift)">
                        <div style="font-size:20px">{{ gift.emoji }}</div>
                        <div style="font-size:9px; font-weight:600; color:#ddd; margin-top:2px;">{{ gift.name }}</div>
                        <!-- Coins -->
                        <span class="flex items-center justify-center gap-1 text-[9px] text-yellow-400">
                            <Coins class="h-4 w-4" />
                            {{ gift.coins }}
                        </span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Gift Animations -->
        <div v-for="animation in giftAnimations" :key="animation.id" class="gift-animation" :style="{
            top: `${animation.top}%`,
            left: `${animation.progress}%`,
            animation: `flyAcross ${animation.duration}s linear forwards`
        }">
            {{ animation.emoji }}
        </div>
        <div v-if="activeModal === 'wallet'" class="fixed inset-0 z-[200] grid place-items-center bg-black/60"
            @click.self="closeModal">
            <WalletModal :initial-tab="walletInitialTab" @close="closeModal" @submit="closeModal"
                @select-pack="closeModal" />
        </div>

        <div class="payout-modal-overlay" :class="{ active: showPayoutModal }" @click.self="closeTransferModal">
            <div class="payout-modal">
                <div class="payout-modal-header">
                    <h2 class="payout-modal-title">Transfer to Wallet</h2>
                    <button class="payout-close-btn" @click="closeTransferModal">×</button>
                </div>

                <div class="payout-rule-box">
                    <span class="payout-rule-icon">💰</span>
                    <strong>Revenue & payout rules</strong><br>
                    LinkUp Live payouts are split 50/50.<br>
                    50% goes to your wallet • LinkUp keeps 50%<br>
                    Minimum cash-out: ${{ MIN_CASH_OUT.toFixed(2) }}
                </div>

                <div class="payout-stats-grid">
                    <div class="payout-stat-card">
                        <div class="payout-stat-label">AVAILABLE CASH</div>
                        <div class="payout-stat-value payout-cash-val">${{ sessionCash.toFixed(2) }}</div>
                    </div>
                    <div class="payout-stat-card">
                        <div class="payout-stat-label">MINIMUM REQUIRED</div>
                        <div class="payout-stat-value">${{ MIN_CASH_OUT.toFixed(2) }}</div>
                    </div>
                </div>

                <div class="payout-breakdown">
                    <div style="text-align:center; margin-bottom:0.8rem;">
                        <span class="payout-pill">Your wallet gets 50%</span>
                    </div>
                    <div class="payout-breakdown-row">
                        <div>You receive → Wallet</div>
                        <div class="payout-you-get">${{ (transferAmount * 0.5).toFixed(2) }}</div>
                    </div>
                    <div class="payout-breakdown-row">
                        <div>LinkUp keeps → Platform</div>
                        <div class="payout-platform-keeps">${{ (transferAmount * 0.5).toFixed(2) }}</div>
                    </div>
                    <div class="payout-breakdown-row">
                        <div>Transfer amount (this time)</div>
                        <div style="font-weight:900;">${{ (transferAmount * 0.5).toFixed(2) }}</div>
                    </div>
                </div>

                <div class="payout-btn-row">
                    <button class="payout-btn payout-btn-cancel" @click="closeTransferModal" :disabled="isTransferring">
                        Cancel
                    </button>
                    <button class="payout-btn payout-btn-confirm" @click="confirmTransfer"
                        :disabled="isTransferring || !canConfirmTransfer">
                        {{ isTransferring ? 'Transferring...' : 'Confirm Transfer' }}
                    </button>
                </div>
            </div>
        </div>
        <Toaster position="top-center" />
        <!-- Subscription Modal for private streams (audience only) -->
        <SubscriptionModal :visible="showSubscriptionModal" :creator="creatorHandle" :amount="subscriptionAmount"
            @close="showSubscriptionModal = false; showNetworkModal = true" @continue="joinAfterSubscription" />

        <!-- Incoming Invite Modal -->
        <div v-if="incomingInvite" class="fixed inset-0 z-[300] grid place-items-center bg-black/80 backdrop-blur-sm">
            <div
                class="bg-slate-800 p-6 rounded-2xl border border-slate-600 text-center max-w-sm w-full mx-4 shadow-2xl">
                <img :src="incomingInvite.host.avatar || 'https://ui-avatars.com/api/?name=' + incomingInvite.host.name"
                    class="w-20 h-20 rounded-full mx-auto mb-4 border-4 border-yellow-400 object-cover shadow-lg">
                <h3 class="text-xl font-bold text-white mb-2 leading-tight">
                    <span class="text-yellow-400">{{ incomingInvite.host.name }}</span> invited you!
                </h3>
                <p class="text-slate-300 mb-8">They want you to join their live stream conversation.</p>
                <div class="flex gap-4 justify-center">
                    <button @click="declineInvite"
                        class="px-6 py-3 rounded-xl bg-slate-700 text-white font-bold hover:bg-slate-600 transition-colors">
                        Decline
                    </button>
                    <button @click="acceptInvite"
                        class="px-6 py-3 rounded-xl bg-yellow-400 text-black font-bold hover:bg-yellow-300 transition-colors shadow-lg shadow-yellow-400/20">
                        Accept & Join
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Coins, ArrowLeftIcon } from 'lucide-vue-next';
import WalletModal from "./Components/Modals/WalletModal.vue";
import { Toaster, toast } from 'vue-sonner'
import 'vue-sonner/style.css';
import './style.css';
import { AppState, GiftAnimation } from './Components/liveInterface';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import PollComponent from './Components/PollComponent.vue';
import LiveStreamComponent from './Components/LiveStreamComponent.vue';
import QnaComponent from './Components/QnaComponent.vue';
import ChatComponent from './Components/ChatComponent.vue';
import SubscriptionModal from './Components/SubscriptionModal.vue';
import AnalyticsModal from './Components/AnalyticsModal.vue';
import confetti from 'canvas-confetti';
import { router } from '@inertiajs/vue3';
type ModalName =
    | 'wallet'
    | 'transfer'
    | 'send'
    | 'request'
    | 'bill'
    | 'merchant'
    | 'coins'
    | 'help'
    | 'asue'
    | 'crypto'
    | 'pos'
    | 'scan'
    | 'add-contact'
    | null;

const page = usePage() as any;
const user = page?.props?.auth?.user;
const liveStramData = ref({}) as any;
const liveUserData = ref(null) as any;
const playback = ref(null) as any;
const pendingPlaybackKey = ref<string | null>(null);
const props = defineProps<{
    user_Streams: any,
    balance: any,
    all_Streams: any,
    gifts: any,
    live_stream_categories: any,
    ToasterProps: any,
    users: any,
}>();

const liveStreams = ref(props.all_Streams || []);
const statsPollingInterval = ref<any>(null);
const isEndingStream = ref(false);
const walletInitialTab = ref<'cash' | 'coins'>('cash');
const activeModal = ref<ModalName>(null);
const isSendingGift = ref(false);
const incomingInvite = ref<any>(null);

const state = reactive<AppState>({
    coins: user?.coins ?? 50,
    cash: props.balance ?? 1450,
    totalCashEarned: 0,
    session: null,
    sessions: [],
    followedUsers: [],
    guests: [],
    totalLikes: 0,
    followers: 0,
    polls: [],
    qna: [],
    liveStartTime: 0,
    recordStartTime: 0,
})

const liveCategories = computed(() => {
    const rows = props.live_stream_categories || []
    return rows
        .map((r: any) => String(r?.category || '').trim())
        .filter((v: string) => Boolean(v))
})

const sessionCoins = ref(0)
const sessionCash = ref(0)

const MIN_CASH_OUT = 50
const showPayoutModal = ref(false)
const isTransferring = ref(false)

const transferAmount = computed(() => {
    return Number(sessionCash.value || 0)
})

const canConfirmTransfer = computed(() => {
    return joined.value && isHost.value && Number(sessionCash.value || 0) >= MIN_CASH_OUT && transferAmount.value > 0
})

const showHelpModal = ref(false)
const showSettingsModal = ref(false)
const showGuestsModal = ref(false)
const guestModalTab = ref<'guests' | 'viewers'>('guests')
const activeViewers = ref<any[]>([])
const isLoadingViewers = ref(false)

const fetchActiveViewers = async () => {
    if (!liveStramData.value?.id) return
    isLoadingViewers.value = true
    try {
        const response = await axios.get(route('frontend.live.viewers', { stream: liveStramData.value.id }))
        if (response.data.success) {
            activeViewers.value = response.data.viewers
        }
    } catch (error) {
        console.error('Failed to fetch viewers:', error)
    } finally {
        isLoadingViewers.value = false
    }
}

watch(showGuestsModal, (val) => {
    if (val && isHost.value) {
        fetchActiveViewers()
    }
})
const showNetworkModal = ref(false)
const showAnalyticsModal = ref(false)
const activeTab = ref<'chat' | 'qna' | 'polls'>('chat')

// Subscription modal state (for private streams)
const showSubscriptionModal = ref(false)
const creatorHandle = computed(() => liveUserData.value?.linkup_id || (liveUserData.value?.name ? `@${liveUserData.value.name}` : null))
const subscriptionAmount = computed(() => {
    const amt = liveStramData.value?.subscription_rate
    const n = Number(amt)
    return Number.isFinite(n) ? n : 0
})

const networkTopics = computed(() => {
    const cats = liveCategories.value.filter((c: string) => c !== 'Private')
    return ['All', 'Private', ...cats]
})

const activeNetworkTopic = ref<string>('All')

watch(networkTopics, (topics) => {
    if (!topics?.length) {
        activeNetworkTopic.value = 'All'
        return
    }
    if (!topics.includes(activeNetworkTopic.value)) {
        activeNetworkTopic.value = 'All'
    }
}, { immediate: true })

const filteredLiveStreams = computed(() => {
    const topic = activeNetworkTopic.value
    if (topic === 'All') return liveStreams.value || []
    if (topic === 'Private') {
        return (liveStreams.value || []).filter((s: any) => String(s?.visibility) === 'private')
    }
    return (liveStreams.value || []).filter((s: any) => {
        const t = String(s?.broadcast_type || s?.type || '')
        return t === topic
    })
})

const guestInput = ref('')
const broadcastType = ref('Just Chatting')

watch(liveCategories, (cats) => {
    if (!cats?.length) return
    if (!broadcastType.value || broadcastType.value === 'Just Chatting') {
        broadcastType.value = cats[0]
    }
}, { immediate: true })
const visibility = ref('public')
const subscriptionRate = ref(5)
const liveTitle = ref('')
const userLocation = [
    user?.city,
    user?.state,
    user?.country
].filter(Boolean).join(', ');

const geoLocation = ref(userLocation || 'Port of Spain, Trinidad')

const liveStreamComponent = ref<any>(null)

const coverImageInput = ref<HTMLInputElement | null>(null)
const coverImageFile = ref<File | null>(null)
const coverImagePreview = ref<string | null>(null)

const videoSettings = ref({
    baseResolution: '1920x1080',
    outputResolution: '1280x720',
    downscaleFilter: 'bicubic'
})

const isSavingSettings = ref(false)

const floaters = ref<any[]>([])

const liveTimer = ref('00:00:00')
let timerInterval: number | null = null
let liveStartTime = 0

const giftAnimations = ref<GiftAnimation[]>([])

const isHost = ref(false);
const joined = ref(false);
const channelName = ref("live_show_1");

const giftItems = [
    { id: 1, emoji: '🧩', name: 'Dominoes', coins: 10 },
    { id: 2, emoji: '🌶️', name: 'Pepper', coins: 15 },
    { id: 3, emoji: '🥥', name: 'Coconut', coins: 20 },
    { id: 4, emoji: '🥭', name: 'Mango Salsa', coins: 30 },
    { id: 5, emoji: '🌮', name: 'Tacos', coins: 50 },
    { id: 6, emoji: '🥃', name: 'Rum', coins: 75 },
    { id: 7, emoji: '🍹', name: 'Sex on Beach', coins: 100 },
    { id: 8, emoji: '💃', name: 'Soca', coins: 150 },
    { id: 9, emoji: '🍗', name: 'Jerk Chicken', coins: 200 },
    { id: 10, emoji: '🥁', name: 'Steel Pan', coins: 300 },
    { id: 11, emoji: '🏖️', name: 'Beach', coins: 500 },
    { id: 12, emoji: '🌴', name: 'Palm Tree', coins: 750 },
    { id: 13, emoji: '👑', name: 'Carnival King', coins: 1000 },
    { id: 14, emoji: '🛥️', name: 'Yacht', coins: 4000 },
    { id: 15, emoji: '🚀', name: 'Rocket', coins: 5000 },
];

const isFollowing = ref(false);
const isGuestOnStage = computed(() => {
    return state.guests.some(g => String(g.id) === String(user?.id) && g.status === 'joined');
});
const currentStreamId = computed(() => liveStramData.value?.id ?? state.session?.id ?? null)

const toggleFollow = async () => {
    const streamId = liveStramData.value?.user_id;
    if (!streamId) {
        toast.error("Error: Stream ID missing. Please refresh the page.");
        return;
    }

    try {
        const manualUrl = `/live/stream/${streamId}/follow`;

        const response = await axios.post(manualUrl);

        if (response.data.success) {
            isFollowing.value = response.data.is_following;

            if (liveUserData.value) {
                liveUserData.value.followers = response.data.count;
            }
            state.followers = response.data.count;

            isFollowing.value ? toast.success("Followed!") : toast.info("Unfollowed");
        }
    } catch (error: any) {
        if (error.response) {
            const errorMessage = error.response.data?.message || 'Server error occurred';
            toast.error(errorMessage);
        } else if (error.request) {
            toast.error("No response from server. Please check your connection.");
        } else {
            toast.error("Request failed: " + error.message);
        }
    }
}
function openModal(name: ModalName | 'wallet-coins' | string) {

    if (name === 'wallet-coins') {
        walletInitialTab.value = 'coins';
        activeModal.value = 'wallet';
        return;
    }
    if (name === 'wallet') {
        walletInitialTab.value = 'cash';
        activeModal.value = 'wallet';
        return;
    }
    activeModal.value = name as ModalName;
}

function closeModal() {
    activeModal.value = null;
}

const goHome = () => {
    window.location.href = '/home'
}

const sendReaction = async (type: string) => {
    if (!joined.value) {
        toast.error("Go Live to react!")
        return
    }
    if (type === 'heart') {
        if (state.session && String(user?.id || '') === String(state.session.host_id || '')) {
            toast.error("You can't like your own stream")
            return
        }

        // Send reaction to server
        try {
            const response = await axios.post(route('frontend.live.reaction', {
                stream: liveStramData.value.id
            }), {
                type: type
            });
            if (response.data.success) {
                // Update local state immediately for better UX
                if (state.session) {
                    state.session.like_count = response.data.like_count;
                }
                spawnFloater('❤️');
            }
        } catch (error) {
            console.error('Failed to send reaction:', error);
            toast.error('Failed to send reaction');
        }
    } else {
        spawnFloater('🍾')
    }
}

const spawnFloater = (emoji: string) => {
    const id = Math.random().toString(36).substring(7)
    floaters.value.push({ id, emoji, left: Math.random() * 80 + 10 })
    setTimeout(() => {
        floaters.value = floaters.value.filter(f => f.id !== id)
    }, 2000)
}

const sendGiftItem = (gift: any) => {
    if (!joined.value) {
        toast.error("Go Live first!")
        return
    }
    if (isSendingGift.value) {
        toast.error("Please wait, gift is being sent...")
        return
    }
    if (state.session && String(user?.id || '') === String(state.session.host_id || '')) {
        toast.error("You can't send gifts to your own stream")
        return
    }
    if (state.coins < gift.coins) {
        toast.error("Not enough coins!")
        return
    }

    isSendingGift.value = true
    state.coins -= gift.coins
    sessionCoins.value += gift.coins
    sessionCash.value += gift.coins * 0.01

    confetti({ particleCount: 20, spread: 40, origin: { x: 0.9, y: 0.8 } })

    sendGift(gift, 1)

    // Reset loading state after a delay
    setTimeout(() => {
        isSendingGift.value = false
    }, 2000)
}

const openTransferModal = () => {
    if (!joined.value || !isHost.value) return
    if (Number(sessionCash.value || 0) < MIN_CASH_OUT) {
        toast.error(`Not enough — minimum cash-out is $${MIN_CASH_OUT.toFixed(2)}`)
        return
    }
    showPayoutModal.value = true
}

const closeTransferModal = () => {
    showPayoutModal.value = false
}

const confirmTransfer = async () => {
    if (!canConfirmTransfer.value) return
    if (!liveStramData.value?.id) {
        toast.error('Stream not found.')
        return
    }

    const total = Number(transferAmount.value)

    isTransferring.value = true
    try {
        const response = await axios.post(route('frontend.live.transfer-earnings', { stream: liveStramData.value.id }), {
            amount: total
        })

        if (response.data.success) {
            if (response.data.new_balance !== undefined) {
                state.cash = response.data.new_balance
            } else {
                state.cash = (state.cash || 0) + Number(total) * 0.5
            }

            // Reset session revenue locally
            sessionCash.value = 0
            sessionCoins.value = 0
            state.cash = Number(state.cash)

            // Immediately refresh stats from server to get updated session revenue
            try {
                const statsResponse = await axios.get(route('frontend.live.stats', { stream: liveStramData.value.id }))
                if (statsResponse.data?.ok && state.session) {
                    // Update host earnings from server (should be 0 after transfer)
                    if (isHost.value) {
                        sessionCoins.value = statsResponse.data.session_coins || 0
                        sessionCash.value = statsResponse.data.session_cash || 0
                    }
                }
            } catch (statsError) {
                console.error('Failed to refresh stats after transfer:', statsError)
            }

            toast.success(`Transfer Complete! Added $${(total * 0.5).toFixed(2)} to your wallet.`)
            closeTransferModal()
        } else {
            toast.error(response.data.message || 'Transfer failed!')
        }
    } catch (error: any) {
        toast.error(error?.response?.data?.message || 'Transfer failed!')
    } finally {
        isTransferring.value = false
    }
}

const handleCoverImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            return
        }
        coverImageFile.value = file
        const reader = new FileReader()
        reader.onload = (e) => {
            coverImagePreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
    }
}

const removeCoverImage = () => {
    coverImageFile.value = null
    coverImagePreview.value = null
    if (coverImageInput.value) {
        coverImageInput.value.value = ''
    }
}

const saveSettings = async () => {
    isSavingSettings.value = true
    try {
        const formData = new FormData()
        formData.append('title', liveTitle.value)
        formData.append('broadcast_type', broadcastType.value)
        formData.append('visibility', visibility.value)
        formData.append('location', geoLocation.value)
        formData.append('subscription_rate', String(subscriptionRate.value))
        formData.append('base_resolution', videoSettings.value.baseResolution)
        formData.append('output_resolution', videoSettings.value.outputResolution)
        formData.append('downscale_filter', videoSettings.value.downscaleFilter)

        if (coverImageFile.value) {
            formData.append('cover_image', coverImageFile.value)
        }

        if (liveStramData.value?.id) {
            const response = await axios.post(route('frontend.go-live.update-settings', { stream: liveStramData.value.id }), formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            if (response.data?.success) {
                toast.success("Settings updated!")
            }
        }

        showSettingsModal.value = false
    } catch (error: any) {
        toast.error(error.response?.data?.message || "Failed to save settings")
    } finally {
        isSavingSettings.value = false
    }
}

const inviteGuest = async (targetUser?: any) => {
    const username = targetUser ? (targetUser.linkup_id || targetUser.name) : guestInput.value
    if (!username || !username.trim()) {
        toast.error("Please enter a username or select a viewer.")
        return
    }

    if (state.guests.some(g => g.id === username || g.name === username)) {
        toast.info("This user is already a guest.")
        return
    }

    try {
        const response = await axios.post(route('frontend.live.invite', { stream: liveStramData.value.id }), {
            username: username
        })
        if (response.data.success) {
            // Add to local guests list with more info if available
            state.guests.push({
                id: targetUser?.id || username,
                name: targetUser?.name || username,
                avatar: targetUser?.avatar || null,
                status: 'pending'
            })
            toast.success(`👥 ${username} invited!`)
            guestInput.value = ''
            showSuggestions.value = false
        } else {
            toast.error(response.data.message || 'Failed to invite')
        }
    } catch (error: any) {
        toast.error(error.response?.data?.message || "Invitation failed")
    }
}

const declineInvite = async () => {
    if (incomingInvite.value?.stream_id) {
        try {
            await axios.post(route('frontend.live.invite.reply', { stream: incomingInvite.value.stream_id }), { accept: false })
        } catch (e) { }
    }
    incomingInvite.value = null
}

const acceptInvite = async () => {
    if (!incomingInvite.value) return
    const streamId = incomingInvite.value.stream_id

    try {
        const res = await axios.post(route('frontend.live.invite.reply', { stream: streamId }), { accept: true })
        incomingInvite.value = null

        // If current stream is active, leave it?
        if (joined.value) {
            await leaveStream()
        }

        const joinToken = res?.data?.join_token || streamId
        window.location.href = route('frontend.go-live.index') + '?join_stream=' + joinToken
    } catch (e) {
        toast.error("Failed to join")
    }
}

const addGuestToStage = (guestId: string) => {
    const guest = state.guests.find(g => g.id === guestId)
    if (guest) {
        guest.status = 'joined'
        showGuestsModal.value = false
    }
}

const removeGuest = async (guestId: string | number) => {
    if (!isHost.value || !liveStramData.value?.id) return

    try {
        await axios.post(route('frontend.live.guest.remove', { stream: liveStramData.value.id }), {
            guest_id: guestId
        })
        // Local state will update via broadcast listener
    } catch (error) {
        console.error('Failed to remove guest:', error)
        toast.error('Failed to remove guest')
    }
}



const formatTime = (seconds: number) => {
    const h = Math.floor(seconds / 3600).toString().padStart(2, '0')
    const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0')
    const s = (seconds % 60).toString().padStart(2, '0')
    return `${h}:${m}:${s}`
}

const startTimer = () => {
    liveStartTime = Date.now()
    timerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - liveStartTime) / 1000)
        liveTimer.value = formatTime(elapsed)
    }, 1000) as unknown as number
}

const stopTimer = () => {
    if (timerInterval) clearInterval(timerInterval)
    liveTimer.value = '00:00:00'
}

const toggleLive = async () => {
    if (!joined.value) {
        await joinStream(`live_${user.id}`, 'host')
    } else {
        await endLiveStream()
    }
}

const handleStreamJoined = async (streamId?: string | number) => {
    joined.value = true
    if (streamId && isHost.value) {
        liveStramData.value = { ...liveStramData.value, id: streamId }

        try {
            const result = await liveStreamComponent.value?.fetchLiveStreamDetail(streamId)
            if (result) {
                liveStramData.value = { ...result.stream, id: result.stream.id || streamId }
                playback.value = result.playback
                liveUserData.value = result.user

                try {
                    const stateRes = await axios.get(route('frontend.live.state', { stream: streamId }))
                    if (stateRes.data?.ok && stateRes.data?.session) {
                        state.session = {
                            id: liveStramData.value.id,
                            host_id: String(user?.id || ''),
                            title: liveStramData.value.title || liveTitle.value || 'Live Stream',
                            type: liveStramData.value.broadcast_type || broadcastType.value,
                            tags: [],
                            geo: liveStramData.value.location || geoLocation.value,
                            visibility: liveStramData.value.visibility || visibility.value as any,
                            subscription_rate: liveStramData.value.subscription_rate || 0,
                            status: stateRes.data.session.status || 'live',
                            started_at: Date.now(),
                            viewer_count: stateRes.data.session.viewer_count || 0,
                            like_count: stateRes.data.session.like_count || 0,
                            gift_count: stateRes.data.session.gift_count || 0,
                            earnings_cents: stateRes.data.session.earnings_cents || 0,
                            events: [],
                            ledger: [],
                            polls: [],
                            qna: [],
                        } as any
                    }
                } catch (stateError) {
                    console.error('Failed to fetch stream state after join:', stateError)
                }
            }
        } catch (error) {
            liveStramData.value = {
                id: streamId,
                title: liveTitle.value || 'Live Stream',
                broadcast_type: broadcastType.value,
                status: 'live'
            }
        }

        const streamData = {
            id: streamId,
            title: liveTitle.value || 'Live Stream',
            broadcast_type: broadcastType.value,
            user: {
                id: user?.id,
                name: user?.name,
                avatar: user?.avatar
            },
            viewer_count: 0,
            like_count: 0,
            gift_count: 0,
            status: 'live'
        }

        if (!liveStreams.value.find((s: any) => String(s.id) === String(streamId))) {
            liveStreams.value.unshift(streamData)
        }
    }
}

const handleStreamLeft = () => {
    joined.value = false
}

const handleStreamEnded = () => {
    joined.value = false;
};

const joinStream = async (channel: string, role: "host" | "audience") => {
    try {
        isHost.value = (role === 'host')
        channelName.value = channel

        if (role === 'host') {
            await liveStreamComponent.value?.joinStream(channel, role)

            let attempts = 0
            const maxAttempts = 50
            while (!liveStramData.value?.id && attempts < maxAttempts) {
                await new Promise(resolve => setTimeout(resolve, 100))
                attempts++
            }

            if (liveStramData.value?.id) {
                if (!state.session || state.session.id !== liveStramData.value.id) {
                    state.session = {
                        id: liveStramData.value.id,
                        host_id: String(user?.id || ''),
                        title: liveStramData.value.title || liveTitle.value || 'Live Stream',
                        type: liveStramData.value.broadcast_type || broadcastType.value,
                        tags: [],
                        geo: liveStramData.value.location || geoLocation.value,
                        visibility: liveStramData.value.visibility || visibility.value as any,
                        subscription_rate: liveStramData.value.subscription_rate || 0,
                        status: 'live',
                        started_at: Date.now(),
                        viewer_count: 0,
                        like_count: 0,
                        gift_count: 0,
                        earnings_cents: 0,
                        events: [],
                        ledger: [],
                        polls: [],
                    } as any

                    // Don't reset session earnings - they should persist across streams
                    // sessionCoins.value = 0
                    // sessionCash.value = 0
                }
                startTimer()
                subscribeToLiveChannel()
                startStatsPolling()
                await liveStreamComponent.value?.startViewerTracking(liveStramData.value.id)
            }
        } else {
            await liveStreamComponent.value?.joinStream(channel, role)
        }
    } catch (err) {
        console.error('Failed to join stream:', err)
    }
}

const endLiveStream = async () => {
    if (isEndingStream.value) return;

    isEndingStream.value = true;

    const streamIdToEnd = liveStramData.value?.id || state.session?.id;

    try {
        stopStatsPolling();
        stopTimer();

        if (streamIdToEnd) {
            await axios.post(route("frontend.go-live.end"), {
                stream_id: streamIdToEnd
            });
        }

        if (liveStreamComponent.value) {
            await liveStreamComponent.value.endStream();
        }

        joined.value = false;
        liveStramData.value = {};
        state.session = null;

        toast.success("Stream ended");

    } catch (err) {
        toast.error("Failed to end stream properly on server");
    } finally {
        isEndingStream.value = false;
    }
}

const fetchLiveStreamDetail = async (data: any) => {
    try {
        const result = await liveStreamComponent.value?.fetchLiveStreamDetail(data.id)

        if (result) {
            // Check if private and NOT subscribed
            if (!isHost.value && result.stream?.visibility === 'private' && !result.is_subscribed) {
                showNetworkModal.value = true
                showSubscriptionModal.value = true
                // temporarily set data so modal has info
                liveStramData.value = result.stream
                liveUserData.value = result.user
                return
            } else {

                showSubscriptionModal.value = false;
                showNetworkModal.value = false; // Close network list so we can see the stream

                liveStramData.value = result.stream
                playback.value = result.playback
                liveUserData.value = result.user

                subscribeToLiveChannel()

                await liveStreamComponent.value?.startViewerTracking(liveStramData.value.id)

                const channelToJoin = liveStramData.value.stream_url || `live_${liveUserData.value?.id}`
                await joinStream(channelToJoin, 'audience')
            }
        }
    } catch (error) {
        console.error('Failed to fetch stream details:', error)
    }
}

const handleStreamDataLoaded = async (data: { stream: any, playback: string, user: any, is_subscribed?: boolean }) => {
    liveStramData.value = data.stream
    liveUserData.value = data.user

    // Load existing stream settings
    if (data.stream?.subscription_rate) {
        subscriptionRate.value = Number(data.stream.subscription_rate)
    }
    if (data.stream?.title) {
        liveTitle.value = data.stream.title
    }
    if (data.stream?.broadcast_type) {
        broadcastType.value = data.stream.broadcast_type
    }
    if (data.stream?.visibility) {
        visibility.value = data.stream.visibility
    }
    if (data.stream?.location) {
        geoLocation.value = data.stream.location
    }

    // If viewer (not host) is loading a private stream, check if NOT subscribed
    if (!isHost.value && (data.stream?.visibility === 'private') && !data.is_subscribed) {
        showNetworkModal.value = true
        showSubscriptionModal.value = true
        pendingPlaybackKey.value = data.playback
        playback.value = null
    } else {
        // If public or private+subscribed
        showSubscriptionModal.value = false // Ensure it's closed
        if (showNetworkModal.value) {
            // We might want to keep network modal open if looking at list,
            // but this function usually runs when "entering" the stream.
            // For now, let's assume if data loaded we might want to close if it was the blockade.
            // However, let's leave showNetworkModal alone or explicitly close it if it was open for subscription.
        }

        playback.value = data.playback
        pendingPlaybackKey.value = null
    }

    if (data.user?.id) {
        try {
            const response = await axios.get(route('frontend.live.stream.followers', { stream: data.user.id }));
            if (response.data?.success) {
                isFollowing.value = response.data.is_following || false;
                if (liveUserData.value) {
                    liveUserData.value.followers = response.data.count || 0;
                }
                state.followers = response.data.count || 0;
            }
        } catch (error: any) {
            isFollowing.value = false;
            if (liveUserData.value) {
                liveUserData.value.followers = 0;
            }
            state.followers = 0;
        }
    } else {
        isFollowing.value = false;
        if (liveUserData.value) {
            liveUserData.value.followers = 0;
        }
        state.followers = 0;
    }
}

const handleStreamStateLoaded = (sessionState: any) => {
    if (sessionState && liveStramData.value) {
        state.session = {
            id: liveStramData.value.id,
            host_id: String(liveUserData.value?.id || ''),
            title: liveStramData.value.title,
            type: liveStramData.value.broadcast_type || 'Entertainment',
            tags: [],
            geo: liveStramData.value.location || '',
            visibility: liveStramData.value.visibility || 'public' as any,
            subscription_rate: 0,
            status: liveStramData.value.status === 'live' ? 'live' : (sessionState.status || 'live'),
            started_at: Date.now(),
            viewer_count: sessionState.viewer_count || 0,
            like_count: sessionState.like_count || 0,
            gift_count: sessionState.gift_count || 0,
            earnings_cents: sessionState.earnings_cents || 0,
            events: [],
            ledger: [],
            polls: [],
            qna: [],
        } as any

        if (sessionState.guests) {
            state.guests = sessionState.guests.map((g: any) => ({
                id: g.id,
                name: g.name,
                avatar: g.avatar,
                status: g.status || 'joined'
            }));
        }
    }
}

const sendGift = async (gift: any, qty: number = 1) => {
    if (!joined.value || !liveStramData.value?.id) return
    try {
        await axios.post(route('frontend.live.gift', { stream: liveStramData.value.id }), { gift: gift, qty });
    } catch (error) {
        console.error('Gift failed:', error);
    }
}

const animateGift = (emoji: string) => {
    const animation: GiftAnimation = {
        id: Math.random().toString(36).substring(7),
        emoji,
        top: Math.random() * 70 + 15,
        progress: 100,
        duration: 1.5 + Math.random()
    }
    giftAnimations.value.push(animation)
    setTimeout(() => {
        giftAnimations.value = giftAnimations.value.filter(a => a.id !== animation.id)
    }, animation.duration * 1000)
}

const stopViewerTracking = async () => {
    await liveStreamComponent.value?.stopViewerTracking()
}

const handleGuestPublished = (data: { uid: string | number, track: any }) => {
    const guestId = data.uid;
    const track = data.track;

    nextTick(() => {
        const containerId = 'guest-player-' + guestId;
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '';
            track.play(containerId);
        }
    });
}

const handleGuestUnpublished = (uid: string | number) => {
    const containerId = 'guest-player-' + uid;
    const container = document.getElementById(containerId);
    if (container) {
        const guest = state.guests.find(g => String(g.id) === String(uid));
        container.innerHTML = `<span style="font-size:24px;">${guest?.name?.charAt(0) || uid}</span>`;
    }
}

const handleLocalGuestPublished = (track: any) => {
    if (user?.id) {
        handleGuestPublished({ uid: user.id, track });
    }
}

const startStatsPolling = () => {
    if (!liveStramData.value?.id || statsPollingInterval.value) return
    statsPollingInterval.value = setInterval(async () => {
        try {
            const response = await axios.get(route('frontend.live.stats', { stream: liveStramData.value.id }))
            if (response.data?.ok && state.session) {
                state.session.viewer_count = response.data.viewer_count || 0
                state.session.gift_count = response.data.gift_count || 0
                state.session.like_count = response.data.like_count || 0

                // Update host earnings from server
                if (isHost.value) {
                    sessionCoins.value = response.data.session_coins || 0
                    sessionCash.value = response.data.session_cash || 0
                }
            }
        } catch {
            console.error('Stats poll failed')
        }
    }, 10000)
}

const stopStatsPolling = () => {
    if (statsPollingInterval.value) {
        clearInterval(statsPollingInterval.value)
        statsPollingInterval.value = null
    }
}


const subscribeToLiveChannel = () => {
    try {
        if (!liveStramData.value?.public_id) {
            return
        }

        const channel = 'live-stream.' + String(liveStramData.value.public_id)

        window.Echo.private(channel)
            .listen('.LiveGiftSent', (event: any) => {
                if (event.gift?.emoji) animateGift(event.gift.emoji)
                if (state.session) {
                    // Use actual gift count from broadcast event instead of incrementing locally
                    state.session.gift_count = event.gift_count || 0
                }

                // Update host earnings when gift is sent
                if (isHost.value && event.gift) {
                    sessionCoins.value += event.gift.coins || 0
                    sessionCash.value += (event.gift.coins || 0) * 0.01
                }

                confetti({ particleCount: 20, spread: 40, origin: { x: 0.9, y: 0.8 } })
            })
            .listen('.LiveReactionSent', (event: any) => {
                if (state.session) {
                    // Update like count from broadcast event
                    state.session.like_count = event.like_count || 0
                }
                // Show floating reaction for all viewers
                if (event.emoji) {
                    spawnFloater(event.emoji)
                }
            })
            .listen('.ViewerCountUpdated', (event: any) => {
                if (state.session) {
                    state.session.viewer_count = event.viewer_count || 0
                }
            })
            .listen('.LiveInviteReply', (event: any) => {
                const guestId = event.user.id
                const guestName = event.user.name
                const guestAvatar = event.user.avatar
                const guest = state.guests.find(g => String(g.id) === String(guestId))

                if (guest) {
                    if (event.accepted) {
                        guest.status = 'joined'
                        if (isHost.value) toast.success(`${guestName} accepted invitation!`)
                    } else {
                        state.guests = state.guests.filter(g => String(g.id) !== String(guestId))
                        if (isHost.value) toast.info(`${guestName} declined invitation.`)
                    }
                } else if (event.accepted) {
                    state.guests.push({
                        id: guestId,
                        name: guestName,
                        avatar: guestAvatar,
                        status: 'joined'
                    })
                }
            })
            .listen('.LiveGuestRemoved', (event: any) => {
                const removedId = event.guest_id
                state.guests = state.guests.filter(g => String(g.id) !== String(removedId))

                // If I am the removed guest, immediately leave the Agora channel and exit the stream UI.
                if (user?.id && String(user.id) === String(removedId)) {
                    toast.error('You were removed from the stream')
                    forceLeaveStream()
                    return
                }

                toast.info('A guest has left the stream')
            })
    } catch (error) {
        console.error('Failed to subscribe to live channel:', error)
    }
}

const refreshNetworkStats = async () => {
    try {
        // Use Inertia's reload method to get fresh props
        await router.reload({
            only: ['all_Streams'],
            preserveState: true,
            preserveScroll: true,
            async: true,
            onSuccess: (page) => {

                if (page.props?.all_Streams) {
                    const newStreams = page.props.all_Streams

                    if (newStreams.length === 0) {
                        return
                    }

                    // If liveStreams is empty (new user), initialize it
                    if (liveStreams.value.length === 0) {
                        liveStreams.value = [...newStreams]
                        return
                    }

                    let updatedCount = 0
                    // Update existing streams with new stats
                    liveStreams.value.forEach((stream: any, index: number) => {
                        const updatedStream = newStreams.find((s: any) => String(s.id) === String(stream.id))
                        if (updatedStream) {
                            // Check if stats actually changed
                            const statsChanged =
                                updatedStream.viewer_count !== stream.viewer_count ||
                                updatedStream.like_count !== stream.like_count ||
                                updatedStream.gift_count !== stream.gift_count

                            // Update only stats that might have changed
                            liveStreams.value[index].viewer_count = updatedStream.viewer_count || stream.viewer_count
                            liveStreams.value[index].like_count = updatedStream.like_count || stream.like_count
                            liveStreams.value[index].gift_count = updatedStream.gift_count || stream.gift_count
                            updatedCount++
                        }
                    })
                }
            }
        })

    } catch (err) {
        console.error('[Network] Failed to refresh network stats:', err)
    }
}

const refreshLiveStreams = async () => {
    try {
        const response = await axios.get(route('frontend.go-live.index'))
        if (response.data?.props?.all_Streams) {
            liveStreams.value = response.data.props.all_Streams
        }
    } catch (err) {
        console.error('Refresh failed:', err)
    }
}

let liveStreamsInterval: number | null = null
let networkStatsInterval: number | null = null

const handleStreamStartedEvent = (e: any) => {
    const streamData = e
    const streamId = streamData.id

    if (!streamId) {
        return
    }

    const existingIndex = liveStreams.value.findIndex((s: any) => String(s.id) === String(streamId))

    if (existingIndex === -1) {
        // Compute is_subscribed for new private streams (monthly/one-time)
        if (String(streamData.visibility) === 'private' && user?.id && String(streamData.user_id) !== String(user.id)) {
            // Simple client-side check: if user has any monthly sub for this creator, mark subscribed
            const monthlySub = liveStreams.value.find((s: any) =>
                String(s.visibility) === 'private' &&
                String(s.user_id) === String(streamData.user_id) &&
                s.is_subscribed === true
            )
            if (monthlySub) {
                streamData.is_subscribed = true
            } else {
                streamData.is_subscribed = false
            }
        } else {
            streamData.is_subscribed = true // public or host
        }
        liveStreams.value.unshift(streamData)
    } else {
        liveStreams.value[existingIndex] = { ...liveStreams.value[existingIndex], ...streamData }
    }
}

const loadFollowerCount = async () => {
    if (!user?.id) return;

    try {
        const response = await axios.get(route('frontend.live.stream.followers', { stream: user.id }));
        if (response.data?.success) {
            state.followers = response.data.count || 0;
        }
    } catch (error: any) {
        console.error('Failed to load follower count:', error);
        state.followers = 0;
    }
};

onMounted(() => {
    loadFollowerCount();

    // Immediately refresh stats to get latest counts
    refreshNetworkStats();

    liveStreamsInterval = setInterval(refreshLiveStreams, 30000) as unknown as number

    // Also start network stats polling as a backup
    networkStatsInterval = setInterval(refreshNetworkStats, 5000) as unknown as number

    const setupEchoSubscription = (retryCount = 0) => {
        const maxRetries = 20

        if (!window.Echo) {
            if (retryCount < maxRetries) {
                setTimeout(() => setupEchoSubscription(retryCount + 1), 500)
            } else {
                console.error('❌ Echo not available after maximum retries')
            }
            return
        }

        const pusher = (window.Echo as any).connector?.pusher
        if (pusher) {
            const connectionState = pusher.connection?.state

            if (connectionState !== 'connected' && connectionState !== 'connecting') {
                if (retryCount < maxRetries) {
                    setTimeout(() => setupEchoSubscription(retryCount + 1), 500)
                    return
                }
            }
        }

        try {
            const liveStreamsChannel = window.Echo.channel('live-streams')

            // Listen for subscription success
            liveStreamsChannel.subscribed(() => {
                console.log('[Network] Successfully subscribed to live-streams channel')
            })

            // Listen for subscription errors
            liveStreamsChannel.error((error: any) => {
                console.error('[Network] Failed to subscribe to live-streams channel:', error)
                // Fallback to polling if WebSocket fails
                if (!networkStatsInterval) {
                    networkStatsInterval = setInterval(refreshNetworkStats, 5000) as unknown as number
                }
            })

            liveStreamsChannel
                .listen('.StreamStarted', (e: any) => {
                    handleStreamStartedEvent(e)
                })
                .listen('.StreamEnded', (e: any) => {
                    const streamId = e.id

                    if (!streamId) {
                        return
                    }
                    const beforeCount = liveStreams.value.length
                    liveStreams.value = liveStreams.value.filter((s: any) => String(s.id) !== String(streamId))
                    const afterCount = liveStreams.value.length

                    if (beforeCount > afterCount) {
                        toast.info('Stream ended')
                    }
                })
                .listen('.ViewerCountUpdated', (e: any) => {
                    // Update viewer count in network list for the specific stream
                    const streamIndex = liveStreams.value.findIndex((s: any) => String(s.id) === String(e.stream_id))
                    if (streamIndex !== -1) {
                        liveStreams.value[streamIndex].viewer_count = e.viewer_count || 0
                    }
                })
                .listen('.LiveReactionSent', (e: any) => {

                    // Update like count in network list for the specific stream
                    const streamIndex = liveStreams.value.findIndex((s: any) => String(s.id) === String(e.stream_id))

                    if (streamIndex !== -1) {
                        const oldCount = liveStreams.value[streamIndex].like_count || 0
                        liveStreams.value[streamIndex].like_count = e.like_count || 0
                    }
                })
                .listen('.LiveGiftSent', (e: any) => {

                    // Update gift count in network list for the specific stream
                    const streamIndex = liveStreams.value.findIndex((s: any) => String(s.id) === String(e.stream_id))

                    if (streamIndex !== -1) {
                        const oldCount = liveStreams.value[streamIndex].gift_count || 0
                        liveStreams.value[streamIndex].gift_count = e.gift_count || 0
                    }
                })
        } catch (error) {
            console.error('Failed to setup Echo subscription:', error)
        }
    }

    setupEchoSubscription()

    if (user?.id) {
        window.Echo.private(`App.Models.User.${user.id}`)
            .listen('.LiveInviteSent', (e: any) => {
                // If I am the host of this stream, ignore (shouldn't happen)
                if (String(e.host.id) === String(user.id)) return

                // If I am already a guest on stage in this stream, ignore
                const amOnStage = state.guests.some(g => String(g.id) === String(user.id) && g.status === 'joined')
                if (amOnStage) return

                // We allow invitations even if we are already viewing the stream
                // This lets viewers upgrade to guests
                incomingInvite.value = e
                // Play notification sound if possible
            })
    }

    // Check for auto-join query param
    const urlParams = new URLSearchParams(window.location.search);
    const joinStreamId = urlParams.get('join_stream');
    const showNetwork = urlParams.get('network');

    if (showNetwork === '1') {
        showNetworkModal.value = true;
        // Clean up URL
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: newUrl }, '', newUrl);
        return; // Prevent any auto-join logic from running
    }

    // Also check URL path for audience-style joining (e.g. /go-live/123)
    let urlStreamId: string | null = null;
    const pathParts = window.location.pathname.split('/');
    const lastPart = pathParts[pathParts.length - 1];
    if (lastPart && !isNaN(Number(lastPart))) {
        urlStreamId = lastPart;
    }

    if (joinStreamId) {
        // Clear param
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: newUrl }, '', newUrl);

        setTimeout(async () => {
            // Fetch stream details
            try {
                const details = await liveStreamComponent.value?.fetchLiveStreamDetail(joinStreamId);
                if (details && details.stream) {
                    liveStramData.value = details.stream;
                    liveUserData.value = details.user || null
                    playback.value = details.playback || null

                    // Ensure we join the correct Agora channel and subscribe to realtime stream events.
                    channelName.value = liveStramData.value.stream_url || `live_${liveUserData.value?.id}`
                    subscribeToLiveChannel()
                    await liveStreamComponent.value?.startViewerTracking(liveStramData.value.id)

                    // Add myself to guest list so slot appears
                    if (user?.id) {
                        const idx = state.guests.findIndex(g => String(g.id) === String(user.id));
                        if (idx === -1) {
                            state.guests.push({
                                id: user.id,
                                name: user.name,
                                avatar: user.avatar,
                                status: 'joined'
                            });
                        } else {
                            state.guests[idx].status = 'joined';
                        }
                    }

                    await liveStreamComponent.value?.joinStream(null, 'guest');
                }
            } catch (e) {
                toast.error("Could not auto-join stream. Use the list to join.");
            }
        }, 800)
    } else if (urlStreamId && !isHost.value) {
        // Guest/Audience joining via URL path
        setTimeout(async () => {
            try {
                await fetchLiveStreamDetail({ id: urlStreamId });
            } catch (e) {
                console.error("Failed to auto-load stream from URL", e);
            }
        }, 500);
    }
})

onUnmounted(() => {
    stopTimer()
    stopViewerTracking()
    stopStatsPolling()
    if (liveStreamsInterval) clearInterval(liveStreamsInterval)
    if (networkStatsInterval) clearInterval(networkStatsInterval)
    liveStreamComponent.value?.leaveStream()
})

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.success(flash.success)
    if (flash?.error) toast.error(flash.error)
}, { deep: true, immediate: true })

const showSuggestions = ref(false);
const filteredUsers = computed(() => {
    if (!guestInput.value.startsWith('@')) {
        return [];
    }

    const query = guestInput.value.slice(1).toLowerCase();
    return props.users.filter((user: any) =>
        user.name.toLowerCase().includes(query) ||
        user.linkup_id.toLowerCase().includes(query)
    );
});

const handleGuestInput = () => {
    showSuggestions.value = guestInput.value.startsWith('@') && guestInput.value.length > 1;
};

const selectUser = (user: Record<string, any>) => {
    guestInput.value = user.linkup_id;
    showSuggestions.value = false;
};
const forceLeaveStream = async () => {
    await leaveStreamInternal(true)
}

const leaveStream = async () => {
    await leaveStreamInternal(false)
}

const handleUnsubscribe = async (stream: any) => {
    if (!stream?.user_id || !user?.id) {
        toast.error('Unable to unsubscribe. Please refresh and try again.')
        return
    }

    if (!confirm('Are you sure you want to unsubscribe from this creator? You will lose access to their private streams.')) {
        return
    }

    try {
        await axios.post(route('frontend.live.unsubscribe'), {
            user_streamer_id: stream.user_id
        })

        toast.success('Unsubscribed successfully')

        // Update UI immediately
        stream.is_subscribed = false

        // Also update other streams from this creator in the list
        liveStreams.value.forEach((s: any) => {
            if (String(s.user_id) === String(stream.user_id) && String(s.visibility) === 'private') {
                s.is_subscribed = false
            }
        })
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to unsubscribe')
    }
}

const leaveStreamInternal = async (skipServerRemove: boolean) => {
    try {
        // If I am a joined guest in this stream, inform server so host/others remove my guest slot.
        if (!skipServerRemove && !isHost.value && liveStramData.value?.id && user?.id) {
            const amJoinedGuest = state.guests.some(
                (g) => String(g.id) === String(user.id) && g.status === 'joined'
            )
            if (amJoinedGuest) {
                try {
                    await axios.post(route('frontend.live.guest.remove', { stream: liveStramData.value.id }), {
                        guest_id: user.id,
                    })
                } catch { }
            }
        }

        await stopViewerTracking();
        stopStatsPolling();

        if (liveStreamComponent.value) {
            await liveStreamComponent.value.leaveStream();
        }

        joined.value = false;
        liveStramData.value = {};
        state.session = null;
        state.guests = [];
        playback.value = null;
        liveUserData.value = null;
        channelName.value = '';
        incomingInvite.value = null;

        showNetworkModal.value = true;

        // If I'm a guest, leave the page entirely (prevents refresh from re-joining any stream state).
        if (!isHost.value) {
            window.location.href = route('frontend.go-live.index') + '?network=1'
        }

    } catch (err) {
        console.error('Error leaving stream:', err);
    }
}

const joinAfterSubscription = async (payload: any) => {
    if (!liveStramData.value?.id) return;

    try {
        const response = await axios.post(route('frontend.live.subscribe', {
            stream: liveStramData.value.id
        }), {
            plan: payload.plan
        })

        if (response.data.url) {
            window.location.href = response.data.url
        } else {
            toast.error("Failed to initiate payment")
        }
    } catch (error: any) {
        console.error("Subscription error:", error)
        toast.error(error.response?.data?.error || "Subscription failed")
    }
}
</script>
<style scoped>
.btn-leave-overlay {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 100;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-leave-overlay:hover {
    background: rgba(235, 64, 52, 0.8);
    /* Turns reddish on hover */
    transform: scale(1.05);
}

.back-btn {
    background: transparent;
    border: none;
    color: white;
    cursor: pointer;
    margin-right: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    border-radius: 50%;
    transition: background 0.3s ease;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}
</style>
