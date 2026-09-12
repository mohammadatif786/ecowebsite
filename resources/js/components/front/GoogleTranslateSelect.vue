<script setup lang="ts">
import { onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import languages, { includedGoogleLanguages } from "@/common/languages";

declare global {
  interface Window {
    googleTranslateElementInit?: () => void;
    google?: any;
    __gt_ensure?: () => void; // expose ensure fn for other components (optional)
    __gt_script_injected?: boolean; // sentinel for script tag only
  }
}

const WIDGET_CONTAINER_ID = "google_translate_element";

function waitForTranslateElement(maxTries = 60, delayMs = 100): Promise<boolean> {
  return new Promise((resolve) => {
    let tries = 0;
    const tick = () => {
      const ready = !!window.google?.translate?.TranslateElement;
      if (ready) return resolve(true);
      tries++;
      if (tries >= maxTries) return resolve(false);
      setTimeout(tick, delayMs);
    };
    tick();
  });
}

function getStoredCode(): string | null {
  const key = localStorage.getItem("I18N_LANGUAGE") as keyof typeof languages | null;
  const meta = key ? languages[key] : null;
  return meta?.google ?? "en";
}

function setCookie(name: string, value: string, days = 365) {
  const expires = new Date(Date.now() + days * 864e5).toUTCString();
  document.cookie = `${name}=${value}; expires=${expires}; path=/`;
  const parts = location.hostname.split(".");
  if (parts.length > 2) {
    const base = "." + parts.slice(-2).join(".");
    document.cookie = `${name}=${value}; expires=${expires}; path=/; domain=${base}`;
  }
}
function applyGoogTransCookie(code: string) {
  const lang = localStorage.getItem("I18N_LANGUAGE");
  console.log("Applying googtrans cookie for", code, "lang", lang);
  setCookie("googtrans", `/en/${lang}`);
}

async function ensureTranslateWidget() {
  const ok = await waitForTranslateElement();
  if (!ok) return;

  const container = document.getElementById(WIDGET_CONTAINER_ID);
  if (!container) return;

  // If the hidden select isn't present, (re)create the widget in our container.
  const hasCombo =
    container.querySelector(".goog-te-combo") || document.querySelector(".goog-te-combo");
  if (!hasCombo) {
    new window.google.translate.TranslateElement(
      {
        pageLanguage: "en",
        autoDisplay: false,
        includedLanguages: includedGoogleLanguages,
      },
      WIDGET_CONTAINER_ID
    );
  }

  // Re-apply the last chosen language (so navigation keeps the page translated)
  try {
    const code = getStoredCode();
    if (code) {
      applyGoogTransCookie(code);
      const combo = document.querySelector(".goog-te-combo") as HTMLSelectElement | null;
      if (combo) {
        if (combo.value !== code) combo.value = code;
        combo.dispatchEvent(new Event("change"));
        setTimeout(() => {
          try {
            if (combo.value !== code) {
              combo.value = code;
              combo.dispatchEvent(new Event("change"));
            }
          } catch (e) {
            // Silently ignore Google Translate errors during navigation
          }
        }, 250);
      }
    }
  } catch (error) {
    // Silently ignore Google Translate errors
  }
}

onMounted(() => {
  // 1) set callback BEFORE adding the script
  window.googleTranslateElementInit = () => {
    ensureTranslateWidget();
  };

  // 2) inject script once per app life
  if (!window.__gt_script_injected) {
    const s = document.createElement("script");
    s.id = "google-translate-script";
    s.src =
      "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
    s.async = true;
    s.defer = true;
    s.setAttribute("data-google-translate", "1");
    s.addEventListener("load", ensureTranslateWidget, { once: true });
    document.head.appendChild(s);
    window.__gt_script_injected = true;
  } else {
    // script already there; just (re)ensure widget
    ensureTranslateWidget();
  }

  // 3) ensure again after each Inertia navigation (container may remount)
  router.on("navigate", () => {
    setTimeout(() => {
      try {
        ensureTranslateWidget();
      } catch (error) {
        // Silently ignore Google Translate errors during navigation
      }
    }, 50);
  });

  // (optional) expose for other components
  window.__gt_ensure = ensureTranslateWidget;
});
</script>

<template>
  <!-- Keep this in the DOM; we hide it off-screen -->
  <div
    id="google_translate_element"
    style="position: absolute; left: -9999px; top: -9999px"
  ></div>
</template>

<style>
.goog-te-banner-frame.skiptranslate {
  display: none !important;
}
body {
  top: 0 !important;
}
.goog-te-gadget {
  font-size: 0 !important;
}
.goog-te-gadget .goog-te-combo {
  font-size: 14px;
}
</style>
