<template>
  <div
    class="min-h-screen text-ink bg-[radial-gradient(1200px_800px_at_-10%_-20%,#11396a_0%,transparent_60%),radial-gradient(1200px_800px_at_120%_120%,#1a5fbf_0%,transparent_55%),linear-gradient(140deg,#0c2a47,#0e5296)] text-white">
    <!-- Floating Back Button -->
    <Sonner />
    <div class="floating-btn absolute top-6 right-6 z-10">
      <button @click="goBackToSite"
        class="btn ghost bg-white/18 text-white border border-line px-4 py-2 rounded-xl font-bold cursor-pointer transition-transform hover:scale-105">
        Back to Main Site
      </button>
    </div>

    <div class="app grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-4 p-5 max-w-full box-border relative">
      <!-- Sidebar -->
      <aside class="glass rounded-custom p-4 sticky top-5 h-[calc(100dvh-40px)] rounded-md overflow-hidden">
        <div class="brand flex items-center gap-2 mb-3">
          <div
            class="logo w-7 h-7 grid place-items-center rounded-xl bg-gradient-to-br from-[#28a4ff] to-[#6a5cff] shadow-[inset_0_0_0_1px_rgba(255,255,255,0.35)]">
            🔗
          </div>
          <strong class="text-white text-xl font-bold">LinkUp</strong>
        </div>

        <div
          class="user flex items-center gap-5 p-3 rounded-2xl bg-gradient-to-b from-[rgba(255,255,255,0.18)] to-[rgba(255,255,255,0.08)] flex-wrap">
          <img :src="user.avatar" alt="Shanae" class="w-16 h-16 rounded-2xl object-cover border border-line" />
          <div>
            <div><strong class="text-white font-bold">{{ user.name }}</strong></div>
            <div class="sub text-muted text-lg">
              <span :class="[
                'popularity-badge inline-flex items-center gap-5 px-2 py-1 rounded-full text-xs font-semibold',
                popularityBadgeClass
              ]">
                {{ user.country }}
              </span>
            </div>
          </div>
          <button @click="toggleFollow" :class="[
            'follow-btn ml-auto px-3 py-1.5 text-xs rounded-xl text-white border-none cursor-pointer transition-colors',
            isFollowing ? 'bg-[#ff4d4d] hover:bg-red-600' : 'bg-[#28a4ff] hover:bg-[#6a5cff]'
          ]">
            {{ isFollowing ? 'Unfollow' : 'Follow' }}
          </button>
          <span class="follower-count text-muted text-xs ml-2">
            {{ followersCount.toLocaleString() }} followers
          </span>
        </div>

        <nav class="mt-3 grid gap-2">
          <!-- No buttons in nav as per request -->
        </nav>

        <div class="wallet grid gap-2 mt-3">
          <div
            class="pill flex justify-between items-center p-3 rounded-2xl border border-line bg-gradient-to-b from-black/30 to-black/20">
            <span class="text-white font-bold">Coins</span>
            <span class="value px-2.5 py-1 rounded-full bg-white/18 text-white font-bold">
              {{ props.balance }}
            </span>
          </div>
          <div
            class="pill flex justify-between items-center p-3 rounded-2xl border border-line bg-gradient-to-b from-black/30 to-black/20">
            <span class="text-white font-bold">Total Cash ($)</span>
            <span class="value px-2.5 py-1 rounded-full bg-white/18 text-white font-bold">
              {{ (state.totalCashEarned / 100).toFixed(2) }}
            </span>
          </div>
        </div>

        <div class="guest-invite mt-3">
          <span
            class="badge flex items-center gap-2 border border-line bg-white/10 px-2.5 py-2 rounded-full text-white">
            Invite Guest:
            <input v-model="guestInput" @keydown.enter="inviteGuest" placeholder="Enter guest ID (e.g., guest1)"
              class="bg-white/10 border border-line text-white px-2.5 py-1.5 rounded-xl outline-none placeholder:text-muted/80 w-32" />
            <button @click="inviteGuest"
              class="btn small bg-white text-gray-900 px-2.5 py-1.5 rounded-xl text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
              Invite
            </button>
          </span>
        </div>

        <button @click="transferToWallet"
          class="btn ghost w-full mt-3 bg-white/18 text-white border border-line px-4 py-2.5 rounded-xl font-bold cursor-pointer transition-transform hover:scale-105">
          Transfer to Wallet
        </button>
      </aside>

      <!-- Main Content -->
      <main class="grid gap-4">
        <!-- Live Control Panel -->
        <div class="glass rounded-custom rounded-md overflow-hidden">
          <h3
            class="panel-title flex items-center gap-2 m-0 p-3 border-b border-line bg-gradient-to-b from-black/30 to-black/20 text-white text-xl font-bold">
            🎥 Live Control
          </h3>
          <div class="panel-body p-3">
            <div class="toolbar flex gap-2 flex-wrap items-center">
              <button @click="goLive" :disabled="!!state.session" :class="[
                'btn primary px-4 py-2.5 rounded-xl font-bold text-gray-900 cursor-pointer transition-transform hover:scale-105',
                state.session?.status === 'live' ? 'bg-[#00cc00] pulsate' : 'bg-gradient-to-r from-[#28a4ff] to-[#6a5cff] text-white'
              ]">
                Go Live
              </button>
              <button @click="endLive" :disabled="!state.session"
                class="btn ghost bg-white/18 text-white border border-line px-4 py-2.5 rounded-xl font-bold cursor-pointer transition-transform hover:scale-105 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                End Live
              </button>

              <span
                class="badge flex items-center gap-2 border border-line bg-white/10 text-white font-bold px-2.5 py-2 rounded-full">
                Broadcast Type:
                <select v-model="broadcastType" @change="updateTagOptions"
                  class="bg-[#0e5296] border border-line text-white px-2 py-1 rounded-xl outline-none">
                  <option value="Entertainment" selected>Entertainment</option>
                  <option value="Politics">Politics</option>
                  <option value="Current Events">Current Events</option>
                  <option value="News">News</option>
                </select>
              </span>

              <span
                class="badge flex items-center gap-2 border border-line bg-white/10 px-2.5 py-2 rounded-full text-white font-bold">
                Visibility:
                <select v-model="visibility" @change="updateSubscriptionRateVisibility"
                  class="bg-[#0e5296] border border-line text-white px-2 py-1 rounded-xl outline-none">
                  <option value="public" selected>Public</option>
                  <option value="followers">Followers</option>
                  <option value="private">Private</option>
                </select>
              </span>

              <span v-if="visibility === 'private'"
                class="badge flex items-center gap-2 border border-line bg-white/10 px-2.5 py-2 rounded-full text-white font-bold">
                Subscription Rate:
                <select v-model="subscriptionRate"
                  class="bg-[#0e5296] border border-line text-black px-2 py-1 rounded-xl outline-none">
                  <option value="200">$2/month</option>
                  <option value="500" selected>$5/month</option>
                  <option value="1000">$10/month</option>
                </select>
              </span>

              <span
                class="badge flex items-center gap-2 border border-line bg-white/10 px-2.5 py-2 rounded-full text-white font-bold">
                Geo: <span>{{ geoLocation }}</span>
              </span>

              <span
                class="badge flex items-center gap-2 border border-line bg-white/10 px-2.5 py-2 rounded-full text-white font-bold">
                Tag:
                <select v-model="selectedTag" @change="updateStageBackground"
                  class="bg-[#0e5296] border border-line text-white px-2 py-1 rounded-xl outline-none">
                  <option v-for="tag in availableTags" :key="tag" :value="tag">
                    {{ tag }}
                  </option>
                </select>
              </span>

              <span
                class="badge flex items-center gap-2 border border-line bg-white/10 px-2.5 py-2 rounded-full text-white font-bold">
                Title:
                <input v-model="liveTitle" placeholder="Enter live title..."
                  class="bg-white/10 border border-line text-white px-2 py-1 rounded-xl outline-none placeholder:text-muted/80 w-40" />
              </span>

              <button @click="showHelpModal = true"
                class="btn ghost bg-white/18 text-white border border-line px-4 py-2.5 rounded-xl font-bold cursor-pointer transition-transform hover:scale-105">
                Help ❓
              </button>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-4 mt-3">
              <!-- Live Stage -->

              <div class="glass p-3 rounded-custom rounded-md overflow-hidden">
                <div :class="[
                  'live-stage relative aspect-video rounded-2xl overflow-hidden',
                  stageBackgroundClass,
                  state.mediaStream ? 'live-active' : ''
                ]">

                  <div v-if="liveStramData.title"
                    class="live-title absolute bottom-12 left-2 bg-black/60 px-3 py-2 rounded-lg text-base font-semibold z-10">
                    {{ liveStramData?.title || '' }}
                  </div>

                  <div v-if="state.totalLikes >= 10000"
                    class="trophy absolute top-2 right-2 text-4xl text-yellow-400 z-10">
                    🏆
                  </div>

                  <div class="video-grid w-full h-full">
                    <div class="guest-video bg-[#07162d] rounded-lg overflow-hidden relative aspect-video">
                      <!-- when the playback url  -->
                      <iframe v-if="liveStramData?.playback_url" loading="lazy" class="w-full h-full object-cover block"
                        title="Gumlet video player" :src=playback
                        allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen"></iframe>
                      <!-- when no play back url  -->
                      <div v-else class="absolute inset-0 flex items-center justify-center">
                        <span class="text-white text-sm sm:text-base font-medium">No Live Stream Available</span>
                      </div>
                      <span v-if="liveUserData?.name"
                        class="label absolute bottom-2 left-2 bg-black/60 px-2 py-1 rounded text-xs">{{
                          liveUserData?.name }}</span>
                    </div>

                    <div v-for="guest in state.guests.filter(g => g.status === 'joined')" :key="guest.id"
                      class="guest-video bg-[#07162d] rounded-lg overflow-hidden relative aspect-video">
                      <div class="w-full h-full bg-[#07162d] flex items-center justify-center text-white">
                        {{ guest.id }}
                      </div>
                      <span class="label absolute bottom-2 left-2 bg-black/60 px-2 py-1 rounded text-xs">
                        {{ guest.id }}
                      </span>
                    </div>


                  </div>

                  <!-- Live Overlay -->
                  <div class="live-overlay absolute inset-0 flex flex-col justify-between p-3 pointer-events-none">
                    <div class="stats flex gap-2 flex-wrap pointer-events-auto">
                      <div
                        class="chip flex items-center gap-1.5 bg-black/45 px-2.5 py-2 rounded-full border border-line">
                        🟢 <span>{{ state.session?.status?.toUpperCase() || 'Idle' }}</span>
                      </div>
                      <div
                        class="chip flex items-center gap-1.5 bg-black/45 px-2.5 py-2 rounded-full border border-line">
                        ⏰ <span>{{ liveTimer }}</span>
                      </div>
                      <div
                        class="chip flex items-center gap-1.5 bg-black/45 px-2.5 py-2 rounded-full border border-line">
                        🎥 <span>{{ recordTimer }}</span>
                      </div>
                      <div
                        class="chip flex items-center gap-1.5 bg-black/45 px-2.5 py-2 rounded-full border border-line">
                        👀 <span>{{ state.session?.viewer_count || 0 }}</span>
                      </div>
                      <div
                        class="chip flex items-center gap-1.5 bg-black/45 px-2.5 py-2 rounded-full border border-line">
                        ❤️ <span>{{ state.session?.like_count || 0 }}</span>
                      </div>
                      <div
                        class="chip flex items-center gap-1.5 bg-black/45 px-2.5 py-2 rounded-full border border-line">
                        🎁 <span>{{ state.session?.gift_count || 0 }}</span>
                      </div>
                      <div
                        class="chip flex items-center gap-1.5 bg-black/45 px-2.5 py-2 rounded-full border border-line">
                        💰 <span>{{ state.session ? (state.session.earnings_cents / 100).toFixed(2) : '0' }}</span>
                      </div>
                      <div v-if="activePoll"
                        class="chip flex items-center gap-1.5 bg-black/45 px-2.5 py-2 rounded-full border border-line pointer-events-auto">
                        📊 <span>{{ activePoll.question }}</span>
                      </div>
                    </div>

                    <!-- Poll Container -->
                    <div v-if="activePoll"
                      class="poll-container bg-black/60 p-2 rounded-xl w-48 mt-2 pointer-events-auto">
                      <div v-for="(option, index) in activePoll.options" :key="index"
                        class="poll-option flex justify-between items-center mb-2 cursor-pointer"
                        @click="votePoll(activePoll.id, index)">
                        <span>{{ option.text }}</span>
                        <span>
                          {{ option.votes }}
                          ({{ totalVotes > 0 ? ((option.votes / totalVotes) * 100).toFixed(1) : 0 }}%)
                        </span>
                        <div class="w-full h-2 bg-gray-700 rounded mt-1">
                          <div class="poll-bar h-2 bg-brand rounded"
                            :style="{ width: totalVotes > 0 ? `${(option.votes / totalVotes) * 100}%` : '0%' }"></div>
                        </div>
                      </div>
                      <button @click="endPoll(activePoll.id)"
                        class="btn small bg-white text-gray-900 px-2.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer mt-2">
                        End Poll
                      </button>
                    </div>

                    <!-- Chat Feed -->
                    <div
                      class="chat-feed absolute right-3 top-14 bottom-14 w-48 max-h-72 overflow-y-auto flex flex-col gap-2 pointer-events-auto">
                      <div v-for="message in chatMessages" :key="message.id"
                        class="chat-message bg-black/60 p-2 rounded-xl text-xs flex items-center gap-2">
                        <img :src="`https://source.unsplash.com/24x24/?avatar${Math.random()}`" alt="Avatar"
                          class="w-6 h-6 rounded-full border border-line" />
                        <span><strong>{{ message.from }}</strong>: {{ message.text }}</span>
                      </div>
                    </div>

                    <!-- Live Actions -->
                    <div class="live-actions flex gap-2 justify-end flex-wrap pointer-events-auto">
                      <button @click="simulateJoin"
                        class="btn small bg-white text-gray-900 px-2.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
                        Simulate Join
                      </button>
                      <button @click="sendLike(1000)"
                        class="btn small bg-white text-gray-900 px-2.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
                        Like ×1000
                      </button>
                      <button @click="reportContent"
                        class="btn small bg-record-red text-white px-2.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
                        Report
                      </button>
                      <button @click="showPollModal = true"
                        class="btn small bg-white text-gray-900 px-2.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
                        Create Poll
                      </button>
                      <button @click="showQnaModal = true"
                        class="btn small bg-white text-gray-900 px-2.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
                        Q&A
                      </button>
                      <button @click="toggleRecording" :class="[
                        'btn small px-2.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105',
                        isRecording ? 'bg-record-red text-white record-pulsate' : 'bg-white text-gray-900'
                      ]">
                        {{ isRecording ? 'Stop Recording' : 'Record' }}
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Chat and Q&A Inputs -->
                <div class="mt-3">
                  <div class="row flex gap-2">
                    <input v-model="chatInput" @keydown.enter="sendChatMessage" :disabled="chatCooldown"
                      placeholder="Type to chat as Viewer Demo… (rate-limited 5s)"
                      class="grow bg-white/10 border border-line text-white px-3 py-2 rounded-xl outline-none placeholder:text-muted/80 disabled:opacity-50" />
                    <button @click="sendChatMessage" :disabled="chatCooldown"
                      class="btn bg-white text-gray-900 px-4 py-2 rounded-xl font-bold cursor-pointer shadow-lg transition-transform hover:scale-105 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                      Send
                    </button>
                  </div>
                  <div class="row flex gap-2 mt-3">
                    <input v-model="qnaInput" @keydown.enter="submitQuestion" :disabled="qnaCooldown"
                      placeholder="Ask a question… (rate-limited 5s)"
                      class="grow bg-white/10 border border-line text-white px-3 py-2 rounded-xl outline-none placeholder:text-muted/80 disabled:opacity-50" />
                    <button @click="submitQuestion" :disabled="qnaCooldown"
                      class="btn bg-white text-gray-900 px-4 py-2 rounded-xl font-bold cursor-pointer shadow-lg transition-transform hover:scale-105 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                      Ask
                    </button>
                  </div>
                </div>
              </div>

              <!-- Right Panel -->
              <div class="right grid gap-4">
                <!-- Gift Catalog -->
                <GiftsPannel :gifts="props.gifts" :liveStramData="liveStramData" />

                <!-- Guests -->
                <div class="glass rounded-custom rounded-md overflow-hidden">
                  <h3
                    class="m-0 p-3 border-b border-line bg-gradient-to-b from-black/30 to-black/20 text-white text-xl font-bold">
                    Guests</h3>
                  <div class="panel-body p-3">
                    <div class="guest-list grid grid-cols-2 gap-2">
                      <div v-for="guest in state.guests" :key="guest.id"
                        class="guest flex justify-between items-center p-2 rounded-xl border border-line bg-gradient-to-b from-white/14 to-white/6">
                        <span class="guest-name text-ink">{{ guest.id }}</span>
                        <span class="guest-status text-muted text-sm capitalize">{{ guest.status }}</span>
                        <button @click="removeGuest(guest.id)"
                          class="btn small bg-white text-gray-900 px-2 py-1 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
                          Remove
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Ledger -->
                <div class="glass rounded-custom rounded-md overflow-hidden">
                  <h3
                    class="m-0 p-3 border-b border-line bg-gradient-to-b from-black/30 to-black/20 text-white text-xl font-bold">
                    Ledger (Session)
                  </h3>
                  <div class="panel-body p-3">
                    <div class="list grid gap-2 max-h-48 overflow-y-auto font-mono">
                      <div v-for="entry in ledgerEntries" :key="entry.ts"
                        class="event flex gap-2 items-start bg-black/28 border border-line p-2 rounded-xl">
                        {{ new Date(entry.ts).toLocaleTimeString() }} — {{ entry.type }} {{ entry.note }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Live Now -->
        <AllLiveStrams :all_Streams="all_Streams" :fetchLiveStreamDetail="fetchLiveStreamDetail" />
        <!-- User Streams -->
        <UserStreams :user_Streams="props.user_Streams" />
        <!-- Debug / API Log -->
        <div class="glass rounded-custom rounded-md overflow-hidden">
          <h3
            class="m-0 p-3 border-b border-line bg-gradient-to-b from-black/30 to-black/20 text-white text-xl font-bold">
            Debug / API Log</h3>
          <div class="panel-body p-3">
            <div class="list grid gap-2 font-mono max-h-48 overflow-y-auto">
              <div v-for="log in apiLogs" :key="log.timestamp" class="text-sm">
                [{{ log.timestamp }}] {{ log.message }} {{ log.data ? JSON.stringify(log.data) : '' }}
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Modals -->
    <!-- Help Modal -->
    <ShowHelpModal :showHelpModal="showHelpModal" @close="showHelpModal = false" />

    <!-- Poll Modal -->
    <div v-if="showPollModal" class="modal fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-5"
      @click="showPollModal = false">
      <div class="modal-content glass rounded-custom border border-line backdrop-blur-lg max-w-md w-full p-5 relative"
        @click.stop>
        <button @click="showPollModal = false"
          class="modal-close absolute top-2 right-2 bg-brand text-white border-none rounded-xl px-3 py-2 cursor-pointer font-semibold hover:bg-brand-2">
          Close
        </button>
        <h2 class="text-xl mt-0 mb-4">Create a Poll</h2>
        <p class="mb-4">Enter a question and up to four options for your viewers to vote on.</p>
        <div class="row flex gap-2 mb-3">
          <input v-model="pollQuestion" placeholder="Enter poll question..."
            class="grow bg-white/10 border border-line text-white px-3 py-2 rounded-xl outline-none placeholder:text-muted/80" />
        </div>
        <div class="row flex gap-2 mb-3">
          <input v-model="pollOptions[0]" placeholder="Option 1"
            class="grow bg-white/10 border border-line text-white px-3 py-2 rounded-xl outline-none placeholder:text-muted/80" />
        </div>
        <div class="row flex gap-2 mb-3">
          <input v-model="pollOptions[1]" placeholder="Option 2"
            class="grow bg-white/10 border border-line text-white px-3 py-2 rounded-xl outline-none placeholder:text-muted/80" />
        </div>
        <div class="row flex gap-2 mb-3">
          <input v-model="pollOptions[2]" placeholder="Option 3 (optional)"
            class="grow bg-white/10 border border-line text-white px-3 py-2 rounded-xl outline-none placeholder:text-muted/80" />
        </div>
        <div class="row flex gap-2 mb-3">
          <input v-model="pollOptions[3]" placeholder="Option 4 (optional)"
            class="grow bg-white/10 border border-line text-white px-3 py-2 rounded-xl outline-none placeholder:text-muted/80" />
        </div>
        <div class="row flex gap-2">
          <button @click="createPoll"
            class="btn bg-white text-gray-900 px-4 py-2 rounded-xl font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
            Start Poll
          </button>
        </div>
      </div>
    </div>

    <!-- Q&A Modal -->
    <div v-if="showQnaModal" class="modal fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-5"
      @click="showQnaModal = false">
      <div
        class="modal-content glass rounded-custom border border-line backdrop-blur-lg max-w-md w-full max-h-[80vh] overflow-y-auto p-5 relative"
        @click.stop>
        <button @click="showQnaModal = false"
          class="modal-close absolute top-2 right-2 bg-brand text-white border-none rounded-xl px-3 py-2 cursor-pointer font-semibold hover:bg-brand-2">
          Close
        </button>
        <h2 class="text-xl mt-0 mb-4">Manage Q&A</h2>
        <p class="mb-4">View and manage viewer-submitted questions. Mark as answered or delete.</p>
        <div class="list grid gap-2">
          <div v-for="question in state.qna" :key="question.id"
            class="qna-item flex justify-between items-center p-2 border-b border-line">
            <span class="qna-text text-ink">
              {{ question.from }}: {{ question.text }}{{ question.answered ? ' (Answered)' : '' }}
            </span>
            <div>
              <button @click="answerQuestion(question.id)" :disabled="question.answered"
                class="btn small bg-white text-gray-900 px-2 py-1 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                Answer
              </button>
              <button @click="deleteQuestion(question.id)"
                class="btn small bg-record-red text-white px-2 py-1 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105 ml-1">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gift Animations -->
    <div v-for="animation in giftAnimations" :key="animation.id" class="gift-animation absolute text-3xl z-10" :style="{
      top: `${animation.top}%`,
      left: `${animation.progress}%`,
      animation: `flyAcross ${animation.duration}s linear forwards`
    }">
      {{ animation.emoji }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3';
import Sonner from 'vue-sonner';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

import UserStreams from './GoLive/Components/UserStreams.vue';
import AllLiveStrams from './GoLive/Components/AllLiveStrams.vue';
import ShowHelpModal from './GoLive/Components/Modals/ShowHelpModal.vue';
import GiftsPannel from './GoLive/Components/GiftsPannel.vue';
import { Gift, Guest, Poll, QnA, Session, AppState, LogEntry, ChatMessage, GiftAnimation } from './Components/liveInterface';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';


const enable_gifting = ref(false)
// props

const page = usePage() as any;
const user = page?.props?.auth?.user?.user;
const liveStramData = ref({}) as any;
const liveUserData = ref(null) as any;
const playback = ref(null) as any;
const props = defineProps<{
  user_Streams: any,
  balance: any,
  all_Streams: any,
  gifts: any,
}>();

interface LiveMessage {
  id: string
  type: 'chat' | 'gift'
  user: string
  text: string
  avatar: string
  emoji?: string
}

// Dummy messages for testing UI
const liveMessages = ref<LiveMessage[]>


const fetchLiveStreamDetail = async (data: any) => {
  try {
    const responce = await axios.get(route('frontend.livestream.join', { id: data.id }));
    if (responce.data.status) {
      liveStramData.value = responce.data.liveStream;
      playback.value = responce.data.playBackKey;
      liveUserData.value = responce.data.data;

      toast.success(`Joined live stream: ${liveStramData.value.title || 'Untitled'}`)

      // hydrate server state for top bar
      try {
        const stateRes = await axios.get(route('frontend.live.state', { stream: liveStramData.value.id }));
        if (stateRes.data?.ok) {
          const s = stateRes.data.session || {};
          state.session = {
            id: liveStramData.value.id,
            host_id: String(liveUserData.value?.id || ''),
            title: liveStramData.value.title,
            type: broadcastType.value,
            tags: [selectedTag.value],
            geo: geoLocation.value,
            visibility: visibility.value as any,
            subscription_rate: visibility.value === 'private' ? parseInt(subscriptionRate.value as any) : 0,
            status: s.status || 'live',
            started_at: Date.now(),
            viewer_count: s.viewer_count || 0,
            like_count: s.like_count || 0,
            gift_count: s.gift_count || 0,
            earnings_cents: s.earnings_cents || 0,
            events: [],
            ledger: [],
            polls: [],
            qna: [],
          } as any
          if (stateRes.data.active_poll) {
            state.polls = [Object.assign({}, stateRes.data.active_poll, { active: true })]
            toast.info("Active poll loaded!")
          }
          if (Array.isArray(stateRes.data.qna)) {
            state.qna = stateRes.data.qna
            toast.info(`${stateRes.data.qna.length} Q&A questions loaded!`)
          }
        }
      } catch (e) {
        console.error('Error loading stream state:', e)
        toast.warning("Could not load stream state")
      }
      // subscribe to pusher channel for this stream
      subscribeToLiveChannel();
    } else {
      toast.error("Failed to join live stream")
    }
  } catch (error) {
    console.error('Error fetching live stream user:', error);
    toast.error("Failed to join live stream")
  }
};

// Reactive state
const state = reactive<AppState>({
  coins: 0,
  cash: 0,
  totalCashEarned: 0,
  session: null,
  sessions: [],
  mediaStream: null,
  followedUsers: [],
  guests: [],
  totalLikes: 0,
  followers: 0,
  polls: [],
  qna: [],
  liveStartTime: 0,
  recordStartTime: 0,
  catalog: [
    { id: "rose", name: "Rose", coins: 5, hostShare: 0.7, emoji: "🌹" },
    { id: "jerk-chicken", name: "Jerk Chicken", coins: 30, hostShare: 0.7, emoji: "🍗" },
    { id: "coconut-water", name: "Coconut Water", coins: 35, hostShare: 0.7, emoji: "🥥" },
    { id: "coffee", name: "Coffee", coins: 40, hostShare: 0.7, emoji: "☕" },
    { id: "mango-salsa", name: "Mango Salsa", coins: 45, hostShare: 0.7, emoji: "🥭" },
    { id: "mojito", name: "Mojito", coins: 55, hostShare: 0.75, emoji: "🍈" },
    { id: "sex-on-the-beach", name: "Sex on the Beach", coins: 60, hostShare: 0.75, emoji: "🍸" },
    { id: "piña-colada", name: "Piña Colada", coins: 65, hostShare: 0.75, emoji: "🍍" },
    { id: "mic", name: "Mic Drop", coins: 25, hostShare: 0.7, emoji: "🎤" },
    { id: "steel-drum", name: "Steel Drum", coins: 50, hostShare: 0.75, emoji: "🥁" },
    { id: "rasta-roadman", name: "Rasta Roadman", coins: 75, hostShare: 0.75, emoji: "🧑" },
    { id: "rum-punch", name: "Rum Punch", coins: 80, hostShare: 0.75, emoji: "🍹" },
    { id: "carnival-mask", name: "Carnival Mask", coins: 150, hostShare: 0.75, emoji: "🎭" },
    { id: "calypso-crown", name: "Calypso Crown", coins: 200, hostShare: 0.75, emoji: "👑" },
    { id: "yacht", name: "Yacht", coins: 999, hostShare: 0.8, emoji: "🛥️" },
    { id: "star", name: "Super Star", coins: 2500, hostShare: 0.8, emoji: "⭐" },
    { id: "diamond", name: "Diamond", coins: 5000, hostShare: 0.85, emoji: "💎" },
  ]
})

// UI State
const showHelpModal = ref(false)
const showPollModal = ref(false)
const showQnaModal = ref(false)
const guestInput = ref('')
const broadcastType = ref('Entertainment')
const visibility = ref('public')
const subscriptionRate = ref(500)
const selectedTag = ref('Carnival Vibes')
const liveTitle = ref('')
const geoLocation = ref('Bahamas')
const chatInput = ref('')
const qnaInput = ref('')
const pollQuestion = ref('')
const pollOptions = ref(['', '', '', ''])
const cameraFeed = ref<HTMLVideoElement | null>(null)
const chatCooldown = ref(false)
const qnaCooldown = ref(false)
const isRecording = ref(false)

// Timers
const liveTimer = ref('00:00:00')
const recordTimer = ref('00:00:00')
let timerInterval: number | null = null

// Data collections
const apiLogs = ref<LogEntry[]>([])
const chatMessages = ref<ChatMessage[]>([])
const giftAnimations = ref<GiftAnimation[]>([])

// Tag options
let tagOptions: any = reactive({
  Entertainment: [
    "Carnival Vibes", "Reggae Session", "Dancehall Party", "Soca Fever",
    "Calypso Jam", "Creole Culture", "Food Festival", "Beach Chill",
    "Rum Tasting", "Steelpan Showcase", "Art & Craft", "Jerk Cook-off",
    "Island Adventure", "Cultural Storytelling", "Salsa Night",
    "Bachata Blast", "Reggaeton Rave"
  ],
  Politics: ["Election Watch", "Policy Debate"],
  "Current Events": ["Community Spotlight", "Breaking Story"],
  News: [
    "Caribbean News", "Latin American News", "Regional Update", "Global Headlines"
  ],
})

// Computed properties
const availableTags = computed(() => tagOptions[broadcastType.value as keyof typeof tagOptions] || [])



const popularityTier = computed(() => {
  if (state.totalLikes < 1000) return 'Newbie'
  if (state.totalLikes < 5000) return 'Rising Star'
  if (state.totalLikes < 10000) return 'Island Icon'
  return 'Legend'
})

const popularityBadgeEmoji = computed(() => {
  if (state.totalLikes < 1000) return '🌱'
  if (state.totalLikes < 5000) return '⭐'
  if (state.totalLikes < 10000) return '🌴'
  return '🏆'
})

const popularityBadgeClass = computed(() => {
  if (state.totalLikes < 1000) return 'bg-blue-500'
  if (state.totalLikes < 5000) return 'bg-orange-500'
  if (state.totalLikes < 10000) return 'bg-red-500'
  return 'bg-gradient-to-r from-yellow-400 to-orange-400'
})

const stageBackgroundClass = computed(() => {
  const tag = selectedTag.value.toLowerCase().replace(/\s+/g, '-')
  return `bg-radial-gradient ${tag}`
})

const activePoll = computed(() => state.polls.find(p => p.active))

const totalVotes = computed(() =>
  activePoll.value ? activePoll.value.options.reduce((sum, opt) => sum + opt.votes, 0) : 0
)

const ledgerEntries = computed(() => state.session?.ledger || [])

// Utility functions
const log = (message: string, data?: any) => {
  apiLogs.value.unshift({
    timestamp: new Date().toLocaleTimeString(),
    message,
    data
  })
}

const formatTime = (seconds: number) => {
  const h = Math.floor(seconds / 3600).toString().padStart(2, '0')
  const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0')
  const s = (seconds % 60).toString().padStart(2, '0')
  return `${h}:${m}:${s}`
}

// Timer functions
const startTimers = () => {
  if (timerInterval) clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    if (state.session && state.session.status === 'live') {
      const elapsed = Math.floor((Date.now() - state.liveStartTime) / 1000)
      liveTimer.value = formatTime(elapsed)
    }
    if (state.recordStartTime) {
      const elapsed = Math.floor((Date.now() - state.recordStartTime) / 1000)
      recordTimer.value = formatTime(elapsed)
    }
  }, 1000)
}

