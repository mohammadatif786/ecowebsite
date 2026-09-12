<template>
    <Dialog class="overflow-y-auto">
        <DialogTrigger as-child>
            <Button class="ms-auto w-fit font-medium bg-transparent hover:bg-blue-600">
                <span class="text-white">Apply Filters</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-funnel-icon lucide-funnel text-white">
                    <path
                        d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z" />
                </svg>
            </Button>
        </DialogTrigger>
        <DialogScrollContent
            class="bg-[#0A1E45]/95 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl text-white">
            <DialogHeader>
                <DialogTitle>Apply Filters</DialogTitle>
            </DialogHeader>
            <div class="space-y-3 text-sm">
                <!-- <slot></slot> -->

                <div class="flex flex-col px-1">
                    <h3 class="text-sm font-semibold text-white mb-3 tracking-wide">Age Range</h3>

                    <SliderRoot v-model="form.age" class="relative flex h-5 touch-none items-center select-none"
                        :max="100" :min="18" :step="1">
                        <SliderTrack class="bg-primary-front relative h-[3px] grow rounded-full">
                            <SliderRange class="absolute h-full rounded-full bg-blue-500" />
                        </SliderTrack>
                        <SliderThumb v-for="thumb in form.age.length" :key="thumb"
                            class="shadow-blackA7 hover:bg-violet3 focus:shadow-blackA8 block h-5 w-5 rounded-[10px] bg-blue-500 shadow-[0_2px_10px] focus:shadow-[0_0_0_5px] focus:outline-none"
                            aria-label="Volume" />
                    </SliderRoot>

                    <div class="mt-2 flex items-center justify-between">
                        <NumberFieldRoot v-model="form.age[0]" :max="form.age[1]">
                            <NumberFieldInput
                                class="bg-card border-muted text-foreground w-12 rounded border py-1.5 text-center text-xs font-semibold" />
                        </NumberFieldRoot>
                        <NumberFieldRoot v-model="form.age[1]" :min="form.age[0]">
                            <NumberFieldInput
                                class="bg-card border-muted text-foreground w-12 rounded border py-1.5 text-center text-xs font-semibold" />
                        </NumberFieldRoot>
                    </div>
                </div>
                <div>
                    <div class="flex flex-col px-1">
                        <h3 class="text-sm font-semibold text-white mb-3 tracking-wide">Distance Range</h3>
                        <SliderRoot v-model="form.distance"
                            class="relative flex h-5 touch-none items-center select-none" :max="100" :step="1">
                            <SliderTrack class="bg-primary-front relative h-[3px] grow rounded-full">
                                <SliderRange class="absolute h-full rounded-full bg-blue-500" />
                            </SliderTrack>
                            <SliderThumb v-for="thumb in form.distance.length" :key="thumb"
                                class="shadow-blackA7 hover:bg-violet3 focus:shadow-blackA8 block h-5 w-5 rounded-[10px] bg-blue-500 shadow-[0_2px_10px] focus:shadow-[0_0_0_5px] focus:outline-none"
                                aria-label="Volume" />
                        </SliderRoot>

                        <div class="mt-2 flex items-center justify-between">
                            <NumberFieldRoot v-model="form.distance[0]" :max="form.distance[1]">
                                <NumberFieldInput
                                    class="bg-card border-muted text-foreground w-12 rounded border py-1.5 text-center text-xs font-semibold" />
                            </NumberFieldRoot>
                            <NumberFieldRoot v-model="form.distance[1]" :min="form.distance[0]">
                                <NumberFieldInput
                                    class="bg-card border-muted text-foreground w-12 rounded border py-1.5 text-center text-xs font-semibold" />
                            </NumberFieldRoot>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white mb-3 tracking-wide">Search Preferences</h3>
                    <div class="mt-2 flex gap-2">
                        <span v-for="preference in preferences" :class="[
                            'hover:border-primary-front cursor-pointer rounded-2xl border border-blue-500 p-1 px-4',
                            form.preferences.includes(preference) ? 'bg-primary-front text-white' : '',
                        ]" @click="genderFilter(preference)">{{ preference }}</span>
                    </div>
                </div>
                <hr />

                <div>
                    <h3 class="text-sm font-semibold text-white mb-3 tracking-wide">Choose a Caribbean or Latin American
                        Country to See People from</h3>
                    <div class="mt-2 flex items-center gap-2">
                        <div>
                            <input type="radio" id="allSee" class="me-1" :value="true" v-model="isSeePeople" />
                            <label for="allSee">All</label>
                        </div>
                        <div>
                            <input type="radio" id="otherSee" class="me-1" :value="false" v-model="isSeePeople" />
                            <label for="otherSee">Other</label>
                        </div>
                    </div>
                    <select v-if="!isSeePeople" v-model="form.caribbean_interest"
                        class="mt-2 w-full rounded-2xl border border-blue-500 p-1 px-4 ">
                        <option value="">Select</option>
                        <option v-for="country in caribbeanCountry" :value="country.name" class="text-black">{{
                            country.name }}</option>
                    </select>
                </div>

                <hr />
                <div>
                    <h3 class="text-sm font-semibold text-white mb-3 tracking-wide">Interests</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span v-for="interest in interests" :key="interest" :class="[
                            'hover:border-primary-front cursor-pointer rounded-2xl border border-blue-500 p-1 px-4',
                            form.interests.includes(interest) ? 'bg-primary-front text-white' : '',
                        ]" @click="InteresetFilter(interest)">{{ interest }}</span>
                    </div>
                </div>
                <hr />
                <div>
                    <h3 class="text-sm font-semibold text-white mb-3 tracking-wide">Languages I Know</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span v-for="language in languages" :key="language"
                            class="hover:border-primary-front cursor-pointer rounded-2xl border border-blue-500 p-1 px-4"
                            :class="[
                                'hover:border-primary-front cursor-pointer rounded-2xl border border-blue-500 p-1 px-4',
                                form.languages.includes(language) ? 'bg-primary-front text-white' : '',
                            ]" @click="languageFilter(language)">{{ language }}</span>
                    </div>
                </div>
                <hr />
                <div>
                    <h3 class="text-sm font-semibold text-white mb-3 tracking-wide">Religion</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span v-for="religion in religions" :key="religion" :class="[
                            'hover:border-primary-front cursor-pointer rounded-2xl border border-blue-500 p-1 px-4',
                            form.religions.includes(religion) ? 'bg-primary-front text-white' : '',
                        ]" @click="religionFilter(religion)">{{ religion }}</span>
                    </div>
                </div>
                <hr />
                <div>
                    <h3 class="text-sm font-semibold text-white mb-3 tracking-wide">Relationship Goals</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span v-for="relationship in relationshipGoals" :key="relationship" :class="[
                            'hover:border-primary-front cursor-pointer rounded-2xl border border-blue-500 p-1 px-4',
                            form.relationships.includes(relationship) ? 'bg-primary-front text-white' : '',
                        ]" @click="relationshipFilter(relationship)">{{ relationship }}</span>
                    </div>
                </div>
                <hr />
            </div>
            <DialogFooter>
                <DialogClose as-child>
                    <Button class="font-medium mb-5" @click="
                                {
                        $emit('apply');
                        form.reset();
                    }
                        ">Reset</Button>

                    <Button class="font-medium" @click="
                        () => {
                            handleForm();
                            $emit('apply');
                        }
                    ">Apply</Button>
                </DialogClose>
            </DialogFooter>
        </DialogScrollContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/front/ui/button';
