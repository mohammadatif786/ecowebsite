<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";

type Props = {
    userdata: any;
    isFriends: boolean;
    isFriendRequestSent: boolean;
};
const { userdata: user, isFriends, isFriendRequestSent } = usePage<Props>().props;

const form = useForm({});

const submit = () => {
    form.post(route("frontend.friend-request.send", { slug: user.name, user: user.uid }), {
        preserveState: false,
    });
};
</script>
<template>
    <button @click="submit"
        class="bg-primary-front text-white font-bold py-2 px-4 rounded-full cursor-pointer disabled:cursor-default"
        :disabled="form.processing || isFriends || isFriendRequestSent">
        <span v-if="isFriends">Already Friends</span>
        <span v-else-if="isFriendRequestSent">Friend Request Sent</span>
        <span v-else>Send Friend Request</span>
    </button>
</template>