const stopTimers = () => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
  liveTimer.value = '00:00:00'
  recordTimer.value = '00:00:00'
  state.liveStartTime = 0
  state.recordStartTime = 0
}

// Camera functions
const startCamera = async (): Promise<boolean> => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { width: { ideal: 1280 }, height: { ideal: 720 } },
      audio: true,
    })

    if (cameraFeed.value) {
      cameraFeed.value.srcObject = stream
    }

    state.mediaStream = stream
    state.liveStartTime = Date.now()
    startTimers()
    log("Camera started")
    toast.success("Camera access granted!")
    return true
  } catch (err) {
    log("Camera access failed", { error: (err as Error).message })
    toast.error(
      "Failed to access camera: " +
      (err as Error).message +
      ". Please ensure a camera is available and permissions are granted."
    )
    return false
  }
}

const stopCamera = () => {
  if (state.mediaStream) {
    state.mediaStream.getTracks().forEach(track => track.stop())
    state.mediaStream = null
    if (cameraFeed.value) {
      cameraFeed.value.srcObject = null
    }
    stopTimers()
    log("Camera stopped")
    toast.info("Camera stopped")
  }
}

// Live session functions
const goLive = async () => {
  if (state.session && state.session.status === 'live') {
    toast.warning("Already live")
    return
  }

  const cameraStarted = await startCamera()
  if (!cameraStarted) return

  const title = liveTitle.value.trim() || `${broadcastType.value}: ${selectedTag.value} with Shanae`
  const session: Session = {
    id: "sess_" + Math.random().toString(36).slice(2, 9),
    host_id: "shanae",
    title: title,
    type: broadcastType.value,
    tags: [selectedTag.value],
    geo: geoLocation.value,
    visibility: visibility.value as 'public' | 'followers' | 'private',
    subscription_rate: visibility.value === 'private' ? parseInt(subscriptionRate.value as any) : 0,
    status: 'live',
    started_at: Date.now(),
    viewer_count: 0,
    like_count: 0,
    gift_count: 0,
    earnings_cents: 0,
    events: [],
    ledger: [],
    polls: [],
    qna: [],
  }

  log("POST /live/start", session)
  state.session = session
  state.guests = []
  state.polls = []
  state.qna = []
  state.sessions.unshift(session)

  updateStageBackground()
  toast.success(`Live stream started: ${title}`)
}

