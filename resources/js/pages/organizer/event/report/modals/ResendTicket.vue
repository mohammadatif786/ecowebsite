<template>
  <div v-if="show && attendee" class="modal-backdrop">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Resend Ticket</h2>
        <button class="close-btn" @click="$emit('close')" aria-label="Close">&times;</button>
      </div>

      <div class="content">
        <p style="font-size:13px;line-height:1.5;">
          This will resend the e‑ticket email to:
          <br><strong>{{ attendee?.user?.name || attendee?.user_name }}</strong><br>
          <span>{{ attendee?.user?.email || attendee?.user_email }}</span>
        </p>
        <p style="font-size:12px;color:#6b7280;">
          The attendee will receive the same ticket PDF / E‑Ticket they got after checkout.
        </p>
      </div>

      <div class="print-actions">
        <button class="btn" @click="$emit('close')">Cancel</button>
        <button class="btn btn-primary" :disabled="submitting" @click="confirmResend">
          {{ submitting ? 'Resending...' : 'Resend ticket' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{ show: boolean; attendee: any | null; eventId: number | string }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'done'): void }>();

const attendee = computed(() => props.attendee);
const submitting = ref(false);

function confirmResend(){
  if (!attendee.value?.id) {
    toast.error('No attendee selected');
    return;
  }
  submitting.value = true;
  router.post(
    route('organizer.event.report.resend', props.eventId),
    { attendee_ids: [attendee.value.id] },
    {
      onSuccess: () => {
        toast.success('Tickets resent successfully!');
        submitting.value = false;
        emit('done');
        emit('close');
      },
      onError: () => {
        toast.error('Failed to resend tickets.');
        submitting.value = false;
      },
      onFinish: () => { submitting.value = false; },
    }
  );
}
</script>

<style scoped>
.modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,0.5);display:flex;align-items:center;justify-content:center;z-index:999}
.modal-box{background:#fff;border-radius:18px;padding:18px 18px 14px;max-width:520px;width:100%;box-shadow:0 20px 55px rgba(15,23,42,0.4)}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.modal-header h2{font-size:16px;margin:0}
.close-btn{border:none;background:transparent;font-size:20px;cursor:pointer}
.print-actions{margin-top:12px;display:flex;justify-content:flex-end;gap:8px}
.btn{padding:7px 12px;border-radius:999px;border:1px solid #e5e7eb;font-size:12px;cursor:pointer;background:#fff}
.btn-primary{background:#007aff;color:#fff;border-color:#007aff}
.content{font-size:13px}
</style>
