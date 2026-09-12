<template>
    <div class="rounded-[20px] border border-slate-100 p-4 mb-4">
        <!-- Section Header -->
        <p class="font-black text-blue-700 mb-3 pb-2"
            style="border-bottom: 2px solid; border-image: linear-gradient(90deg,#2f9bef,#f59e0b) 1">
            Scanner
        </p>

        <label class="text-xs font-black text-slate-500">Scanners *</label>

        <!-- Selected scanners as tags -->
        <div class="flex flex-wrap gap-2 mt-2 mb-2">
            <span v-for="(scanner, index) in modelValue" :key="scanner.id"
                class="rounded-full bg-blue-50 text-blue-700 px-3 py-1.5 text-xs font-bold flex items-center gap-1.5">
                {{ scanner.first_name }} {{ scanner.last_name }}
                <button type="button" @click="removeScanner(index)" class="text-blue-700/60">✕</button>
            </span>
        </div>

        <!-- Input with autocomplete -->
        <div class="relative">
            <input type="text" v-model="newScanner" @input="filterSuggestions"
                class="w-full border border-slate-200 rounded-2xl px-4 py-3 outline-none focus:border-blue-400"
                placeholder="Search scanner..." />

            <!-- Suggestions dropdown -->
            <ul v-if="filteredScanners.length" class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                <li v-for="scanner in filteredScanners" :key="scanner.id" @click="selectScanner(scanner)"
                    class="px-4 py-2 hover:bg-slate-50 cursor-pointer text-sm font-bold">
                    {{ scanner.first_name }} {{ scanner.last_name }}
                </li>
            </ul>
        </div>

        <!-- Quick preset buttons for available scanners -->
        <div v-if="availablePool.length" class="flex flex-wrap gap-2 mt-3">
            <button v-for="scanner in availablePool" :key="scanner.id" type="button" @click="selectScanner(scanner)"
                class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-50 transition">
                + {{ scanner.first_name }} {{ scanner.last_name }}
            </button>
        </div>

        <div v-if="props.errors && props.errors.length" class="text-red-500 text-sm mt-2">
            <ul class="list-disc pl-4">
                <li v-for="(err, idx) in props.errors" :key="idx">{{ err }}</li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";

const props = defineProps<{
    scanners: { id: number; first_name: string; last_name: string; email: string }[];
    modelValue: any[];
    errors?: string[];
    eventDetail: Array<Record<string, null>>
}>();



const emit = defineEmits(["update:modelValue"]);

const showFields = ref<{ [k: number]: boolean }>({ 0: !!props.eventDetail });
const toggleSection = (index: number) => {
    showFields.value[index] = !showFields.value[index];
};

const newScanner = ref("");
const filteredScanners = ref<any[]>([]);

const availablePool = computed(() => {
    const selectedIds = props.modelValue.map((s) => s.id);
    return props.scanners.filter((s) => !selectedIds.includes(s.id));
});

const filterSuggestions = () => {
    const query = newScanner.value.toLowerCase();
    if (!query) {
        filteredScanners.value = [];
        return;
    }

    const selectedIds = props.modelValue.map((s) => s.id);

    filteredScanners.value = props.scanners.filter((scanner) => {
        const name = `${scanner.first_name} ${scanner.last_name}`.toLowerCase();
        return (
            !selectedIds.includes(scanner.id) &&
            (name.includes(query) || scanner.email.toLowerCase().includes(query))
        );
    });
};

const selectScanner = (scanner: any) => {
    if (!props.modelValue.find((s) => s.id === scanner.id)) {
        emit("update:modelValue", [...props.modelValue, scanner]);
    }
    newScanner.value = "";
    filteredScanners.value = [];
};

const removeScanner = (index: number) => {
    const updated = [...props.modelValue];
    updated.splice(index, 1);
    emit("update:modelValue", updated);
    filterSuggestions();
};

onMounted(() => {
    if (props.eventDetail?.scanner_id?.length) {
        const preselected = props.scanners.filter(scanner =>
            props.eventDetail.scanner_id.includes(scanner.id.toString())
        );
        if (preselected.length) {
            emit("update:modelValue", preselected);
        }
    }
});

</script>


