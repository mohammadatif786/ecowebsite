<template>
    <AppLayout>
        <div class="wrap">
            <!-- Optional compact banner for event pages -->
            <div class="inline-banner" role="status" aria-live="polite">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#b77900" stroke-width="2">
                    <path
                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L14.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <div><strong>KYC pending.</strong> You can't create an event yet. <a href="#kyc">Review your
                        documents</a></div>
            </div>

            <!-- Main KYC panel -->
            <section id="kyc" class="panel center" aria-labelledby="kycTitle">
                <div class="icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L14.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                </div>
                <h1 id="kycTitle">KYC Verification Required</h1>
                <p class="lead">Complete the <strong>KYC process</strong> to keep LinkUp safe and secure for everyone.
                </p>

                <div class="status" :class="currentStatus" id="pageStatus" role="status" aria-live="polite">
                    <span v-if="currentStatus === 'pending'" class="dot"
                        style="width:10px;height:10px;border:none;background:var(--warn)"></span>
                    <span v-else-if="currentStatus === 'approved'" class="dot"
                        style="width:10px;height:10px;border:none;background:var(--ok)"></span>
                    <span v-else-if="currentStatus === 'canceled'" class="dot"
                        style="width:10px;height:10px;border:none;background:var(--bad)"></span>
                    Status: {{ statusText }}
                </div>

                <!-- Stepper -->
                <div class="stepper" aria-label="KYC progress">
                    <div class="step" :class="getStepClass(0)">
                        <span class="dot">✓</span> Upload
                    </div>
                    <div class="step" :class="getStepClass(1)">
                        <span class="dot">2</span> Review
                    </div>
                    <div class="step" :class="getStepClass(2)">
                        <span class="dot">3</span> Approval
                    </div>
                </div>

                <!-- Documents -->
                <section class="docs">
                    <h2 class="center">Your KYC Documents</h2>
                    <div class="grid" role="list">
                        <!-- Doc: pending -->
                        <article class="card" role="listitem" aria-label="Passport Front">
                            <header class="card-hd">
                                <span class="title">Passport Front</span>
                                <span class="chip" :class="getDocumentStatus('passport_front')">{{
                                    getDocumentStatusText('passport_front') }}</span>
                            </header>
                            <div class="thumb">
                                <img v-if="props.kyc?.passport_front" :src="`${appURL}${props.kyc?.passport_front}`"
                                    alt="Uploaded passport front" />
                                <img v-else
                                    src="https://images.unsplash.com/photo-1536337005238-94b997371b40?q=80&w=1200&auto=format&fit=crop"
                                    alt="Uploaded passport front" />
                            </div>
                              <!-- Alert for Passport Front -->
                              <div v-if="['canceled', 'cancelled', 'rejected'].includes(getDocumentStatus('passport_front'))"
                                class="alert" role="alert">
                                <strong>Reason:</strong>
                                <div>
                                    Image is cropped; full document edges not visible. Please re-upload a clear front
                                    image of your passport.
                                    <small>Tip: Ensure all corners are visible and text is readable.</small>
                                </div>
                            </div>
                            <footer class="card-ft">
                                <div class="note">{{ getDocumentNote('passport_front') }}</div>
                                <div>
                                    <Link :href="route('organizer.profile.index')" class="btn btn-outline">
                                    Replace
                                    </Link>

                                    <button class="btn btn-blue"
                                        @click="openImageModal(`${appURL}/${props.kyc?.passport_front}`)">
                                        View
                                    </button>

                                </div>
                            </footer>
                        </article>

                        <!-- Doc: received/ok -->
                        <article class="card" role="listitem" aria-label="Passport Back">
                            <header class="card-hd">
                                <span class="title">Passport Back</span>
                                <span class="chip" :class="getDocumentStatus('passport_back')">{{
                                    getDocumentStatusText('passport_back') }}</span>
                            </header>
                            <div class="thumb">
                                <img v-if="props.kyc?.passport_back" :src="`${appURL}${props.kyc?.passport_back}`"
                                    alt="Uploaded passport back" />
                                <img v-else
                                    src="https://images.unsplash.com/photo-1557821552-17105176677c?q=80&w=1200&auto=format&fit=crop"
                                    alt="Uploaded passport back" />
                            </div>
                            <!-- Alert for Passport Back -->
                            <div v-if="['canceled', 'cancelled', 'rejected'].includes(getDocumentStatus('passport_back'))"
                                class="alert" role="alert">
                                <strong>Reason:</strong>
                                <div>
                                    Image is cropped; full document edges not visible. Please re-upload a clear back
                                    image of your passport.
                                    <small>Tip: Ensure all corners are visible and text is readable.</small>
                                </div>
                            </div>

                            <footer class="card-ft">
                                <div class="note">{{ getDocumentNote('passport_back') }}</div>
                                <div>
                                    <Link :href="route('organizer.profile.index')" class="btn btn-outline">
                                    Replace
                                    </Link>

                                    <button class="btn btn-blue"
                                        @click="openImageModal(`${appURL}${props.kyc?.passport_back}`)">
                                        View
                                    </button>
                                </div>
                            </footer>
                        </article>

                        <!-- Doc: rejected / needs attention -->
                        <article class="card" role="listitem" aria-label="Proof of Address">
                            <header class="card-hd">
                                <span class="title">Proof of Address</span>
                                <span class="chip" :class="getDocumentStatus('proof_of_address')">{{
                                    getDocumentStatusText('proof_of_address') }}</span>
                            </header>
                            <div class="thumb">
                                <img v-if="props.kyc?.proof_of_address" :src="`${appURL}${props.kyc?.proof_of_address}`"
                                    alt="Uploaded proof of address" />
                                <img v-else
                                    src="https://images.unsplash.com/photo-1584433144859-1fc3ab64a957?q=80&w=1200&auto=format&fit=crop"
                                    alt="Uploaded proof of address" />
                            </div>

                            <!-- Inline reason + quick fix CTA -->
                            <div v-if="['canceled', 'cancelled', 'rejected'].includes(getDocumentStatus('proof_of_address'))"
                                class="alert" role="alert">
                                <strong>Reason:</strong>
                                <div>
                                    Image is cropped; full document edges not visible. The date is older than our
                                    allowed window.
                                    <small>Tip: Upload a clear photo or PDF dated within the last 90 days showing your
                                        full name and address.</small>
                                </div>
                            </div>

                            <footer class="card-ft">
                                <div class="note">{{ getDocumentNote('proof_of_address') }}</div>
                                <div>
                                    <!-- <button class="btn btn-ghost">See examples</button> -->
                                    <Link :href="route('organizer.profile.index')" class="btn btn-outline">
                                    Re-Upload
                                    </Link>
                                    <button class="btn btn-blue"
                                        @click="openImageModal(`${appURL}${props.kyc?.proof_of_address}`)">
                                        View
                                    </button>
                                </div>
                            </footer>
                        </article>
                    </div>
                </section>

                <!-- Info + Help -->
                <div class="info" aria-label="What happens next and help">
                    <div class="callout">
                        <h3>What happens next?</h3>
                        <ul class="list">
                            <li>Our team checks your documents for clarity & validity.</li>
                            <li>We'll notify you by email and in-app when you're approved.</li>
                            <li>If something is missing, we'll ask you to re-upload just that item.</li>
                        </ul>
                    </div>
                    <div class="callout">
                        <h3>Why do we verify?</h3>
                        <ul class="list">
                            <li><button class="btn btn-ghost" @click="openWhyKycModal">Why we verify (Florida &
                                    Federal)</button></li>
                            <li><button class="btn btn-ghost">Contact support</button></li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>

        <!-- WHY KYC (Florida & Federal) — Modal -->
        <div class="backdrop" :class="{ show: showWhyKycModal }" id="whyKycModal" aria-hidden="true">
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="whyKycTitle">
                <div class="modal-hd">
                    <h3 id="whyKycTitle">Why identity verification (KYC) is required</h3>
                    <button class="x" @click="closeWhyKycModal" aria-label="Close">×</button>
                </div>

                <div class="modal-bd">
                    <p class="lead tiny">
                        Platforms that facilitate payments or money transmission must follow <strong>Florida anti–money
                            laundering (AML) laws</strong>
                        and <strong>U.S. federal Bank Secrecy Act (BSA)</strong> rules. These require risk-based
                        programs to verify customers,
                        keep accurate transaction records, and report suspicious activity—helping prevent fraud and
                        financial crime.
                    </p>

                    <div class="grid-3">
                        <section class="info-card" aria-label="Florida AML obligations">
                            <h4>Florida AML obligations <span class="tag">Fla. Stat. ch. 560</span></h4>
                            <ul>
                                <li>Money services providers must keep records and implement AML programs.</li>
                                <li>Florida law incorporates key federal BSA requirements.</li>
                            </ul>
                            <p class="tiny">
                                References: §§560.123 (recordkeeping/AML purpose), 560.1235 (AML program referencing
                                federal rules).<br />
                                <a target="_blank" rel="noopener"
                                    href="https://www.leg.state.fl.us/Statutes/index.cfm?App_mode=Display_Statute&URL=0500-0599/0560/Sections/0560.123.html">§560.123</a>
                                ·
                                <a target="_blank" rel="noopener"
                                    href="https://www.leg.state.fl.us/Statutes/index.cfm?App_mode=Display_Statute&URL=0500-0599/0560/Sections/0560.1235.html">§560.1235</a>
                            </p>
                        </section>

                        <section class="info-card" aria-label="Federal BSA/FinCEN">
                            <h4>Federal BSA / FinCEN <span class="tag">31 C.F.R. §1022.210</span></h4>
                            <ul>
                                <li>MSBs must maintain a written, risk-based AML program.</li>
                                <li>Controls, training, independent review, compliance officer — typically include KYC.
                                </li>
                            </ul>
                            <p class="tiny">
                                <a target="_blank" rel="noopener"
                                    href="https://www.fincen.gov/bsa-requirements-msbs">FinCEN MSB Overview</a> ·
                                <a target="_blank" rel="noopener"
                                    href="https://www.ecfr.gov/current/title-31/subtitle-B/chapter-X/part-1022/subpart-B/section-1022.210">§1022.210</a>
                            </p>
                        </section>

                        <section class="info-card" aria-label="Funds transfer recordkeeping">
                            <h4>Funds transfer recordkeeping <span class="tag">31 C.F.R. §1010.410</span></h4>
                            <ul>
                                <li>Obtain & retain sender/recipient info for certain transfers.</li>
                                <li>If placed in person, verify identity before accepting the order.</li>
                            </ul>
                            <p class="tiny">
                                <a target="_blank" rel="noopener"
                                    href="https://www.law.cornell.edu/cfr/text/31/1010.410">§1010.410</a>
                            </p>
                        </section>
                    </div>

                    <div class="callout-why">
                        <strong>What this means for you</strong><br />
                        We verify organizer identity (KYC) to comply with Florida and federal AML/BSA obligations,
                        reduce fraud and chargebacks,
                        and keep events and payments safe.
                    </div>

                    <p class="tiny">This explainer is for product guidance only and isn't legal advice. Regulations can
                        change.</p>
                </div>

                <div class="modal-ft">
                    <button class="btn btn-ghost" @click="closeWhyKycModal">Close</button>
                    <button class="btn btn-blue" @click="closeWhyKycModal">Continue</button>
                </div>
            </div>
        </div>
        <!-- IMAGE PREVIEW MODAL -->
        <div class="backdrop" :class="{ show: showImageModal }" aria-hidden="true" @click.self="closeImageModal">
            <div class="modal image-modal">
                <div class="modal-hd">
                    <h3>Document Preview</h3>
                    <button class="x" @click="closeImageModal" aria-label="Close">×</button>
                </div>
                <div class="modal-bd center">
                    <img v-if="previewImage" :src="previewImage" alt="Document preview"
                        style="max-width:100%;border-radius:12px;">
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    kyc: {
        passport_front?: string;
        passport_back?: string;
        proof_of_address?: string;
        status: 'pending' | 'approved' | 'canceled';
        p_front_status: 'pending' | 'approved' | 'canceled';
        p_back_status: 'pending' | 'approved' | 'canceled';
        p_o_add_status: 'pending' | 'approved' | 'canceled';
    };
    appURL: string;
}>();

