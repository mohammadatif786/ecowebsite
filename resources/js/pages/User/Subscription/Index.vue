<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

interface PlanFeatures {
    go_live: boolean
    clubs_restaurants: boolean
    marketplace: boolean
    boost_store: boolean
    boost_events: boolean
    video_voice_calls: boolean
    the_lab: boolean
}

interface PlanPerks {
    unlimited_swipes: boolean
    see_who_liked_you: boolean
    priority_matching_boost: boolean
    advanced_filters: boolean
    read_receipts: boolean
    weekly_profile_boost: boolean
    exclusive_plan_badge: boolean
    priority_support: boolean
}

interface SubscriptionPlan {
    id: number
    name: string
    emoji: string | null
    tagline: string | null
    description: string | null
    price: string
    billing_cycle: 'monthly' | 'yearly' | 'one_time' | 'lifetime'
    duration_days: number
    status: 'active' | 'inactive' | 'draft'
    features: PlanFeatures
    perks: PlanPerks
    stripe_price_id: string
    stripe_product_id: string
}

const props = defineProps<{
    plans: { data: SubscriptionPlan[] } | SubscriptionPlan[]
    activePlanIds: number[]
}>()

// ✅ unwrap paginated or plain array
const planList = computed<SubscriptionPlan[]>(() =>
    Array.isArray(props.plans) ? props.plans : (props.plans as any)?.data ?? []
)

const FEATURE_CONFIG: Record<keyof PlanFeatures, { label: string; icon: string; tag: string; tagClass: string }> = {
    go_live: { label: 'Go Live', icon: '🔴', tag: 'Live', tagClass: 'tag-live' },
    clubs_restaurants: { label: 'Clubs & Restaurants', icon: '📍', tag: 'Nearby', tagClass: 'tag-nearby' },
    marketplace: { label: 'Marketplace', icon: '🛍', tag: 'Market', tagClass: 'tag-market' },
    boost_store: { label: 'Boost Store', icon: '🚀', tag: 'Boost', tagClass: 'tag-boost' },
    boost_events: { label: 'Boost Events', icon: '🎟', tag: 'Events', tagClass: 'tag-event' },
    video_voice_calls: { label: 'Video & Voice Calls', icon: '📹', tag: 'Calls', tagClass: 'tag-calls' },
    the_lab: { label: 'The Lab', icon: '🔬', tag: 'Lab', tagClass: 'tag-lab' },
}

const PERK_LABELS: Record<keyof PlanPerks, string> = {
    unlimited_swipes: 'Unlimited swipes',
    see_who_liked_you: 'See who liked you',
    priority_matching_boost: 'Priority matching boost',
    advanced_filters: 'Advanced filters',
    read_receipts: 'Read receipts',
    weekly_profile_boost: 'Weekly profile boost',
    exclusive_plan_badge: 'Exclusive plan badge',
    priority_support: 'Priority support',
}

const BANNER_GRADIENTS = [
    'linear-gradient(90deg,#38bdf8,#818cf8)',
    'linear-gradient(90deg,#f59e0b,#f97316)',
    'linear-gradient(90deg,#C8E63A,#a8c420)',
    'linear-gradient(90deg,#f59e0b,#fbbf24,#f59e0b)',
    'linear-gradient(90deg,#ec4899,#a855f7,#3b82f6,#22d3a4)',
]

function bannerGradient(i: number) { return BANNER_GRADIENTS[i % BANNER_GRADIENTS.length] }

function priceAmount(plan: SubscriptionPlan) { return parseFloat(plan.price) }

function formattedPrice(plan: SubscriptionPlan) {
    const amt = priceAmount(plan)
    return isNaN(amt) || amt === 0 ? 'Free' : `$${amt.toFixed(2)}`
}

function pricePeriod(plan: SubscriptionPlan) {
    if (priceAmount(plan) === 0) return ''
    return plan.billing_cycle === 'monthly' ? '/month'
        : plan.billing_cycle === 'yearly' ? '/year'
            : ''
}

function priceNote(plan: SubscriptionPlan) {
    if (priceAmount(plan) === 0) return 'Forever · No card needed'
    switch (plan.billing_cycle) {
        case 'monthly': return 'Billed monthly · Cancel anytime'
        case 'yearly': return 'Billed yearly · Cancel anytime'
        case 'one_time': return 'One-time payment'
        case 'lifetime': return 'One-time · Lifetime access'
        default: return ''
    }
}

