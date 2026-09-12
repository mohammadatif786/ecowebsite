<template>
    <div class="drink-package-builder">
        <h2>🥂 Drink Package Builder</h2>

        <form>
            <!-- Load Saved Packages -->
            <div class="template-controls" v-if="props.packages && props.packages.length > 0">
                <select class="package-select" v-model="selectedPackageId">
                    <option :value="null">-- Load Saved Package --</option>
                    <option v-for="pkg in props.packages" :key="pkg.id" :value="pkg.id">
                        {{ pkg.name }}
                    </option>
                </select>
            </div>

            <!-- Package Name Input -->
            <div class="template-controls">
                <input type="text" v-model="packageName" placeholder="Enter package name (e.g. VIP)">
            </div>
            <div class="template-controls" v-if="selectedPackageId">
                <button type="button" class="btn-remove" @click="removeDrinkPackage(selectedPackageId)">
                    Remove Package
                </button>
            </div>

            <DrinkGroup v-model="bottles" title="🥃 Main Bottles" placeholder="bottle" />

            <DrinkGroup v-model="chasers" title="🍹 Chasers / Mixers" placeholder="chaser" />

            <DrinkGroup v-model="waters" title="💧 Water Options" placeholder="water" />

            <!-- Notes -->
            <div class="ticket-builder">
                <h4>📝 Special Requests or Notes</h4>
                <textarea v-model="notes" placeholder="e.g., Chill the vodka, add extra lemons..."></textarea>
            </div>

            <!-- Submit Button -->
            <div class="text-center mt-4">
                <button type="button" class="btn-submit" v-if="!selectedPackageId" @click="saveDrinkPackage">
                    💾 Save Drink Package
                </button>
            </div>

            <!-- Update Button -->
            <div class="text-center mt-4">
                <button type="button" class="btn-submit" v-if="selectedPackageId" @click="updateDrinkPackage">
                    🔄 Update Drink Package
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import DrinkGroup from './DrinkGroup.vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { route } from 'ziggy-js';


const props = defineProps<{
    packages?: any[];
}>();

const emit = defineEmits<{
    'package-created': [pkg: any];
    'package-updated': [pkg: any];
    'package-deleted': [pkg: any];
    'closeDrinkAddonModal': [];
}>();

const selectedPackageId = ref<number | null>(null);

const packageName = ref('');
const bottles = ref<any[]>([]);
const chasers = ref<any[]>([]);
const waters = ref<any[]>([]);
const notes = ref('');

const getPackagePayload = () => ({
    packageId: selectedPackageId.value,
    name: packageName.value,
    bottles: bottles.value,
    chasers: chasers.value,
    waters: waters.value,
    notes: notes.value
});

const emptyPackagePayload = () => {
    packageName.value = '';
    bottles.value = [];
    chasers.value = [];
    waters.value = [];
    notes.value = '';
};

const saveDrinkPackage = async () => {

    try {
        const response = await axios.post(route('organizer.drink.package.store'), getPackagePayload());

        toast.success(response.data.message);

        if (response.data.package) {
            emit('package-created', response.data.package);
        }

        emptyPackagePayload();
        emit('closeDrinkAddonModal');

    } catch (error: any) {
        toast.error(error.response?.data?.message);
    }
}

const updateDrinkPackage = async () => {

    try {
        const response = await axios.post(route('organizer.drink.package.update', { id: selectedPackageId.value }), getPackagePayload());
        toast.success(response.data.message);

        if (response.data.package) {
            emit('package-updated', response.data.package);
        }
        emit('closeDrinkAddonModal');

    } catch (error: any) {
        toast.error(error.response?.data?.message);
    }
}

const removeDrinkPackage = async (id: number | null) => {
    if (!id) return;

    if (!confirm('Are you sure you want to delete this drink package?')) return;

    try {
        const response = await axios.delete(route('organizer.drink.package.destroy', { id: id }));
        toast.success(response.data.message);

        emit('package-deleted', id);

        selectedPackageId.value = null;
        emptyPackagePayload();
        emit('closeDrinkAddonModal');

    } catch (error: any) {
        toast.error(error.response?.data?.message);
    }
}

watch(selectedPackageId, (id) => {
    const pkg = props.packages?.find(p => p.id === id);

    if (!pkg) {
        emptyPackagePayload();
        return;
    }

    packageName.value = pkg.name;
    bottles.value = [...(pkg.bottles || [])];
    chasers.value = [...(pkg.chasers || [])];
    waters.value = [...(pkg.waters || [])];
    notes.value = pkg.notes || '';
});

</script>

<style scoped>
.drink-package-builder {
    font-family: "Inter", sans-serif;
    background: #f8fafc;
    padding: 2rem;
    color: #1e293b;
}

h2 {
    color: #1e3a8a;
}

.drink-group {
    background: #eef2ff;
    border-left: 6px solid #6366f1;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
}

.drink-group h3 {
    color: #1e40af;
    margin-bottom: 0.5rem;
}

.input-row {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.input-row input {
    flex: 1;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #c7d2fe;
}

.input-row input[type=number] {
    width: 100px;
}

.btn-add {
    padding: 0.5rem 1rem;
    background: #6366f1;
    border: none;
    color: white;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
}

.btn-add:hover {
    background: #4f46e5;
}

.item-list {
    margin-top: 0.5rem;
}

.item {
    background: white;
    padding: 0.4rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #e0e7ff;
    margin-bottom: 0.3rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.item span {
    font-weight: 500;
}

.btn-remove {
    background: #ef4444;
    border: none;
    color: white;
    padding: 0.3rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
}

.btn-remove:hover {
    background: #dc2626;
}

.ticket-builder {
    background: #f1f5f9;
    padding: 1rem;
    border-radius: 12px;
    border-left: 6px solid #0ea5e9;
}

textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 0.5rem;
    min-height: 80px;
    resize: vertical;
}

.summary {
    background: #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    margin-top: 1rem;
}

.template-controls {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.template-controls input,
.template-controls select {
    flex: 1;
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
}

.package-select {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    border: 2px solid #6366f1;
    background: white;
    font-size: 1rem;
    font-weight: 500;
    color: #1e293b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.package-select:hover {
    border-color: #4f46e5;
    box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
}

.package-select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.btn-save {
    background: #16a34a;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    white-space: nowrap;
}

.btn-save:hover {
    background: #15803d;
}

.btn-submit {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(99, 102, 241, 0.3);
    transition: all 0.3s ease;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    box-shadow: 0 6px 8px rgba(99, 102, 241, 0.4);
    transform: translateY(-2px);
}

.btn-submit:active {
    transform: translateY(0);
}
</style>
