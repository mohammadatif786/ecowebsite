<template>
    <div id="dlg-ticket-drinks" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-hidden hidden">
        <div class="bg-white rounded-[20px] w-full max-w-3xl max-h-[92vh] overflow-hidden shadow-2xl transform transition-transform flex flex-col" @click.stop>
            <div class="flex justify-between items-center p-4 sticky top-0 bg-white/90 backdrop-blur-sm z-10 py-2 border-b border-slate-100">
                <div class="text-xl font-extrabold text-indigo-600">Drink Add-Ons</div>
                <button
                    class="text-slate-500 hover:text-slate-800 transition bg-slate-100 hover:bg-slate-200 p-2 rounded-full"
                    @click="close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="modal-scroll overflow-y-auto p-5 flex-1">
            <!-- Mix Drinks -->
            <div class="flex flex-col gap-2 p-3 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg mb-2">
                <div class="font-bold flex items-center gap-2">Mix Drinks <span>🍹</span></div>
                <div v-for="(item, index) in selectedDrinks.mix_drinks" :key="index"
                    class="flex gap-2 flex-wrap items-center">
                    <select v-model="item.id" @change="updateDrinkSelection('mix_drinks', index)"
                        class="p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select drink</option>
                        <option v-for="drink in drinks?.mix_drinks" :key="drink.id" :value="drink.id">{{ drink.name }} -
                            ${{ drink.amount }}</option>
                    </select>
                    <input type="number" v-model="item.quantity" min="1"
                        class="w-[90px] p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="font-bold text-green-600">Price: ${{ calculatePrice('mix_drinks', item) }}</span>
                    <button @click="removeDrink('mix_drinks', index)"
                        class="bg-red-600 text-white font-semibold py-1 px-2 rounded-full hover:bg-red-700 transition duration-150">Remove</button>
                </div>
                <button @click="addDrink('mix_drinks')"
                    class="bg-indigo-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-indigo-800 transition duration-150">+
                    Add Mix Drink</button>
            </div>

            <!-- Wines -->
            <div class="flex flex-col gap-2 p-3 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg mb-2">
                <div class="font-bold flex items-center gap-2">Wines <span>🍷</span></div>
                <div v-for="(item, index) in selectedDrinks.wines" :key="index"
                    class="flex gap-2 flex-wrap items-center">
                    <select v-model="item.id" @change="updateDrinkSelection('wines', index)"
                        class="p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select wine</option>
                        <option v-for="drink in drinks?.wines" :key="drink.id" :value="drink.id">{{ drink.name }} - ${{
                            drink.amount }}</option>
                    </select>
                    <input type="number" v-model="item.quantity" min="1"
                        class="w-[90px] p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="font-bold text-green-600">Price: ${{ calculatePrice('wines', item) }}</span>
                    <button @click="removeDrink('wines', index)"
                        class="bg-red-600 text-white font-semibold py-1 px-2 rounded-full hover:bg-red-700 transition duration-150">Remove</button>
                </div>
                <button @click="addDrink('wines')"
                    class="bg-indigo-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-indigo-800 transition duration-150">+
                    Add Wine</button>
            </div>

            <!-- Bottles -->
            <div class="flex flex-col gap-2 p-3 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg mb-2">
                <div class="font-bold flex items-center gap-2">Bottles <span>🍾</span></div>
                <div v-for="(item, index) in selectedDrinks.bottles" :key="index"
                    class="flex gap-2 flex-wrap items-center">
                    <select v-model="item.id" @change="updateDrinkSelection('bottles', index)"
                        class="p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select bottle</option>
                        <option v-for="drink in drinks?.bottles" :key="drink.id" :value="drink.id">{{ drink.name }} -
                            ${{ drink.amount }}</option>
                    </select>
                    <input type="number" v-model="item.quantity" min="1"
                        class="w-[90px] p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="font-bold text-green-600">Price: ${{ calculatePrice('bottles', item) }}</span>
                    <button @click="removeDrink('bottles', index)"
                        class="bg-red-600 text-white font-semibold py-1 px-2 rounded-full hover:bg-red-700 transition duration-150">Remove</button>
                </div>
                <button @click="addDrink('bottles')"
                    class="bg-indigo-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-indigo-800 transition duration-150">+
                    Add Bottle</button>
            </div>

            <!-- Waters -->
            <div class="flex flex-col gap-2 p-3 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg mb-2">
                <div class="font-bold flex items-center gap-2">Waters <span>💧</span></div>
                <div v-for="(item, index) in selectedDrinks.waters" :key="index"
                    class="flex gap-2 flex-wrap items-center">
                    <select v-model="item.id" @change="updateDrinkSelection('waters', index)"
                        class="p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select water</option>
                        <option v-for="drink in drinks?.waters" :key="drink.id" :value="drink.id">{{ drink.name }} - ${{
                            drink.amount }}</option>
                    </select>
                    <input type="number" v-model="item.quantity" min="1"
                        class="w-[90px] p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="font-bold text-green-600">Price: ${{ calculatePrice('waters', item) }}</span>
                    <button @click="removeDrink('waters', index)"
                        class="bg-red-600 text-white font-semibold py-1 px-2 rounded-full hover:bg-red-700 transition duration-150">Remove</button>
                </div>
                <button @click="addDrink('waters')"
                    class="bg-indigo-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-indigo-800 transition duration-150">+
                    Add Water</button>
            </div>

            <!-- Beers -->
            <div class="flex flex-col gap-2 p-3 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg mb-2">
                <div class="font-bold flex items-center gap-2">Beers <span>🍺</span></div>
                <div v-for="(item, index) in selectedDrinks.beers" :key="index"
                    class="flex gap-2 flex-wrap items-center">
                    <select v-model="item.id" @change="updateDrinkSelection('beers', index)"
                        class="p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select beer</option>
                        <option v-for="drink in drinks?.beers" :key="drink.id" :value="drink.id">{{ drink.name }} - ${{
                            drink.amount }}</option>
                    </select>
                    <input type="number" v-model="item.quantity" min="1"
                        class="w-[90px] p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="font-bold text-green-600">Price: ${{ calculatePrice('beers', item) }}</span>
                    <button @click="removeDrink('beers', index)"
                        class="bg-red-600 text-white font-semibold py-1 px-2 rounded-full hover:bg-red-700 transition duration-150">Remove</button>
                </div>
                <button @click="addDrink('beers')"
                    class="bg-indigo-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-indigo-800 transition duration-150">+
                    Add Beer</button>
            </div>

            <!-- Soft Drinks -->
            <div class="flex flex-col gap-2 p-3 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg mb-2">
                <div class="font-bold flex items-center gap-2">Soft Drinks <span>🥤</span></div>
                <div v-for="(item, index) in selectedDrinks.soft_drinks" :key="index"
                    class="flex gap-2 flex-wrap items-center">
                    <select v-model="item.id" @change="updateDrinkSelection('soft_drinks', index)"
                        class="p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select soft drink</option>
                        <option v-for="drink in drinks?.soft_drinks" :key="drink.id" :value="drink.id">{{ drink.name }}
                            - ${{ drink.amount }}</option>
                    </select>
                    <input type="number" v-model="item.quantity" min="1"
                        class="w-[90px] p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="font-bold text-green-600">Price: ${{ calculatePrice('soft_drinks', item) }}</span>
                    <button @click="removeDrink('soft_drinks', index)"
                        class="bg-red-600 text-white font-semibold py-1 px-2 rounded-full hover:bg-red-700 transition duration-150">Remove</button>
                </div>
                <button @click="addDrink('soft_drinks')"
                    class="bg-indigo-600 text-white font-semibold py-1 px-3 rounded-full hover:bg-indigo-800 transition duration-150">+
                    Add Soft Drink</button>
            </div>

            <!-- Totals -->
            <div class="p-3 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg mt-2 sticky bottom-0">
                <strong>Estimated Total: ${{ totalPrice.toFixed(2) }}</strong>
            </div>

            <div class="flex gap-2 flex-wrap mt-2">
                <button @click="saveDrinks"
                    class="bg-green-600 text-white font-semibold py-1 px-3 rounded-lg hover:bg-green-700 transition duration-150">
                    Save Drinks
                </button>
            </div>
        </div>
    </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface DrinkItem {
    id: number | string;
    name: string;
    amount: string | number;
}