// 🔹 Reactive state
const currentStatus = ref<'pending' | 'approved' | 'canceled'>(props.kyc?.status);
const showWhyKycModal = ref(false);

// 🔹 Computed text for main status
const statusText = computed(() => {
    switch (currentStatus.value) {
        case 'approved':
            return 'Approved — you can now create events.';
        case 'canceled':
            return 'Rejected — please fix the highlighted items.';
        default:
            return 'Pending — your documents are being reviewed.';
    }
});

// 🔹 Get document-specific status from DB fields
const getDocumentStatus = (docType: string) => {
    switch (docType) {
        case 'passport_front':
            return props.kyc?.p_front_status;
        case 'passport_back':
            return props.kyc?.p_back_status;
        case 'proof_of_address':
            return props.kyc?.p_o_add_status;
        default:
            return 'pending';
    }
};

// 🔹 Get human-readable text
const getDocumentStatusText = (docType: string) => {
    const status = getDocumentStatus(docType);
    switch (status) {
        case 'approved':
            return 'Approved';
        case 'canceled':
            return 'Rejected';
        default:
            return 'Pending';
    }
};

// 🔹 Notes for each document
const getDocumentNote = (docType: string) => {
    const status = getDocumentStatus(docType);
    switch (status) {
        case 'approved':
            return 'Looks good — no action needed.';
        case 'canceled':
            return 'Rejected — please re-upload a valid document.';
        default:
            return 'Submitted — under review.';
    }
};

