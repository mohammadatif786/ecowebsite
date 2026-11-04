<template>
  <Modal ref="modalRef" maxWidth="max-w-lg">
    <div class="-m-1">
      <!-- Header -->
      <div class="rounded-t-[19px] overflow-hidden" style="background:linear-gradient(120deg,#7c3aed,#a855f7)">
        <div class="p-5 flex items-start justify-between gap-3 text-white">
          <div class="flex items-center gap-3 min-w-0">
            <span class="h-11 w-11 rounded-2xl bg-white/20 grid place-items-center shrink-0">
              <GraduationCap class="w-5 h-5" />
            </span>
            <div class="min-w-0">
              <h3 class="font-black text-xl truncate">Post to U Vibe</h3>
              <p class="text-white/70 text-xs font-bold">Share parties, events and campus life across universities.</p>
            </div>
          </div>
          <button type="button" @click="close" class="shrink-0 text-white/80 hover:text-white transition" aria-label="Close">
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!--
        This window only puts the pieces together. Each piece is its own component in
        components/User/uvibe/compose/ and does its own work; the backend rules live in
        the shared U Vibe endpoints (the same ones the mobile app uses).
      -->
      <div v-if="options" class="p-5 space-y-3">
        <PostAsPicker ref="postAsRef" v-model="form.postAs" :university="university"
                      :organizations="publishers.organizations" :customOrganizations="publishers.custom"
                      :orgTypes="options.org_types" :limits="options.limits" />

        <PostToCampus ref="campusRef" v-model="form.campusId" :universities="myUniversities"
                      :defaultId="defaultUniversityId" @openUniversityModal="$emit('openUniversityModal')" />

        <PostTypeSelect v-model="form.category" :categories="options.categories" />

        <PostTitleField v-model="form.title" />

        <PostTextCardEditor v-if="form.textMode" v-model="form.textStyle" v-model:text="form.description"
                            :options="options.text_card" @starter="descriptionRef?.focus()" />

        <PostDescription ref="descriptionRef" v-model="form.description" />

        <PostPhotoPicker v-if="!form.textMode" v-model="form.photos"
                         :max="options.limits.max_photos" :maxKb="options.limits.max_photo_kb" />

        <PostTextCardToggle v-model="form.textMode" />

        <PostPriceField v-model="form.price" />

        <PostEventLink v-model="form.eventId" :events="userEvents" />

        <PostBoostToggle v-model="form.allowBoosts" />

        <PostTagSection v-model="form.tagged" :items="affiliateItems" />

        <PostCrossPostToggle v-model="form.crossPost" />

        <PostCleanNote />

        <button type="button" @click="submit" :disabled="submitting"
                class="btn btn-primary w-full py-3 disabled:opacity-60">
          {{ submitting ? 'Posting…' : 'Post to U Vibe' }}
        </button>
      </div>
      <p v-else class="p-8 text-center text-sm font-bold text-slate-500">Getting the composer ready…</p>
    </div>
  </Modal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { GraduationCap, X } from 'lucide-vue-next';
import Modal from '../ui/Modal.vue';
import { getUser } from '../MockDataStore';
import { buildPostForm, createPost, fetchOptions, firstError, notify } from '../../../services/uvibe';
import PostAsPicker from '../uvibe/compose/PostAsPicker.vue';
import PostToCampus from '../uvibe/compose/PostToCampus.vue';
import PostTypeSelect from '../uvibe/compose/PostTypeSelect.vue';
import PostTitleField from '../uvibe/compose/PostTitleField.vue';
import PostTextCardEditor from '../uvibe/compose/PostTextCardEditor.vue';
import PostDescription from '../uvibe/compose/PostDescription.vue';
import PostPhotoPicker from '../uvibe/compose/PostPhotoPicker.vue';
import PostTextCardToggle from '../uvibe/compose/PostTextCardToggle.vue';
import PostPriceField from '../uvibe/compose/PostPriceField.vue';
import PostEventLink from '../uvibe/compose/PostEventLink.vue';
import PostBoostToggle from '../uvibe/compose/PostBoostToggle.vue';
import PostTagSection from '../uvibe/compose/PostTagSection.vue';
import PostCrossPostToggle from '../uvibe/compose/PostCrossPostToggle.vue';
import PostCleanNote from '../uvibe/compose/PostCleanNote.vue';