const endLive = (reason = "host_end") => {
  if (!state.session) {
    toast.warning("No active live session to end")
    return
  }

  const earnings = state.session.earnings_cents
  state.session.status = 'ended'
  state.session.ended_at = Date.now()
  log("POST /live/end", { session_id: state.session.id, reason })

  state.totalCashEarned += earnings
  state.cash = state.totalCashEarned
  state.session = null
  state.guests = []
  state.polls = []
  state.qna = []

  stopCamera()
  stopRecording()
  stopTimers()
  saveState()

  toast.success(`Live stream ended! Total earnings: $${(earnings / 100).toFixed(2)}`)
}

// Guest management
const inviteGuest = () => {
  if (!state.session || state.session.status !== 'live') {
    toast.error("You must be live to invite guests.")
    return
  }

  if (state.guests.filter(g => g.status === 'joined').length >= 2) {
    toast.warning("Upgrade to a premium subscription to invite more than 2 guests.")
    return
  }

  if (state.guests.some(g => g.id === guestInput.value)) {
    toast.warning("Guest already invited.")
    return
  }

  if (!guestInput.value.trim()) {
    toast.error("Please enter a guest ID.")
    return
  }

  state.guests.push({ id: guestInput.value, status: 'pending' })
  log("Event: invite_guest", { guest_id: guestInput.value })
  saveState()
  toast.success(`Guest ${guestInput.value} invited!`)

  setTimeout(() => {
    const guest = state.guests.find(g => g.id === guestInput.value)
    if (guest) {
      guest.status = 'joined'
      log("Event: guest_joined", { guest_id: guestInput.value })
      saveState()
      toast.success(`Guest ${guestInput.value} joined!`)
    }
  }, 5000)
}

