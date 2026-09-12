<template>
    <GuestLayout>
        <Head title="Log in to LinkUp" />

        <div class="bg-blue-600">
            <header class="hero_section">
                <Header />

                <!-- Hero Section -->
                <section class="pt-12 pb-24">
                    <Card>
                        <h2 class="text-center text-3xl font-bold">Welcome to Link Up</h2>
                        <form @submit.prevent="submit" class="mt-8 space-y-3 text-sm md:px-10 lg:px-14">
                            <!-- Email -->
                            <div>
                                <Input v-model="form.email" :error="form.errors.email" placeholder="Email" />
                                <InputError class="mt-1" :message="form.errors.email" />
                            </div>

                            <!-- Password -->
                            <div>
                                <Input
                                    v-model="form.password"
                                    type="password"
                                    :error="form.errors.password"
                                    autocomplete="new-password"
                                    placeholder="Password"
                                />
                                <InputError class="mt-1" :message="form.errors.password" />
                            </div>

                            <!-- Remember Me -->
                            <div class="mt-4 flex items-center">
                                <input
                                    type="checkbox"
                                    id="remember"
                                    v-model="form.remember"
                                    class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="remember" class="ml-2 block text-sm"> Remember me </label>
                            </div>

                            <div class="mt-6 text-center">
                                <p>
                                    Forgot Password?
                                    <a :href="route('password.request')" class="text-center text-sm font-medium text-blue-200 hover:underline">
                                        Reset It
                                    </a>
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="mx-auto text-center">
                                <Button
                                    type="submit"
                                    class="transform cursor-pointer rounded-lg bg-blue-400 px-14 py-1 text-white shadow-lg transition duration-300 hover:bg-blue-300 hover:shadow-xl focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600 focus:outline-none"
                                    :class="{ 'cursor-not-allowed opacity-70': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="!form.processing">Sign In</span>
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

                            <div class="mt-6 text-center text-sm">
                                Don't have an account?
                                <a :href="route('register')" class="font-semibold text-gray-400 underline">Sign up</a>
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

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
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
