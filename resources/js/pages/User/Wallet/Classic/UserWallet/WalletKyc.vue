
<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

// Props (if any)
const props = defineProps({
  kycUrl: {
    type: String,
    default: 'https://YOUR-KYC-LINK-HERE.com'
  },
  rulesUrl: {
    type: String,
    default: 'https://YOUR-KYC-RULES-PAGE.com'
  },
  status: {
    type: String,
    default: 'not_submitted'
  }
});

// State
const localStatus = ref(props.status);
const isModalVisible = ref(true);
const isRulesVisible = ref(false);
const isAcknowledged = ref(false);
const isKycFormVisible = ref(false);

watch(() => props.status, (newStatus) => {
  localStatus.value = newStatus;
});

// Form handling
const form = useForm({
  full_name: '',
  dob: '',
  id_type: '',
  id_number: '',
  address: '',
  tax_id: '',
  terms_acknowledged: false,
  kyc_documents: [],
});

const previews = ref([]);

const handleFileChange = (e) => {
  const files = Array.from(e.target.files);
  form.kyc_documents = files;
  
  previews.value = [];
  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = (e) => {
      previews.value.push(e.target.result);
    };
    reader.readAsDataURL(file);
  });
};

const removeImage = (index) => {
  const newFiles = [...form.kyc_documents];
  newFiles.splice(index, 1);
  form.kyc_documents = newFiles;
  previews.value.splice(index, 1);
};

// Methods
const closeModal = () => {
  isModalVisible.value = false;
  isRulesVisible.value = false;
  isKycFormVisible.value = false;
};

const toggleRules = () => {
  isRulesVisible.value = !isRulesVisible.value;
};

const hideRules = () => {
  isRulesVisible.value = false;
};

const showKycForm = () => {
  if (canStartKyc.value) {
    isKycFormVisible.value = true;
    form.terms_acknowledged = isAcknowledged.value;
  }
};

const submitKyc = () => {
  form.post(route('frontend.user.wallet.kyc.store'), {
    forceFormData: true,
    onSuccess: () => {
      localStatus.value = 'pending';
      isKycFormVisible.value = false;
    },
  });
};

// Computed
const canStartKyc = computed(() => isAcknowledged.value);
</script>