const removeGuest = (guestId: string) => {
  state.guests = state.guests.filter(g => g.id !== guestId)
  log("Event: remove_guest", { guest_id: guestId })
  saveState()
  toast.success(`Guest ${guestId} removed!`)
}

const isFollowing = ref(false);
const followersCount = ref(0);
const currentUserId = ref(user?.id || null);

const toggleFollow = async () => {
  if (!currentUserId.value) {
    toast.error('User ID not found');
    return;
  }

  try {
    const response = await axios.post(route('frontend.live.follow', { stream: currentUserId.value }));
    
    if (response.data.success) {
      isFollowing.value = response.data.is_following;
      followersCount.value = response.data.count;
      
      isFollowing.value ? toast.success('Followed!') : toast.info('Unfollowed');
    }
  } catch (error: any) {
    console.error('Follow request error:', error);
    if (error.response?.data?.message) {
      toast.error(error.response.data.message);
    } else {
      toast.error('Could not process follow request');
    }
  }
};

const loadFollowStatus = async () => {
  if (!currentUserId.value) return;
  
  try {
    const response = await axios.get(route('frontend.live.stream.followers', { stream: currentUserId.value }));
    if (response.data?.success) {
      isFollowing.value = response.data.is_following || false;
      followersCount.value = response.data.count || 0;
    }
  } catch (error: any) {
    console.error('Failed to load follow status:', error);
    // Set defaults on error
    isFollowing.value = false;
    followersCount.value = 0;
  }
};

