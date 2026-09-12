<script setup lang="ts">
import axios from 'axios'
import { computed, ref, watch } from 'vue'

const emit = defineEmits(['closeModal', 'planCreated'])

const props = defineProps<{
    openModal: string;
    submitUrl?: string;
    editingPlan?: any;
}>()

const isEditing = computed(() => !!props.editingPlan)
const submitUrl = computed(() =>
    isEditing.value
        ? `/admin/subscription-plans/${props.editingPlan.id}/update`
        : (props.submitUrl ?? '/admin/subscription-plans/store')
)

const form = defineModel<{
    plan_name: string
    plan_emoji: string
    tagline: string
    stripe_product_id: string
    price: number | null
    billing_cycle: 'monthly' | 'yearly' | 'one_time' | 'lifetime'
    duration_days: number | null
    status: 'active' | 'inactive' | 'draft'
    description: string
    features: {
        go_live: boolean
        clubs_restaurants: boolean
        marketplace: boolean
        boost_store: boolean
        boost_events: boolean
        video_voice_calls: boolean
        the_lab: boolean
    }
    perks: {
        unlimited_swipes: boolean
        see_who_liked_you: boolean
        priority_matching_boost: boolean
        advanced_filters: boolean
        read_receipts: boolean
        weekly_profile_boost: boolean
        exclusive_plan_badge: boolean
        priority_support: boolean
    }
}>({
    default: () => ({
        plan_name: '',
        plan_emoji: '',
        tagline: '',
        stripe_product_id: '',
        price: null,
        billing_cycle: 'monthly',
        duration_days: 30,
        status: 'active',
        description: '',
        features: {
            go_live: false,
            clubs_restaurants: false,
            marketplace: false,
            boost_store: false,
            boost_events: false,
            video_voice_calls: false,
            the_lab: false,
        },
        perks: {
            unlimited_swipes: false,
            see_who_liked_you: false,
            priority_matching_boost: false,
            advanced_filters: false,
            read_receipts: false,
            weekly_profile_boost: false,
            exclusive_plan_badge: false,
            priority_support: false,
        },
    }),
})

const isSaving = ref(false)
const errors = ref<Record<string, string>>({})

const generalError = ref<string | null>(null)

async function savePlan() {
    isSaving.value = true
    errors.value = {}
    generalError.value = null

    try {
        const { data } = await axios.post(submitUrl.value, form.value)
        emit('planCreated', data)
        emit('closeModal')
    } catch (e: any) {
        if (e.response?.status === 422 && e.response.data.errors) {
            const rawErrors = e.response.data.errors as Record<string, string | string[]>
            errors.value = Object.fromEntries(
                Object.entries(rawErrors).map(([field, messages]) => [
                    field,
                    Array.isArray(messages) ? messages[0] : messages,
                ])
            )
        } else {
            generalError.value = e.response?.data?.message ?? 'Something went wrong. Please try again.'
        }
    } finally {
        isSaving.value = false
    }
}

async function deletePlan() {
    if (!confirm('Delete this plan? This cannot be undone.')) return

    await axios.delete(`/admin/subscription-plan/${props.editingPlan!.id}`)
    emit('planCreated', props.editingPlan!)
    emit('closeModal')
}

watch(() => props.editingPlan, (plan) => {
    if (plan) {
        form.value.plan_name = plan.name
        form.value.plan_emoji = plan.emoji ?? ''
        form.value.tagline = plan.tagline ?? ''
        form.value.stripe_product_id = plan.stripe_product_id ?? ''
        form.value.price = parseFloat(plan.price)
        form.value.billing_cycle = plan.billing_cycle
        form.value.duration_days = plan.duration_days
        form.value.status = plan.status
        form.value.description = plan.description ?? ''
        form.value.features = { ...plan.features }
        form.value.perks = { ...plan.perks }
    } else {
        form.value = {
            plan_name: '',
            plan_emoji: '',
            tagline: '',
            stripe_product_id: '',
            price: null,
            billing_cycle: 'monthly',
            duration_days: 30,
            status: 'active',
            description: '',
            features: {
                go_live: false,
                clubs_restaurants: false,
                marketplace: false,
                boost_store: false,
                boost_events: false,
                video_voice_calls: false,
                the_lab: false,
            },
            perks: {
                unlimited_swipes: false,
                see_who_liked_you: false,
                priority_matching_boost: false,
                advanced_filters: false,
                read_receipts: false,
                weekly_profile_boost: false,
                exclusive_plan_badge: false,
                priority_support: false,
            },
        }
    }
}, { immediate: true })
</script>

