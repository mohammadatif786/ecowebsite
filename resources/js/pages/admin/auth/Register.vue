<script setup lang="ts">
    import InputError from '@/components/admin/InputError.vue';
    import TextLink from '@/components/admin/TextLink.vue';
    import { Button } from '@/components/admin/ui/button';
    import { Input } from '@/components/admin/ui/input';
    import { Label } from '@/components/admin/ui/label';
    import AuthBase from '@/layouts/admin/AuthLayout.vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import { LoaderCircle } from 'lucide-vue-next';

    const form = useForm({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = () => {
        form.post(route('admin.register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };
</script>
<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">

        <Head title="Register" />
        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">First Name</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="first_name"
                        v-model="form.first_name" placeholder="First Name" />
                    <InputError :message="form.errors.first_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="name">Last Name</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="last_name"
                        v-model="form.last_name" placeholder="Last Name" />
                    <InputError :message="form.errors.last_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email"
                        placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required :tabindex="3" autocomplete="new-password"
                        v-model="form.password" placeholder="Password" />
                    <InputError :message="form.errors.password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input id="password_confirmation" type="password" required :tabindex="4" autocomplete="new-password"
                        v-model="form.password_confirmation" placeholder="Confirm password" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>
                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" /> Create account
                </Button>
            </div>
            <div class="text-center text-sm text-muted-foreground"> Already have an account? <TextLink
                    :href="route('admin.login')" class="underline underline-offset-4" :tabindex="6">Log in </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