onMounted(() => {
  loadFollowStatus();
});

// Gift system


const animateGift = (emoji: string, giftId: string) => {
  const animation: GiftAnimation = {
    id: Math.random().toString(36).substring(7),
    emoji,
    top: Math.random() * 70 + 15,
    progress: 100,
    duration: 1.5 + Math.random()
  }

  giftAnimations.value.push(animation)

  // Remove animation after it completes
  setTimeout(() => {
    giftAnimations.value = giftAnimations.value.filter(a => a.id !== animation.id)
  }, animation.duration * 1000)

  const gift = state.catalog.find(g => g.id === giftId)
  if (gift && gift.coins >= 999) {
    confetti({
      particleCount: 100,
      spread: 70,
      origin: { y: animation.top / 100 },
      colors: ["#FFD700", "#FF6B6B", "#28A4FF"],
    })
  }
}

// Poll system
const createPoll = async () => {
  if (!state.session || state.session.status !== 'live') {
    toast.error("You must be live to create a poll.")
    return
  }

  const question = pollQuestion.value.trim()
  const options = pollOptions.value.filter(opt => opt.trim()).map(opt => ({
    text: opt.trim(),
    votes: 0
  }))

  if (!question || options.length < 2) {
    toast.error("Please provide a question and at least two options.")
    return
  }

  try {
    const res = await axios.post(route('frontend.live.poll.create', { stream: (liveStramData.value as any)?.id }), {
      question,
      options: options.map(o => o.text)
    })
    if (res.data?.ok && res.data?.poll) {
      state.polls.push(res.data.poll)
      showPollModal.value = false
      pollQuestion.value = ''
      pollOptions.value = ['', '', '', '']
      toast.success('Poll created successfully!')
    }
  } catch (error) {
    console.error('Failed to create poll:', error)
    toast.error('Failed to create poll')
  }
}