import { Dialog, DialogClose, DialogFooter, DialogHeader, DialogScrollContent, DialogTrigger } from '@/components/front/ui/dialog';
import { useCountryStateCity } from '@/composables/useCountryStateCity';
import { Link, useForm } from '@inertiajs/vue3';
import { NumberFieldInput, NumberFieldRoot, SliderRange, SliderRoot, SliderThumb, SliderTrack } from 'reka-ui';
import { onMounted, ref, watch } from 'vue';

const distance = ref([0, 100]);
const age = ref([15, 40]);

const { countries, fetchCountries } = useCountryStateCity();
const { url, filters, caribbeanCountry } = defineProps<{
    url: string;
    filters?: any;
    caribbeanCountry: any;
}>();
const isRootAll = ref(true);
const isSeePeople = ref(true);
watch(isRootAll, (newValue) => {
    if (isRootAll.value) {
        form.link_me_with_country_name = 'all';
    }
});
watch(isSeePeople, (newValue) => {
    if (isSeePeople.value) {
        form.caribbean_interest = 'all';
    }
});

// const { fetchCountries } = useCountryStateCity;
const form = useForm({
    age: filters?.age ? filters?.age : [15, 40],
    distance: filters?.distance ? filters?.distance : [0, 100],
    preferences: filters?.preference ? filters?.preference : ([] as string[]),
    link_me_with_country_name: filters?.link_me_with_country_name ? filters?.link_me_with_country_name : '',
    interests: filters?.interests ? filters?.interests : ([] as string[]),
    languages: filters?.languages ? filters?.languages : ([] as string[]),
    caribbean_interest: filters?.caribbean_interest ? filters?.caribbean_interest : '',

    religions: filters?.religions ? filters?.religions : ([] as string[]),
    relationships: filters?.relationships ? filters?.relationships : ([] as string[]),

    networkingOptions: filters?.networkingOptions ? filters?.networkingOptions : ([] as string[]),
    verificationStatus: filters?.verificationStatus ? filters?.verificationStatus : ([] as string[]),
});
const languages = ['English', 'Spanish', 'French', 'Haitian Creole', 'Dutch', 'Papiamento', 'Portuguese', 'Sranan Tongo'];

