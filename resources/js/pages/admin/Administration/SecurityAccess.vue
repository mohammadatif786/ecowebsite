<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import {
    BadgeCheck,
    Bug,
    ClipboardList,
    Database,
    Gauge,
    KeyRound,
    Lock,
    Save,
    Shield,
    ShieldAlert,
    Smartphone,
    Timer,
    Users,
} from 'lucide-vue-next';

type SecuritySettings = {
    twofa: boolean;
    scope: string;
    method: string;
    timeout: string;
    policy: string;
    approval: string;
    ip: string;
    alerts: boolean;
    secret: string;
    recovery: string[];
};

type AutoLockSettings = {
    on: boolean;
    mins: number;
    pin: string;
};

type SecurityControl = {
    name: string;
    detail: string;
    status: 'Active' | 'Compliant' | 'Configurable' | 'Scheduled';
    icon: unknown;
};

const defaultSecurity: SecuritySettings = {
    twofa: true,
    scope: 'All admins',
    method: 'Authenticator app (TOTP)',
    timeout: '30 minutes',
    policy: 'Strong (12+ chars, symbols, 90d rotation)',
    approval: 'Two-person approval',
    ip: 'Office IP, VPN IP, Developer IP',
    alerts: true,
    secret: '',
    recovery: [],
};

const defaultLock: AutoLockSettings = {
    on: false,
    mins: 5,
    pin: '1234',
};

const loadJson = <T,>(key: string, fallback: T): T => {
    if (typeof window === 'undefined') return { ...fallback };
    try {
        const saved = localStorage.getItem(key);
        return saved ? { ...fallback, ...JSON.parse(saved) } : { ...fallback };
    } catch {
        return { ...fallback };
    }
};

const security = reactive<SecuritySettings>(loadJson('linkupSecurity', defaultSecurity));
const autoLock = reactive<AutoLockSettings>(loadJson('linkupAutoLock', defaultLock));
const verifyCode = ref('');
const verifyNote = ref('');
const recoveryNote = ref('');
const savedNote = ref('');
const lockNote = ref('');
const lockOpen = ref(false);
const lockPinInput = ref('');
const lockError = ref('');
let idleTimer: ReturnType<typeof setTimeout> | null = null;

const securityControls: SecurityControl[] = [
    { name: 'Encryption in Transit', detail: 'TLS 1.3 on all endpoints; HSTS enforced.', status: 'Active', icon: Lock },
    { name: 'Encryption at Rest', detail: 'AES-256 for DB, backups & PII fields.', status: 'Active', icon: Database },
    { name: 'Multi-Factor Auth (MFA)', detail: 'TOTP/SMS for admins, partners & sensitive actions.', status: 'Active', icon: Smartphone },
    { name: 'Role-Based Access (RBAC)', detail: 'Least-privilege roles + per-tenant isolation.', status: 'Active', icon: Users },
    { name: 'Immutable Audit Trail', detail: 'Every action logged with actor, IP & device.', status: 'Active', icon: ClipboardList },
    { name: 'Rate Limiting & Bot Defense', detail: 'Per-IP throttling, velocity rules, CAPTCHA on risk.', status: 'Active', icon: Gauge },
    { name: 'WAF & DDoS Protection', detail: 'Edge WAF, L3/L7 DDoS mitigation, geo-rules.', status: 'Active', icon: Shield },
    { name: 'Fraud & AML Monitoring', detail: 'Real-time scoring, sanctions screening, SAR workflow.', status: 'Active', icon: ShieldAlert },
    { name: 'Secrets Management', detail: 'Vault-managed keys, rotation, no secrets in code.', status: 'Active', icon: KeyRound },
    { name: 'Idle Session Auto-Lock', detail: 'Auto-lock + re-auth after inactivity.', status: 'Configurable', icon: Timer },
    { name: 'Penetration Testing', detail: 'Quarterly third-party pen-tests + bug bounty.', status: 'Scheduled', icon: Bug },
    { name: 'PCI-DSS & Data Residency', detail: 'PCI-DSS scope via tokenization; regional data residency.', status: 'Compliant', icon: BadgeCheck },
];

