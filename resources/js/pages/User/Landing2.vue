<template>
    <div class="bg-blue-500">
        <header class="hero_section bg-center sm:bg-right">
            <Header />
            <!-- start hero section -->
            <section>
                <div class="container mx-auto my-8 px-4 sm:my-10 sm:px-6 lg:my-12 lg:px-21.25">
                    <div class="md:max-w-3xl">
                        <h1 class="text-xl font-bold leading-tight sm:text-2xl md:text-3xl md:leading-16 lg:text-5xl">
                            LINKING THE <span class="text-primary-front">CARIBBEAN</span> AND
                            <span class="text-primary-front">LATIN AMERICAN PEOPLE</span> EVERYWHERE!
                        </h1>
                        <h2 class="mt-4 text-sm text-white sm:text-base md:text-xl lg:text-2xl">
                            Join LinkUp to connect with individuals from the
                            <br class="hidden md:block" />
                            Caribbean and Latin America worldwide.
                        </h2>
                        <div class="mt-6 flex flex-col gap-2 text-sm sm:flex-row sm:gap-3">
                            <button @click="handelRegister"
                                class="bg-primary-front rounded-full px-6 py-2 font-semibold text-blue-500 cursor-pointer">
                                GET STARTED
                            </button>
                            <button @click="handelRegister"
                                class="rounded-full border border-white px-6 py-2 font-semibold text-white cursor-pointer">
                                KNOW MORE
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <!-- end hero section -->

            <!-- Desktop / large-tablet step row. Switched from md: to lg: because three
                 350px-wide boxes do not fit inside a 768px (md) viewport without overflowing. -->
            <div class="container mx-auto -mb-27.25 hidden px-4 lg:block">
                <div class="flex flex-col items-center justify-center gap-5 lg:flex-row lg:gap-10 xl:gap-20">
                    <div
                        class="bg-primary-front flex w-full max-w-87.5 items-center justify-center gap-3 rounded-lg py-9">
                        <p
                            class="text-primary-front flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-500 text-3xl font-bold">
                            1
                        </p>
                        <p class="text-2xl text-blue-500">Create Account</p>
                    </div>
                    <div
                        class="bg-primary-front flex w-full max-w-87.5 items-center justify-center gap-3 rounded-lg py-9">
                        <p
                            class="text-primary-front flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-500 text-3xl font-bold">
                            2
                        </p>
                        <p class="text-2xl text-blue-500">Find Matches</p>
                    </div>
                    <div
                        class="bg-primary-front flex w-full max-w-87.5 items-center justify-center gap-3 rounded-lg py-9">
                        <p
                            class="text-primary-front flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-500 text-3xl font-bold">
                            3
                        </p>
                        <p class="text-2xl text-blue-500">Start Dating</p>
                    </div>
                </div>
            </div>
        </header>
    </div>

    <!-- Mobile + tablet step section. Now shows up to lg (not just md), and the boxes
         use max-width instead of a fixed width so they shrink on narrow phones instead
         of overflowing horizontally. -->
    <section class="bg-white py-10 text-white lg:hidden">
        <div class="container mx-auto px-4">
            <div class="flex flex-col items-center justify-center gap-5 sm:flex-row sm:flex-wrap sm:gap-6">
                <div class="bg-primary-front flex w-full max-w-87.5 items-center justify-center gap-3 rounded-lg py-9">
                    <p
                        class="text-primary-front flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-500 text-3xl font-bold">
                        1
                    </p>
                    <p class="text-2xl text-blue-500">Create Account</p>
                </div>
                <div class="bg-primary-front flex w-full max-w-87.5 items-center justify-center gap-3 rounded-lg py-9">
                    <p
                        class="text-primary-front flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-500 text-3xl font-bold">
                        2
                    </p>
                    <p class="text-2xl text-blue-500">Find Matches</p>
                </div>
                <div class="bg-primary-front flex w-full max-w-87.5 items-center justify-center gap-3 rounded-lg py-9">
                    <p
                        class="text-primary-front flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-500 text-3xl font-bold">
                        3
                    </p>
                    <p class="text-2xl text-blue-500">Dating</p>
                </div>
            </div>
        </div>
    </section>

    <!-- "Find Your Perfect Vibe" section. Removed the col-span-2 classes that were sitting
         on top of a grid-cols-1 container on mobile/tablet — a span larger than the number
         of declared columns forces the browser to create an extra implicit column, which is
         what was breaking the layout below lg. -->
    <section class="bg-gray-200 pt-12 pb-10 sm:pt-16 lg:pt-20">
        <div class="container mx-auto grid grid-cols-1 gap-8 px-4 lg:grid-cols-12 lg:gap-0">
            <div class="relative flex flex-wrap justify-center lg:col-span-5 lg:h-110">
                <img src="../../../assets/images/girl1.png" alt="Person 1" class="image-1" />
                <img src="../../../assets/images/girl2.jpg" alt="Person 2" class="image-2" />
                <img src="../../../assets/images/girl3.png" alt="Person 3" class="image-3" />
            </div>
            <div class="lg:col-span-7">
                <h2 class="mb-4 text-xl font-bold text-black sm:text-2xl lg:text-4xl">
                    Find Your Perfect Vibe — Any Age, Any Island
                </h2>
                <p class="mb-4 text-base text-black sm:text-lg lg:text-2xl">
                    Whether you’re searching for lifelong love, a new friend to explore the city
                    with, or a travel buddy for Carnival season, LinkUp makes it effortless. Our
                    smart matching celebrates the rich rhythms and languages of the Caribbean and
                    Latin America, so you’re always just one click away from someone who gets your
                    culture, your jokes, and your playlist.
                </p>
                <h3 class="text-xl font-bold text-black sm:text-2xl lg:text-4xl">
                    More Than Matches — Events, Tickets & Good Times
                </h3>
                <p class="text-base text-black sm:text-lg lg:text-2xl">
                    From beach fêtes in Barbados to salsa nights in Bogotá, discover what’s
                    happening right now—and snag your tickets without ever leaving the app. We
                    partner with trusted promoters across the region, so you’ll never miss a lime,
                    concert, or culinary festival again.
                </p>
            </div>
        </div>
    </section>

    <!-- "Why LinkUp" section. Swapped the fixed h-250/md:h-100 (which forced a hard
         1000px box on phones, regardless of how much text actually wrapped) for padding-driven
         height, so the section is always exactly as tall as its content on every screen. -->
    <section class="Whyplatform relative py-14 sm:py-16 md:py-20">
        <div class="absolute top-0 left-0 h-full w-full bg-[rgba(255,255,255,0.8)]"></div>
        <div class="relative container mx-auto px-4 text-center">
            <h3 class="text-2xl font-bold text-black sm:text-3xl">Why LinkUp is the best Platform</h3>
            <p class="mt-4 text-base text-black sm:text-xl lg:text-2xl">
                LinkUp connects Caribbean and Latin American people everywhere through culture, community, and fun. From
                finding
                love or friendship to discovering local events and festivals, LinkUp makes it easy to meet real people
                who share
                your vibe, roots, and rhythm — all in one app.
            </p>
            <div
                class="mx-auto mt-8 grid grid-cols-2 gap-6 sm:grid-cols-3 sm:gap-8 md:flex md:flex-row md:justify-around md:gap-10 lg:max-w-250">
                <div>
                    <img src="../../../assets/images/5.png" alt="Event Tickets"
                        class="mx-auto mb-2 h-12 w-12 sm:h-16 sm:w-16" />
                    <p class="text-base font-bold text-sky-500 sm:text-xl lg:text-2xl">Get Event Tickets</p>
                </div>
                <div>
                    <img src="../../../assets/images/4.png" alt="Find Club/Restaurants"
                        class="mx-auto mb-2 h-12 w-12 sm:h-16 sm:w-16" />
                    <p class="text-base font-bold text-sky-500 sm:text-xl lg:text-2xl">Find Club / Restaurants</p>
                </div>
                <div>
                    <img src="../../../assets/images/3.png" alt="Linkup Live"
                        class="mx-auto mb-2 h-12 w-12 sm:h-16 sm:w-16" />
                    <p class="text-base font-bold text-sky-500 sm:text-xl lg:text-2xl">Linkup Live</p>
                </div>
                <div>
                    <img src="../../../assets/images/2.png" alt="Wallet"
                        class="mx-auto mb-2 h-12 w-12 sm:h-16 sm:w-16" />
                    <p class="text-base font-bold text-sky-500 sm:text-xl lg:text-2xl">LinkUp Wallet</p>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <img src="../../../assets/images/1.png" alt="Caribbean 360 News"
                        class="mx-auto mb-2 h-12 w-12 sm:h-16 sm:w-16" />
                    <p class="text-base font-bold text-sky-500 sm:text-xl lg:text-2xl">Caribbean 360 News</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="bg-white px-4 py-12 sm:px-6 sm:py-14 dark:bg-transparent">
        <div class="container mx-auto lg:px-21.25">
            <h2 class="mb-8 text-center text-xl font-bold sm:mb-10 sm:text-2xl lg:text-4xl">Testimonials</h2>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg p-6 shadow-[0_3px_10px_rgb(0,0,0,0.2)] sm:p-10">
                    <div class="cardHeader flex flex-wrap items-center gap-3">
                        <img class="border-primary-front mb-2 h-20 w-20 rounded-full border-2 object-cover sm:h-30 sm:w-30"
                            src="https://picsum.photos/200" />
                        <div class="text-left">
                            <p>
                                <span class="font-bold">Francis G. & Francine B.</span> – Nassau ↔
                                Kingston
                            </p>
                            <div v-for="n in 5" :key="n" class="text-primary-front inline text-xl sm:text-2xl">
                                <i class="ri-star-fill"></i>
                            </div>
                        </div>
                    </div>

                    <p class="mt-2 text-left text-base italic sm:text-lg lg:text-2xl">
                        “We matched on LinkUp the week before Junkanoo. One ticket purchase and a
                        quick flight later, we were dancing side-by-side on Bay Street. Six months in,
                        we’re still splitting flight costs with the in-app money transfer—and planning
                        Carnival in Jamaica next!”
                    </p>
                </div>
                <div class="rounded-lg p-6 shadow-[0_3px_10px_rgb(0,0,0,0.2)] sm:p-10">
                    <div class="cardHeader flex flex-wrap items-center gap-3">
                        <img class="border-primary-front mb-2 h-20 w-20 rounded-full border-2 object-cover sm:h-30 sm:w-30"
                            src="https://picsum.photos/200" />
                        <div class="text-left">
                            <p>
                                <span class="font-bold">Janelle T. & Andre M. </span>– Toronto Diaspora
                            </p>
                            <div v-for="n in 5" :key="n" class="text-primary-front inline text-xl sm:text-2xl">
                                <i class="ri-star-fill"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-left text-base italic sm:text-lg lg:text-2xl">
                        “We filtered for Trinidadians in Canada and connected instantly. From
                        streaming Soca news to snagging last-minute concert passes, LinkUp keeps us
                        rooted in our culture—together—even 2,600 km from Port of Spain.”
                    </p>
                </div>
                <div class="rounded-lg p-6 shadow-[0_3px_10px_rgb(0,0,0,0.2)] sm:p-10">
                    <div class="cardHeader flex flex-wrap items-center gap-3">
                        <img class="border-primary-front mb-2 h-20 w-20 rounded-full border-2 object-cover sm:h-30 sm:w-30"
                            src="https://picsum.photos/200" />
                        <div class="text-left">
                            <p><span class="font-bold"> Luis R.</span> – Bogotá → Miami</p>
                            <div v-for="n in 5" :key="n" class="text-primary-front inline text-xl sm:text-2xl">
                                <i class="ri-star-fill"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-left text-base italic sm:text-lg lg:text-2xl">
                        “I moved to Florida for work and knew zero people here. LinkUp helped me find
                        a Colombian meet-up, buy the event tickets in one tap, and wire a birthday
                        gift back home—all before payday. It’s my social life and my remittance app
                        combined.”
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="bg-primary-front px-4 py-6 text-center sm:py-5">
            <h1 class="text-xl font-bold sm:text-2xl lg:text-4xl dark:text-black">
                Connect with your perfect Soulmate here, on linkUp.
            </h1>
            <h2 class="py-2 text-lg sm:text-2xl lg:text-3xl dark:text-black">
                Anytime anywhere
            </h2>
            <button @click="handelRegister"
                class="text-primary-front my-3 rounded-md bg-blue-500 px-5 py-1 text-lg font-bold sm:text-xl md:text-2xl cursor-pointer">
                GET STARTED
            </button>
        </div>
    </section>
    <div>
        <Footer1 />
    </div>
    <!-- end footer -->
