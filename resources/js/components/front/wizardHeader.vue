<template>
    <nav class="container mx-auto flex items-center justify-between lg:py-20">
        <div class="text-5xl font-bold">
            <Link :href="route('user.home')">
                <ApplicationLogo />
            </Link>
        </div>
        <!-- <div v-if="$page.props.auth.user" class="space-x-4 pe-1 lg:pe-48">
            <Link
                :href="route('frontend.find.matches')"
                class="text-tex-sm text-primary hover:bg-primary-front-hover cursor-pointer rounded border px-2 py-1 font-bold duration-150 sm:text-xl md:px-4 md:text-2xl"
            >
                Dashboard</Link
            >
            <Link
                :href="route('logout')"
                method="post"
                class="text-tex-sm text-primary cursor-pointer rounded border px-2 py-1 font-bold sm:text-xl md:px-4 md:text-2xl"
            >
                Logout</Link
            >
        </div>
        <div v-else class="space-x-4 pe-1 lg:pe-48">
            <Link
                :href="route('login')"
                class="bg-primary-front hover:text-primary-front border-primary-front cursor-pointer rounded-lg py-1 text-sm font-bold text-blue-500 duration-150 hover:border-1 hover:bg-transparent sm:text-sm md:px-8 md:py-2 md:text-2xl"
            >
                LOGIN</Link
            >
            <Link
                :href="route('register')"
                class="text-tex-sm hover:bg-primary-front-hover text-primary border-primary-front cursor-pointer rounded-lg border py-1 font-bold duration-200 hover:text-sky-600 sm:text-xl md:px-8 md:py-2 md:text-2xl"
            >
                SIGNUP</Link
            >
        </div> -->
    </nav>
</template>
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from './../ApplicationLogo.vue';
</script>