function descriptionLines(plan: SubscriptionPlan) {
    if (!plan.description) return []
    const lines = plan.description.split('\n').map(l => l.trim()).filter(Boolean)
    return lines[0]?.toLowerCase() === "what's included" ? lines.slice(1) : lines
}

function includedPerks(plan: SubscriptionPlan) {
    return Object.entries(plan.perks ?? {})
        .filter(([, v]) => v)
        .map(([k]) => PERK_LABELS[k as keyof PlanPerks] ?? k)
}

function includedFeatures(plan: SubscriptionPlan) {
    return Object.entries(plan.features ?? {})
        .filter(([, v]) => v)
        .map(([k]) => FEATURE_CONFIG[k as keyof PlanFeatures])
        .filter(Boolean)
}

function lockedFeatures(plan: SubscriptionPlan) {
    return Object.entries(plan.features ?? {})
        .filter(([, v]) => !v)
        .map(([k]) => FEATURE_CONFIG[k as keyof PlanFeatures])
        .filter(Boolean)
}

function isActive(plan: SubscriptionPlan) {
    return props.activePlanIds.includes(plan.id)
}

const form = useForm({ plan_price_id: '', plan_id: 0 })

function choosePlan(plan: SubscriptionPlan) {
    form.plan_price_id = plan.stripe_price_id ?? ''
    form.plan_id = plan.id
    form.post(route('frontend.subscription.choose.plan'))
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Subscription Plans" />

        <main class="plans-page">

            <!-- Hero -->
            <div class="plans-hero">
                <h1 class="plans-hero-title">¡Viva Link Up! 🌴🎶</h1>
                <p class="plans-hero-sub">
                    Connect across the Caribbean & Latin America — salsa, reggae, vibrant communities.
                    Unlock exclusive experiences and elevate your journey.
                </p>
            </div>

            <!-- Grid -->
            <div class="plans-grid">
                <div v-for="(plan, index) in planList" :key="plan.id" class="plan-card"
                    :class="{ 'plan-card--active': isActive(plan) }">
                    <!-- colour bar -->
                    <div class="plan-banner" :style="{ background: bannerGradient(index) }" />

                    <div class="plan-inner">

                        <!-- head -->
                        <div class="plan-head">
                            <div class="plan-head-left">
                                <div class="plan-emoji">{{ plan.emoji || '✨' }}</div>
                                <div>
                                    <div class="plan-name">{{ plan.name }}</div>
                                    <div v-if="plan.tagline" class="plan-tagline">{{ plan.tagline }}</div>
                                </div>
                            </div>
                            <span v-if="isActive(plan)" class="badge badge-active">✓ Subscribed</span>
                        </div>

                        <!-- price -->
                        <div class="plan-price-row">
                            <span class="plan-amount">{{ formattedPrice(plan) }}</span>
                            <span v-if="pricePeriod(plan)" class="plan-period">{{ pricePeriod(plan) }}</span>
                        </div>
                        <div class="plan-note">{{ priceNote(plan) }}</div>

                        <!-- description lines -->
                        <template v-if="descriptionLines(plan).length">
                            <div class="plan-section-label">What's included</div>
                            <div v-for="line in descriptionLines(plan)" :key="'d-' + line" class="feat">
                                <div class="feat-dot on">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                {{ line }}
                            </div>
                        </template>

                        <!-- perks -->
                        <template v-if="includedPerks(plan).length">
                            <div class="plan-section-label" style="margin-top:12px">Perks</div>
                            <div v-for="perk in includedPerks(plan)" :key="'p-' + perk" class="feat">
                                <div class="feat-dot on">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                {{ perk }}
                            </div>
                        </template>

                        <!-- unlocked features -->
                        <template v-if="includedFeatures(plan).length">
                            <div class="plan-section-label" style="margin-top:12px">Features Unlocked</div>
                            <div v-for="feat in includedFeatures(plan)" :key="'f-' + feat.label" class="feat">
                                <div class="feat-dot on">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                {{ feat.label }}
                                <span class="feat-tag" :class="feat.tagClass">{{ feat.icon }} Unlocked</span>
                            </div>
                        </template>

                        <!-- locked features -->
                        <template v-if="lockedFeatures(plan).length">
                            <div class="plan-divider" />
                            <div class="plan-section-label">
                                {{ priceAmount(plan) === 0 ? 'Requires subscription' : 'Upgrade to unlock' }}
                            </div>
                            <div v-for="feat in lockedFeatures(plan)" :key="'l-' + feat.label" class="feat dim">
                                <div class="feat-dot off">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                {{ feat.label }}
                                <span class="feat-tag" :class="feat.tagClass">{{ feat.icon }} {{ feat.tag }}</span>
                            </div>
                        </template>

                    </div>

                    <!-- CTA -->
                    <div class="plan-footer">
                        <button v-if="isActive(plan)" class="btn btn-subscribed" disabled>
                            ✓ You're Subscribed
                        </button>
                        <button v-else class="btn btn-choose" :disabled="form.processing" @click="choosePlan(plan)">
                            {{ form.processing && form.plan_id === plan.id ? 'Redirecting…' : `Choose ${plan.name}` }}
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </AuthenticatedLayout>
</template>

<style scoped>
.plans-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 48px 24px;
}