const votePoll = async (pollId: string, optionIndex: number) => {
  try {
    await axios.post(route('frontend.live.poll.vote', { stream: (liveStramData.value as any)?.id, poll: pollId }), { option_id: optionIndex + 1 })
    toast.success('Vote submitted!')
  } catch (error) {
    console.error('Failed to vote on poll:', error)
    toast.error('Failed to vote on poll')
  }
}

const endPoll = async (pollId: string) => {
  try {
    await axios.post(route('frontend.live.poll.end', { stream: (liveStramData.value as any)?.id, poll: pollId }))
    toast.success('Poll ended successfully!')
  } catch (error) {
    console.error('Failed to end poll:', error)
    toast.error('Failed to end poll')
  }
}

// Q&A system
const submitQuestion = async () => {
  if (!state.session || state.session.status !== 'live') {
    toast.error("You must be live to submit questions.")
    return
  }

  if (qnaCooldown.value) {
    toast.warning("Wait 5 seconds before submitting another question.")
    return
  }

  const text = qnaInput.value.trim()
  if (!text) {
    toast.error("Please enter a question.")
    return
  }

  try {
    await axios.post(route('frontend.live.qna.submit', { stream: (liveStramData.value as any)?.id }), { text })
    qnaCooldown.value = true
    qnaInput.value = ''
    toast.success('Question submitted successfully!')
    setTimeout(() => { qnaCooldown.value = false }, 5000)
  } catch (error) {
    console.error('Failed to submit question:', error)
    toast.error('Failed to submit question')
  }
}

const answerQuestion = async (questionId: string) => {
  try {
    await axios.post(route('frontend.live.qna.answer', { stream: (liveStramData.value as any)?.id, qna: questionId }))
    toast.success('Question marked as answered!')
  } catch (error) {
    console.error('Failed to answer question:', error)
    toast.error('Failed to answer question')
  }
}

const deleteQuestion = async (questionId: string) => {
  try {
    await axios.delete(route('frontend.live.qna.delete', { stream: (liveStramData.value as any)?.id, qna: questionId }))
    toast.success('Question deleted successfully!')
  } catch (error) {
    console.error('Failed to delete question:', error)
    toast.error('Failed to delete question')
  }
}

// Chat system
const sendChatMessage = async () => {
  const text = chatInput.value.trim()
  if (!text) {
    toast.error("Please enter a message.")
    return
  }

  if (!state.session) {
    toast.error("You must be in a live session to send messages.")
    return
  }

  if (chatCooldown.value) {
    toast.warning("Wait 5 seconds before sending another message.")
    return
  }

  const payloadText = text
    .replace(":)", "😊")
    .replace(":heart", "❤️")
    .replace(":fire", "🔥")

  try {
    await axios.post(route('frontend.live.comment', { stream: (liveStramData.value as any)?.id }), { text: payloadText })
    chatInput.value = ""
    chatCooldown.value = true
    toast.success('Message sent!')
    setTimeout(() => { chatCooldown.value = false }, 5000)
  } catch (error) {
    console.error('Failed to send comment:', error)
    toast.error('Failed to send comment')
  }
}

// Ledger system
const addLedger = (row: any) => {
  if (state.session) {
    state.session.ledger.unshift(row)
  }

  if (row.type === "chat") {
    chatMessages.value.unshift({
      id: Math.random().toString(36).substring(7),
      from: row.from,
      text: row.text,
      timestamp: row.ts
    })

    // Auto-remove old messages
    if (chatMessages.value.length > 50) {
      chatMessages.value = chatMessages.value.slice(0, 50)
    }
  }
}

// Simulation functions
const simulateJoin = () => {
  if (state.session) {
    if (state.session.visibility === 'private') {
      const subscriptionRate = state.session.subscription_rate || 0
      if (subscriptionRate > 0) {
        const hostShare = Math.round(subscriptionRate * 0.6)
        const linkUpShare = subscriptionRate - hostShare
        state.session.earnings_cents += hostShare
        addLedger({
          ts: Date.now(),
          type: "subscription",
          note: `viewer-demo subscribed ($${subscriptionRate / 100}) → host +$${hostShare / 100
            }, LinkUp +$${linkUpShare / 100}`,
        })
        log("Event: join", { user: 'viewer-demo', subscription_rate: subscriptionRate })
        toast.success(`New subscriber! +$${(hostShare / 100).toFixed(2)}`)
      } else {
        toast.error("You must be subscribed to view this private broadcast.")
        return
      }
    }
    state.session.viewer_count++
    log("Event: join", { user: 'viewer-demo' })
    toast.info("New viewer joined!")
  }
}

const sendLike = (count = 1000) => {
  if (!state.session || state.session.status !== 'live') {
    toast.error("You must be live to send likes.")
    return
  }

  state.session.like_count += count
  state.totalLikes += count
  log("Event: like", { count })
  toast.success(`Sent ${count} likes!`)

  if (state.session.like_count % 1000 === 0) {
    const gift = state.catalog.find(g => g.id === "sex-on-the-beach")
    if (gift) {
      state.coins += gift.coins
      addLedger({
        ts: Date.now(),
        type: "likes",
        note: `Reached ${state.session.like_count} likes, awarded 1× ${gift.name} (${gift.coins} coins)`,
      })
      log("Event: like_reward", { gift_id: gift.id, coins: gift.coins })
      toast.success(`Milestone reached! Awarded ${gift.name} (${gift.coins} coins)`)
      saveState()
    }
  }

  saveState()
}