const props = defineProps({
  affiliateItems: { type: Array, default: () => [] },
  publishers: { type: Object, default: () => ({ organizations: [], custom: [] }) },
  userEvents: { type: Array, default: () => [] },
  // The member's own universities (from the server). You can post only to these.
  myUniversities: { type: Array, default: () => [] },
  defaultUniversityId: { type: Number, default: null },
  // Post types, organization types and text-card looks (from the backend, same as the app gets).
  composeOptions: { type: Object, default: null },
});

const emit = defineEmits(['postCreated', 'openUniversityModal']);

const page = usePage();
const user = computed(() => page.props.auth?.user || getUser());

const modalRef = ref(null);
const postAsRef = ref(null);
const campusRef = ref(null);
const descriptionRef = ref(null);

// ---- the choices (from the backend) ----
const options = ref(props.composeOptions?.categories ? props.composeOptions : null);
watch(() => props.composeOptions, (v) => { if (v?.categories) options.value = v; });

// ---- what the member is filling in ----
const blank = () => ({
  postAs: { mode: 'me', orgKey: null, role: '' },
  campusId: null,
  category: 'campus-life',
  title: '',
  description: '',
  textMode: false,
  textStyle: { pal: 0, font: 0, motion: 'flow', sticker: '' },
  photos: [],
  price: '',
  eventId: null,
  allowBoosts: true,
  tagged: null,
  crossPost: true,
});
const form = reactive(blank());
const submitting = ref(false);

const reset = () => Object.assign(form, blank());

const university = computed(() => props.myUniversities.find((u) => u.id === form.campusId) || null);

// Starting a text card clears any photos (a post is one or the other).
watch(() => form.textMode, (on) => { if (on) form.photos = []; });

const open = async () => {
  // You can only post to your own university, so set one first.
  if (!props.myUniversities.length) {
    emit('openUniversityModal');
    return;
  }
  reset();
  if (!options.value) {
    try {
      options.value = await fetchOptions();
    } catch (e) {
      notify(firstError(e, 'Could not open the composer. Please try again.'));
      return;
    }
  }
  campusRef.value?.pickDefault();
  modalRef.value?.open();
};

const close = () => modalRef.value?.close();

defineExpose({ open, close });

const submit = async () => {
  if (submitting.value) return;

  const title = form.title.trim();
  const caption = form.description.trim();
  if (!title) return notify('Add a title');
  if (form.textMode && !caption) return notify('Write something for your text card');
  if (!university.value) {
    notify('Please set your university first');
    emit('openUniversityModal');
    return;
  }

  submitting.value = true;
  try {
    const who = await postAsRef.value.resolve();
    if (!who.ok) {
      if (who.message) notify(who.message);
      return;
    }

    const res = await createPost(buildPostForm({
      publisher: who.publisher,
      userId: user.value.id,
      universityId: university.value.id,
      campus: university.value.name,
      category: form.category,
      title,
      caption,
      price: form.price,
      allowBoosts: form.allowBoosts,
      crossPost: form.crossPost,
      textStyle: form.textMode ? { ...form.textStyle } : null,
      photos: form.photos,
      productId: form.tagged?.kind === 'product' ? form.tagged.id : null,
      // An event can come from the "link your event" list or from the Tag picker; send both, once each.
      eventIds: [form.eventId, form.tagged?.kind === 'event' ? form.tagged.id : null],
    }));

    emit('postCreated', res.post);
    close();
    notify(res.message || '🎓 Posted to U Vibe');
    reset();
  } catch (e) {
    console.error('Failed to post to U Vibe', e);
    notify(firstError(e, 'Failed to post to U Vibe'));
  } finally {
    submitting.value = false;
  }
};
</script>