const securityScore = computed(() => {
    const active = securityControls.filter((control) => control.status === 'Active' || control.status === 'Compliant').length;
    const partial = securityControls.filter((control) => control.status === 'Configurable' || control.status === 'Scheduled').length * 0.6;
    return Math.round(((active + partial) / securityControls.length) * 100);
});

const securityCards = computed(() => [
    { label: '2FA Status', value: security.twofa ? 'On' : 'Off', sub: security.scope },
    { label: 'Session Timeout', value: shortTimeout(security.timeout), sub: '' },
    { label: 'Password Policy', value: security.policy.split(' (')[0], sub: '' },
    { label: 'Login Alerts', value: security.alerts ? 'On' : 'Off', sub: '' },
]);

const shortTimeout = (value: string) => {
    return value.replace(' minutes', 'm').replace(' minute', 'm').replace(' hours', 'h').replace(' hour', 'h');
};

const statusClass = (status: SecurityControl['status']) => {
    if (status === 'Active' || status === 'Compliant') return 'bg-green-50 text-green-700';
    if (status === 'Configurable') return 'bg-sky-50 text-sky-700';
    return 'bg-amber-50 text-amber-700';
};

const scoreClass = computed(() => {
    if (securityScore.value >= 85) return 'text-green-600';
    if (securityScore.value >= 70) return 'text-amber-500';
    return 'text-rose-600';
});

const saveSecurity = () => {
    localStorage.setItem('linkupSecurity', JSON.stringify(security));
    savedNote.value = 'Security settings saved.';
    window.setTimeout(() => {
        savedNote.value = '';
    }, 2000);
};

const setup2FA = async () => {
    verifyNote.value = 'Generating setup...';

    try {
        const { data } = await axios.post(route('admin.administration.two-factor.setup'));
        security.secret = data.secret;
        localStorage.setItem('linkupSecurity', JSON.stringify(security));
        verifyNote.value = `Demo code: ${data.demo_code}`;
    } catch {
        verifyNote.value = 'Unable to start 2FA setup.';
    }
};

const verify2FASetup = async () => {
    try {
        const { data } = await axios.post(route('admin.administration.two-factor.verify'), {
            code: verifyCode.value.trim(),
        });

        security.twofa = true;
        verifyNote.value = data.message || 'Verified - 2FA active';
        saveSecurity();
    } catch {
        verifyNote.value = 'Invalid code.';
    }
};

const generateRecoveryCodes = async () => {
    recoveryNote.value = 'Generating...';

    try {
        const { data } = await axios.post(route('admin.administration.recovery-codes.generate'));
        security.recovery = data.recovery_codes;
        localStorage.setItem('linkupSecurity', JSON.stringify(security));
        recoveryNote.value = '8 single-use codes generated - store them safely.';
    } catch {
        recoveryNote.value = 'Unable to generate recovery codes.';
    }
};

const saveLock = () => {
    autoLock.pin = autoLock.pin.trim() || '1234';
    localStorage.setItem('linkupAutoLock', JSON.stringify(autoLock));
    lockNote.value = autoLock.on ? `Auto-lock on - locks after ${autoLock.mins} min idle.` : 'Auto-lock disabled.';
    window.setTimeout(() => {
        lockNote.value = '';
    }, 2500);
    resetIdleTimer();
};

const resetIdleTimer = () => {
    if (idleTimer) window.clearTimeout(idleTimer);
    if (!autoLock.on || lockOpen.value) return;
    idleTimer = window.setTimeout(lockNow, autoLock.mins * 60000);
};

const lockNow = () => {
    lockOpen.value = true;
    lockPinInput.value = '';
    lockError.value = '';
};

const unlock = () => {
    if (lockPinInput.value === autoLock.pin) {
        lockOpen.value = false;
        lockError.value = '';
        resetIdleTimer();
    } else {
        lockError.value = 'Incorrect PIN. Try again.';
    }
};