<template>
  <div class="w-full max-w-xl mx-auto">
    <div v-if="isModalVisible" class="glass ring-glow rounded-3xl overflow-hidden transition-all duration-300">
        <div class="k-gradient p-6 md:p-7">

          <!-- ✅ BIGGER HEADING -->
          <div class="mb-4 flex justify-center text-center">
            <div>
              <div class="text-3xl text-white md:text-4xl font-extrabold tracking-tight leading-tight">
                LinkUp Wallet
              </div>
              <div class="mt-1 text-xs md:text-sm text-white font-semibold">
                Powered by Sanddollar
              </div>
            </div>
          </div>

          <div v-if="localStatus === 'pending'" class="mt-8 text-center p-10 chip rounded-3xl">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-sky-400/20 mb-6">
              <svg class="w-10 h-10 text-sky-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-white">Verification Pending</h3>
            <p class="text-white">
              Your KYC documents have been submitted and are currently under review. 
              We'll notify you once your account is verified.
            </p>
          </div>

          <div v-else-if="localStatus === 'rejected'" class="mt-8 text-center p-10 chip rounded-3xl">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-400/20 mb-6">
              <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-white">Verification Rejected</h3>
            <p class="text-white mb-6">
              Unfortunately, your verification was not successful. Please review your details and try again.
            </p>
            <button @click="localStatus = 'not_submitted'" class="btn-primary rounded-2xl px-8 py-3 font-bold">
              Try Again
            </button>
          </div>

          <div v-else>
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="inline-flex items-center gap-2 chip rounded-full px-3 py-1 text-xs text-white">
                  <span class="h-2 w-2 rounded-full" style="background:#dfff00"></span>
                  LinkUp Wallet • Verification
                </div>

                <p class="mt-2 text-sm md:text-base text-white">
                  Thank you for being a part of the LinkUp digital ecosystem. To keep transfers, payouts, and merchant payments safe,
                  we’re required to verify your identity and tax status.
                </p>
              </div>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="chip rounded-2xl p-4">
                <div class="text-sm font-bold text-white">Why we ask</div>
                <div class="mt-1 text-xs text-white">Fraud prevention + secure payouts.</div>
              </div>
              <div class="chip rounded-2xl p-4">
                <div class="text-sm font-bold text-white">Florida compliance</div>
                <div class="mt-1 text-xs text-white">We follow KYC/AML rules to operate.</div>
              </div>
              <div class="chip rounded-2xl p-4">
                <div class="text-sm font-bold text-white">Regional readiness</div>
                <div class="mt-1 text-xs text-white">Aligned for Caribbean & LatAm standards.</div>
              </div>
            </div>

            <div class="mt-5 chip rounded-2xl p-4">
              <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="text-sm font-semibold text-white">What you’ll need (2–3 minutes)</div>
                <div class="flex gap-2 flex-wrap">
                  <span class="chip rounded-full px-3 py-1 text-xs text-white">Government ID</span>
                  <span class="chip rounded-full px-3 py-1 text-xs text-white">Selfie check</span>
                  <span class="chip rounded-full px-3 py-1 text-xs text-white">Tax info</span>
                </div>
              </div>
              <ul class="mt-3 text-sm text-white list-disc pl-5 space-y-1">
                <li><span class="font-semibold text-white">KYC (Know Your Customer):</span> confirms it’s really you.</li>
                <li><span class="font-semibold text-white">AML:</span> helps prevent fraud and illegal transactions.</li>
                <li><span class="font-semibold text-white">Tax:</span> required for payouts and reporting where applicable.</li>
              </ul>
            </div>

            <!-- ✅ ACKNOWLEDGEMENT CHECKBOX (required) -->
            <div class="mt-5 chip rounded-2xl p-4">
              <label class="flex items-start gap-3 cursor-pointer select-none">
                <input v-model="isAcknowledged" type="checkbox" class="mt-1 h-5 w-5 accent-sky-400">
                <span class="text-sm text-white">
                  I acknowledge that LinkUp Wallet will collect and verify my information for compliance and fraud prevention,
                  and that I may be asked for tax details for payout eligibility where applicable.
                </span>
              </label>
            </div>

            <div v-if="!isKycFormVisible">
              <div class="mt-6 flex flex-col md:flex-row gap-3">
                <!-- Start KYC button (disabled until checkbox checked) -->
                <button @click="showKycForm"
                   class="btn-primary rounded-2xl px-5 py-3 font-extrabold text-center transition-all duration-200"
                   :class="{ 'disabled-primary': !canStartKyc }"
                   :aria-disabled="!canStartKyc">
                  Start KYC Verification →
                </button>

                <!-- Explain KYC rules button (toggles panel) -->
                <button @click="toggleRules" class="btn-ghost rounded-2xl px-5 py-3 font-bold">
                  Explain KYC Rules
                </button>

              </div>
            </div>

            <!-- ✅ KYC DATA COLLECTION FORM -->
            <div v-else class="mt-6 chip rounded-2xl p-6 transition-all duration-300">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white">Verify Your Identity</h3>
                <button @click="isKycFormVisible = false" class="text-white/60 hover:text-white">Back</button>
              </div>

              <form @submit.prevent="submitKyc" class="space-y-4" novalidate>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-white mb-1 uppercase tracking-wider">Full Name</label>
                    <input v-model="form.full_name" type="text" required
                           class="text-white w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400 text-sm"
                           placeholder="As shown on ID">
                    <div v-if="form.errors.full_name" class="text-red-400 text-xs mt-1">{{ form.errors.full_name }}</div>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-white mb-1 uppercase tracking-wider">Date of Birth</label>
                    <input v-model="form.dob" type="date" required
                           class="text-white w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400 text-sm">
                    <div v-if="form.errors.dob" class="text-red-400 text-xs mt-1">{{ form.errors.dob }}</div>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-white mb-1 uppercase tracking-wider">ID Type</label>
                    <select v-model="form.id_type" required
                            class="text-white w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400 text-sm appearance-none">
                      <option value="" disabled class="bg-slate-900">Select ID type</option>
                      <option value="passport" class="bg-slate-900">Passport</option>
                      <option value="drivers_license" class="bg-slate-900">Driver's License</option>
                      <option value="national_id" class="bg-slate-900">National ID Card</option>
                    </select>
                    <div v-if="form.errors.id_type" class="text-red-400 text-xs mt-1">{{ form.errors.id_type }}</div>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-white mb-1 uppercase tracking-wider">ID Number</label>
                    <input v-model="form.id_number" type="text" required
                           class="text-white w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400 text-sm"
                           placeholder="Enter ID number">
                    <div v-if="form.errors.id_number" class="text-red-400 text-xs mt-1">{{ form.errors.id_number }}</div>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-white mb-1 uppercase tracking-wider">Residential Address</label>
                  <textarea v-model="form.address" required rows="2"
                            class="text-white w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400 text-sm"
                           placeholder="Full street address, city, state/province"></textarea>
                  <div v-if="form.errors.address" class="text-red-400 text-xs mt-1">{{ form.errors.address }}</div>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-white mb-1 uppercase tracking-wider">Tax ID / SSN (Optional)</label>
                  <input v-model="form.tax_id" type="text"
                         class="text-white w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400 text-sm"
                         placeholder="For tax reporting compliance">
                  <div v-if="form.errors.tax_id" class="text-red-400 text-xs mt-1">{{ form.errors.tax_id }}</div>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-white mb-1 uppercase tracking-wider">Upload Documents (ID Front/Back, Selfie)</label>
                  <div class="mt-1 flex flex-col gap-4">
                    <div class="flex items-center justify-center w-full">
                      <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-white/20 rounded-2xl cursor-pointer hover:bg-white/5 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                          <svg class="w-8 h-8 mb-3 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                          </svg>
                          <p class="text-sm text-white/70">Click to upload multiple images</p>
                          <p class="text-xs text-white/50 mt-1">PNG, JPG, JPEG (Max 5MB each)</p>
                        </div>
                        <input type="file" multiple class="hidden" @change="handleFileChange" accept="image/*" />
                      </label>
                    </div>

                    <!-- Preview Grid -->
                    <div v-if="previews.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                      <div v-for="(preview, index) in previews" :key="index" class="relative aspect-square rounded-xl overflow-hidden group border border-white/10">
                        <img :src="preview" class="w-full h-full object-cover" />
                        <button @click.prevent="removeImage(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div v-if="form.errors.kyc_documents" class="text-red-400 text-xs mt-1">{{ form.errors.kyc_documents }}</div>
                </div>

                <div class="pt-2">
                  <button type="submit" :disabled="form.processing"
                          class="text-white w-full btn-primary rounded-2xl px-5 py-3 font-extrabold text-center transition-all duration-200 flex items-center justify-center gap-2">
                    <span v-if="form.processing" class="animate-spin h-4 w-4 border-2 border-slate-900 border-t-transparent rounded-full"></span>
                    {{ form.processing ? 'Submitting...' : 'Submit Verification Data' }}
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- ✅ RULES PANEL -->
          <div v-if="isRulesVisible" class="mt-4 chip rounded-2xl p-4 transition-all duration-300">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-extrabold text-white">KYC + Tax Rules (Simple Version)</div>
                <p class="mt-1 text-sm text-white/80">
                  LinkUp Wallet must confirm identity and tax status to comply with financial regulations, reduce fraud,
                  and enable lawful payouts across supported regions.
                </p>
              </div>
              <button @click="hideRules" class="btn-ghost rounded-xl px-3 py-2 text-sm font-semibold">✕</button>
            </div>

            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-white/80">
              <div class="chip rounded-2xl p-3">
                <div class="font-bold text-white">What we verify</div>
                <ul class="mt-2 list-disc pl-5 space-y-1">
                  <li>Name + date of birth</li>
                  <li>ID authenticity</li>
                  <li>Face match (selfie)</li>
                  <li>Basic address details</li>
                </ul>
              </div>
              <div class="chip rounded-2xl p-3">
                <div class="font-bold text-white">Why tax info</div>
                <ul class="mt-2 list-disc pl-5 space-y-1">
                  <li>Payout eligibility</li>
                  <li>Reporting when required</li>
                  <li>Helps avoid holds or delays</li>
                </ul>
              </div>
            </div>

            <div class="mt-3 chip rounded-2xl p-3 text-sm text-white/80">
              <div class="font-bold text-white">Your privacy</div>
              <p class="mt-1">
                Your verification data is used only for compliance, fraud prevention, and enabling wallet services.
                (Your KYC provider will display their policy during onboarding.)
              </p>
            </div>
          </div>

        </div>
      </div>
    </div>
