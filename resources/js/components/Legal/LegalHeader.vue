<template>
    <header class="glass sticky top-0 z-30 border-b border-slate-200/70 h-[68px] flex items-center">
        <nav class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <Link :href="route('new_frontend.home')" class="transition hover:opacity-80">
                        <BrandedLogo />
                    </Link>
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <Link :href="route('new_frontend.home')" class="text-slate-600 px-3 py-2 text-sm font-bold hover:text-lkink transition">Home</Link>
                        <Link :href="route('legal.support')" class="text-slate-600 px-3 py-2 text-sm font-bold hover:text-lkink transition">Support</Link>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('new_frontend.dating.find')" class="text-slate-600 px-3 py-2 text-sm font-bold hover:text-lkink transition">Dashboard</Link>
                        <Link :href="route('logout')" method="post" as="button" class="btn btn-primary px-5 py-2 text-sm shadow-md">Logout</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-slate-600 px-3 py-2 text-sm font-bold hover:text-lkink transition">Login</Link>
                        <Link :href="route('register')" class="btn btn-primary px-5 py-2 text-sm shadow-md">Signup</Link>
                    </template>
                </div>
            </div>
        </nav>
    </header>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import BrandedLogo from '../BrandedLogo.vue';
</script>
