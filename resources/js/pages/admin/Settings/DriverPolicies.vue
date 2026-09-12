<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { ShieldCheck, CheckCircle2 } from 'lucide-vue-next';

const showToast = ref(false);

const policies = ref([
    {
        key: 'agreement',
        title: 'Driver Services Agreement',
        body: `LinkUp Vibes — Driver Services Agreement
Effective 1 January 2026

1. INDEPENDENT CONTRACTOR. You provide delivery services as an independent contractor, not an employee, partner or agent of LinkUp Vibes Ltd. ("LinkUp"). You decide when, where and whether to accept deliveries. Nothing here creates an employment, joint-venture or franchise relationship.

2. ELIGIBILITY. You must be at least 18, legally entitled to work and drive in your country, hold a valid driver's licence, and pass LinkUp's identity verification (KYC) and background check. You must maintain a registered, road-worthy, insured vehicle.

3. THE SERVICES. LinkUp is a technology platform that connects you with delivery requests from LinkUp Eats (food) and the LinkUp Marketplace (goods). LinkUp does not itself prepare food or sell goods; you transport items between a pickup (restaurant/seller) and a customer drop-off.

4. CUSTODY OF GOODS. From pickup to verified drop-off you are in sole custody of the customer's property and are responsible for delivering it complete, sealed and undamaged. Tampering with, consuming, opening, withholding, or stealing any item is theft, will result in immediate permanent deactivation, forfeiture of pending earnings, and referral to law enforcement (see the Item Handling & Anti-Theft Policy).

5. PAYMENTS. You earn a per-delivery fee plus customer tips, shown before you accept. Earnings accrue to your Driver Wallet and may be cashed out subject to verification, holds and fees (see the Payments & Payouts Policy).

6. CONDUCT. You agree to the Driver Code of Conduct, all traffic and safety laws, and to treat customers, merchants and staff with respect.

7. INSURANCE & LIABILITY. You are responsible for maintaining valid vehicle insurance and for your own acts. LinkUp is not liable for accidents, injuries, fines or losses arising from your provision of services (see Insurance & Liability).

8. TERM & DEACTIVATION. Either party may end this Agreement at any time. LinkUp may suspend or deactivate your account for policy breaches, safety risk, fraud, theft, low ratings or legal reasons (see Deactivation & Appeals).

9. DATA. Your information is handled per the Driver Privacy Notice and is shared with KYC/banking partners and, where required, authorities.

10. ARBITRATION. Disputes are resolved by binding individual arbitration where permitted by law; class actions are waived. Governing law: The Bahamas.

By tapping "Submit application" or accepting a delivery, you agree to this Agreement and all linked policies.`
    },
    {
        key: 'conduct',
        title: 'Driver Code of Conduct',
        body: `LinkUp Driver — Code of Conduct

• Be honest. Never steal, open, sample or tamper with an order. Deliver exactly what you picked up.
• Be safe. Obey all traffic laws, never drive impaired, no phone-in-hand driving, wear a seatbelt/helmet.
• Be on time. Accept only what you can complete; follow the in-app route; keep food upright and insulated.
• Be respectful. No harassment, discrimination, threats or unwanted contact with customers or merchants. Keep customer details private.
• Be presentable. Maintain a clean vehicle and a courteous manner.
• Verify drop-off. Hand orders to the right person/location; follow contactless and ID-on-delivery rules where required.
• No fraud. No fake GPS, fake deliveries, multi-accounting, or manipulating incentives.
• Communicate. Use in-app call/chat for delivery matters only; report issues immediately.

Violations may lead to warnings, suspension or permanent deactivation depending on severity. Theft, violence, impaired driving and fraud are zero-tolerance and result in immediate removal.`
    },
    {
        key: 'kyc',
        title: 'Identity Verification & Background Check',
        body: `LinkUp Driver — Identity Verification (KYC) & Background Check

Because drivers take custody of customers' food, goods and money, LinkUp verifies every driver thoroughly before activation and on an ongoing basis.

REQUIRED TO ACTIVATE:
1. Government-issued photo ID (passport, national ID or driver's licence).
2. A live selfie used for liveness detection and biometric face-match to your ID.
3. Proof of address (recent utility bill / bank statement / official letter).
4. Valid driver's licence (number, photo, expiry).
5. Vehicle registration and a valid insurance certificate.
6. Police record / criminal background check (where available in your country).
7. Bank account or payout details for earnings.

REVIEW: Documents are checked automatically and then manually approved by LinkUp compliance and, where applicable, banking/KYC partners (e.g., the Scotiabank KYC portal). Until approved your status is "Pending Review" and you cannot go online.

ONGOING: We may re-verify periodically, re-run background checks, require document renewals (e.g., expiring insurance/licence), and screen against sanctions and watch lists. Providing false documents is grounds for immediate permanent deactivation and referral to authorities.

Your documents are encrypted in transit and at rest and shared only with authorized verification/banking partners and regulators.`
    },
    {
        key: 'handling',
        title: 'Item Handling, Custody & Anti-Theft Policy',
        body: `LinkUp Driver — Item Handling, Custody & Anti-Theft Policy

THE RULE: From the moment you pick up an order until the customer confirms receipt, every item is the customer's property held in your trust. You must deliver it complete, sealed and undamaged.

YOU MUST NOT:
• Open, unseal, consume, sample, remove, swap or "short" any item.
• Keep, resell or divert an order, or mark a delivery complete without delivering.
• Deliver to the wrong person to misappropriate goods.

HANDLING STANDARDS:
• Check the order/seal at pickup; report discrepancies in-app before leaving.
• Keep food upright, hot/cold separated, and use an insulated bag.
• Handle fragile/marketplace parcels with care; do not bend, crush or expose to weather.
• Follow drop-off instructions; capture proof of delivery (photo/PIN/signature) where requested.

ENFORCEMENT: Theft or tampering — including opening or consuming food, or withholding marketplace goods — results in IMMEDIATE PERMANENT DEACTIVATION, forfeiture of all pending earnings tied to the incident, recovery of the item's value, and referral to law enforcement. High-value marketplace items may require ID verification and signed handover. Repeated "items missing" complaints trigger investigation and holds.

You consent to GPS tracking, delivery photos, and order audits as part of loss prevention.`
    },
    {
        key: 'payments',
        title: 'Driver Payments & Payouts',
        body: `LinkUp Driver — Payments & Payouts

EARNINGS: You earn a per-delivery fee plus 100% of customer tips. The payout is shown before you accept. Multi-stop or surge trips may pay more.

DRIVER WALLET: Completed-delivery earnings accrue to your in-app Driver Wallet. Cash-outs transfer to your LinkUp Wallet and then to your verified bank account.

REQUIREMENTS & HOLDS: Cash-out requires completed KYC and a verified payout method. We may place holds or reverse amounts linked to fraud, chargebacks, customer refunds, "missing item" claims or policy breaches. Earnings obtained through fraud or theft are forfeited.

FEES & TAXES: A bank/processing fee may apply to withdrawals (disclosed before you confirm). As an independent contractor, you are solely responsible for your own taxes and any required registrations.

ADJUSTMENTS: Tips may be added/adjusted by the customer for a period after delivery. Disputed deliveries may be reviewed before release.

Questions: drivers@linkupvibes.com.`
    },
    {
        key: 'insurance',
        title: 'Insurance & Liability',
        body: `LinkUp Driver — Insurance & Liability

YOUR RESPONSIBILITY: You must hold and maintain valid vehicle insurance and a current driver's licence and registration that meet your country's legal minimums, and provide proof on request. You are responsible for the safe operation of your vehicle at all times.

LIABILITY: As an independent contractor you are responsible for your own acts and omissions. To the maximum extent permitted by law, LinkUp is not liable for accidents, injuries, property damage, traffic fines, vehicle wear, or losses arising from your provision of services, and you indemnify LinkUp against claims arising from your conduct.

INCIDENTS: Report any accident, injury, theft or safety incident to LinkUp and the relevant authorities immediately. Cooperate with investigations and insurers.

LAPSES: Driving with expired/insufficient insurance, licence or registration is prohibited and will result in suspension until valid documents are provided.`
    },
    {
        key: 'deactivation',
        title: 'Deactivation & Appeals',
        body: `LinkUp Driver — Deactivation & Appeals

GROUNDS FOR ACTION: LinkUp may warn, suspend or permanently deactivate a driver for: theft or tampering with orders; fraud (fake deliveries, GPS spoofing, multi-accounting); violence, harassment or discrimination; impaired or unsafe driving; consistently low ratings or high cancellation rates; expired/invalid documents; failing re-verification; or breaking the law or these policies.

ZERO TOLERANCE (immediate permanent deactivation): theft, tampering with orders, violence, sexual misconduct, impaired driving, weapons, and identity/background fraud.

PROCESS: Where appropriate you'll receive notice and the reason. Some issues trigger an immediate hold pending investigation.

APPEALS: You may appeal a deactivation by emailing drivers@linkupvibes.com within 14 days with any supporting evidence. Decisions on zero-tolerance and legal matters may be final. Earnings tied to fraud or theft are not payable.`
    },
    {
        key: 'privacy',
        title: 'Driver Privacy Notice',
        body: `LinkUp Driver — Privacy Notice

WHAT WE COLLECT: Identity & KYC documents and biometric face-match data; licence, insurance, registration and background-check results; contact details; bank/payout details; precise location and trip GPS while the app is in use; device data; delivery photos and ratings; and earnings/transaction history.

HOW WE USE IT: To verify and activate you, match you to deliveries, route and track trips, process payouts, prevent fraud and theft, ensure safety, meet KYC/AML and legal obligations, and improve the service.

SHARING: With customers and merchants as needed to complete a delivery (limited details); with verification, background-check and banking/KYC partners; with insurers/authorities for incidents or legal requirements. We do not sell your personal data.

LOCATION: We collect precise location while you are online/on a delivery to enable dispatch, navigation and customer live-tracking. You can stop sharing by going offline (which prevents receiving deliveries).

RETENTION & RIGHTS: KYC, financial and trip records are retained for legally required periods. Subject to law you may access, correct or request deletion of your data: privacy@linkupvibes.com.`
    }
]);

