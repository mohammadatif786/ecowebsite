<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div class="">
            <header class="hero_section">
                <Header />

                <!-- Hero Section -->
                <section class="pt-12 pb-24">
                    <Card>
                        <div v-if="status" class="mb-6 rounded-lg bg-green-500/20 p-4 text-center text-green-600">
                            {{ status }}
                        </div>
                        <h2 class="text-center text-3xl font-bold">Forgot Password?</h2>
                        <form @submit.prevent="submit" class="mt-8 space-y-3 text-sm md:px-10 lg:px-14">
                            <!-- Email -->
                            <div>
                                <Input v-model="form.email" :error="form.errors.email" placeholder="Email" />
                                <InputError class="mt-1" :message="form.errors.email" />
                            </div>
                            <!-- Submit Button -->
                            <div class="mx-auto text-center">
                                <Button
                                    type="submit"
                                    class="transform cursor-pointer rounded-lg bg-blue-400 px-14 py-1 text-white shadow-lg transition duration-300 hover:bg-blue-300 hover:shadow-xl focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600 focus:outline-none"
                                    :class="{ 'cursor-not-allowed opacity-70': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="!form.processing">Send</span>
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

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<style scoped>
.hero_section {
    background-image: url('/resources/assets/images/bg-image2.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    width: 100%;
    height: 80vh;
}

input:focus {
    outline: none;
    box-shadow: none;
}
</style>
