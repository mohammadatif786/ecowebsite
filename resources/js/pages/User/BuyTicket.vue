<template>
  <AuthenticatedLayout>
    <div class="p-5 mx-auto bg-gray-100 rounded-md shadow-md overflow-hidden">
      <img
        src="./../../../assets/images/bgimageBuyticket.png"
        alt="Event"
        class="w-full object-cover"
      />
      <div class="p-4 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex justify-between w-full">
            <h2 class="text-lg font-semibold text-gray-900">
              Floral Singing International
            </h2>
            <div class="flex flex-wrap items-center text-sm text-gray-600 mt-1">
              <div class="flex -space-x-2 overflow-hidden">
                <p
                  class="inline-block size-6 rounded-full ring-2 ring-white bg-sky-300"
                ></p>
                <p
                  class="inline-block size-6 rounded-full ring-2 ring-white bg-sky-300"
                ></p>
                <p
                  class="inline-block size-6 rounded-full ring-2 ring-white bg-sky-300"
                ></p>
              </div>
              <span class="mx-1">78+ Going</span>
              <span
                class="ml-2 bg-lime-300 text-black text-xs font-semibold px-2 py-0.5 rounded-full"
                >INVITE</span
              >
            </div>
          </div>
        </div>
        <div class="text-sm text-gray-600 my-6 flex flex-wrap gap-6 justify-between">
          <div>
            <p class="text-2xl">5th March, 2023</p>
            <p>Tuesday 7:00 PM - 3am</p>
          </div>
          <div>
            <p class="text-2xl">Name Here</p>
            <p>Organizer</p>
          </div>
          <div class="flex gap-7 items-center">
            <div>
              <i
                class="ri-smartphone-fill text-lime-300 text-2xl border rounded-full p-2"
              ></i>
              <p class="text-sm text-center mt-2">call</p>
            </div>
            <div>
              <i
                class="ri-map-pin-fill text-lime-300 text-2xl border rounded-full p-2"
              ></i>
              <p class="text-sm text-center mt-2">call</p>
            </div>
            <div>
              <i
                class="ri-ticket-fill text-lime-300 text-2xl border rounded-full p-2"
              ></i>
              <p class="text-sm text-center mt-2">call</p>
            </div>
          </div>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800 mt-3 text-xl">About Event</h3>
          <p class="text-sm text-gray-600 mt-1">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula
            eget dolor... Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore
            nihil quia eveniet perspiciatis recusandae laborum, voluptate reiciendis vero
            consequuntur a quaerat minus commodi fugiat tenetur consequatur? Commodi totam
            error architecto?
          </p>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800 mt-3 text-xl">EVENT DISCLAIMER</h3>
          <p class="text-sm text-gray-600 mt-1">
            Lorem ipsum dolor sit amet consectetur, adipisicing elit. Sit totam enim
            aliquam? Nobis quaerat repellendus, explicabo assumenda recusandae quisquam
            excepturi temporibus corporis repellat perferendis quasi rem consectetur quod,
            eum nam! Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean
            commodo ligula eget dolor...
          </p>
        </div>
        <button
          class="w-full bg-primary hover:bg-lime-500 text-green-500 py-1 rounded-2xl font-bold px-4 mt-4"
        >
          BUY TICKETS
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
</script>
<style scoped>
/* Optional custom styles */
</style>
