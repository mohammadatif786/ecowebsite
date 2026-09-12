<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  Select, SelectTrigger, SelectValue, SelectContent,
  SelectGroup, SelectLabel, SelectItem, SelectSeparator,
} from "@/components/front/ui/select";
import languages, { type LangKey } from '@/common/languages';

const STORE_KEY = 'I18N_LANGUAGE';
const selectedLang = ref<LangKey>((localStorage.getItem(STORE_KEY) as LangKey) || 'en');
const ready = ref(false);

let pollTimer: number | null = null;
let tries = 0;
let mo: MutationObserver | null = null;

function getGoogleCombo(): HTMLSelectElement | null {
  return document.querySelector('.goog-te-combo') as HTMLSelectElement | null;
}

/** cookie helpers */
function getBaseDomain(): string | null {
  const h = location.hostname;
  if (h === 'localhost' || /^\d{1,3}(\.\d{1,3}){3}$/.test(h)) return null;
  const parts = h.split('.');
  if (parts.length < 2) return null;
  return '.' + parts.slice(-2).join('.');
}
function setCookie(name: string, value: string, days = 365) {
  const expires = new Date(Date.now() + days * 864e5).toUTCString();
  // current host
  document.cookie = `${name}=${value}; expires=${expires}; path=/`;
  // base domain (covers subdomains)
  const base = getBaseDomain();
  if (base) document.cookie = `${name}=${value}; expires=${expires}; path=/; domain=${base}`;
}
function clearCookie(name: string) {
  const past = 'Thu, 01 Jan 1970 00:00:00 GMT';
  document.cookie = `${name}=; expires=${past}; path=/`;
  const base = getBaseDomain();
  if (base) document.cookie = `${name}=; expires=${past}; path=/; domain=${base}`;
}

function applyGoogTransCookie(code: string) {
  // Google checks both variants; set both
  const lang = localStorage.getItem("I18N_LANGUAGE");
  setCookie('googtrans', `/en/${lang}`);
  setCookie('googtrans', `/auto/${lang}`);
}

/** Drive the hidden <select> and re-assert to beat GT's internal sync */
function forceGoogleChange(code: string) {
  const combo = getGoogleCombo();
  if (!combo) return;

  if (combo.value !== code) combo.value = code;
  combo.dispatchEvent(new Event('change'));

  setTimeout(() => {
    if (combo.value !== code) {
      combo.value = code;
      combo.dispatchEvent(new Event('change'));
    }
  }, 250);

  setTimeout(() => {
    if (combo.value !== code) {
      combo.value = code;
      combo.dispatchEvent(new Event('change'));
    }
  }, 1000);
}

/** Cleanly revert to original English page */
function resetToEnglish() {
  // 1) remove cookies so GT stops forcing a target
  clearCookie('googtrans');

  // 2) remove classes GT sometimes leaves behind
  document.documentElement.classList.remove('translated-ltr', 'translated-rtl');
  document.body.classList.remove('goog-te-banner-frame'); // harmless if not present

  // 3) drive select back to 'en'
  const combo = getGoogleCombo();
  if (combo) {
    combo.value = 'en';
    combo.dispatchEvent(new Event('change'));
  }

  // 4) re-assert a couple more times
  setTimeout(() => {
    const c = getGoogleCombo();
    if (c && c.value !== 'en') {
      c.value = 'en';
      c.dispatchEvent(new Event('change'));
    }
  }, 250);

  setTimeout(() => {
    const c = getGoogleCombo();
    if (c && c.value !== 'en') {
      c.value = 'en';
      c.dispatchEvent(new Event('change'));
    }
  }, 1000);

  // Optional nuclear fallback (usually NOT needed):
  // setTimeout(() => window.location.reload(), 1500);
}

function changeLanguage(lang: LangKey) {
  if (selectedLang.value === lang) return; // no-op if same

  selectedLang.value = lang;
  localStorage.setItem(STORE_KEY, lang);

  if (lang === 'en') {
    // Going back to English: clear cookie & drive combo now
    resetToEnglish();
  } else {
    const code = languages[lang].google;
    if (!languages[lang].supported || !code) return;

    // Set cookie first so the new page comes up in the chosen language
    applyGoogTransCookie(code);

    // Optional: drive combo now for immediate feedback (not required since we reload)
    // driveComboTo(code);
  }

  // 🔒 One-shot reload guard
//   sessionStorage.setItem(RELOAD_ONCE_KEY, '1');

  // Full reload (use reload(true) if you want to skip cache)
  window.location.reload();
}


function reapplyFromStorage() {
  const lang = (localStorage.getItem(STORE_KEY) as LangKey) || 'en';
  selectedLang.value = lang;
  if (lang === 'en') {
    resetToEnglish();
  } else {
    const code = languages[lang].google;
    if (code) {
      applyGoogTransCookie(code);
      forceGoogleChange(code);
    }
  }
}

function markReadyIfComboExists() {
  if (!ready.value && getGoogleCombo()) {
    ready.value = true;
    reapplyFromStorage();
  }
}

onMounted(() => {
  // Watch for the hidden .goog-te-combo to appear
  mo = new MutationObserver(markReadyIfComboExists);
  mo.observe(document.documentElement, { childList: true, subtree: true });

  // Polling fallback
  pollTimer = window.setInterval(() => {
    markReadyIfComboExists();
    tries++;
    if ((ready.value || tries >= 120) && pollTimer) {
      window.clearInterval(pollTimer);
      pollTimer = null;
    }
  }, 500);

  // Re-apply after Inertia navigations
  router.on('navigate', () => {
    setTimeout(() => {
    (window as any).__gt_ensure?.();
      markReadyIfComboExists();
      if (ready.value) reapplyFromStorage();
    }, 200);
  });
});

onBeforeUnmount(() => {
  if (pollTimer) window.clearInterval(pollTimer);
  mo?.disconnect();
});
</script>

<template>
  <div class="flex justify-end">
    <Select
      :modelValue="selectedLang"
      @update:modelValue="(v) => changeLanguage(v as LangKey)"
    >
      <SelectTrigger
        class="w-[220px] rounded-lg bg-white p-2 text-sm font-medium shadow-sm hover:shadow-md"
      >
        <SelectValue placeholder="Select language">
          <div class="flex items-center gap-2">
            <span class="text-lg">{{ languages[selectedLang]?.emoji }}</span>
            <span>{{ languages[selectedLang]?.label || "Language" }}</span>
          </div>
        </SelectValue>
      </SelectTrigger>

      <SelectContent class="max-h-72 overflow-auto">
        <SelectGroup>
          <SelectLabel>Choose language</SelectLabel>
          <SelectSeparator />
          <SelectItem
            v-for="(meta, key) in languages"
            :key="key"
            :value="key"
            :disabled="!meta.supported"
          >
            <div class="flex items-center gap-2">
              <span class="text-lg">{{ meta.emoji }}</span>
              <span>{{ meta.label }}</span>
              <span
                v-if="!meta.supported"
                class="ml-2 rounded bg-amber-100 px-2 py-[2px] text-[10px] font-semibold text-amber-700"
              >
                Not supported
              </span>
            </div>
          </SelectItem>
        </SelectGroup>
      </SelectContent>
    </Select>
  </div>
</template>