.plans-hero {
    text-align: center;
    margin-bottom: 44px;
}

.plans-hero-title {
    font-size: 36px;
    font-weight: 800;
    color: #111827;
    letter-spacing: -.5px;
}

.plans-hero-sub {
    margin-top: 12px;
    font-size: 16px;
    color: #6b7280;
    max-width: 560px;
    margin-inline: auto;
    line-height: 1.6;
}

/* grid */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

@media (max-width: 900px) {
    .plans-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 580px) {
    .plans-grid {
        grid-template-columns: 1fr;
    }
}

/* card */
.plan-card {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, .08);
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform .15s, box-shadow .15s;
}

.plan-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, .1);
}

.plan-card--active {
    border-color: #C8E63A;
    box-shadow: 0 0 0 2px rgba(200, 230, 58, .3);
}

.plan-banner {
    height: 5px;
    width: 100%;
    flex-shrink: 0;
}

.plan-inner {
    padding: 20px 20px 0;
    flex: 1;
}

/* head */
.plan-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 14px;
}

.plan-head-left {
    display: flex;
    align-items: center;
    gap: 11px;
}

.plan-emoji {
    font-size: 30px;
    line-height: 1;
}

.plan-name {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -.3px;
    color: #111827;
}

.plan-tagline {
    font-size: 11px;
    color: #6b7280;
    margin-top: 2px;
    font-style: italic;
}

/* price */
.plan-price-row {
    display: flex;
    align-items: baseline;
    gap: 5px;
    margin-bottom: 3px;
}

.plan-amount {
    font-size: 32px;
    font-weight: 800;
    color: #111827;
}

.plan-period {
    font-size: 13px;
    color: #6b7280;
}

.plan-note {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 16px;
}

/* section label */
.plan-section-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .9px;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 9px;
}

.plan-divider {
    height: 1px;
    background: rgba(0, 0, 0, .08);
    margin: 14px 0;
}

/* features */
.feat {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    margin-bottom: 7px;
    color: #111827;
}

.feat.dim {
    color: #6b7280;
}

.feat-dot {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.feat-dot.on {
    background: rgba(34, 211, 164, .15);
    color: #16a34a;
}

.feat-dot.off {
    background: rgba(0, 0, 0, .04);
    color: #9ca3af;
}

.feat-dot svg {
    width: 9px;
    height: 9px;
}

.feat-tag {
    margin-left: auto;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 20px;
    white-space: nowrap;
}

.tag-live {
    background: rgba(239, 68, 68, .15);
    color: #f87171;
}

.tag-nearby {
    background: rgba(56, 189, 248, .15);
    color: #38bdf8;
}

.tag-market {
    background: rgba(245, 158, 11, .15);
    color: #f59e0b;
}

.tag-boost {
    background: rgba(168, 85, 247, .15);
    color: #a855f7;
}

.tag-event {
    background: rgba(251, 146, 60, .15);
    color: #fb923c;
}

.tag-calls {
    background: rgba(34, 211, 164, .15);
    color: #16a34a;
}

.tag-lab {
    background: rgba(139, 92, 246, .15);
    color: #8b5cf6;
}

/* badge */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.badge-active {
    background: rgba(200, 230, 58, .15);
    color: #465012;
}

/* footer */
.plan-footer {
    padding: 16px 20px 20px;
    border-top: 1px solid rgba(0, 0, 0, .08);
    margin-top: 16px;
}

.btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all .15s;
    font-family: inherit;
}

.btn-choose {
    background: #C8E63A;
    color: #0f1117;
}

.btn-choose:hover:not(:disabled) {
    background: #a8c420;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(200, 230, 58, .35);
}

.btn-subscribed {
    background: rgba(200, 230, 58, .1);
    color: #465012;
    cursor: not-allowed;
    border: 1px solid rgba(200, 230, 58, .3);
}

.btn:disabled {
    opacity: .7;
}
</style>
