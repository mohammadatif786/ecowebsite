<template>
  <AuthenticatedLayout>
    <Head title="Find Matches" />
    <Card class="bg-transparent">
      <CardHeader>
        <!-- <ProfilesHeader @apply-filters="handleApplyFilters" /> -->
        <!-- <Filter2 url="allmatches" /> -->
      </CardHeader>
      <CardContent> </CardContent>
    </Card>
    <GiftDialog ref="giftDialogRef" />
  </AuthenticatedLayout>
</template>
<script setup lang="ts">
import CardContent from "@/components/front/ui/card/CardContent.vue";
import CardHeader from "@/components/front/ui/card/CardHeader.vue";
import UserProfileCard from "@/components/front/UserProfileCard.vue";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import GiftDialog from "@/components/front/GiftDialog.vue";
interface UserProfile {
  id: number;
  name: string;
  age: number;
  country: string;
  gender: string;
  more_photos: string[];
  avatar: string;
}

const props = defineProps<{ users: UserProfile[] }>();
const profiles = ref<UserProfile[]>([...props.users]);
const form = useForm<any>({});
const giftDialogRef = ref();

const handleLike = (profile: UserProfile) => {
  form.post(route("frontend.profile.like", profile.id), {
    preserveScroll: true,
  });
};

const handlePass = (profile: UserProfile) => {
  form.post(route("frontend.profile.dislike", profile.id), {
    preserveScroll: true,
    onSuccess: () => {
      profiles.value = profiles.value.filter((p) => p.id !== profile.id);
    },
  });
};
const handleGift = (profile: UserProfile) => {
  giftDialogRef.value.open();
};
const handleShuffle = (profile: UserProfile) => {
  router.visit(route("frontend.user.matches"));
};
</script>
