<template>
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased font-sans transition-colors duration-200">
        <LegalHeader />

        <main class="flex-grow">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="card shadow-xl shadow-slate-200/50 overflow-hidden p-6 md:p-10 lg:p-16">
                    <slot />
                </div>
            </div>
        </main>

        <Footer1 />
    </div>
</template>

<script setup>
import LegalHeader from '@/components/Legal/LegalHeader.vue';
import Footer1 from '@/components/footer1.vue';
import '@/layouts/new_front_layout/new_front.css';
</script>