// Recording functions
const toggleRecording = () => {
  if (isRecording.value) {
    stopRecording()
  } else {
    startRecording()
  }
}

const startRecording = () => {
  if (!state.mediaStream) {
    log("No media stream available for recording")
    toast.error("You must be live to start recording.")
    return
  }

  isRecording.value = true
  state.recordStartTime = Date.now()
  startTimers()
  log("Recording started")
  toast.success("Recording started!")
}

const stopRecording = () => {
  isRecording.value = false
  state.recordStartTime = 0
  log("Recording stopped")
  toast.info("Recording stopped! Would be downloaded as webm file in a real implementation")
}

const updateTagOptions = () => {
}

const updateSubscriptionRateVisibility = () => {
}

const updateStageBackground = () => {
}

// Wallet functions
const topUpCoins = (amount = 500) => {
  state.coins += amount
  log("POST /wallet/topup", { coins: amount })
  saveState()
}

const transferToWallet = () => {
  if (state.session && state.session.earnings_cents > 0) {
    const amount = state.session.earnings_cents
    state.totalCashEarned += amount
    state.session.earnings_cents = 0
    addLedger({
      ts: Date.now(),
      type: "transfer",
      note: `Transferred $${(amount / 100).toFixed(2)} to wallet`,
    })
    log("Event: transfer_to_wallet", { amount })
    saveState()
    toast.success(`Funds transferred to wallet! $${(amount / 100).toFixed(2)}`)
  } else {
    toast.warning("No earnings to transfer.")
  }
}

const reportContent = () => {
  log("Event: report", { reason: "inappropriate content" })
  toast.success("Content reported to moderators.")
}

const goBackToSite = () => {
  router.visit(route('frontend.find.matches'));
}

// Pusher/Echo subscription
const subscribeToLiveChannel = () => {
  try {

    const Echo = (window as any).Echo
    if (!Echo || !liveStramData.value?.public_id) return
    const channel = Echo.private('live-stream.' + liveStramData.value.public_id)
      .subscribed(() => {
        console.log('Subscribed to', channel);
      })
      .error((error: any) => {
        console.error('Channel subscription error:', error);
      });


    // Handle Live Comment Posted
    channel.listen('LiveCommentPosted', (e: any) => {
      console.log('LiveCommentPosted event received:', e)

      // Add to live messages
      liveMessages.value.unshift({
        id: e.id,
        type: 'chat',
        user: e.user?.name || 'User',
        text: e.text,
        avatar: e.user?.avatar || ''
      })

      // Add to chat messages for display
      chatMessages.value.unshift({
        id: e.id,
        from: e.user?.name || 'User',
        text: e.text,
        timestamp: e.ts || Date.now()
      })

      // Show toast notification
      toast.success(`${e.user?.name || 'User'} commented: ${e.text}`)

      // Auto-remove old messages
      if (chatMessages.value.length > 50) {
        chatMessages.value = chatMessages.value.slice(0, 50)
      }
    })

      // Handle Live Gift Sent
      .listen('LiveGiftSent', (e: any) => {
        console.log('LiveGiftSent event received:', e)

        const text = `sent ${e.qty}x ${e.gift?.name || ''}`
        liveMessages.value.unshift({
          id: e.id,
          type: 'gift',
          user: e.sender?.name || 'User',
          text,
          avatar: e.sender?.avatar || '',
          emoji: e.gift?.emoji || '🎁'
        })

        // Show toast notification
        toast.success(`${e.sender?.name || 'User'} sent ${e.qty}x ${e.gift?.name || 'gift'}!`)

        // Animate gift if emoji available
        if (e.gift?.emoji) {
          animateGift(e.gift.emoji, e.gift.id)
        }
      })

      // Handle Poll Created
      .listen('PollCreated', (e: any) => {
        console.log('PollCreated event received:', e)

        const newPoll = {
          id: e.id,
          question: e.question,
          options: e.options || [],
          active: true
        }

        // Remove any existing active polls
        state.polls = state.polls.filter(p => !p.active)
        state.polls.unshift(newPoll)

        // Show toast notification
        toast.success(`New poll created: ${e.question}`)
      })

      // Handle Poll Voted
      .listen('PollVoted', (e: any) => {
        console.log('PollVoted event received:', e)

        const p = state.polls.find(p => p.id === e.poll_id)
        if (p && p.active) {
          const opt = p.options?.[e.option_id - 1]
          if (opt) {
            opt.votes = (opt.votes || 0) + 1
            // Show toast notification
            toast.info(`Someone voted on the poll!`)
          }
        }
      })

      // Handle Poll Ended
      .listen('PollEnded', (e: any) => {
        console.log('PollEnded event received:', e)

        const p = state.polls.find(p => p.id === e.poll_id)
        if (p) {
          p.active = false
          // Show toast notification
          toast.info('Poll has ended!')
        }
      })

      // Handle Q&A Submitted
      .listen('QnaSubmitted', (e: any) => {
        console.log('QnaSubmitted event received:', e)

        const newQna = {
          id: e.id,
          from: e.from?.name || 'User',
          text: e.text,
          answered: false,
          timestamp: e.timestamp || Date.now()
        }

        state.qna.unshift(newQna)

        // Show toast notification
        toast.success(`New question from ${e.from?.name || 'User'}: ${e.text}`)
      })

      // Handle Q&A Answered
      // Use dot-prefixed event name to match Laravel broadcastAs('QnaAnswered')
      .listen('.QnaAnswered', (e: any) => {
        console.log('QnaAnswered event received:', e)

        const q = state.qna.find(x => x.id === e.id)
        if (q) {
          q.answered = true
          // Show toast notification
          toast.info('A question has been answered!')
        }
      })

      // Handle Q&A Deleted
      // Use dot-prefixed event name to match Laravel broadcastAs('QnaDeleted')
      .listen('.QnaDeleted', (e: any) => {
        console.log('QnaDeleted event received:', e)

        const deletedQna = state.qna.find(x => x.id === e.id)
        state.qna = state.qna.filter(x => x.id !== e.id)

        // Show toast notification
        if (deletedQna) {
          toast.warning(`Question deleted: ${deletedQna.text}`)
        }
      })

    console.log('Successfully subscribed to live channel:', 'live-stream.' + liveStramData.value.public_id)
  } catch (error) {
    console.error('Error subscribing to live channel:', error)
    toast.error('Failed to connect to live stream updates')
  }
}

// Backend config
const loadLiveConfig = async () => {
  try {
    const res = await axios.get(route('frontend.live.config'))
    if (res.data?.ok) {
      const cfg = res.data
      if (cfg?.user?.coins !== undefined) {
        state.coins = parseInt(cfg.user.coins) || 0
      }
      if (cfg?.tags_by_type) {
        tagOptions = reactive(cfg.tags_by_type)
      }
      toast.success("Live configuration loaded!")
    }
  } catch (error) {
    console.error('Failed to load live config:', error)
    toast.error("Failed to load live configuration")
  }
}

