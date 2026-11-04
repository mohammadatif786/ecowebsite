<template>
  <div class="overlay" v-show="visible" @click.self="$emit('close')" aria-hidden="!visible">
    <div class="modal" role="dialog" aria-label="Subscribe required">
      <div class="mTop">
        <div class="mTitle">Private Live</div>
        <button class="x" @click="$emit('close')">✕</button>
      </div>
      <div class="mBody">
        <div class="notice">
          <div class="badge">🔒</div>
          <div>
            <h3>Subscription required</h3>
            <p>
              This live is private and hosted by {{ creator }}.
            </p>
            <p>
              You must be subscribed to enter.
            </p>
            <p>
              Do you want to subscribe now?.
            </p>
          </div>
        </div>

        <div class="kv">
          <div class="card">
            <div class="t">Creator</div>
            <div class="big">{{ creator || '@creator' }}</div>
          </div>
          <div class="card">

            <div class="t">Selected Plan</div>
            <div class="big">${{ Number(amount || 0).toFixed(2) }} / month</div>
          </div>
        </div>

        <div class="toggleRow" role="group" aria-label="Subscription plan">
          <button class="toggleBtn" :class="{ active: plan === 'monthly' }" @click="plan = 'monthly'">Monthly</button>
          <button class="toggleBtn" :class="{ active: plan === 'one-time' }" @click="plan = 'one-time'">One-time</button>
        </div>
        <div class="toggleHint">
          {{ plan === 'monthly' ? 'Monthly subscriptions automatically renew each month. You can cancel anytime.' :
          'One-time access is a single purchase. No renewals.' }}
        </div>
      </div>
      <div class="mFoot">
        <button class="btn ghost" @click="$emit('close')">Not now</button>
        <button class="btn primary" @click="$emit('continue', { plan })">Continue</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

defineProps<{
  visible: boolean
  creator?: string | null
  amount?: number | string | null
}>()

defineEmits<{
  close: []
  continue: [payload: { plan: 'monthly' | 'one-time' }]
}>()

const plan = ref<'monthly' | 'one-time'>('monthly')

watch(() => plan.value, () => {
  // reserved for future use
})
</script>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, .55);
  backdrop-filter: blur(10px);
  display: grid;
  place-items: center;
  z-index: 2000;
  padding: 18px
}

.modal {
  width: min(740px, 96vw);
  border-radius: 22px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, .15);
  box-shadow: 0 30px 80px rgba(0, 0, 0, .55);
  background: rgba(10, 53, 82, .94);
  color: #eaf2ff
}

.mTop {
  padding: 14px 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, rgba(14, 165, 233, .16), rgba(223, 255, 0, .09));
  border-bottom: 1px solid rgba(255, 255, 255, .12)
}

.mTitle {
  font-weight: 950;
  font-size: 16px
}

.x {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, .18);
  background: rgba(255, 255, 255, .07);
  color: rgba(234, 242, 255, .9);
  font-size: 18px;
  cursor: pointer
}

.x:hover {
  background: rgba(255, 255, 255, .12)
}

.mBody {
  padding: 18px
}

.notice {
  border: 1px solid rgba(255, 255, 255, .14);
  background: rgba(255, 255, 255, .05);
  border-radius: 16px;
  padding: 14px;
  display: flex;
  gap: 12px;
  align-items: flex-start
}

.notice .badge {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(223, 255, 0, .14);
  border: 1px solid rgba(223, 255, 0, .30);
  font-size: 20px;
  flex-shrink: 0
}

.notice h3 {
  margin: 0;
  font-size: 14px;
  font-weight: 950
}

.notice p {
  margin: 6px 0 0;
  opacity: .86;
  line-height: 1.35;
  font-weight: 650;
  font-size: 13px
}

.kv {
  margin-top: 14px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px
}

@media (max-width:680px) {
  .kv {
    grid-template-columns: 1fr
  }
}

.kv .card {
  border: 1px solid rgba(255, 255, 255, .14);
  background: rgba(255, 255, 255, .05);
  border-radius: 16px;
  padding: 12px
}

.kv .t {
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: rgba(234, 242, 255, .70)
}

.kv .big {
  margin-top: 6px;
  font-size: 22px;
  font-weight: 950
}

.toggleRow {
  margin-top: 12px;
  border: 1px solid rgba(255, 255, 255, .14);
  background: rgba(255, 255, 255, .05);
  border-radius: 16px;
  padding: 10px;
  display: flex;
  gap: 10px
}

.toggleBtn {
  flex: 1;
  border: 1px solid rgba(255, 255, 255, .16);
  background: rgba(255, 255, 255, .06);
  color: rgba(234, 242, 255, .9);
  border-radius: 14px;
  padding: 10px 12px;
  font-weight: 950;
  cursor: pointer
}

.toggleBtn.active {
  background: linear-gradient(135deg, rgba(14, 165, 233, .26), rgba(223, 255, 0, .10));
  border-color: rgba(14, 165, 233, .55)
}

.toggleHint {
  margin-top: 10px;
  font-size: 12px;
  color: rgba(234, 242, 255, .72);
  font-weight: 750;
  line-height: 1.35
}

.mFoot {
  padding: 14px 18px;
  border-top: 1px solid rgba(255, 255, 255, .12);
  background: rgba(10, 53, 82, .88);
  display: flex;
  justify-content: flex-end;
  gap: 10px
}

.btn {
  border: none;
  border-radius: 999px;
  padding: 12px 16px;
  font-weight: 950;
  cursor: pointer;
  min-width: 160px
}

.btn.ghost {
  background: rgba(255, 255, 255, .08);
  border: 1px solid rgba(255, 255, 255, .18);
  color: #eaf2ff
}

.btn.primary {
  background: linear-gradient(135deg, rgba(34, 197, 94, .98), rgba(34, 197, 94, .78));
  color: white;
  box-shadow: 0 12px 32px rgba(34, 197, 94, .25)
}
</style>
