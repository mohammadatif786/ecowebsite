<template>
  <div v-if="show && attendee" class="modal-backdrop">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Contact Attendee</h2>
        <button class="close-btn" @click="$emit('close')" aria-label="Close">&times;</button>
      </div>

      <div class="content">
        <div class="contact-grid">
          <div class="contact-main">
            <div>
              <strong>{{ attendee?.user?.name || attendee?.user_name || 'Attendee' }}</strong>
              <span class="small-label">Attendee for {{ attendee?.event?.title || 'Event' }}</span>
            </div>
            <div>
              <div class="field-label">Email</div>
              <div class="field-value">{{ attendee?.user?.email || attendee?.user_email || '—' }}</div>
            </div>
            <div>
              <div class="field-label">Phone</div>
              <div class="field-value">{{ attendee?.user?.phone_number || attendee?.user_phone || '—' }}</div>
            </div>
          </div>
          <div class="contact-actions">
            <div>
              <div class="small-label">Quick actions</div>
            </div>
            <div v-if="attendee?.user?.email || attendee?.user_email">
                <a :href="gmailHref" target="_blank" rel="noopener noreferrer" class="btn">Gmail</a>
                <a :href="outlookHref" target="_blank" rel="noopener noreferrer" class="btn">Outlook</a>
            </div>
            <button class="btn" @click="copyEmail">📋 Copy email address</button>
            <button class="btn" @click="copyPhone">📱 Copy phone number</button>
          </div>
        </div>
      </div>

      <div class="print-actions">
        <button class="btn" @click="$emit('close')">Close</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
  show: boolean;
  attendee: any | null;
}>();

const attendee = computed(() => props.attendee);

const mailtoHref = computed(() => {
  const email = attendee.value?.user?.email || attendee.value?.user_email;
  const eventTitle = attendee.value?.event?.title || 'Your ticket';
  if (!email) return '#';
  const subject = encodeURIComponent(String(eventTitle) + ' – Your ticket');
  return `mailto:${email}?subject=${subject}`;
});

const gmailHref = computed(() => {
  const email = attendee.value?.user?.email || attendee.value?.user_email;
  const eventTitle = attendee.value?.event?.title || 'Your ticket';
  if (!email) return '#';
  const subject = encodeURIComponent(String(eventTitle) + ' – Your ticket');
  return `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${subject}`;
});

const outlookHref = computed(() => {
  const email = attendee.value?.user?.email || attendee.value?.user_email;
  const eventTitle = attendee.value?.event?.title || 'Your ticket';
  if (!email) return '#';
  const subject = encodeURIComponent(String(eventTitle) + ' – Your ticket');
  return `https://outlook.office.com/mail/deeplink/compose?to=${encodeURIComponent(email)}&subject=${subject}`;
});

function openMailto(event: MouseEvent) {
  // ensure default navigation happens to the mailto handler
  const href = mailtoHref.value as unknown as string;
  if (!href || !href.startsWith('mailto:')) {
    event.preventDefault();
    toast.error('No email available');
    return;
  }
  try {
    // Some browsers require explicit navigation via location for external protocols
    window.location.href = href;
  } catch (e) {
    // Fall back to window.open
    try {
      window.open(href, '_self');
    } catch (err) {
      event.preventDefault();
      toast.error('Unable to open email client');
    }
  }
}

function emailNow() {
  const email = attendee.value?.user?.email || attendee.value?.user_email;
  const eventTitle = attendee.value?.event?.title || 'Your ticket';
  if (!email) {
    toast.error('No email available');
    return;
  }
  const subject = encodeURIComponent(String(eventTitle) + ' – Your ticket');
  const mailto = `mailto:${email}?subject=${subject}`;
  try {
    const a = document.createElement('a');
    a.href = mailto;
    a.target = '_self';
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  } catch (e) {
    try {
      window.open(mailto, '_self');
    } catch (err) {
      toast.error('Unable to open email client');
    }
  }
}

function copyEmail() {
  const email = attendee.value?.user?.email || attendee.value?.user_email || '';
  if (!email) {
    toast.error('No email to copy');
    return;
  }
  if (navigator.clipboard) {
    navigator.clipboard.writeText(email)
      .then(() => toast.success('Email address copied'))
      .catch(() => toast.error('Failed to copy email'));
  } else {
    try {
      const ta = document.createElement('textarea');
      ta.value = email;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      toast.success('Email address copied');
    } catch (e) {
      toast.error('Clipboard not supported');
    }
  }
}

function copyPhone() {
  const phone = attendee.value?.user?.phone_number || attendee.value?.user_phone || '';
  if (!phone) {
    toast.error('No phone to copy');
    return;
  }
  if (navigator.clipboard) {
    navigator.clipboard.writeText(phone)
      .then(() => toast.success('Phone number copied'))
      .catch(() => toast.error('Failed to copy phone number'));
  } else {
    try {
      const ta = document.createElement('textarea');
      ta.value = phone;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      toast.success('Phone number copied');
    } catch (e) {
      toast.error('Clipboard not supported');
    }
  }
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999
}

.modal-box {
  background: #fff;
  border-radius: 18px;
  padding: 18px 18px 14px;
  max-width: 720px;
  width: 100%;
  box-shadow: 0 20px 55px rgba(15, 23, 42, 0.4)
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px
}

.modal-header h2 {
  font-size: 16px;
  margin: 0
}

.close-btn {
  border: none;
  background: transparent;
  font-size: 20px;
  cursor: pointer
}

.print-actions {
  margin-top: 12px;
  display: flex;
  justify-content: flex-end;
  gap: 8px
}

.btn {
  padding: 7px 12px;
  border-radius: 999px;
  border: 1px solid #e5e7eb;
  font-size: 12px;
  cursor: pointer;
  background: #fff
}

.btn-primary {
  background: #007aff;
  color: #fff;
  border-color: #007aff
}

.content {
  font-size: 13px
}

.contact-grid {
  display: grid;
  grid-template-columns: 1.4fr 1.2fr;
  gap: 14px;
  font-size: 13px
}

.contact-main div {
  margin-bottom: 8px
}

.contact-main strong {
  display: block
}

.contact-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 12px
}

.small-label {
  font-size: 11px;
  color: #6b7280
}

.field-label {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 2px
}

.field-value {
  font-size: 13px;
  font-weight: 500
}
</style>