const interests = [
    // General
    'Music Festivals',
    'Travel',
    'Cooking',
    'Books',
    'Yoga',
    'Movies',
    'Wine',
    'Church Events',

    // Caribbean-specific
    'Liming',
    'Carnival / Mas',
    'Junkanoo',
    'Soca Fêtes',
    'Reggae & Dancehall',
    'Kompa / Zouk',
    'Steel Pan',
    'Boat Fêtes',
    'Beach Lime / Bonfire',
    'Food & Rum Festivals',
    'Crop Over',
    'J’ouvert',
    'Rake & Scrape',

    // Latin America-specific
    'Salsa Socials',
    'Bachata Nights',
    'Reggaetón Parties',
    'Cumbia & Vallenato',
    'Samba Blocos / Pagode',
    'Forró Nights',
    'Mariachi / Regional Mexicano',
    'Día de los Muertos (festivals)',
    'Ferias & Street Fairs',
    'Latin Food Fairs',
    'Folkloric Dance Shows',
];


const religions = ['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Judaism', 'Sikhism'];
const relationshipGoals = [
    'Here to party',
    'Here to Link Up',
    'Here to casual chat',
    'Looking for something serious',
    'Just Here for Events',
    'Here to make friends',
];
const Caribbeans = ['Cuba', 'Jamaica', 'Dominican Republic', 'Mexico', 'Brazil', 'Argentina'];
const verificationStatus = ['Unverified', 'Verified'];
const preferences = ['Male', 'Female', 'Both'];

onMounted(() => {
    fetchCountries();
});
const genderFilter = (gender: string) => {
    // console.log('CaribbeanFilter called with gender:', gender, 'and index:', genderIndex);

    const genderIndex = form.preferences.indexOf(gender);

    if (genderIndex !== -1) {
        form.preferences.splice(genderIndex, 1); // Remove the gender if it exists
    } else {
        form.preferences.push(gender); // Add the country if it doesn't exist
    }
};

const InteresetFilter = (interest: string) => {
    const interestIndex = form.interests.indexOf(interest);
    if (interestIndex !== -1) {
        form.interests.splice(interestIndex, 1);
    } else {
        form.interests.push(interest);
    }
};

const languageFilter = (lang: string) => {
    const languageIndex = form.languages.indexOf(lang);
    if (languageIndex !== -1) {
        form.languages.splice(languageIndex, 1);
    } else {
        form.languages.push(lang);
    }
};

const religionFilter = (religion: string) => {
    const religionIndex = form.religions.indexOf(religion);
    if (religionIndex !== -1) {
        form.religions.splice(religionIndex, 1);
    } else {
        form.religions.push(religion);
    }
};

const relationshipFilter = (relationship: string) => {
    const relationshipIndex = form.relationships.indexOf(relationship);
    if (relationshipIndex !== -1) {
        form.relationships.splice(relationshipIndex, 1);
    } else {
        form.relationships.push(relationship);
    }
};
const verifyFilter = (verify: string) => {
    const verifyIndex = form.verificationStatus.indexOf(verify);
    if (verifyIndex !== -1) {
        form.verificationStatus.splice(verifyIndex, 1);
    } else {
        form.verificationStatus.push(verify);
    }
};

const handleForm = () => {
    form.get(`/${url}`);
};
</script>
