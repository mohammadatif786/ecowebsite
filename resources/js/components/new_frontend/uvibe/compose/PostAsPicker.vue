<template>
  <!-- "Post as": yourself, or an organization (your university, an organizer profile, or one you add here). -->
  <div class="space-y-2">
    <p class="text-[11px] font-black text-slate-400 uppercase">Post as</p>
    <div class="grid grid-cols-2 gap-2">
      <button type="button" @click="setMode('me')" :class="modeClass('me')">🙋 Myself</button>
      <button type="button" @click="setMode('org')" :class="modeClass('org')">🏛️ An organization</button>
    </div>

    <div v-if="modelValue.mode === 'org'" class="rounded-2xl bg-slate-50 p-3">
      <div v-if="choices.length" class="flex gap-2 overflow-x-auto hide-scroll pb-1.5">
        <button v-for="c in choices" :key="c.key" type="button" @click="pick(c.key)"
                :class="['shrink-0 rounded-full px-3 py-[7px] text-xs font-black whitespace-nowrap border-2 transition',
                         selectedKey === c.key ? 'border-violet-600 bg-violet-600 text-white' : 'border-slate-200 bg-white text-slate-700']">
          {{ c.emoji }} {{ c.label }}
        </button>
      </div>
      <p v-else class="text-xs font-bold text-slate-500 mb-2">You haven’t added an organization yet — add the one you represent:</p>
      <p v-if="choices.length" class="text-[11px] font-bold text-slate-500 mt-0.5 mb-1.5">Posting for a union, club or committee instead? Add it:</p>

      <div class="flex flex-wrap gap-1.5">
        <input v-model="newName" :maxlength="limits.org_name_max || 60" placeholder="Organization name"
               class="flex-[1_1_150px] min-w-0 border border-slate-200 rounded-xl px-2.5 py-2 text-[13px] outline-none bg-white"
               @keydown.enter.prevent="add" />
        <select v-model="newType" class="flex-[1_1_120px] min-w-0 border border-slate-200 rounded-xl px-1.5 py-2 text-[13px] bg-white">
          <option v-for="t in orgTypes" :key="t.label" :value="t.label">{{ t.label }}</option>
        </select>
        <button type="button" @click="add" :disabled="adding"
                class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-black text-white bg-violet-600 disabled:opacity-60">
          {{ adding ? 'Adding…' : 'Add' }}
        </button>
      </div>

      <input v-if="selectedKey" :value="modelValue.role" @input="patch({ role: $event.target.value })"
             :maxlength="limits.role_max || 40" placeholder="Your role (optional) — e.g. President, Events Officer"
             class="mt-2 w-full border border-slate-200 rounded-xl px-2.5 py-2 text-[13px] outline-none bg-white" />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { addOrganization, firstError, notify } from '../../../../services/uvibe';

const props = defineProps({
  // { mode: 'me' | 'org', orgKey: 'university' | 'organizer:<id>' | 'custom:<id>' | null, role: '' }
  modelValue: { type: Object, required: true },
  // The campus chosen under "Posting to" — "your university" posts as that school.
  university: { type: Object, default: null },
  // Real organizer profiles the member already has.
  organizations: { type: Array, default: () => [] },
  // Organizations the member added here before.
  customOrganizations: { type: Array, default: () => [] },
  orgTypes: { type: Array, default: () => [] },
  limits: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['update:modelValue']);

const patch = (change) => emit('update:modelValue', { ...props.modelValue, ...change });
const setMode = (mode) => patch({ mode });

const modeClass = (mode) => [
  'rounded-2xl border-2 px-2 py-[11px] text-[13px] font-black text-slate-900 transition',
  props.modelValue.mode === mode ? 'border-violet-600 bg-violet-50' : 'border-slate-200 bg-white hover:bg-slate-50',
];

// ---- who the member can post as ----
const added = ref([...props.customOrganizations]);
watch(() => props.customOrganizations, (list) => {
  list.forEach((o) => { if (!added.value.some((x) => x.id === o.id)) added.value.push(o); });
});

const norm = (s) => String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
const universityName = computed(() => props.university?.full_name || props.university?.name || '');

const choices = computed(() => {
  const list = [];
  if (props.university) list.push({ key: 'university', kind: 'university', label: universityName.value, emoji: '🎓' });
  props.organizations.forEach((o) => list.push({ key: `organizer:${o.id}`, kind: 'organizer', id: o.id, label: o.name, emoji: '🏛️' }));
  added.value
    .filter((o) => !props.university || norm(o.name) !== norm(universityName.value))
    .forEach((o) => list.push({ key: `custom:${o.id}`, kind: 'custom', id: o.id, label: o.name, emoji: o.emoji || '🏫' }));
  return list;
});

// The university is first and pre-selected, like in the design.
const selectedKey = computed(() => {
  const key = props.modelValue.orgKey;
  return choices.value.some((c) => c.key === key) ? key : (choices.value[0]?.key ?? null);
});
watch([() => props.modelValue.mode, choices], () => {
  if (props.modelValue.mode === 'org' && props.modelValue.orgKey !== selectedKey.value) patch({ orgKey: selectedKey.value });
});

const pick = (key) => patch({ orgKey: key });

// ---- add an organization ----
const newName = ref('');
const newType = ref(props.orgTypes[0]?.label || 'Other');
watch(() => props.orgTypes, (t) => { if (!t.some((x) => x.label === newType.value)) newType.value = t[0]?.label || 'Other'; });
const adding = ref(false);

/** Saves the typed organization and selects it. Returns its key, or null if it couldn't be added. */
const add = async () => {
  const name = newName.value.trim().slice(0, props.limits.org_name_max || 60);
  if (!name) { notify('Type the organization’s name'); return null; }
  adding.value = true;
  try {
    const { organization, created } = await addOrganization({ name, orgType: newType.value });
    if (!added.value.some((o) => o.id === organization.id)) added.value.push(organization);
    newName.value = '';
    const key = `custom:${organization.id}`;
    patch({ mode: 'org', orgKey: key });
    if (created) notify(`✅ ${organization.name} added`);
    return key;
  } catch (e) {
    notify(firstError(e, 'Could not add the organization'));
    return null;
  } finally {
    adding.value = false;
  }
};

/**
 * Called when the member taps Post. Works out who the post goes out as. A name typed but not
 * yet added still counts — it is added and used, so the post never goes out under a different
 * organization than the one the member was typing.
 *
 * @returns {Promise<{ok:boolean, message?:string, publisher?:object}>}
 */
const resolve = async () => {
  if (props.modelValue.mode !== 'org') return { ok: true, publisher: { kind: 'me' } };

  let key = selectedKey.value;
  if (newName.value.trim()) {
    key = await add();
    if (!key) return { ok: false };
  }
  const choice = choices.value.find((c) => c.key === key);
  if (!choice) return { ok: false, message: 'Choose or add the organization you’re posting as' };

  return { ok: true, publisher: { kind: choice.kind, id: choice.id ?? null, role: (props.modelValue.role || '').trim() } };
};

defineExpose({ resolve });
</script>
