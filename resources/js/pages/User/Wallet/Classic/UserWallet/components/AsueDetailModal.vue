<template>
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between mb-6">
                <h3 class="text-2xl font-bold text-linkup-teal">{{ asue.name }}</h3>
                <button @click="$emit('close')">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <div
                class="bg-gradient-to-r from-linkup-teal to-linkup-ocean p-6 rounded-2xl shadow-lg text-white mb-6 flex justify-between items-center">
                <div>
                    <div class="text-xs uppercase opacity-80">Total Pot</div>
                    <div class="text-3xl font-bold font-mono">{{ formatCurrency(totalPot) }}</div>
                    
                    <div class="mt-2 text-xs opacity-90">
                        <div class="flex items-center gap-2">
                            <span>Fee (3%):</span>
                            <span>-{{ formatCurrency(platformFee) }}</span>
                        </div>
                        <div class="flex items-center gap-2 font-bold border-t border-white/20 pt-1 mt-1">
                            <span>Payout:</span>
                            <span>{{ formatCurrency(payoutAmount) }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs uppercase opacity-80">Status</div>
                    <div class="text-xl font-bold">{{ asue.status }}</div>
                </div>
            </div>

            <div class="space-y-2 overflow-y-auto max-h-64 pr-2 custom-scrollbar">
                <div v-for="(member, index) in membersWithDates" :key="index"
                    class="flex items-center justify-between p-3 bg-white rounded-xl border"
                    :class="member.isCurrent ? 'border-teal-500 ring-1 ring-teal-100' : 'border-slate-100'">

                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center w-10">
                            <span class="text-[10px] font-bold text-slate-400">{{ member.displayDate }}</span>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold"
                                :class="member.isYou ? 'bg-linkup-teal text-white' : 'bg-slate-100 text-slate-500'">
                                {{ index + 1 }}
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-900">{{ member.name }}</span>
                        </div>
                    </div>

                    <span class="text-xs font-bold px-2 py-1 rounded"
                        :class="memberStatusClass(member)">
                        {{ memberStatusLabel(member) }}
                    </span>
                </div>
            </div>

            <div v-if="!isCompleted" class="mt-6">
                <template v-if="!isActive">
                    <div class="bg-amber-50 border border-amber-200 p-3 rounded-xl mb-4">
                        <p class="text-xs text-amber-700 font-medium">
                            Awaiting participation confirmations: {{ acceptedCount }} / {{ asue.invited_users.length }} accepted.
                        </p>
                    </div>
                    <button v-if="currentParticipationStatus !== 'accepted'" @click="acceptParticipation"
                        class="w-full py-3 bg-linkup-teal text-white rounded-xl font-bold text-xs hover:opacity-90 transition-opacity">
                        Accept Invitation
                    </button>
                    <div v-else class="bg-slate-50 border border-slate-200 p-3 rounded-xl text-center">
                        <p class="text-xs text-slate-600 font-medium">
                            You have accepted. Waiting for the remaining participants.
                        </p>
                    </div>
                </template>
                <template v-else>
                    <div v-if="!asue.payout_accepted" class="bg-amber-50 border border-amber-200 p-3 rounded-xl mb-4">
                        <p class="text-xs text-amber-700 font-medium">
                            Waiting for <span class="font-bold underline text-amber-900">{{ activeMember?.name }}</span> to
                            accept the payout of {{ formatCurrency(payoutAmount) }} (after fee).
                        </p>
                    </div>
                    <div v-else class="bg-green-50 border border-green-200 p-3 rounded-xl mb-4 text-center">
                        <p class="text-xs text-green-700 font-medium">
                            Payout accepted! Click <strong>Simulate Week/Payout</strong> to start the next cycle.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button @click="simulateAccept" :disabled="asue.payout_accepted || !canSimulateAccept"
                            class="py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ asue.payout_accepted ? 'Payout Accepted' : 'Simulate Accepts' }}
                        </button>
                        <button v-if="isOwner" @click="processCycle" :disabled="!isActive"
                            class="py-3 bg-linkup-teal text-white rounded-xl font-bold text-xs hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed">
                            Simulate Week/Payout
                        </button>
                    </div>
                </template>
            </div>
            <div v-else class="mt-6 flex gap-2">
                <button @click="startNewCircle"
                    class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-colors">
                    Start New Circle
                </button>
                <button @click="exportRecord"
                    class="w-full py-3 border border-slate-200 rounded-xl font-bold hover:bg-slate-50 transition-colors">
                    Export Record
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Member {
    id: number;
    name: string;
    linkup_id: string;
    status?: string;
    position?: number;
    pivot?: {
        position: number;
        participation_status?: string;
    };
}

interface Asue {
    id: number;
    user_id: number;
    name: string;
    frequency: string;
    hand_amount: number | string;
    start_date: string;
    status: string;
    invited_users: Member[];
    current_turn: number;
    payout_accepted: boolean;
}

const props = defineProps<{
    asue: Asue;
    currentUser: { id: number; linkup_id: string };
}>();

const emit = defineEmits(['close', 'refresh']);

const statusNormalized = computed(() => (props.asue.status || '').toLowerCase());
const isCompleted = computed(() => statusNormalized.value === 'completed');
const isActive = computed(() => statusNormalized.value === 'active');
const isOwner = computed(() => props.currentUser.id === props.asue.user_id);

const totalPot = computed(() => {
    const amount = typeof props.asue.hand_amount === 'string' ? parseFloat(props.asue.hand_amount) : props.asue.hand_amount;
    return amount * (props.asue.invited_users?.length || 1);
});

const platformFee = computed(() => {
    return totalPot.value * 0.03;
});

const payoutAmount = computed(() => {
    return totalPot.value - platformFee.value;
});

const activeMember = computed(() => {
    return props.asue.invited_users.find(m => (m.pivot?.position || m.position) === props.asue.current_turn);
});

const isYourTurn = computed(() => {
    return activeMember.value?.id === props.currentUser.id;
});

const canSimulateAccept = computed(() => {
    return isActive.value && (isYourTurn.value || isOwner.value);
});

const currentMember = computed(() => {
    return props.asue.invited_users.find(m => m.id === props.currentUser.id || m.linkup_id === props.currentUser.linkup_id);
});

const currentParticipationStatus = computed(() => {
    const raw = (currentMember.value?.pivot as any)?.participation_status || (currentMember.value as any)?.participation_status;
    if (raw) return String(raw).toLowerCase();
    return isActive.value ? 'accepted' : 'invited';
});

const acceptedCount = computed(() => {
    return (props.asue.invited_users || []).filter(m => {
        const raw = (m.pivot as any)?.participation_status || (m as any)?.participation_status;
        const normalized = raw ? String(raw).toLowerCase() : (isActive.value ? 'accepted' : 'invited');
        return normalized === 'accepted';
    }).length;
});

const membersWithDates = computed(() => {
    if (!props.asue.invited_users) return [];

    return props.asue.invited_users.map((member, index) => {
        const startDate = new Date(props.asue.start_date);
        const interval = props.asue.frequency.toLowerCase() === 'weekly' ? 7 : 30;
        const turnDate = new Date(startDate.getTime());
        turnDate.setDate(startDate.getDate() + (index * interval));

        const pos = member.pivot?.position || member.position || (index + 1);
        const hasBeenPaid = pos < props.asue.current_turn || (pos === props.asue.current_turn && props.asue.payout_accepted);
        const participationRaw = (member.pivot as any)?.participation_status || (member as any)?.participation_status;
        const participationStatus = (participationRaw ? String(participationRaw) : (isActive.value ? 'accepted' : 'invited')).toUpperCase();
        const payoutStatus = hasBeenPaid ? 'PAID' : 'PENDING';

        return {
            ...member,
            displayDate: turnDate.toLocaleDateString('en-US', { day: '2-digit', month: '2-digit' }),
            isCurrent: props.asue.current_turn === pos && !isCompleted.value,
            isYou: member.id === props.currentUser.id || member.linkup_id === props.currentUser.linkup_id,
            participationStatus,
            payoutStatus
        };
    });
});

const memberStatusLabel = (member: any) => {
    return isActive.value ? (member.payoutStatus || 'PENDING') : (member.participationStatus || 'INVITED');
};

const memberStatusClass = (member: any) => {
    if (isActive.value) {
        return member.payoutStatus === 'PAID' ? 'text-green-600 bg-green-50' : 'text-slate-400';
    }
    return member.participationStatus === 'ACCEPTED' ? 'text-green-600 bg-green-50' : 'text-amber-700 bg-amber-50';
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const acceptParticipation = () => {
    router.post(route('frontend.user.asues.accept-participation', props.asue.id), {}, {
        onSuccess: () => {
            emit('refresh');
        }
    });
};

const simulateAccept = () => {
    if (!confirm(`Are you sure you want to collect contributions from everyone and payout to ${activeMember.value?.name}? A 3% fee (${formatCurrency(platformFee.value)}) will be deducted.`)) return;

    router.post(route('frontend.user.asues.simulate-accept', props.asue.id), {}, {
        onSuccess: () => {
            emit('refresh');
        }
    });
};

const processCycle = () => {
    const msg = props.asue.payout_accepted
        ? 'Advance to next turn?'
        : 'Advance cycle anyway?';

    if (!confirm(msg)) return;

    router.post(route('frontend.user.asues.simulate-cycle', props.asue.id), {}, {
        onSuccess: () => {
            emit('refresh');
        }
    });
};

const startNewCircle = () => {
    alert('Starting new circle flow...');
    emit('close');
};

const exportRecord = () => {
    alert('Exporting record...');
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.bg-linkup-teal {
    background-color: #008080;
}

.text-linkup-teal {
    color: #008080;
}

.from-linkup-teal {
    --tw-gradient-from: #008080;
    --tw-gradient-to: rgb(0 128 128 / 0);
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.to-linkup-ocean {
    --tw-gradient-to: #00a4b4;
}
</style>