const savePolicies = () => {
    const data: Record<string, any> = {};
    policies.value.forEach(p => {
        data[p.key] = { title: p.title, body: p.body };
    });

    localStorage.setItem('lk_driver_legal', JSON.stringify(data));

    showToast.value = true;
    setTimeout(() => { showToast.value = false; }, 3000);
};

onMounted(() => {
    const saved = localStorage.getItem('lk_driver_legal');
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            policies.value.forEach(p => {
                if (parsed[p.key]) {
                    p.body = parsed[p.key].body;
                }
            });
        } catch (e) {
            console.error('Error parsing saved policies', e);
        }
    }
});
</script>

<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                    <ShieldCheck class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900">Driver Policies</h2>
                </div>
            </div>
            <p class="text-sm text-slate-500 font-medium max-w-3xl leading-relaxed">
                The legal agreements and rules drivers must accept — identity/KYC, code of conduct, the anti-theft & item-custody policy, payments, insurance, deactivation and privacy. Edit here; changes sync into the <b>LinkUp Driver app</b>.
            </p>

            <button @click="savePolicies" class="rounded-xl bg-[#0f172a] text-white px-5 py-2.5 font-bold text-sm hover:bg-slate-800 transition active:scale-95">
                Save all driver policies
            </button>
        </div>

        <!-- Policy List -->
        <div class="space-y-4">
            <div v-for="p in policies" :key="p.key" class="card rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h4 class="text-lg font-black text-slate-900 mb-3">{{ p.title }}</h4>
                <textarea
                    v-model="p.body"
                    class="w-full min-h-[160px] p-4 rounded-xl bg-slate-50 border border-slate-100 focus:bg-white focus:border-slate-300 font-mono text-[13px] leading-relaxed outline-none transition-all scrollbar"
                ></textarea>
            </div>
        </div>

        <!-- Bottom Action -->
        <div class="mt-6">
            <button @click="savePolicies" class="rounded-xl bg-[#0f172a] text-white px-5 py-2.5 font-bold text-sm hover:bg-slate-800 transition active:scale-95">
                Save all driver policies
            </button>
        </div>

        <!-- Success Toast -->
        <Transition
            enter-active-class="transform transition duration-300 ease-out"
            enter-from-class="translate-y-10 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showToast" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100]">
                <div class="bg-slate-900 text-white px-6 py-3 rounded-full font-bold flex items-center gap-3 shadow-2xl">
                    <CheckCircle2 class="w-5 h-5 text-emerald-400" />
                    <span>Driver policies saved — sync complete</span>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.scrollbar::-webkit-scrollbar {
    width: 6px;
}
.scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 99px;
}
.scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

.card {
    transition: all 0.2s ease-in-out;
}
</style>