// 🔹 Stepper progress
const getStepClass = (stepIndex: number) => {
    if (currentStatus.value === 'approved') return 'step done';
    if (currentStatus.value === 'canceled') {
        return stepIndex === 2 ? 'step active' : 'step done';
    }
    return stepIndex === 1 ? 'step active' : 'step';
};

// 🔹 State change preview buttons (optional)
const setState = (state: 'pending' | 'approved' | 'canceled') => {
    currentStatus.value = state;
    props.kyc.status = state;
};

// 🔹 Modal functions
const openWhyKycModal = () => {
    showWhyKycModal.value = true;
    document.body.style.overflow = 'hidden';
};

const closeWhyKycModal = () => {
    showWhyKycModal.value = false;
    document.body.style.overflow = '';
};

// 🔹 Escape key to close modal
const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && showWhyKycModal.value) {
        closeWhyKycModal();
    }
};

// 🔹 Lifecycle hooks
onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
    currentStatus.value = props.kyc?.status;
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});

const showImageModal = ref(false);
const previewImage = ref<string | null>(null);

const openImageModal = (imgPath: string) => {
    previewImage.value = imgPath;
    showImageModal.value = true;
    document.body.style.overflow = 'hidden';
};

const closeImageModal = () => {
    showImageModal.value = false;
    previewImage.value = null;
    document.body.style.overflow = '';
};

