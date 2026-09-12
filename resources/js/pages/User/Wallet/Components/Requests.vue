<template>
  <div class="bg-white rounded-2xl p-5 shadow-glass border">
    <!-- Header -->
    <div class="flex items-center gap-2 mb-2">
      <i data-lucide="bell" class="w-4 h-4 text-slate-500"></i>
      <div class="font-semibold">Money Requests</div>
    </div>

    <div class="max-h-60 overflow-y-auto">
      <!-- Incoming -->
      <div class="text-xs text-slate-500">Incoming</div>
      <div class="mt-2 space-y-2">
        <div
          v-for="req in receivedRequests"
          :key="req.id"
          class="flex items-center gap-3 p-2 border rounded-xl"
        >
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold truncate">
              {{ req.requester.name }}
            </div>
            <div class="text-xs text-slate-500">
              Requested: {{ req.amount }}
            </div>
          </div>
          <div class="flex gap-2">
            <button
              class="px-3 py-1.5 text-xs rounded-xl bg-linkup-lime text-linkup-dark"
              @click="acceptRequest(req)"
            >
              Accept
            </button>
            <button
              class="px-3 py-1.5 text-xs rounded-xl border"
              @click="declineRequest(req)"
            >
              Decline
            </button>
          </div>
        </div>
        <div v-if="!receivedRequests.length" class="text-xs text-slate-400 italic">
          No incoming requests
        </div>
      </div>

      <!-- Outgoing -->
      <div class="text-xs text-slate-500 mt-4">Sending</div>
      <div class="mt-2 space-y-2">
        <div
          v-for="req in sentRequests"
          :key="req.id"
          class="flex items-center gap-3 p-2 border rounded-xl"
        >
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold truncate">
              {{ req.recipient.name }}
            </div>
            <div class="text-xs text-slate-500">
              You requested: {{ req.amount }} - ({{ req.status }})
            </div>
          </div>
          <div>
            <button
              class="px-3 py-1.5 text-xs rounded-xl border"
              @click="cancelRequest(req)"
            >
              Cancel
            </button>
          </div>
        </div>
        <div v-if="!sentRequests.length" class="text-xs text-slate-400 italic">
          No outgoing requests
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3"

const props = defineProps({
  receivedRequests: { type: Array, default: () => [] },
  sentRequests: { type: Array, default: () => [] }
})

// Actions
const acceptRequest = (req) => {
  router.post(route("frontend.user.money.respond", req.id), { status: "accepted" })
}

const declineRequest = (req) => {
  router.post(route("frontend.user.money.respond", req.id), { status: "declined" })
}

const cancelRequest = (req) => {
  router.delete(route("frontend.user.money.cancel", req.id))
}
</script>