interface SelectedDrink {
    id: number | string;
    name: string;
    price: number;
    quantity: number;
}

const props = defineProps<{
    ticket: {
        drink_addons?: any;
        [key: string]: any;
    };
    drinks: {
        mix_drinks?: DrinkItem[];
        wines?: DrinkItem[];
        waters?: DrinkItem[];
        beers?: DrinkItem[];
        soft_drinks?: DrinkItem[];
        bottles?: DrinkItem[];
    };
}>()

const emit = defineEmits(["close", "save-drinks"]);

const selectedDrinks = ref<Record<string, SelectedDrink[]>>({
    mix_drinks: [],
    wines: [],
    bottles: [],
    waters: [],
    beers: [],
    soft_drinks: []
});

onMounted(() => {
    if (props.ticket?.drink_addons?.items) {
        const items = props.ticket.drink_addons.items;
        const map: Record<string, string> = {
            mixDrinks: 'mix_drinks',
            wines: 'wines',
            waters: 'waters',
            beers: 'beers',
            softDrinks: 'soft_drinks',
            bottles: 'bottles'
        };
        for (const [newCat, rawCat] of Object.entries(map)) {
            if (items[newCat]) {
                items[newCat].forEach((addon: any) => {
                    const drink = findDrinkByName(rawCat, addon.name);
                    selectedDrinks.value[rawCat].push({
                        id: drink?.id || '',
                        name: addon.name,
                        price: parseFloat(addon.cost || addon.price || '0'),
                        quantity: parseInt(addon.qty || addon.quantity || '0')
                    });
                });
            }
        }
    } else if (props.ticket?.drink_addons?.length) {
        // Legacy support
        props.ticket.drink_addons.forEach((addon: any) => {
            const category = findCategoryByName(addon.name);
            if (category) {
                const drink = findDrinkByName(category, addon.name);
                selectedDrinks.value[category].push({
                    id: drink?.id || '',
                    name: addon.name,
                    price: addon.price,
                    quantity: addon.quantity
                });
            }
        });
    }
});