</script>


<style scoped>
* {
    box-sizing: border-box;
}

.image-modal img {
    max-height: 75vh;
    width: auto;
    object-fit: contain;
}

.image-modal .modal-bd {
    display: flex;
    justify-content: center;
    align-items: center;
    background: #fafcff;
}

.wrap {
    max-width: 980px;
    margin: 28px auto;
    padding: 0 16px;
}

.panel {
    background: #fff;
    border: 1px solid #e6eef6;
    border-radius: 24px;
    box-shadow: 0 18px 40px rgba(11, 34, 57, .08);
    padding: 28px;
}

.center {
    text-align: center;
}

h1 {
    margin: 6px 0 4px 0;
    font-size: 32px;
}

p.lead {
    margin: 0;
    color: #6b7c92;
    font-size: 18px;
}

.icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    margin: 4px auto 8px auto;
    background: #fff7e0;
    border: 1px solid #ffe6a7;
}

.icon svg {
    color: #b77900;
}

.status {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    margin: 18px 0 8px 0;
    padding: 8px 14px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 14px;
}

.pending {
    background: #fff4cc;
    color: #7a5a00;
    border: 1px solid #ffe29c;
}

.approved {
    background: #e7fff6;
    color: #065f46;
    border: 1px solid #bff3de;
}

.canceled {
    background: #ffe8e8;
    color: #8b1d1d;
    border: 1px solid #ffc8c8;
}

.stepper {
    display: flex;
    justify-content: center;
    gap: 18px;
    margin: 4px 0 18px 0;
    flex-wrap: wrap;
}

.step {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7c92;
    font-weight: 700;
}

.dot {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 2px solid #e6eef6;
    display: grid;
    place-items: center;
    background: #fff;
}

.step.active .dot {
    border-color: #C8D60A;
    background: #C8D60A;
    color: #0b2239;
}

.step.done .dot {
    border-color: #10b981;
    background: #10b981;
    color: #fff;
}

