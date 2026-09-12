<template>
    <section class="mb-4">
        <div class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
            <input id="reserved-seating" v-model="enabled" type="checkbox" class="mt-1 accent-blue-600" />
            <label for="reserved-seating" class="cursor-pointer">
                <span class="font-black text-sm block text-slate-800">Reserved seating</span>
                <span class="mt-1 block text-[11px] text-slate-500">Design a visual table/section layout with its own
                    pricing below — attendees pick their seat at checkout.</span>
            </label>
        </div>

        <div v-if="enabled" class="mt-3 rounded-2xl border border-slate-200 p-4">
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <button type="button" @click="add('table')"
                    class="h-9 rounded-xl bg-amber-500 px-4 text-sm font-black text-white hover:bg-amber-600">+ Add
                    Table</button>
                <button type="button" @click="add('section')"
                    class="h-9 rounded-xl bg-blue-500 px-4 text-sm font-black text-white hover:bg-blue-600">+ Add
                    Section</button>
            </div>

            <div @mousemove="drag" @mouseup="stopDrag" @mouseleave="stopDrag"
                class="relative h-[380px] overflow-hidden rounded-2xl border-2 border-slate-200 bg-slate-50 select-none">
                <div
                    class="absolute top-3 left-1/2 -translate-x-1/2 rounded-full bg-slate-800 px-6 py-1.5 text-xs font-black text-white">
                    STAGE</div>
                <div v-for="item in layout" :key="item.id" @mousedown="startDrag($event, item)" @click="select(item)"
                    :style="{ left: `${item.x}%`, top: `${item.y}%`, width: `${item.w}%`, height: `${item.h}%`, zIndex: item.type === 'table' ? 20 : 10, backgroundColor: item.color, borderRadius: item.type === 'table' ? '9999px' : '12px' }"
                    class="absolute flex items-center justify-center p-1 text-center text-[11px] font-black text-white shadow-sm transition cursor-grab active:cursor-grabbing"
                    :class="selectedId === item.id ? 'ring-4 ring-indigo-500 ring-offset-2' : ''">
                    <span>{{ item.label }}<br>({{ item.sold }}/{{ item.capacity }})</span>
                </div>
            </div>

            <div v-if="selected"
                class="mt-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
                <label class="text-xs font-black text-slate-500 lg:col-span-2">Label<input v-model="selected.label"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-400" /></label>
                <label class="text-xs font-black text-slate-500">Capacity<input v-model.number="selected.capacity"
                        type="number" min="1"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-400" /></label>
                <label class="text-xs font-black text-slate-500">Price<input v-model.number="selected.price"
                        type="number" min="0" step="0.01"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-400" /></label>
                <label class="text-xs font-black text-slate-500">Color<input v-model="selected.color" type="color"
                        class="mt-1 h-10 w-full rounded-xl border border-slate-200 p-1" /></label>
                <label class="text-xs font-black text-slate-500">Type<select v-model="selected.type"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="table">Table (sold as one unit)</option>
                        <option value="section">Section (sold per person)</option>
                    </select></label>
                <button type="button" @click="remove"
                    class="rounded-xl bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-700 hover:bg-rose-100">Delete</button>
            </div>
            <p v-else class="mt-3 text-sm font-bold text-slate-400">Click a table or section to edit it, then drag it
                around the floor plan.</p>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

type SeatingItem = { id: string; label: string; type: 'table' | 'section'; x: number; y: number; w: number; h: number; capacity: number; sold: number; price: number; color: string }
const enabled = ref(false)
const selectedId = ref<string | null>(null)
const layout = ref<SeatingItem[]>([])
const dragState = ref<{ id: string; startX: number; startY: number; originX: number; originY: number } | null>(null)
const selected = computed(() => layout.value.find(item => item.id === selectedId.value) ?? null)

const add = (type: 'table' | 'section') => {
    const number = layout.value.filter(item => item.type === type).length + 1
  const item: SeatingItem = {
    id: `${type}-${Date.now()}`,
    label: type === 'table' ? `Table ${number}` : `Section ${number}`,
    type,
    x: type === 'table' ? 40 : 18,
    y: type === 'table' ? 32 : 66,
    // Match the existing reference items: sections are wide blocks, tables are compact pills.
    w: type === 'table' ? 12 : 30,
    h: type === 'table' ? 12 : 15,
    capacity: type === 'table' ? 6 : 50,
    sold: 0,
    price: type === 'table' ? 500 : 35,
    color: type === 'table' ? '#f59e0b' : '#2563eb'
  }
    layout.value.push(item); selectedId.value = item.id
}
const select = (item: SeatingItem) => { selectedId.value = item.id }
const remove = () => { layout.value = layout.value.filter(item => item.id !== selectedId.value); selectedId.value = null }
const removeItem = (id: string) => { layout.value = layout.value.filter(item => item.id !== id); if (selectedId.value === id) selectedId.value = null }
const startDrag = (event: MouseEvent, item: SeatingItem) => { event.preventDefault(); dragState.value = { id: item.id, startX: event.clientX, startY: event.clientY, originX: item.x, originY: item.y }; selectedId.value = item.id }
const drag = (event: MouseEvent) => { if (!dragState.value) return; const canvas = event.currentTarget as HTMLElement; const item = layout.value.find(value => value.id === dragState.value?.id); if (!item) return; item.x = Math.max(0, Math.min(100 - item.w, dragState.value.originX + ((event.clientX - dragState.value.startX) / canvas.clientWidth) * 100)); item.y = Math.max(16, Math.min(100 - item.h, dragState.value.originY + ((event.clientY - dragState.value.startY) / canvas.clientHeight) * 100)) }
const stopDrag = () => { dragState.value = null }
</script>