// Persistence
const loadState = () => {
  try {
    const savedCoins = localStorage.getItem("linkup_coins")
    const savedCash = localStorage.getItem("linkup_cash")
    const savedTotalCashEarned = localStorage.getItem("linkup_total_cash_earned")
    const savedFollowedUsers = localStorage.getItem("linkup_followed_users")
    const savedGuests = localStorage.getItem("linkup_guests")
    const savedTotalLikes = localStorage.getItem("linkup_total_likes")

    state.coins = savedCoins !== null ? parseInt(savedCoins, 10) || 0 : 0
    state.cash = savedCash !== null ? parseFloat(savedCash) || 0 : 0
    state.totalCashEarned = savedTotalCashEarned !== null ? parseFloat(savedTotalCashEarned) || 0 : 0
    state.followedUsers = savedFollowedUsers !== null ? JSON.parse(savedFollowedUsers) || [] : []
    state.guests = savedGuests !== null ? JSON.parse(savedGuests) || [] : []
    state.totalLikes = savedTotalLikes !== null ? parseInt(savedTotalLikes, 10) || 0 : 0
    state.followers = state.followedUsers.length

    log("State loaded successfully", {
      coins: state.coins,
      cash: state.cash,
      totalCashEarned: state.totalCashEarned,
    })
  } catch (e) {
    log("Failed to load state", { error: (e as Error).message })
    // Reset to defaults
    state.coins = 0
    state.cash = 0
    state.totalCashEarned = 0
    state.followedUsers = []
    state.guests = []
    state.totalLikes = 0
    state.followers = 0
  }
}

const saveState = () => {
  try {
    localStorage.setItem("linkup_coins", state.coins.toString())
    localStorage.setItem("linkup_cash", state.cash.toString())
    localStorage.setItem("linkup_total_cash_earned", state.totalCashEarned.toString())
    localStorage.setItem("linkup_followed_users", JSON.stringify(state.followedUsers))
    localStorage.setItem("linkup_guests", JSON.stringify(state.guests))
    localStorage.setItem("linkup_total_likes", state.totalLikes.toString())
  } catch (e) {
    log("Failed to save state", { error: (e as Error).message })
  }
}

// Geolocation
const getUserLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords
        const cities = [
          { name: "Bridgetown", lat: 13.106, lon: -59.613 },
          { name: "St. John's", lat: 17.121, lon: -61.843 },
          { name: "Havana", lat: 23.113, lon: -82.366 },
          { name: "Roseau", lat: 15.302, lon: -61.388 },
          { name: "St. George's", lat: 12.056, lon: -61.748 },
          { name: "Port-au-Prince", lat: 18.594, lon: -72.307 },
          { name: "Montego Bay", lat: 18.471, lon: -77.919 },
          { name: "Basseterre", lat: 17.295, lon: -62.725 },
          { name: "Castries", lat: 14.01, lon: -60.987 },
          { name: "Kingstown", lat: 13.155, lon: -61.224 },
          { name: "San Juan", lat: 18.466, lon: -66.106 },
          { name: "Willemstad", lat: 12.122, lon: -68.882 },
          { name: "San José", lat: 9.933, lon: -84.083 },
          { name: "Panama City", lat: 8.994, lon: -79.518 },
          { name: "Mexico City", lat: 19.432, lon: -99.133 },
          { name: "Bogotá", lat: 4.711, lon: -74.072 },
          { name: "Lima", lat: -12.046, lon: -77.042 },
          { name: "Buenos Aires", lat: -34.613, lon: -58.377 },
          { name: "São Paulo", lat: -23.548, lon: -46.638 },
          { name: "Santiago", lat: -33.448, lon: -70.669 },
        ]
        let closestCity = cities[0] // Default to Bridgetown
        let minDistance = Infinity
        cities.forEach((city) => {
          const dLat = ((city.lat - latitude) * Math.PI) / 180
          const dLon = ((city.lon - longitude) * Math.PI) / 180
          const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos((latitude * Math.PI) / 180) *
            Math.cos((city.lat * Math.PI) / 180) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2)
          const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
          const distance = 6371 * c // Earth radius in km
          if (distance < minDistance) {
            minDistance = distance
            closestCity = city
          }
        })
        geoLocation.value = closestCity.name
        log("Geolocation set", { city: closestCity.name })
      },
      (error) => {
        log("Geolocation failed", { error: error.message })
        geoLocation.value = "Bahamas" // Default to Bahamas
      }
    )
  } else {
    log("Geolocation not supported")
    geoLocation.value = "Bahamas" // Default to Bahamas
  }
}

// Initialize
onMounted(() => {
  loadState()
  getUserLocation()
  loadLiveConfig()
})

onUnmounted(() => {
  stopTimers()
  stopCamera()
})

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      toast.success(flash.success)
    }
    if (flash?.error) {
      toast.error(flash.error)
    }
  },
  { deep: true, immediate: true }
)
</script>

<style>
:root {
  --bg-1: #0c2a47;
  --bg-2: #0e5296;
  --brand: #28a4ff;
  --brand-2: #6a5cff;
  --ink: #eaf2ff;
  --muted: #b9d3ff;
  --line: rgba(255, 255, 255, 0.14);
  --glass: rgba(255, 255, 255, 0.1);
  --glass-2: rgba(255, 255, 255, 0.06);
  --shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
  --radius: 18px;
  --follow-highlight: #ffd700;
  --live-green: #00cc00;
  --record-red: #ff4d4d;
}

.fade-up-enter-active,
.fade-up-leave-active {
  transition: all 0.5s ease;
}

.fade-up-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-up-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.animate-slide-up {
  animation: slide-up 0.4s ease;
}

@keyframes slide-up {
  from {
    transform: translateY(20px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

body {
  font-family: Inter, system-ui, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
  margin: 0;
}

.glass {
  background: linear-gradient(180deg, var(--glass), var(--glass-2));
  border: 1px solid var(--line);
  backdrop-filter: saturate(160%) blur(12px);
  box-shadow: var(--shadow);
}

.bg-radial-gradient {
  background: radial-gradient(closest-side, #163a6e 0%, #0c2342 65%, #07162d 100%);
}

/* Scrollbar width */
::-webkit-scrollbar {
  width: 8px;
}

/* Track transparent */
::-webkit-scrollbar-track {
  background: transparent;
}

/* Thumb invisible but still functional */
::-webkit-scrollbar-thumb {
  background: white;
  border-radius: 15px;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 0.5;
  }

  50% {
    opacity: 0.3;
  }
}

.pulsate {
  animation: pulsate 1.5s ease-in-out infinite;
}

@keyframes pulsate {
  0% {
    transform: scale(1);
    box-shadow: 0 0 10px var(--live-green);
  }

  50% {
    transform: scale(1.05);
    box-shadow: 0 0 20px var(--live-green);
  }

  100% {
    transform: scale(1);
    box-shadow: 0 0 10px var(--live-green);
  }
}

.record-pulsate {
  animation: pulsate-red 1.5s ease-in-out infinite;
}

@keyframes pulsate-red {
  0% {
    transform: scale(1);
    box-shadow: 0 0 10px var(--record-red);
  }

  50% {
    transform: scale(1.05);
    box-shadow: 0 0 20px var(--record-red);
  }

  100% {
    transform: scale(1);
    box-shadow: 0 0 10px var(--record-red);
  }
}

@keyframes flyAcross {
  0% {
    transform: translateX(100%) scale(1);
    opacity: 1;
  }

  50% {
    transform: translateX(0%) scale(1.2);
    opacity: 1;
  }

  100% {
    transform: translateX(-100%) scale(1);
    opacity: 0;
  }
}

@media (max-width: 1060px) {
  .app {
    grid-template-columns: 1fr !important;
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .catalog,
  .guest-list {
    grid-template-columns: 1fr !important;
  }

  .grid {
    grid-template-columns: 1fr !important;
  }

  .guest-video {
    height: 120px;
  }

  .gift-animation {
    font-size: 24px !important;
  }

  .modal-content {
    max-width: 90vw;
    padding: 15px;
  }

  .floating-btn {
    top: 5px;
    right: 5px;
  }
}

@media (max-width: 600px) {
  .user {
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }
}
</style>
