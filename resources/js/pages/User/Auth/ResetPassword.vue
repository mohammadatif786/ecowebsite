<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div class="bg-blue-600">
            <header class="hero_section">
                <Header />

                <!-- Hero Section -->
                <section class="pt-12 pb-24">
                    <Card>
                        <!-- Right Column: Password Reset Form -->
                        <div class="rounded-2xl bg-white/10 p-8 backdrop-blur-sm">
                            <div v-if="status" class="mb-6 rounded-lg bg-green-500/20 p-4 text-center text-green-100">
                                {{ status }}
                            </div>

                            <h2 class="text-center text-3xl font-bold">Reset Password</h2>

                            <form @submit.prevent="submit" class="mt-8 space-y-3 text-sm">
                                <input type="hidden" v-model="form.token" />

                                <!-- Email Field (hidden if not needed) -->
                                <div class=" ">
                                    <!-- Password -->

                                    <Input
                                        v-model="form.password"
                                        type="password"
                                        :error="form.errors.password"
                                        autocomplete="new-password"
                                        placeholder="Password"
                                    />
                                    <InputError class="mt-1" :message="form.errors.password" />
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <Input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        :error="form.errors.password_confirmation"
                                        autocomplete="new-password"
                                        placeholder="Confirm Password"
                                    />
                                    <InputError class="mt-1" :message="form.errors.password_confirmation" />
                                </div>

                                <!-- Submit Button -->
                                <div class="mx-auto mt-8 text-center">
                                    <Button
                                        type="submit"
                                        class="transform rounded-lg bg-blue-400 px-14 py-1 text-white shadow-lg transition duration-300 hover:bg-blue-300 hover:shadow-xl focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600 focus:outline-none"
                                        :class="{ 'cursor-not-allowed opacity-70': form.processing }"
                                        :disabled="form.processing"
                                    >
                                        <span v-if="!form.processing">Reset Passwrod</span>
                                        <span v-else class="flex items-center justify-center">
                                            <svg
                                                class="mr-2 h-5 w-5 animate-spin text-blue-600"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                ></path>
                                            </svg>
                                            Processing...
                                        </span>
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </Card>
                </section>
            </header>
        </div>

        <Footer1 />
    </GuestLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/admin/InputError.vue';
import Footer1 from '@/components/footer1.vue';
import Card from '@/components/front/Card.vue';
import Header from '@/components/front/Header.vue';
import Input from '@/components/front/Input.vue';
import GuestLayout from '@/layouts/admin/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
    status?: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showEmailField = !props.email;

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<style scoped>
.hero_section {
    background-image: url('/resources/assets/images/bg-image2.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    width: 100%;
}

input:focus {
    outline: none;
    box-shadow: none;
}
</style>