<template>
    <div class="modal-overlay" :class="props.openModal === 'open' ? 'open' : ''" id="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title-row">
                    <div class="modal-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="modal-title" id="modal-title-text">{{ isEditing ? 'Edit Plan' : 'Create Plan' }}
                        </div>
                        <div class="modal-subtitle" id="modal-subtitle-text">Add a new Caribbean-inspired tier</div>
                    </div>
                </div>
                <button class="modal-close" @click="$emit('closeModal')">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg></button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Plan Name <span>*</span></label>
                        <input v-model="form.plan_name" class="form-input" placeholder="e.g. Ritmo, El Dorado…">
                        <span v-if="errors.plan_name" class="form-hint" style="color:#dc2626">{{ errors.plan_name
                            }}</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emoji</label>
                        <input v-model="form.plan_emoji" class="form-input" placeholder="🌴" style="font-size:18px">
                        <span v-if="errors.plan_emoji" class="form-hint" style="color:#dc2626">{{
                            errors.plan_emoji }}</span>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Tagline</label>
                        <input v-model="form.tagline" class="form-input" placeholder='"Feel The Rhythm"'>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Stripe Product ID <span>*</span></label>
                        <input v-model="form.stripe_product_id" class="form-input" placeholder="prod_xxxxxxxxxxxxx"
                            style="font-family:monospace">
                        <span class="form-hint">Find this in Stripe dashboard → Products.</span>
                        <span v-if="errors.stripe_product_id" class="form-hint" style="color:#dc2626">{{
                            errors.stripe_product_id }}</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <div class="px-input">
                            <span class="px-icon">$</span>
                            <input v-model.number="form.price" class="form-input" type="number" min="0" step="0.01"
                                placeholder="0.00"><br>
                            <span v-if="errors.price" class="form-hint" style="color:#dc2626">{{
                                errors.price }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Billing</label>
                        <select v-model="form.billing_cycle" class="form-select">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="one_time">One-time</option>
                            <option value="lifetime">Lifetime</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration <span>(days)</span></label>
                        <input v-model.number="form.duration_days" class="form-input" type="number" placeholder="30">
                        <span class="form-hint">0 = unlimited</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-textarea"
                            placeholder="What does this plan unlock?"></textarea>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label" style="margin-bottom:10px">🔒 Gated Feature Access</label>
                    <div class="gate-toggles">
                        <label class="gtoggle">
                            <input type="checkbox" v-model="form.features.go_live">
                            <span class="gtoggle-emoji">🔴</span>
                            <div>
                                <div class="gtoggle-name">Go Live</div>
                                <div class="gtoggle-desc">Livestreaming to followers</div>
                            </div>
                        </label>
                        <label class="gtoggle">
                            <input type="checkbox" v-model="form.features.clubs_restaurants">
                            <span class="gtoggle-emoji">📍</span>
                            <div>
                                <div class="gtoggle-name">Clubs & Restaurants</div>
                                <div class="gtoggle-desc">Venue discovery nearby</div>
                            </div>
                        </label>
                        <label class="gtoggle">
                            <input type="checkbox" v-model="form.features.marketplace">
                            <span class="gtoggle-emoji">🛍</span>
                            <div>
                                <div class="gtoggle-name">Marketplace</div>
                                <div class="gtoggle-desc">Buy & sell on LinkUp</div>
                            </div>
                        </label>
                        <label class="gtoggle">
                            <input type="checkbox" v-model="form.features.boost_store">
                            <span class="gtoggle-emoji">🚀</span>
                            <div>
                                <div class="gtoggle-name">Boost Store</div>
                                <div class="gtoggle-desc">Promote store in marketplace</div>
                            </div>
                        </label>
                        <label class="gtoggle">
                            <input type="checkbox" v-model="form.features.boost_events">
                            <span class="gtoggle-emoji">🎟</span>
                            <div>
                                <div class="gtoggle-name">Boost Events</div>
                                <div class="gtoggle-desc">Promote events to top feed</div>
                            </div>
                        </label>
                        <label class="gtoggle">
                            <input type="checkbox" v-model="form.features.video_voice_calls">
                            <span class="gtoggle-emoji">📹</span>
                            <div>
                                <div class="gtoggle-name">Video & Voice Calls</div>
                                <div class="gtoggle-desc">In-chat calling between matches</div>
                            </div>
                        </label>
                        <label class="gtoggle" style="grid-column:1/-1">
                            <input type="checkbox" v-model="form.features.the_lab">
                            <span class="gtoggle-emoji">🔬</span>
                            <div>
                                <div class="gtoggle-name">The Lab</div>
                                <div class="gtoggle-desc">AI match tools & compatibility insights</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="form-group">
                    <label class="form-label" style="margin-bottom:10px">✨ Additional Perks</label>
                    <div class="perks-grid">
                        <div class="perk-item">
                            <input type="checkbox" id="p1" v-model="form.perks.unlimited_swipes">
                            <label for="p1">Unlimited swipes</label>
                        </div>
                        <div class="perk-item">
                            <input type="checkbox" id="p2" v-model="form.perks.see_who_liked_you">
                            <label for="p2">See who liked you</label>
                        </div>
                        <div class="perk-item">
                            <input type="checkbox" id="p3" v-model="form.perks.priority_matching_boost">
                            <label for="p3">Priority matching boost</label>
                        </div>
                        <div class="perk-item">
                            <input type="checkbox" id="p4" v-model="form.perks.advanced_filters">
                            <label for="p4">Advanced filters</label>
                        </div>
                        <div class="perk-item">
                            <input type="checkbox" id="p5" v-model="form.perks.read_receipts">
                            <label for="p5">Read receipts</label>
                        </div>
                        <div class="perk-item">
                            <input type="checkbox" id="p6" v-model="form.perks.weekly_profile_boost">
                            <label for="p6">Weekly profile boost</label>
                        </div>
                        <div class="perk-item">
                            <input type="checkbox" id="p7" v-model="form.perks.exclusive_plan_badge">
                            <label for="p7">Exclusive plan badge</label>
                        </div>
                        <div class="perk-item">
                            <input type="checkbox" id="p8" v-model="form.perks.priority_support">
                            <label for="p8">Priority support</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button v-if="isEditing" class="btn btn-danger btn-sm" style="margin-right:auto" @click="deletePlan">
                    Delete Plan
                </button>
                <button class="btn btn-ghost" @click="$emit('closeModal')" :disabled="isSaving">Cancel</button>
                <button class="btn btn-primary" :disabled="isSaving" @click="savePlan">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ isSaving ? 'Saving…' : 'Save Plan' }}
                </button>
            </div>
        </div>
    </div>
