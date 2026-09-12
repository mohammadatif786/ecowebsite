<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

const props = defineProps({
    drinks: {
        type: Object as () => Record<string, any[]>,
        default: () => ({}),
    },
});

const allTypes = ['mix_drinks', 'soft_drinks', 'waters', 'bottles', 'wines', 'beers'];
const localDrinks = ref(Object.fromEntries(allTypes.map(t => [t, props.drinks[t] ?? []])));

watch(
    () => props.drinks,
    (next) => {
        const source = next || {};
        localDrinks.value = Object.fromEntries(allTypes.map(t => [t, source[t] ?? []]));
    },
    { deep: true }
);

const format = (value: string) => {
    return value
        .replace(/([A-Z])/g, ' $1')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
};

const addDrinks = (type: string) => {
    if (!localDrinks.value[type]) localDrinks.value[type] = [];
    localDrinks.value[type].push({
        temp_id: Date.now(),
        name: '',
        amount: 0,
        type,
    });
};

const removeDrink = (type: string, drink: any) => {
    localDrinks.value[type] = localDrinks.value[type].filter(
        (d: any) => (d.id || d.temp_id) !== (drink.id || drink.temp_id)
    );
};

const emit = defineEmits(['closeDrinkAddonModal', 'drinks-updated']);

const createUpdateDrinks = async () => {
    try {
        const payload: Record<string, any[]> = {};
        for (const type in localDrinks.value) {
            payload[type] = localDrinks.value[type].map((d: any) => ({
                id: d.id,
                name: d.name,
                amount: d.amount,
                type,
            }));
        }

        const response = await axios.post(route('organizer.drink.createUpdate'), payload);
        toast.success(response.data.message || 'Drinks saved successfully');

        if (response.data?.drinks && typeof response.data.drinks === 'object') {
            emit('drinks-updated', response.data.drinks);
        } else {
            emit('drinks-updated', payload);
        }

        emit('closeDrinkAddonModal');

    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Something went wrong');
    }
};
</script>

<template>
    <dialog id="dlg-individual-drinks"
        class="fixed inset-0 m-auto rounded-xl w-[min(96vw,700px)] shadow-2xl backdrop:bg-black/55 p-0">
        <!-- Header -->
        <div class="flex justify-between items-center p-3 border-b border-gray-200">
            <div class="text-lg font-extrabold text-indigo-600">Individual Drinks</div>
            <button @click="emit('closeDrinkAddonModal')"
                class="bg-red-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-red-700 transition duration-150">
                Close
            </button>
        </div>

        <div class="p-3">
            <div v-for="(items, key) in localDrinks" :key="key"
                class="bg-white shadow-md rounded-2xl p-4 mb-5 border border-gray-200">
                <!-- Type header -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">{{ format(key) }}</h2>
                    <button
                        class="bg-indigo-600 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-indigo-700 transition"
                        @click="addDrinks(key)">
                        + Add
                    </button>
                </div>

                <!-- Drinks list -->
                <div class="space-y-3">
                    <div v-for="drink in items" :key="drink.id || drink.temp_id"
                        class="grid grid-cols-12 gap-3 items-center bg-gray-50 p-3 rounded-xl">
                        <div class="col-span-4">
                            <input type="text" v-model="drink.name" placeholder="Drink name"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div class="col-span-3">
                            <input type="number" v-model.number="drink.amount" placeholder="Amount"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div class="col-span-5 flex gap-2 justify-end">
                            <button
                                class="px-3 py-1.5 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
                                @click="removeDrink(key, drink)">
                                Remove
                            </button>
                        </div>
                    </div>

                    <div v-if="items.length === 0" class="text-sm text-gray-400 text-center py-3">No drinks added</div>
                </div>
            </div>

            <div class="flex gap-2 flex-wrap mt-2">
                <button type="button" @click="createUpdateDrinks()"
                    class="bg-green-600 text-white font-semibold py-1 px-3 rounded-lg hover:bg-green-700 transition duration-150">
                    Save Drinks
                </button>
            </div>
        </div>
    </dialog>
</template>

<style>
dialog::backdrop {
    background: rgba(0, 0, 0, 0.55);
}
</style>
