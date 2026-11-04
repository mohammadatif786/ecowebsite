<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Video Call Test</h1>

    <div v-if="!loggedIn" class="space-x-4 mb-6">
      <button class="px-4 py-2 bg-blue-500 text-white rounded" @click="loginAs('user1')">
        Login as User 1
      </button>
      <button class="px-4 py-2 bg-green-500 text-white rounded" @click="loginAs('user2')">
        Login as User 2
      </button>
    </div>

    <div v-else>
      <p class="mb-4">
        Logged in as: <strong>{{ currentUserId }}</strong>
      </p>

      <div class="space-x-4 mb-4">
        <button @click="startCall" class="px-4 py-2 bg-blue-600 text-white rounded">
          Start Call
        </button>
        <button @click="endCall" class="px-4 py-2 bg-red-600 text-white rounded">
          End Call
        </button>
        <button @click="toggleMuteAudio" class="px-4 py-2 bg-gray-600 text-white rounded">
          {{ audioMuted ? "Unmute Audio" : "Mute Audio" }}
        </button>
        <button @click="toggleMuteVideo" class="px-4 py-2 bg-gray-600 text-white rounded">
          {{ videoMuted ? "Unmute Video" : "Mute Video" }}
        </button>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <h2 class="font-semibold mb-1">My Video</h2>
          <video
            ref="localVideo"
            autoplay
            muted
            playsinline
            class="w-full h-64 bg-black rounded"
          ></video>
        </div>
        <div>
          <h2 class="font-semibold mb-1">Remote Video</h2>
          <video
            ref="remoteVideo"
            autoplay
            playsinline
            class="w-full h-64 bg-black rounded"
          ></video>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import ConnectyCube from "connectycube";

const localVideo = ref<HTMLVideoElement | null>(null);
const remoteVideo = ref<HTMLVideoElement | null>(null);

const currentUserId = ref<string | null>(null);
const opponentId = ref<string | null>(null);
const session = ref<any>(null);

const loggedIn = ref(false);
const audioMuted = ref(false);
const videoMuted = ref(false);

type CredentialsResponse = {
  app_id: string;
  auth_key: string;
  auth_secret: string;
  account_key: string;
  user_id: string;
  user_password: string;
};

async function loginAs(userKey: "user1" | "user2") {
  //   const { data } = await axios.get<CredentialsResponse>(
  //     `/api/connectycube/credentials?as=${userKey}`
  //   );

  ConnectyCube.init(
    {
      appId: "9630",
      authKey: "5C8FAF82-2FFE-40A7-B6CA-12550F1F24D5",
      authSecret: "FBw7uGLZzpXYy-GVjBkz",
    },
    { videochat: { alwaysRelayCalls: false } }
  );

  await ConnectyCube.createSession({
    user: { id: "14123708", password: "password1" },
  });

  await ConnectyCube.chat.connect({
    userId: "14123708",
    password: "password1",
  });

  currentUserId.value = "user1";
  opponentId.value = "user2";

  setupListeners();
  loggedIn.value = true;
}

function setupListeners() {
  ConnectyCube.videochat.onCallListener = async (sess: any) => {
    session.value = sess;
    const localStream = await sess.getUserMedia({ audio: true, video: true });
    sess.attachMediaStream(localVideo.value, localStream, {
      muted: true,
      mirror: true,
    });
    sess.accept({});
  };

  ConnectyCube.videochat.onRemoteStreamListener = (
    sess: any,
    userId: string,
    remoteStream: MediaStream
  ) => {
    sess.attachMediaStream(remoteVideo.value, remoteStream);
  };

  ConnectyCube.videochat.onStopCallListener = () => {
    cleanup();
  };
}

async function startCall() {
  if (!opponentId.value) return;

  session.value = ConnectyCube.videochat.createNewSession(
    [opponentId.value],
    ConnectyCube.videochat.CallType.VIDEO
  );

  const localStream = await session.value.getUserMedia({
    audio: true,
    video: true,
  });

  session.value.attachMediaStream(localVideo.value, localStream, {
    muted: true,
    mirror: true,
  });

  session.value.call({}, (error: any) => {
    if (error) console.error(error);
  });
}

function endCall() {
  session.value?.stop({});
  cleanup();
}

function cleanup() {
  if (localVideo.value?.srcObject)
    (localVideo.value.srcObject as MediaStream).getTracks().forEach((t) => t.stop());

  if (remoteVideo.value?.srcObject)
    (remoteVideo.value.srcObject as MediaStream).getTracks().forEach((t) => t.stop());

  session.value = null;
}

function toggleMuteAudio() {
  if (!session.value) return;
  if (audioMuted.value) session.value.unmute("audio");
  else session.value.mute("audio");
  audioMuted.value = !audioMuted.value;
}

function toggleMuteVideo() {
  if (!session.value) return;
  if (videoMuted.value) session.value.unmute("video");
  else session.value.mute("video");
  videoMuted.value = !videoMuted.value;
}
</script>
