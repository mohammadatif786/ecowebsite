<template>
  <div v-if="show && attendee" class="modal-backdrop">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Check-in</h2>
        <button class="close-btn" @click="$emit('close')" aria-label="Close">&times;</button>
      </div>

      <div class="content">
        <p style="font-size:13px;line-height:1.5;">
          Mark <strong>{{ attendee?.user?.name || 'attendee' }}</strong> as
          <strong>checked-in</strong> for
          <strong>{{ attendee?.event?.title || 'this event' }}</strong>?
        </p>
        <p style="font-size:12px;color:#6b7280;">
          This will update the order status in your dashboard. You can still reverse it later
          from the event's check-in list.
        </p>
        <p v-if="alreadyChecked" style="font-size:12px;color:#b91c1c;">This ticket is already checked-in.</p>
      </div>

      <div class="print-actions">
        <button class="btn" @click="$emit('close')">Cancel</button>
        <button class="btn btn-primary" :disabled="submitting" @click="confirmToggle">
          {{ submitting ? 'Updating...' : (alreadyChecked ? 'Mark as not checked-in' : 'Confirm check-in') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{ show: boolean; attendee: any | null }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'toggled', payload: { id: number; status: string }): void }>();

const attendee = computed(() => props.attendee);
const alreadyChecked = computed(() => (attendee.value?.ticket_status === 'checked_in'));
const submitting = ref(false);

async function confirmToggle(){
  if (!attendee.value?.id) return;
  submitting.value = true;
  try {
    await router.patch(route('organizer.event.report.check-in', attendee.value.id));
    const newStatus = alreadyChecked.value ? 'confirmed' : 'checked_in';
    toast.success(`Attendee ${alreadyChecked.value ? 'check-in undone' : 'checked in'} successfully!`);
    emit('toggled', { id: attendee.value.id, status: newStatus });
    emit('close');
  } catch (e) {
    toast.error('Failed to update check-in status.');
  } finally {
    submitting.value = false;
  }
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
