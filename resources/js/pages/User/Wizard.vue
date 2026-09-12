<style>
.sliderTracker {
    background-color: gainsboro;
    height: 5px;
}
</style>
<template>
    <div class="">
        <header class="hero_section min-h-[90vh]">
            <WizardHeader />
            <!-- Start Hero Section -->
            <section>
                <Card>
                    <!-- Step Info -->
                    <form action="" class="rounded-lg bg-white/10 p-4 text-sm">
                        <div v-if="currentStep === 1">
                            <div class="flex flex-col items-center justify-center">
                                <span class="relative overflow-hidden rounded-full bg-white">
                                    <FileUpload v-model="form.avatar" />
                                </span>
                                <p class="text-xs font-semibold text-red-500">{{ form.errors.avatar }}</p>
                            </div>
                        </div>
                        <div v-if="currentStep === 2">
                            <div class="space-y-4">
                                <h2 class="mb-2 text-2xl font-bold">Where do you live?</h2>
                                <p class="mb-6 text-sm text-gray-600">
                                    Tell us your home base so LinkUp can help you find events, meet people, stay
                                    connected, and go LIVE.
                                </p>
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- First Select -->
                                    <div class="col-span-3 w-full md:col-span-1">
                                        <Label class="block text-sm font-medium">
                                            Country
                                            <span v-if="isLoadingCountry">
                                                <Loader />
                                            </span>
                                        </Label>
                                        <select v-model="form.country" @change="handleCountryChange"
                                            :class="['w-full rounded-lg border-2 border-b-2 bg-transparent py-2 focus:ring-0', 'border-blue-300']"
                                            name="country">
                                            <option class="text-gray-500" value="">Select Country</option>
                                            <option class="text-gray-500" v-for="country in countries"
                                                :key="country.value" :value="country.value">
                                                {{ country.label }}
                                            </option>
                                        </select>
                                        <p class="text-xs font-semibold text-red-500">{{ form.errors.country }}</p>
                                    </div>
                                    <div class="col-span-3 flex w-full gap-4 md:col-span-1">
                                        <div class="w-full">
                                            <Label class="flex text-sm font-medium">
                                                State
                                                <span v-if="isLoadingState">
                                                    <Loader />
                                                </span></Label>
                                            <select v-model="form.state"
                                                :class="['w-full rounded-lg border-2 border-b-2 bg-transparent py-2 focus:ring-0', 'border-blue-300']"
                                                @change="handleStateChange" required>
                                                <option disabled selected value="">Select State</option>
                                                <option v-for="state in states" :key="state.label" class="text-gray-500"
                                                    :value="state.value">
                                                    {{ state.label }}
                                                </option>
                                                <option v-if="!form.country" disabled value="">Please Select Country
                                                </option>
                                            </select>
                                            <p class="text-xs font-semibold text-red-500">{{ form.errors.state }}</p>
                                        </div>
                                    </div>
                                    <!-- Second Select -->
                                    <div class="col-span-full flex w-full gap-4">
                                        <div class="w-full">
                                            <Label class="flex text-sm font-medium">City <span v-if="isLoadingCity">
                                                    <Loader />
                                                </span></Label>

                                            <select v-model="form.city"
                                                :class="['w-full rounded-lg border-2 border-b-2 bg-transparent py-2 focus:ring-0', 'border-blue-300']"
                                                required>
                                                <option disabled selected value="">Select City</option>
                                                <option v-for="city in cities" :key="city.label" class="text-gray-500"
                                                    :value="city.value">
                                                    {{ city.value }}
                                                </option>
                                                <option v-if="!form.state" disabled value="">Please Select State
                                                </option>
                                            </select>
                                            <p class="text-xs font-semibold text-red-500">
                                                {{ form.errors.city }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 flex w-full gap-4"></div>
                        </div>
                        <div v-if="currentStep === 3">
                            <h2 class="text-center text-2xl font-bold">
                                Tell Link Up what language you speak<i class="ri-briefcase-4-line text-sky-400"></i>
                            </h2>
                            <p class="mb-5 text-center text-xs">
                                choose your preferred language(s) to help us connect you with others who share your
                                linguistic background.
                            </p>
                            <div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <div v-for="language in languages" :key="language" :class="[
                                        'w-full cursor-pointer rounded-lg border border-blue-500 p-1 px-4 hover:border-sky-500',
                                        form.language.includes(language) ? 'bg-sky-500 text-white' : '',
                                    ]" @click="languagesFilter(language)">
                                        {{ language }}
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.language }}</p>
                        </div>
                        <div v-if="currentStep === 4">
                            <h2 class="text-2xl font-bold">Lets Keep in touch <i
                                    class="ri-smartphone-line text-sky-500"></i></h2>
                            <p class="mb-5 text-xs">Add your phone with (+92) country code. So you can connect more
                                easily with your match</p>
                            <Label>Phone</Label>
                            <Input type="tel" v-model="form.phone_number" :error="form.errors.phone_number"
                                placeholder=" Phone Number" />
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.phone_number }}</p>
                        </div>
                        <div v-if="currentStep === 5">
                            <h2 class="text-center text-2xl font-bold">
                                Lets Link Up Know when to <br class="hidden lg:block" />
                                send the cake <i class="ri-cake-2-line text-sky-500"></i>
                            </h2>
                            <p class="mb-5 text-center text-xs">
                                Your birth date helps us display your age but dont worry, the exact date <br
                                    class="hidden lg:block" />
                                is private
                            </p>
                            <div class="relative mt-4">
                                <Label class="block mb-2 text-sm font-semibold text-gray-700">Birthdate</Label>

                                <VueDatePicker v-model="form.birthday" :enable-time-picker="false" :auto-apply="true"
                                    placeholder="Select your birth date" format="yyyy-MM-dd" />


                                <p class="mt-1 text-xs font-semibold text-red-500">
                                    {{ form.errors.birthday }}
                                </p>
                            </div>
                        </div>
                        <div v-if="currentStep === 6">
                            <h2 class="text-center text-2xl font-bold">What's your gender?</h2>
                            <p class="mb-5 text-center text-xs">Choose the option that best describes you.</p>

                            <Label>Gender</Label>
                            <div class="mt-2 space-y-3">
                                <div :class="[
                                    'w-full cursor-pointer rounded-lg border px-4 py-2 text-center transition-all duration-150',
                                    form.gender === 'male'
                                        ? 'border-primary-front bg-sky-500 text-white'
                                        : 'border-blue-500 text-blue-700 hover:bg-blue-50',
                                ]" @click="form.gender = 'male'">
                                    Male
                                </div>

                                <div :class="[
                                    'w-full cursor-pointer rounded-lg border px-4 py-2 text-center transition-all duration-150',
                                    form.gender === 'female'
                                        ? 'border-primary-front bg-sky-500 text-white'
                                        : 'border-blue-500 text-blue-700 hover:bg-blue-50',
                                ]" @click="form.gender = 'female'">
                                    Female
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.gender }}</p>
                        </div>
                        <div class="mx-auto text-center lg:w-[70%]" v-if="currentStep === 7">
                            <h2 class="text-center text-2xl font-bold">How far will you link up? <i
                                    class="ri-focus-2-line text-sky-500"></i></h2>
                            <p class="mb-5 text-center text-xs">Adjust your distance to meet matches near you. link Up
                                does the reset.</p>

                            <div class="flex justify-between">
                                <Label> Distance Prefrence </Label>

                                <Label> {{ form.distance_filter[1] }} km</Label>
                            </div>
                            <input type="range" min="0" max="100" placeholder="Distance"
                                v-model="form.distance_filter[1]"
                                class="mx-auto w-full border-0 border-b-2 border-gray-300 bg-transparent py-2 focus:ring-0" />
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.distance_filter }}</p>
                        </div>
                        <div class="mx-auto text-center lg:w-[70%]" v-if="currentStep === 8">
                            <h2 class="text-center text-2xl font-bold">
                                What age ranage are you interested in? <i class="ri-focus-2-line text-sky-500"></i>
                            </h2>
                            <p class="mb-5 text-center text-xs">Choose the age range of people you'd like to connect
                                with.</p>
                            <div class="flex justify-between">
                                <Label> Age Range Prefrence </Label>

                                <Label>{{ form.age_filter[0] }}-{{ form.age_filter[1] }}</Label>
                            </div>

                            <SliderRoot v-model="form.age_filter"
                                class="relative flex h-5 touch-none items-center select-none" :max="100" :min="18"
                                :step="1">
                                <SliderTrack class="sliderTracker relative h-0.73 grow rounded-full">
                                    <SliderRange class="absolute h-full rounded-full bg-blue-500" />
                                </SliderTrack>
                                <SliderThumb v-for="thumb in form.age_filter" :key="thumb"
                                    class="shadow-blackA7 hover:bg-violet3 focus:shadow-blackA8 block h-5 w-5 rounded-[10px] bg-blue-500 shadow-[0_2px_10px] focus:shadow-[0_0_0_5px] focus:outline-none"
                                    aria-label="Volume" />
                            </SliderRoot>
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.age_filter }}</p>
                        </div>
                        <div v-if="currentStep === 9">
                            <h2 class="mb-4 text-center text-2xl font-bold">
                                Tell Link Up who you'd like to see <i class="ri-emoji-sticker-line text-sky-500"></i>
                            </h2>

                            <div class="mx-auto mt-2 space-y-3 md:w-[70%]">
                                <div :class="[
                                    'w-full cursor-pointer rounded-lg border px-4 py-2 text-center transition-all duration-150',
                                    form.link_me_with === 'male'
                                        ? 'border-primary-front bg-sky-500 text-white'
                                        : 'border-blue-500 text-blue-700 hover:bg-blue-50',
                                ]" @click="form.link_me_with = 'male'">
                                    Male
                                </div>

                                <div :class="[
                                    'w-full cursor-pointer rounded-lg border px-4 py-2 text-center transition-all duration-150',
                                    form.link_me_with === 'female'
                                        ? 'border-primary-front bg-sky-500 text-white'
                                        : 'border-blue-500 text-blue-700 hover:bg-blue-50',
                                ]" @click="form.link_me_with = 'female'">
                                    Female
                                </div>

                                <div :class="[
                                    'w-full cursor-pointer rounded-lg border px-4 py-2 text-center transition-all duration-150',
                                    form.link_me_with === 'both'
                                        ? 'border-primary-front bg-sky-500 text-white'
                                        : 'border-blue-500 text-blue-700 hover:bg-blue-50',
                                ]" @click="form.link_me_with = 'both'">
                                    Both
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.link_me_with }}</p>
                        </div>
                        <div v-if="currentStep === 10">
                            <div class="w-full">
                                <h2 class="text-center text-2xl font-bold">
                                    Who do you want to link Up with?<i class="ri-earth-fill text-sky-500"></i>
                                </h2>
                                <p class="mb-5 text-center text-xs">
                                    Choose the Caribbean or latin Ameerican countries you woul'd like to meet people
                                    from. want to see everyone? Just
                                    select
                                </p>
                                <!-- <Label class="block text-sm font-medium">City</Label> -->
                                <img src="/assets/images/flags.PNG" class="mx-auto mb-5 text-center" width="90"
                                    alt="" />
                                <div>
                                    <Label>Tell Link which Caribbean and Latin American countries you want to see people
                                        from.</Label>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div>
                                            <input type="radio" id="all" class="me-1" :value="true"
                                                v-model="isRootAll" />
                                            <label for="all">All</label>
                                        </div>
                                        <div>
                                            <input type="radio" id="other" class="me-1" :value="false"
                                                v-model="isRootAll" />
                                            <label for="other">Other</label>
                                        </div>
                                    </div>

                                    <select v-if="!isRootAll" v-model="form.link_me_with_country_name"
                                        class="mt-2 w-full rounded-2xl border border-blue-500 p-1 px-4">
                                        <option value="">Select</option>
                                        <option v-for="country in caribbeans" :key="country.name" :value="country.name">
                                            {{ country.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.link_me_with_country_name }}
                            </p>
                        </div>
                        <div v-if="currentStep === 11">
                            <div class="w-full">
                                <h2 class="text-center text-2xl font-bold">
                                    Tell Link Up about your roots <i class="ri-earth-fill text-sky-500"></i>
                                </h2>
                                <p class="mb-5 text-center text-xs">
                                    Select the country your connected to-we'll proudly show your flag on your profile to
                                    help other know where you're
                                    rapping from.
                                </p>
                                <!-- <Label class="block text-sm font-medium">City</Label> -->
                                <img src="/assets/images/flags.PNG" class="mx-auto mb-5 text-center" width="90"
                                    alt="" />

                                <select v-model="form.caribbean_interest"
                                    :class="['w-full rounded-lg border-2 border-b-2 bg-transparent py-2 focus:ring-0', 'border-blue-300']"
                                    required>
                                    <option disabled selected value="">Select Country</option>
                                    <option v-for="caribbean in caribbeans" :key="caribbean.name" class="text-gray-500"
                                        :value="caribbean.name">
                                        {{ caribbean.name }}
                                    </option>
                                </select>
                            </div>
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.caribbean_interest }}</p>
                        </div>
                        <div v-if="currentStep === 12">
                            <h2 class="text-center text-2xl font-bold">Tell Link Up what you do?<i
                                    class="ri-briefcase-4-line text-sky-400"></i></h2>
                            <p class="mb-5 text-center text-xs">
                                Whats your profession? This helps us understand you better and connect you with
                                like-minded individuals.
                            </p>

                            <Input v-model="form.job" :error="form.errors.job" autocomplete="new-password"
                                placeholder="What do you do?" maxlength="255" />
                            <p class="text-right text-xs text-gray-500">{{ form.job?.length || 0 }}/255</p>
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.job }}</p>
                        </div>
                        <div v-if="currentStep === 13">
                            <h2 class="text-center text-2xl font-bold">
                                What university did you attend? <i class="ri-graduation-cap-fill text-sky-500"></i>
                            </h2>
                            <p class="mb-5 text-center text-xs">
                                Let Link Up know where you studied. This helps us to connect with others whow share you
                                background.
                            </p>
                            <University v-model="form.university" />
                            <!-- <Input v-model="form.university" :error="form.errors.university" placeholder=" Standford Universty" /> -->
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.university }}</p>
                        </div>

                        <div v-if="currentStep === 14">
                            <h2 class="text-center text-2xl font-bold">
                                Tell Link Up why you're here<i class="ri-briefcase-4-line text-sky-400"></i>
                            </h2>
                            <p class="mb-5 text-center text-xs">
                                Everyone’s here for something different—love, vibes, friends, or just to grab tickets
                                for the next big event. Choose
                                what you’re looking for so we can guide your experience.
                            </p>

                            <div>
                                <div v-for="(reason, index) in reasonsForJoining" :key="index"
                                    @click="form.whyare = reason.title" :class="[
                                        'mt-2 w-full cursor-pointer rounded-lg border px-4 py-2 font-bold transition-all duration-150',
                                        form.whyare === reason.title ? 'border-gray-200 bg-sky-500 text-white' : 'border-gray-500 hover:bg-blue-50',
                                    ]">
                                    <div class="flex">
                                        <span class="text-2xl">{{ reason.emoji }}</span>
                                        <div>
                                            <p class="ml-2">{{ reason.title }}</p>
                                            <p class="ml-2 text-xs text-gray-500">{{ reason.description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-red-500">{{ form.errors.whyare }}</p>
                        </div>
                        <div v-if="currentStep === 15" id="interests-step"
                            class="compact font-poppins mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-lg">
                            <!-- Header -->
                            <div class="mb-2 flex items-center justify-between">
                                <h2 class="text-xl font-bold">Tell Link Up about yourself</h2>
                            </div>

                            <!-- Subtitle -->
                            <p class="mb-4 text-gray-600">
                                Share your passions to connect with others in the Caribbean and Latin American
                                community. You can add or change this
                                later.
                            </p>

                            <!-- About textarea + AI buttons -->
                            <div class="flex items-stretch gap-3">
                                <textarea v-model="form.about_me" maxlength="180"
                                    placeholder="Tell Link Up a bit about yourself (e.g., ‘I love salsa dancing and Caribbean festivals’)"
                                    class="min-h-27.5 flex-1 resize-y rounded-lg border-2 border-gray-300 p-3 text-sm focus:border-blue-400 focus:outline-none"></textarea>

                                <div class="flex flex-col gap-2">
                                    <button type="button"
                                        class="rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs text-green-900 hover:bg-green-100"
                                        @click="mySelections">
                                        ✨ From my selections
                                    </button>
                                    <button type="button"
                                        class="rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs text-blue-900 hover:bg-blue-100"
                                        @click="surpriseMe">
                                        🎲 Surprise me
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-center text-xs font-semibold text-red-500">{{ form.errors.about_me }}
                            </p>

                            <!-- Interests -->
                            <div class="mt-5 flex items-baseline justify-between">
                                <label class="text-sm font-semibold">
                                    Event interests <span class="font-normal text-gray-500">(select all that apply, min
                                        3)</span>
                                </label>
                                <small class="text-gray-500">{{ form.interests.length }} selected</small>
                            </div>

                            <!-- Chips -->
                            <div id="chips"
                                class="mt-3 grid max-h-56 grid-cols-2 gap-2 overflow-y-auto pr-1 sm:grid-cols-3 md:grid-cols-4">
                                <label v-for="(interest, index) in interests" :key="index" :class="[
                                    'flex cursor-pointer items-center gap-2 rounded-full border-2 px-3 py-2 transition select-none',
                                    form.interests.includes(interest.label)
                                        ? 'border-blue-400 bg-blue-50 shadow-sm'
                                        : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50/30',
                                ]" @click="toggleInterest(interest.label)">
                                    <span>{{ interest.icon }}</span> {{ interest.label }}
                                </label>
                            </div>
                            <p class="mt-1 text-xs font-semibold text-red-500">{{ form.errors.interests }}</p>
                        </div>

                        <div v-if="currentStep === 16">
                            <h2 class="text-center text-2xl font-bold">Show your best self</h2>
                            <p class="mb-5 text-center text-xs">
                                Upload up to six of your best photos or video to make a fantastic first impression.
                                Let your personality shine.
                            </p>

                            <div class="grid grid-cols-6 gap-5">
                                <span v-for="(n, index) in 6" :key="index"
                                    class="relative col-span-2 flex flex-col items-center">
                                    <!-- image input -->
                                    <ImageUpload v-model="form.more_photos[index]" />

                                    <!-- error for this specific photo -->
                                    <p v-if="form.errors[`more_photos.${index}`]"
                                        class="mt-1 text-xs font-semibold text-red-500">
                                        {{ form.errors[`more_photos.${index}`] }}
                                    </p>
                                </span>
                            </div>
                            <p v-if="form.errors.more_photos" class="mt-1 text-xs font-semibold text-red-500">
                                {{ form.errors.more_photos }}
                            </p>
                        </div>

                    </form>
                    <!-- start Navigation Buttons -->
                    <div class="flex justify-center">
                        <button @click="nextStep"
                            class="hover:bg-secondaryColor w-75 cursor-pointer rounded-lg bg-blue-400 px-4 py-2 text-xl font-bold text-white duration-150">
                            Next
                        </button>
                    </div>
                    <!-- end Navigation Buttons -->
                </Card>
            </section>
        </header>
    </div>

    <OTPVerification :open="otpOpen" :user="otpUser" :token="otpToken" :redirectOnSuccess="false"
        @close="otpOpen = false" @verified="handleOtpVerified" />

    <Footer1 />
</template>
<script setup lang="ts">
import FileUpload from '@/components/admin/FileUpload.vue';
import ImageUpload from '@/components/admin/ImageUpload.vue';
import Footer1 from '@/components/footer1.vue';
import Card from '@/components/front/Card.vue';
import Input from '@/components/front/Input.vue';
import { Label } from '@/components/front/ui/label';
import University from '@/components/front/University.vue';
import WizardHeader from '@/components/front/wizardHeader.vue';
import Loader from '@/components/Loader.vue';
import { useCountryStateCity } from '@/composables/useCountryStateCity';
import { useForm, usePage } from '@inertiajs/vue3';
import { SliderRange, SliderRoot, SliderThumb, SliderTrack } from 'reka-ui';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import OTPVerification from './Auth/OTPVerification.vue'
const page = usePage() as any;
const {
    countries,
    states,
    cities,
    isLoadingCountry,
    isLoadingState,
    isLoadingCity,
    selectedCountry,
    selectedState,
    fetchCountries,
    fetchStates,
    fetchCities,
    resetStatesAndCities,
    resetCities,
} = useCountryStateCity();
// const interests = ['Travel', 'Cooking', 'Hiking', 'Yoga', 'Gaming', 'Movies', 'Books', 'Animals'];
const languages = ['English', 'Spanish', 'French', 'Haitian Creole', 'Dutch', 'Papiamento', 'Portuguese', 'Sranan Tongo'];

const interests = [
    // General
    { label: 'Music Festivals', icon: '🎵' },
    { label: 'Travel', icon: '🛫' },
    { label: 'Cooking', icon: '🍳' },
    { label: 'Books', icon: '📚' },
    { label: 'Yoga', icon: '🧘' },
    { label: 'Movies', icon: '🎬' },
    { label: 'Wine', icon: '🍷' },
    { label: 'Church Events', icon: '⛪' },

    // Caribbean-specific
    { label: 'Liming', icon: '🌴' }, // (means hanging out / socialising)
    { label: 'Carnival / Mas', icon: '🎭' },
    { label: 'Junkanoo', icon: '🐚' },
    { label: 'Soca Fêtes', icon: '🎶' },
    { label: 'Reggae & Dancehall', icon: '🟩' },
    { label: 'Kompa / Zouk', icon: '🇭🇹' },
    { label: 'Steel Pan', icon: '🥁' },
    { label: 'Boat Fêtes', icon: '🛥️' },
    { label: 'Beach Lime / Bonfire', icon: '🏖️' },
    { label: 'Food & Rum Festivals', icon: '🍹' },
    { label: 'Crop Over', icon: '🎉' },
    { label: 'J’ouvert', icon: '🌅' },
    { label: 'Rake & Scrape', icon: '🪘' },

    // Latin America-specific
    { label: 'Salsa Socials', icon: '🫶' },
    { label: 'Bachata Nights', icon: '💜' },
    { label: 'Reggaetón Parties', icon: '🔥' },
    { label: 'Cumbia & Vallenato', icon: '🥁' },
    { label: 'Samba Blocos / Pagode', icon: '🟡' },
    { label: 'Forró Nights', icon: '🪗' },
    { label: 'Mariachi / Regional Mexicano', icon: '🎺' },
    { label: 'Día de los Muertos (festivals)', icon: '💐' },
    { label: 'Ferias & Street Fairs', icon: '🎪' },
    { label: 'Latin Food Fairs', icon: '🍽️' },
    { label: 'Folkloric Dance Shows', icon: '🩰' },
];

const reasonsForJoining = [
    {
        emoji: '🎉',
        title: 'Here to party',
        // description: 'I’m looking for a good fete',
    },
    {
        emoji: '💬',
        title: 'Here to Link Up',
        // description: 'I’m looking for someone nice. Nothing serious.',
    },
    {
        emoji: '😊',
        title: 'Here to casual chat',
        // description: 'I’m here for nothing serious',
    },
    {
        emoji: '❤️',
        title: 'Looking for something serious',
        // description: 'I’m here looking for a serious relationship.',
    },
    {
        emoji: '🎫',
        title: 'Just Here for Events',
        description: 'I’m here to browse and buy tickets—not looking to connect right now.',
    },
    {
        emoji: '❤️',
        title: 'Here to make friends',
        description: 'I’m here to meet friends from the region.',
    },
];

const { caribbeans } = defineProps<{
    caribbeans: Array<{ name: string }>;
}>();
const isRootAll = ref(true);
watch(isRootAll, (newValue) => {
    if (isRootAll.value) {
        form.link_me_with_country_name = 'all';
    } else {
        form.link_me_with_country_name = '';
    }
});
const baseUser = computed(() => page?.props?.auth?.user?.user ?? page?.props?.auth?.user);
const userId = baseUser.value?.id;
// For new users without an ID, always start at step 2 (don't load from localStorage)
const WIZARD_STEP_KEY = userId ? `linkup_wizard_current_step_${userId}` : null;
const savedStep = WIZARD_STEP_KEY ? localStorage.getItem(WIZARD_STEP_KEY) : null;
const currentStep = ref(savedStep ? parseInt(savedStep, 10) : 2);
const savedForm = localStorage.getItem('wizard_form');
const parsedForm = savedForm ? JSON.parse(savedForm) : null;

const form = useForm({
    // Step 1 data
    avatar: null,
    phone_number: parsedForm?.phone_number ?? '',
    interests: parsedForm?.interests ?? ['Cooking'],
    language: parsedForm?.language ?? '',

    // Step 2 data
    country: parsedForm?.country ?? '',
    gender: parsedForm?.gender ?? '',
    birthday: parsedForm?.birthday ?? '',
    state: parsedForm?.state ?? '',
    city: parsedForm?.city ?? '',
    caribbean_interest: parsedForm?.caribbean_interest ?? '',
    link_me_with: parsedForm?.link_me_with ?? 'female',
    age_filter: parsedForm?.age_filter ?? [18, 50],
    distance_filter: parsedForm?.distance_filter ?? [0, 10],
    age: parsedForm?.age ?? null,

    // Step 3 data
    link_me_with_country_name: parsedForm?.link_me_with_country_name ?? 'all',
    about_me: parsedForm?.about_me ?? '',
    job: parsedForm?.job ?? '',
    university: parsedForm?.university ?? '',
    // Step 5 data
    whyare: parsedForm?.whyare ?? '', // This is the new field added for the last step
    more_photos: [],
});

const otpOpen = ref(false);
const otpToken = ref<string | undefined>(undefined);
const otpUser = computed(() => {
    const u = baseUser.value || {};
    return {
        ...u,
        phone: form.phone_number || u.phone,
    };
});

function handleOtpVerified() {
    otpOpen.value = false;

    currentStep.value = 5;

    if (WIZARD_STEP_KEY) {
        localStorage.setItem(WIZARD_STEP_KEY, '5');
    }
}

function setError(field: string, message: string) {
    form.errors[field as keyof typeof form.errors] = message;
}

async function nextStep() {
    if (currentStep.value === 1 && !form.avatar) {
        setError('avatar', 'Please upload your profile picture');
        return;
    }
    if (currentStep.value === 2) {
        if (!form.country) {
            setError('country', 'Please select a country');
            return;
        }
        if (!form.state) {
            setError('state', 'Please select a state');
            return;
        }
        if (!form.city) {
            setError('city', 'Please select a city');
            return;
        }
    }
    if (currentStep.value === 3 && !form.language) {
        console.log(form.country)
        console.log(form.state)
        console.log(form.city)
        setError('language', 'Please select at least one language');
        return;
    }
    if (currentStep.value === 4) {
        if (!form.phone_number) {
            setError('phone_number', 'Please enter your phone number');
            return;
        }

        try {
            const res = await axios.post(route('change.phone'), { phone: form.phone_number });
            otpToken.value = res?.data?.token;
        } catch (e: any) {
            setError('phone_number', e?.response?.data?.message || 'Unable to send OTP to this phone number');
            return;
        }

        otpOpen.value = true;
        return;
    }
    if (currentStep.value === 5 && !form.birthday) {
        setError('birthday', 'Please select your birthdate');
        return;
    }
    if (currentStep.value === 6 && !form.gender) {
        setError('gender', 'Please select your gender');
        return;
    }
    if (currentStep.value === 7 && (!form.distance_filter || form.distance_filter[1] === undefined)) {
        setError('distance_filter', 'Please set your distance preference');
        return;
    }
    if (currentStep.value === 8 && (!form.age_filter || form.age_filter[1] === undefined)) {
        setError('age_filter', 'Please set your age range preference');
        return;
    }
    if (currentStep.value === 9 && !form.link_me_with) {
        setError('link_me_with', 'Please select who you want to link up with');
        return;
    }
    if (currentStep.value === 10 && !form.link_me_with_country_name) {
        setError('link_me_with_country_name', 'Please select a country');
        return;
    }
    if (currentStep.value === 11 && !form.caribbean_interest) {
        setError('caribbean_interest', 'Please select your country of origin');
        return;
    }
    if (currentStep.value === 12 && !form.job) {
        setError('job', 'Please enter your profession');
        return;
    }
    if (currentStep.value === 13 && !form.university) {
        setError('university', 'Please enter your university');
        return;
    }
    if (currentStep.value === 14 && !form.whyare) {
        setError('whyare', 'Please select why you are here');
        return;
    }
    if (currentStep.value === 15) {
        if (!form.about_me) {
            setError('about_me', 'Please tell us about yourself');
            return;
        }
        if (!form.interests || form.interests.length === 0) {
            setError('interests', 'Please select at least one interest');
            return;
        }
    }
    const uploadedPhotosCount = form.more_photos.filter(file => file && file instanceof File).length;
    if (currentStep.value === 16 && uploadedPhotosCount < 3) {
        setError('more_photos', 'Please upload at least 3 photos');
        return;
    }
    if (currentStep.value >= 16 && uploadedPhotosCount > 2) {
        form.transform((data) => ({
            ...data,
            more_photos: data.more_photos.filter(file => file && file instanceof File),
        })).post(route('frontend.wizard.update'), {
            onSuccess: () => {
                // Clear saved step when wizard is completed (only if key exists)
                if (WIZARD_STEP_KEY) {
                    localStorage.removeItem(WIZARD_STEP_KEY);
                }
            }
        });
    }
    if (currentStep.value < 16) {
        currentStep.value++;
    }
}

onMounted(async () => {
    await fetchCountries();

    if (form.country) {
        selectedCountry.value = form.country;
        await fetchStates(form.country);

        if (form.state) {
            selectedState.value = form.state;
            await fetchCities(form.country, form.state);
        }
    }

    // Reset OTP modal state on mount to prevent it from opening unexpectedly
    otpOpen.value = false;
    otpToken.value = undefined;

    // If the user's phone is already verified, make sure they are at least at step 5
    if (baseUser.value?.phone_verified_at && currentStep.value <= 4) {
        currentStep.value = 5;
        if (WIZARD_STEP_KEY) {
            localStorage.setItem(WIZARD_STEP_KEY, '5');
        }
    }

    // Save step to localStorage whenever it changes (only if user has an ID)
    if (WIZARD_STEP_KEY) {
        watch(currentStep, (newStep) => {
            localStorage.setItem(WIZARD_STEP_KEY, newStep.toString());
        });
    }
});
watch(
    () => form.data(),
    (value) => {
        localStorage.setItem('wizard_form', JSON.stringify(value));
    },
    { deep: true }
);
watch(
    () => form.birthday,
    (newValue) => {
        if (!newValue) return;

        const today = new Date();
        const birthDate = new Date(newValue);

        const age = today.getFullYear() - birthDate.getFullYear();
        if (age < 18) {
            setError('birthday', 'You are Under 18');
            // return;
            form.birthday = '';
        } else {
            return;
        }

    },
);
// start fetching countries on component mount
const handleCountryChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    selectedCountry.value = target.value;
    form.state = '';
    form.city = '';
};

const handleStateChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    selectedState.value = target.value;
    form.city = '';
};
const languagesFilter = (language: string) => {
    form.language = language;
};

function toggleInterest(label: string) {
    if (form.interests.includes(label)) {
        form.interests = form.interests.filter((i) => i !== label);
    } else {
        form.interests.push(label);
    }
}

function surpriseMe() {
    const suggestions = [
        'Passionate about Caribbean culture, live music, and community events.',
        'Love exploring Latin American festivals, cooking classes, and beach gatherings.',
        'Enjoy volunteering at church events, attending concerts, and traveling to new destinations.',
    ];
    form.about_me = suggestions[Math.floor(Math.random() * suggestions.length)];
}
function mySelections() {
    form.about_me = 'I love music festivals, dance events, and cultural gatherings.';
}
</script>
<style scoped>
.hero_section {
    background-image: url('./../../assets/images/bg-image2.png');
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center;
    width: 100%;
    height: fit-content;
    /* Or a fixed height like 600px */
    padding-bottom: 60px;
    position: relative;
    overflow: auto;
}

.dp__theme_light {
    --dp-primary-color: #2563eb;
    --dp-primary-disabled-color: #93c5fd;
    --dp-background-color: #f9fafb;
    --dp-text-color: #1f2937;
    --dp-border-radius: 1rem;
    --dp-cell-border-radius: 0.75rem;
}

input:focus-visible {
    outline: none !important;
    /* border: none; */
}

select:focus-visible {
    outline: none !important;
    /* border: none; */
}

.checkboxFour {
    width: 18px;
    height: 18px;
    background: #ddd;
    border-radius: 100%;
    position: relative;
    outline: 1px solid rgb(74, 161, 196);
    /* box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.5); */
}

.checkboxFour input[type='checkbox'] {
    visibility: hidden;
}

.checkboxFour label {
    display: block;
    width: 13px;
    height: 13px;
    border-radius: 100px;
    transition: all 0.3s ease;
    cursor: pointer;
    position: absolute;
    top: 3.5px;
    left: 3px;
    background: #33333311;
    /* box-shadow: inset 0px 1px 3px rgba(0, 0, 0, 0.5); */
}

.checkboxFour input[type='checkbox']:checked+label {
    background: rgb(41, 125, 158);
}
</style>