</template>

<style scoped>
.page-wrapper {
  background: radial-gradient(1200px 600px at 20% 0%, #0b2b45 0%, #0b1220 45%, #070b14 100%);
  color: white;
}

.glass {
  background: linear-gradient(180deg, rgba(255, 255, 255, .10), rgba(255, 255, 255, .06));
  border: 1px solid rgba(255, 255, 255, .14);
  box-shadow: 0 18px 50px rgba(0, 0, 0, .55);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.ring-glow {
  box-shadow:
    0 0 0 1px rgba(255, 255, 255, .12) inset,
    0 0 0 3px rgba(14, 165, 233, .10),
    0 0 60px rgba(14, 165, 233, .18);
}

.btn-primary {
  background: linear-gradient(135deg, #0ea5e9, #38bdf8);
  color: #07111f;
}

.btn-primary:hover:not(.disabled-primary) {
  filter: brightness(1.06)
}

.btn-ghost {
  background: rgba(255, 255, 255, .08);
  border: 1px solid rgba(255, 255, 255, .14);
  color: white;
}

.btn-ghost:hover {
  background: rgba(255, 255, 255, .12)
}

.chip {
  border: 1px solid rgba(255, 255, 255, .14);
  background: rgba(255, 255, 255, .06);
}

.k-gradient {
  background: radial-gradient(600px 200px at 20% 0%, rgba(223, 255, 0, .20) 0%, transparent 60%),
    radial-gradient(700px 260px at 80% 20%, rgba(14, 165, 233, .25) 0%, transparent 55%);
}

.disabled-primary {
  opacity: .45;
  filter: saturate(.85);
  cursor: not-allowed;
  pointer-events: none;
}
</style>