</template>
<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .78);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s;
}

.modal-overlay.open {
    opacity: 1;
    pointer-events: all;
}

.modal {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 20px;
    width: 580px;
    max-width: 95vw;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 48px rgba(0, 0, 0, .18);
    transform: translateY(24px) scale(.96);
    transition: transform .28s cubic-bezier(.34, 1.56, .64, 1);
}

.modal-overlay.open .modal {
    transform: none;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 26px 18px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.modal-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-icon {
    width: 40px;
    height: 40px;
    border-radius: 11px;
    background: rgba(200, 230, 58, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-icon svg {
    width: 19px;
    height: 19px;
    color: #C8E63A;
}

.modal-title {
    font-size: 18px;
    font-weight: 700;
}

.modal-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin-top: 2px;
}

.modal-close {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #f0f2f9;
    border: none;
    cursor: pointer;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
}

.modal-close:hover {
    background: #e6e9f4;
    color: #111827;
}

.modal-body {
    padding: 22px 26px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 18px 26px 22px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 13px;
    font-weight: 600;
}

.form-label span {
    color: #6b7280;
    font-weight: 400;
    margin-left: 4px;
}

.form-input,
.form-textarea,
.form-select {
    background: #f0f2f9;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 8px;
    color: #111827;
    font-size: 14px;
    font-family: inherit;
    padding: 9px 13px;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
    border-color: #C8E63A;
    box-shadow: 0 0 0 3px rgba(200, 230, 58, 0.18);
}

.form-input::placeholder,
.form-textarea::placeholder {
    color: #6b7280;
}

.form-textarea {
    resize: vertical;
    min-height: 72px;
    line-height: 1.5;
}

.form-select {
    appearance: none;
    cursor: pointer;
    padding-right: 34px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%237b849a' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 11px center;
    background-size: 13px;
}

.form-hint {
    font-size: 11px;
    color: #6b7280;
}

.px-input {
    position: relative;
}

.px-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
}

.px-input .form-input {
    padding-left: 24px;
}

.form-divider {
    height: 1px;
    background: rgba(0, 0, 0, 0.08);
    margin: 18px 0;
}

.gate-toggles {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.gtoggle {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 13px;
    background: #f0f2f9;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 8px;
    cursor: pointer;
    transition: border-color .15s;
}

.gtoggle:hover {
    border-color: rgba(0, 0, 0, .15);
}

.gtoggle input[type=checkbox] {
    accent-color: #C8E63A;
    width: 15px;
    height: 15px;
    cursor: pointer;
    flex-shrink: 0;
}

.gtoggle-emoji {
    font-size: 18px;
}

.gtoggle-name {
    font-size: 13px;
    font-weight: 600;
}

.gtoggle-desc {
    font-size: 11px;
    color: #6b7280;
    margin-top: 1px;
}

.perks-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 7px;
}

.perk-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 11px;
    background: #f0f2f9;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 8px;
}

.perk-item input[type=checkbox] {
    accent-color: #C8E63A;
    width: 14px;
    height: 14px;
    cursor: pointer;
}

.perk-item label {
    font-size: 13px;
    cursor: pointer;
}

::-webkit-scrollbar {
    width: 5px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #e6e9f4;
    border-radius: 3px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .15s;
    white-space: nowrap;
    font-family: inherit;
}

.btn-primary {
    background: #C8E63A;
    color: #0f1117;
}

.btn-primary:hover {
    background: #a8c420;
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(200, 230, 58, 0.18);
}

.btn-ghost {
    background: transparent;
    color: #6b7280;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.btn-ghost:hover {
    background: #f0f2f9;
    color: #111827;
}

.btn-danger {
    background: rgba(239, 68, 68, .12);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, .2);
}

.btn-danger:hover {
    background: rgba(239, 68, 68, .2);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
}
</style>
