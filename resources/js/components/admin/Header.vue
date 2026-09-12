<template>
    <nav class="mx-auto flex items-center justify-between lg:px-[80px] lg:py-20">
        <div class="text-5xl font-bold">
            <ApplicationLogo />
        </div>
        <div v-if="$page.props.auth.user" class="space-x-4 pe-1 lg:pe-48">
            <Link
                :href="route('admin.new-dashboard')"
                class="text-tex-sm text-primary cursor-pointer rounded border px-2 py-1 font-bold sm:text-xl md:px-4 md:text-2xl"
            >
                Dashboard</Link
            >
            <Link
                :href="route('admin.logout')"
                method="post"
                class="text-tex-sm text-primary cursor-pointer rounded border px-2 py-1 font-bold sm:text-xl md:px-4 md:text-2xl"
            >
                Logout</Link
            >
        </div>
        <div v-else class="space-x-4 pe-1 lg:pe-48">
            <Link
                :href="route('admin.login')"
                class="hover:bg-secondaryColor bg-primary cursor-pointer rounded py-1 text-sm font-bold text-blue-500 sm:text-sm md:px-8 md:py-2 md:text-2xl"
            >
                LOGIN</Link
            >
        </div>
    </nav>
</template>
<script setup lang="ts">
import ApplicationLogo from '../ApplicationLogo.vue';
</script>
