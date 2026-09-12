<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl transition-all p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Invite Friends</h2>
            <p class="text-sm text-gray-600 mb-4">
                Send event invitations directly by email, or copy the link below.
            </p>

            <!-- Email Input -->
            <form @submit.prevent="sendInvite" class="space-y-4">
                <div>
                    <label for="emails" class="block text-sm font-medium text-gray-700">
                        Invite via Email
                    </label>
                    <input v-model="emails" type="text" id="emails" placeholder="Enter emails" name="email"
                        class="mt-1 w-full rounded-lg border border-gray-300 p-2 focus:border-indigo-500 focus:ring focus:ring-indigo-200" />

                    <label for="userSelect" class="mt-3 block text-center font-bold text-[21px] text-gray-700">
                        OR
                    </label>
                    <label for="userSelect" class="mt-3 block text-sm font-medium text-gray-700">
                        Send Invitations To Multi Users
                    </label>
                    <Multiselect v-model="selectedUsers" :options="users" :multiple="true" :close-on-select="false"
                        :clear-on-select="false" :preserve-search="true" placeholder="Search & select users..."
                        label="name" track-by="id" name="userEmail[]" />
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 py-2 text-white font-semibold hover:bg-indigo-700 transition cursor-pointer">
                    Send Invitations
                </button>

                <input type="text" :value="eventLink" name="eventLink" hidden
                        class="flex-1 rounded-l-lg border border-gray-300 p-2 text-sm" />
            </form>
            <!-- Copy Link Section -->
            <div class="mt-6 border-t pt-4">
                <p class="text-sm font-medium text-gray-700">Or share link:</p>
                <div class="mt-2 flex items-center">
                    <input type="text" :value="eventLink" name="eventLink" readonly
                        class="flex-1 rounded-l-lg border border-gray-300 p-2 text-sm" />
                    <button @click="copyLink"
                        class="rounded-r-lg bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 transition cursor-pointer">
                        Copy
                    </button>
                </div>
            </div>
            <!-- Close -->
            <button @click="closeModal"
                class="mt-6 w-full rounded-lg border border-gray-300 py-2 text-gray-700 hover:bg-blue-500 hover:text-gray-100 transition">
                Close
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { toast } from "vue-sonner";
import axios from "axios";
import Multiselect from "vue-multiselect";

const props = defineProps<{
    isOpen: boolean;
    eventLink: string;
    event: any;
}>();

const emit = defineEmits(["close"]);

const emails = ref("");
const users = ref<any[]>([]);
const selectedUsers = ref<any[]>([]);

const fetchUsers = async () => {
    try {
        const res = await axios.get(route("frontend.fetch.users.for.invitation"));
        users.value = res.data.users;
    } catch (err) {
        toast.error("Failed to load users");
    }
};

onMounted(fetchUsers);

const copyLink = () => {
    navigator.clipboard.writeText(props.eventLink)
        .then(() => {
            toast.success("Event link copied!");
        })
        .catch(() => {
            toast.error("Failed to copy link");
        });
};


const closeModal = () => {
    emit("close");
};

const sendInvite = async () => {
    if (!emails.value && selectedUsers.value.length === 0) {
        toast.error("Please enter emails or select users");
        return;
    }

    const payload = {
        email: emails.value,
        userEmail: selectedUsers.value.map(u => u.email),
        event: props.event,
        eventLink: props.eventLink
    };

    const res = await axios.post(route("frontend.send.users.invitation.email"), payload);

    if (res) {
        toast.success("Invitations sent successfully!");
        emails.value = "";
        selectedUsers.value = [];
        closeModal();
    }
};

</script>