.step.active,
.step.done {
    color: #213649;
}

.docs {
    margin-top: 8px;
}

.docs h2 {
    font-size: 20px;
    margin: 8px 0 12px 0;
}

.grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.card {
    background: #fff;
    border: 1px solid #e6eef6;
    border-radius: 16px;
    box-shadow: 0 18px 40px rgba(11, 34, 57, .08);
    overflow: hidden;
}

.card-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-bottom: 1px solid #e6eef6;
}

.card-hd .title {
    font-weight: 800;
}

.chip {
    font-size: 12px;
    font-weight: 800;
    border-radius: 999px;
    padding: 6px 10px;
}

.chip.pending {
    background: #fff4cc;
    color: #7a5a00;
    border: 1px solid #ffe29c;
}

.chip.approved {
    background: #e7fff6;
    color: #065f46;
    border: 1px solid #bff3de;
}

.chip.canceled {
    background: #ffe8e8;
    color: #8b1d1d;
    border: 1px solid #ffc8c8;
}

.thumb {
    aspect-ratio: 16/10;
    background: #f1f6fb;
    display: block;
}

.thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.card-ft {
    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
}

.btn {
    border: none;
    border-radius: 10px;
    padding: 9px 12px;
    cursor: pointer;
    font-weight: 700;
}

.btn-blue {
    background: #12A8E8;
    color: #fff;
}

.btn-blue:hover {
    background: #0D91CD;
}

.btn-ghost {
    background: #fff;
    border: 1px solid #e6eef6;
    color: #1f2f44;
}

.note {
    font-size: 12px;
    color: #6b7c92;
}

.alert {
    border: 1px solid #ffd0d0;
    background: #fff1f1;
    color: #811d1d;
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin: 10px 14px;
}

.alert small {
    display: block;
    color: #a33;
}

.info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 18px;
}

.callout {
    border: 1px dashed #e6eef6;
    border-radius: 16px;
    padding: 14px;
    background: #fbfdff;
}

.callout h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
}

.list {
    margin: 0;
    padding-left: 18px;
    color: #35485f;
}

.foot {
    margin-top: 16px;
    border-top: 1px solid #e6eef6;
    padding-top: 14px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}

.inline-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff7e0;
    border: 1px solid #ffe6a7;
    border-radius: 12px;
    padding: 10px 12px;
    margin-bottom: 16px;
}

.inline-banner strong {
    margin-right: 6px;
}

.inline-banner a {
    font-weight: 800;
    color: #7a5a00;
}

.backdrop {
    position: fixed;
    inset: 0;
    background: rgba(9, 20, 33, .45);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    z-index: 9999;
}

.backdrop.show {
    display: flex;
}

.modal {
    width: min(900px, 100%);
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e6eef6;
    box-shadow: 0 30px 70px rgba(10, 26, 46, .35);
    overflow: hidden;
}

.modal-hd {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 18px;
    border-bottom: 1px solid #e6eef6;
    background: #f7fafc;
}

.modal-hd h3 {
    margin: 0;
    font-size: 20px;
}

.modal-bd {
    padding: 16px 18px;
    max-height: 70vh;
    overflow: auto;
}

.modal-ft {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 12px 18px;
    border-top: 1px solid #e6eef6;
}

.x {
    background: transparent;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #79889a;
}

.grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin: 12px 0;
}

.info-card {
    background: #fff;
    border: 1px solid #e6eef6;
    border-radius: 16px;
    box-shadow: 0 18px 40px rgba(11, 34, 57, .08);
    padding: 14px;
}

.info-card h4 {
    margin: 0 0 6px;
    font-size: 16px;
}

.tag {
    display: inline-block;
    background: #eef7ff;
    color: #1e4b72;
    border: 1px solid #d4e9ff;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 12px;
    margin-left: 8px;
}

.callout-why {
    border: 1px dashed #e6eef6;
    border-radius: 16px;
    padding: 14px;
    background: #fbfdff;
    margin-top: 8px;
}

.tiny {
    font-size: 12px;
    color: #6b7c92;
}

.lead.tiny {
    font-size: 14px;
    color: #2b3b50;
}

a {
    color: #0D91CD;
    font-weight: 700;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

@media (max-width: 860px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .info {
        grid-template-columns: 1fr;
    }

    .grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>