</template>
<script setup lang="ts">
import Footer1 from "@/components/footer1.vue";
import Header from "@/components/front/Header.vue";
import * as PusherPushNotifications from "@pusher/push-notifications-web";
import { router } from "@inertiajs/vue3";

const beamsClient = new PusherPushNotifications.Client({
    instanceId: "b23bf468-a07b-4dfb-b9e5-ba75cd1cef53",
});


const handelRegister = () => {
    router.visit(route('register'));
}
beamsClient
    .start()
    .then(() => beamsClient.addDeviceInterest("debug-linkup"))
    .then(() => console.log("Successfully registered and subscribed!"))
    .catch(console.error);
</script>
<style scoped>
.hero_section {
    background-image: url(./../../../assets/images/hero3.png);
    background-repeat: no-repeat;
    width: 100%;
    background-size: cover;
    padding-bottom: 40px;
}

@media (min-width: 768px) {
    .hero_section {
        padding-bottom: 60px;
    }
}

.Whyplatform {
    background-image: url(../../../assets/images/girl3.png);
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
}

li {
    list-style: circle;
    margin-left: 25px;
}

ul span {
    font-weight: bold !important;
}

/* Section styles */
.bg-gray-200 {
    background-color: #e5e7eb;
}

/* Image styles — sized down for small phones, scaling up at each breakpoint,
   and only switching to absolute (overlapping) positioning once there's enough
   room at lg, same as before. */
.image-1,
.image-2,
.image-3 {
    width: 8.5rem;
    margin: 0.375rem;
    border-radius: 0.5rem;
}

@media (min-width: 480px) {

    .image-1,
    .image-2,
    .image-3 {
        width: 10.5rem;
        margin: 0.5rem;
    }
}

@media (min-width: 768px) {

    .image-1,
    .image-2,
    .image-3 {
        width: 12.5rem;
    }
}

@media (min-width: 1024px) {

    .image-1,
    .image-2,
    .image-3 {
        position: absolute;
    }

    .image-1 {
        left: 0rem;
    }

    .image-2 {
        top: 15rem;
        left: 5rem;
    }

    .image-3 {
        top: 4rem;
        left: 10rem;
    }
}

@media (min-width: 1536px) {

    .image-1,
    .image-2,
    .image-3 {
        position: absolute;
    }

    .image-1 {
        left: 7rem;
    }

    .image-2 {
        top: 15rem;
        left: 10rem;
    }

    .image-3 {
        top: 4rem;
        left: 21rem;
    }
}

@media (min-width: 1024px) and (max-width: 1280px) {
    .cardHeader {
        flex-wrap: wrap !important;
    }
}
</style>