const handleActivity = () => {
    if (!lockOpen.value) resetIdleTimer();
};

onMounted(() => {
    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach((eventName) => {
        document.addEventListener(eventName, handleActivity, { passive: true });
    });
    resetIdleTimer();
});

onUnmounted(() => {
    if (idleTimer) window.clearTimeout(idleTimer);
    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach((eventName) => {
        document.removeEventListener(eventName, handleActivity);
    });
});
</script>

<template>
    <section class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">Security & Access Settings</h3>
            <p class="text-slate-500">Global security rules for admin users, sessions, API access and sensitive actions.</p>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-black">Security Posture</h3>
                    <p class="text-sm text-slate-500">LinkUp's defense-in-depth controls across the stack.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-500">Security Score</p>
                    <h3 class="text-4xl font-black" :class="scoreClass">{{ securityScore }}/100</h3>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                <article v-for="control in securityControls" :key="control.name" class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="grid h-9 w-9 place-items-center rounded-xl bg-slate-900 text-white">
                            <component :is="control.icon" class="h-4 w-4" />
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-black" :class="statusClass(control.status)">{{ control.status }}</span>
                    </div>
                    <p class="mt-2 text-sm font-black">{{ control.name }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ control.detail }}</p>
                </article>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <h3 class="mb-1 text-xl font-black">Idle Auto-Lock</h3>
            <p class="mb-3 text-sm text-slate-500">Automatically lock the console after inactivity; re-entry requires the session PIN.</p>
            <div class="flex flex-wrap items-end gap-3">
                <label class="flex items-center gap-2 font-bold">
                    <input v-model="autoLock.on" type="checkbox" class="h-5 w-5 accent-green-600" @change="saveLock" />
                    Enable auto-lock
                </label>
                <div>
                    <label class="text-sm font-bold text-slate-600">Lock after</label>
                    <select v-model.number="autoLock.mins" class="mt-1 block rounded-2xl border border-slate-200 px-4 py-2.5 font-bold" @change="saveLock">
                        <option :value="2">2 minutes</option>
                        <option :value="5">5 minutes</option>
                        <option :value="10">10 minutes</option>
                        <option :value="15">15 minutes</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-600">Session PIN</label>
                    <input v-model="autoLock.pin" maxlength="8" inputmode="numeric" class="mt-1 block w-32 rounded-2xl border border-slate-200 px-4 py-2.5 font-mono" placeholder="4-8 digits" @change="saveLock" />
                </div>
                <button @click="lockNow" class="rounded-2xl bg-slate-950 px-5 py-2.5 font-black text-white">Lock Now</button>
            </div>
            <span v-if="lockNote" class="mt-2 block text-sm font-bold text-green-600">{{ lockNote }}</span>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div v-for="card in securityCards" :key="card.label" class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">{{ card.label }}</p>
                <h3 class="text-3xl font-black">{{ card.value }}</h3>
                <p v-if="card.sub" class="text-xs text-slate-500">{{ card.sub }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Two-Factor Authentication</h3>
                <label class="mb-3 flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                    <span class="font-bold">Require 2FA for admin & partner login</span>
                    <input v-model="security.twofa" type="checkbox" class="h-5 w-5 accent-green-600" @change="saveSecurity" />
                </label>

                <label class="text-sm font-bold text-slate-600">Enforcement scope</label>
                <select v-model="security.scope" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" @change="saveSecurity">
                    <option>All admins</option>
                    <option>Super Admin only</option>
                    <option>Finance & Trust roles</option>
                </select>

                <label class="text-sm font-bold text-slate-600">Method</label>
                <select v-model="security.method" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" @change="saveSecurity">
                    <option>Authenticator app (TOTP)</option>
                    <option>SMS code</option>
                    <option>Email code</option>
                </select>

                <div class="mb-3 rounded-2xl bg-slate-50 p-4">
                    <p class="mb-2 text-sm font-bold text-slate-600">Authenticator Setup</p>
                    <p class="text-xs text-slate-500">
                        Secret key:
                        <b class="font-mono">{{ security.secret || '-' }}</b>
                        <button @click="setup2FA" class="ml-2 rounded-xl bg-slate-950 px-3 py-1 text-xs font-black text-white">Generate</button>
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <input v-model="verifyCode" maxlength="6" inputmode="numeric" placeholder="6-digit code" class="w-40 rounded-2xl border border-slate-200 px-4 py-2 font-mono" />
                        <button @click="verify2FASetup" class="rounded-2xl bg-green-600 px-4 py-2 font-black text-white">Verify</button>
                        <span v-if="verifyNote" class="self-center text-sm font-bold" :class="verifyNote.startsWith('Verified') ? 'text-green-600' : verifyNote.startsWith('Invalid') ? 'text-rose-600' : 'text-slate-500'">{{ verifyNote }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button @click="generateRecoveryCodes" class="rounded-2xl border border-slate-200 px-4 py-2 font-black">Generate Recovery Codes</button>
                    <span v-if="recoveryNote" class="text-sm text-slate-500">{{ recoveryNote }}</span>
                </div>
                <div v-if="security.recovery.length" class="mt-3 grid grid-cols-2 gap-2 text-sm font-mono md:grid-cols-4">
                    <div v-for="code in security.recovery" :key="code" class="rounded-xl bg-slate-50 p-2 text-center">{{ code }}</div>
                </div>
            </div>

            <div class="card rounded-3xl p-6">
                <h3 class="mb-4 text-xl font-black">Access Policies</h3>
                <label class="text-sm font-bold text-slate-600">Session timeout</label>
                <select v-model="security.timeout" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" @change="saveSecurity">
                    <option>15 minutes</option>
                    <option>30 minutes</option>
                    <option>1 hour</option>
                    <option>4 hours</option>
                </select>

                <label class="text-sm font-bold text-slate-600">Password policy</label>
                <select v-model="security.policy" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" @change="saveSecurity">
                    <option>Strong (12+ chars, symbols, 90d rotation)</option>
                    <option>Standard (8+ chars)</option>
                </select>

                <label class="text-sm font-bold text-slate-600">Sensitive action approval</label>
                <select v-model="security.approval" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" @change="saveSecurity">
                    <option>Two-person approval</option>
                    <option>Super Admin only</option>
                </select>

                <label class="text-sm font-bold text-slate-600">IP allowlist</label>
                <input v-model="security.ip" class="mb-3 mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" @change="saveSecurity" />

                <label class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                    <span class="font-bold">Email alert on new-device login</span>
                    <input v-model="security.alerts" type="checkbox" class="h-5 w-5 accent-green-600" @change="saveSecurity" />
                </label>
                <button @click="saveSecurity" class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-4 py-2.5 font-black text-white">
                    <Save class="h-4 w-4" /> Save Settings
                </button>
                <span v-if="savedNote" class="mt-2 block text-sm font-bold text-green-600">{{ savedNote }}</span>
            </div>
        </div>

        <div v-if="lockOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-5">
            <div class="w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl">
                <Lock class="mx-auto mb-4 h-10 w-10 text-slate-950" />
                <h3 class="text-2xl font-black">Session Locked</h3>
                <p class="mt-1 text-sm text-slate-500">Enter the session PIN to continue.</p>
                <input v-model="lockPinInput" type="password" class="mt-5 w-full rounded-2xl border border-slate-200 px-4 py-3 text-center font-mono text-lg outline-none" placeholder="PIN" @keyup.enter="unlock" />
                <p v-if="lockError" class="mt-2 text-sm font-bold text-rose-600">{{ lockError }}</p>
                <button @click="unlock" class="mt-4 w-full rounded-2xl bg-slate-950 px-4 py-3 font-black text-white">Unlock</button>
            </div>
        </div>
    </section>
</template>