function findCategoryByName(name: string): string | null {
    const categories = ['mix_drinks', 'wines', 'bottles', 'waters', 'beers', 'soft_drinks'];
    for (const cat of categories) {
        const drinks = props.drinks?.[cat as keyof typeof props.drinks] || [];
        if (drinks.some((d: DrinkItem) => d.name === name)) {
            return cat;
        }
    }
    return null;
}

function findDrinkByName(category: string, name: string): DrinkItem | undefined {
    const drinks = props.drinks?.[category as keyof typeof props.drinks] || [];
    return drinks.find((d: DrinkItem) => d.name === name);
}

function addDrink(category: string) {
    selectedDrinks.value[category].push({
        id: '',
        name: '',
        price: 0,
        quantity: 1
    });
}

function removeDrink(category: string, index: number) {
    selectedDrinks.value[category].splice(index, 1);
}

function updateDrinkSelection(category: string, index: number) {
    const item = selectedDrinks.value[category][index];
    const drinks = props.drinks?.[category as keyof typeof props.drinks] || [];
    const drink = drinks.find((d: DrinkItem) => d.id == item.id);
    if (drink) {
        item.name = drink.name;
        item.price = parseFloat(String(drink.amount)) || 0;
    }
}

function calculatePrice(category: string, item: SelectedDrink): string {
    if (!item.id) return '0.00';
    const drinks = props.drinks?.[category as keyof typeof props.drinks] || [];
    const drink = drinks.find((d: DrinkItem) => d.id == item.id);
    if (!drink) return '0.00';
    const price = parseFloat(String(drink.amount)) || 0;
    return (price * item.quantity).toFixed(2);
}

const totalPrice = computed(() => {
    let total = 0;
    const categories = Object.keys(selectedDrinks.value);
    for (const cat of categories) {
        for (const item of selectedDrinks.value[cat]) {
            if (item.id) {
                const drinks = props.drinks?.[cat as keyof typeof props.drinks] || [];
                const drink = drinks.find((d: DrinkItem) => d.id == item.id);
                if (drink) {
                    total += (parseFloat(String(drink.amount)) || 0) * item.quantity;
                }
            }
        }
    }
    return total;
});

function saveDrinks() {
    const items: Record<string, any[]> = {
        mixDrinks: [],
        wines: [],
        waters: [],
        beers: [],
        softDrinks: [],
        bottles: []
    };

    const catMap: Record<string, string> = {
        mix_drinks: 'mixDrinks',
        wines: 'wines',
        waters: 'waters',
        beers: 'beers',
        soft_drinks: 'softDrinks',
        bottles: 'bottles'
    };

    const categories = Object.keys(selectedDrinks.value);
    for (const rawCat of categories) {
        const newCat = catMap[rawCat] || rawCat;
        for (const item of selectedDrinks.value[rawCat]) {
            if (item.id && item.name) {
                items[newCat].push({
                    name: item.name,
                    qty: item.quantity,
                    cost: item.price.toFixed(2)
                });
            }
        }
    }

    const payload = {
        enabled: true,
        items,
        total: totalPrice.value.toFixed(2),
        freeOne: {
            enabled: false,
            category: "any"
        }
    };

    emit("save-drinks", payload);
}

const close = () => {
    const modal = document.getElementById('dlg-ticket-drinks');
    if (modal) modal.classList.add('hidden');
    emit("close");
};

const handleBackdropClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        close();
    }
};

// Show modal when component is mounted
onMounted(() => {
    const modal = document.getElementById('dlg-ticket-drinks');
    if (modal) modal.classList.remove('hidden');
});

// Hide modal when component is unmounted
onUnmounted(() => {
    const modal = document.getElementById('dlg-ticket-drinks');
    if (modal) modal.classList.add('hidden');
});
</script>

<style>
@layer utilities {
    .modal-scroll {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .modal-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-scroll::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
        border: 2px solid white;
        background-clip: padding-box;
    }

    .modal-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8;
    }
}
</style>

<style>
dialog::backdrop {
    background: rgba(0, 0, 0, 0.55);
}
</style>
