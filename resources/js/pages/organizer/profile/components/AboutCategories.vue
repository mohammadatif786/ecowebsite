<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { Plus, Minus, Loader2 } from 'lucide-vue-next';

const page = usePage()

const props = defineProps<{
    organizer_detail: any;
    categories: Array<Record<string, any>>
}>();

const form = useForm({
    about_the_organizer: props.organizer_detail?.about_the_organizer ?? '',
    categories: props.organizer_detail?.categories ?? [],
})

const isOpen = ref(!!props.organizer_detail);

const editorContent = ref<HTMLDivElement | null>(null);

const execCommand = (tag: string) => {
    if (!editorContent.value) return;
    const command = tag === 'B' ? 'bold' : tag === 'U' ? 'underline' : tag === 'I' ? 'italic' : '';
    if (command) {
        document.execCommand(command, false);
    } else if (tag === 'X') {
        document.execCommand('removeFormat', false);
    }
};

const updateAbout = () => {
    if (editorContent.value) {
        form.about_the_organizer = editorContent.value.innerHTML;
    }
};

onMounted(() => {
    if (editorContent.value && form.about_the_organizer) {
        editorContent.value.innerHTML = form.about_the_organizer;
    }
});

const categoriesInput = ref("");
const categoriesTags = ref<string[]>([...form.categories]);
const filteredCategories = computed(() => {
    if (!categoriesInput.value.trim()) return [];
    return props.categories.filter(cat =>
        cat.name.toLowerCase().includes(categoriesInput.value.toLowerCase()) &&
        !categoriesTags.value.includes(cat.name)
    );
});

const addCategory = () => {
    if (categoriesInput.value.trim() !== "" && !categoriesTags.value.includes(categoriesInput.value.trim())) {
        categoriesTags.value.push(categoriesInput.value.trim());
        categoriesInput.value = "";
    }
};

const selectCategory = (name: string) => {
    if (!categoriesTags.value.includes(name)) {
        categoriesTags.value.push(name);
    }
    categoriesInput.value = "";
};

const removeCategory = (index: number) => {
    categoriesTags.value.splice(index, 1);
};

const handleSubmit = () => {
    form.categories = [...categoriesTags.value];

    form.post(route('organizer.profile.update', { organizer_profile_type: 'profileDetail' }), {
        onSuccess: () => {
            toast.success("About & Categories updated successfully!");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
            toast.error("Please check the form for errors.");
        },
    });
}
</script>

<template>
    <div class="card p-6 bg-white border border-slate-100 rounded-[20px] shadow-sm mb-4">
        <!-- Header matching orgCardHeader reference line 5007 -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-black text-slate-800">About & Categories</h3>
                <div class="h-[3px] rounded-full mt-1.5 max-w-full bg-gradient-to-r from-indigo-500 to-amber-400"></div>
            </div>
            <button @click="isOpen = !isOpen"
                class="h-9 w-9 rounded-full text-white grid place-items-center shrink-0 ml-3 bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                <Plus v-if="!isOpen" class="w-4 h-4" />
                <Minus v-else class="w-4 h-4" />
            </button>
        </div>

        <p class="text-slate-400 text-sm font-bold mb-4 uppercase tracking-tight" v-if="!isOpen">
            Describe yourself and select categories.
        </p>

        <form v-show="isOpen" @submit.prevent="handleSubmit" class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">
            <!-- About Section matching reference line 5040 -->
            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">About the organiser</label>
                <div class="rounded-2xl border border-slate-200 mt-1 overflow-hidden shadow-sm">
                    <div class="flex gap-1 p-2 bg-slate-50 border-b border-slate-200">
                        <button v-for="t in ['B', 'U', 'I', 'X']" :key="t"
                            @click.prevent="execCommand(t)"
                            class="h-8 w-8 rounded-lg bg-white border border-slate-200 font-black text-xs hover:bg-slate-100 transition active:scale-95">
                            {{ t }}
                        </button>
                    </div>
                    <div class="editor-content p-4 min-h-[160px] outline-none text-sm font-medium text-slate-700"
                        ref="editorContent" contenteditable="true" @input="updateAbout">
                    </div>
                </div>
                <p v-if="form.errors.about_the_organizer" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.about_the_organizer }} </p>
            </div>

            <!-- Categories Section matching reference line 5045 -->
            <div class="rounded-2xl border border-slate-100 p-5 bg-slate-50/30">
                <p class="font-black text-slate-800 mb-1 flex items-center gap-2">
                    <span class="text-xl text-indigo-500">○</span> Categories
                </p>
                <p class="text-[11px] text-slate-400 font-bold uppercase mb-4 ml-7">Select the categories that represent your event types</p>

                <div class="ml-7 space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(tag, index) in categoriesTags" :key="index"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-indigo-600 text-white text-[11px] font-black uppercase tracking-wider shadow-sm">
                            {{ tag }}
                            <button @click="removeCategory(index)" class="hover:text-amber-400 transition">×</button>
                        </span>
                        <span v-if="!categoriesTags.length" class="text-slate-400 text-sm font-bold italic">No categories selected</span>
                    </div>

                    <div class="relative">
                        <input type="text" v-model="categoriesInput" placeholder="Add category and press Enter"
                            @keydown.enter.prevent="addCategory"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />

                        <div v-if="filteredCategories.length" class="absolute left-0 right-0 top-full mt-2 z-20 overflow-y-auto max-height-[200px] border border-slate-100 bg-white rounded-2xl shadow-xl py-2">
                            <div v-for="cat in filteredCategories" :key="cat.id"
                                @click="selectCategory(cat.name)"
                                class="px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 cursor-pointer transition">
                                {{ cat.name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" :disabled="form.processing"
                    class="px-10 py-2.5 rounded-full font-black text-slate-900 transition hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 bg-amber-400">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    Update
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}

.editor-content:focus {
    background: #fcfcfc;
}
</style>
