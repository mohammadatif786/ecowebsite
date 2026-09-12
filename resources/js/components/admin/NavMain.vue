<script setup lang="ts">
import { onMounted, ref } from 'vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/admin/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
const props = defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();

function isActive(href: string , parent: string|null) {
    const matched = href ? href.endsWith(page.url) : false;
    // console.log(matched, href, page.url, parent);
    if(matched && parent){
        openDropdowns.value.add(parent);
    }
    return matched;
}

// Track open state of dropdowns by title
const openDropdowns = ref<Set<string>>(new Set());

function toggleDropdown(key: string) {
    console.log('running toggle dropdown');
    if (openDropdowns.value.has(key)) {
        openDropdowns.value.delete(key);
    } else {
        openDropdowns.value.clear(); // close all others
        openDropdowns.value.add(key);
    }
}

const allowedItems = computed(() => {
    const permissions: string[] = page.props.auth?.permissions || [];

    function filterItems(items: NavItem[]): NavItem[] {
        return items
            .map(item => {
                let children: NavItem[] | undefined;

                if (item.children) {
                    children = filterItems(item.children);
                }

                const hasPermission =
                    !item.permission || permissions.includes(item.permission) || (children && children.length > 0);

                if (!hasPermission) return null;

                return { ...item, children };
            })
            .filter(Boolean) as NavItem[];
    }

    return filterItems(props.items);
});


</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarMenu>
            <template v-for="item in allowedItems" :key="item.title">
                <template v-if="item.isSection">
                    <div class="px-2 pt-6 pb-2 text-xs font-bold text-slate-500 tracking-widest uppercase">
                        {{ item.title }}
                    </div>
                </template>
                <SidebarMenuItem v-else>
                    <!-- Parent item with children -->
                    <template v-if="item.children">
                        <SidebarMenuButton as-child :is-active="isActive(item.href, null)">
                            <button @click="toggleDropdown(item.title)" class="flex items-center gap-2 w-full">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                                <span class="ml-auto">
                                    <ChevronDown v-if="openDropdowns.has(item.title)" :size="15" />
                                    <ChevronRight v-if="!openDropdowns.has(item.title)" :size="15" />
                                </span>
                            </button>
                        </SidebarMenuButton>

                        <transition name="dropdown" @enter="el => (el.style.height = el.scrollHeight + 'px')"
                            @before-enter="el => (el.style.height = '0')" @leave="el => (el.style.height = '0')">
                            <div v-show="openDropdowns.has(item.title)"
                                class="ml-4 mt-1 space-y-1 overflow-hidden transition-all duration-300">
                                <template v-for="child in item.children" :key="child.title">
                                    <template v-if="child.isSection">
                                        <div class="px-2 pt-4 pb-1 text-[10px] font-bold text-slate-400 tracking-widest uppercase">
                                            {{ child.title }}
                                        </div>
                                    </template>
                                    <SidebarMenuItem v-else>
                                        <SidebarMenuButton as-child :is-active="isActive(child.href, item.title)"
                                            :tooltip="child.title">
                                            <Link :href="child.href" :preserve-state="true" :preserve-scroll="true" class="flex w-full justify-between items-center">
                                                <div class="flex items-center gap-2">
                                                    <component :is="child.icon" />
                                                    <span>{{ child.title }}</span>
                                                </div>
                                                <span v-if="child.badge" :class="[
                                                    'px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider',
                                                    child.badgeVariant === 'green' ? 'bg-[#c8e63a] text-[#5e7c00]' : '',
                                                    child.badgeVariant === 'pink' ? 'bg-[#fecdd3] text-[#e11d48]' : '',
                                                    child.badgeVariant === 'blue' ? 'bg-[#bae6fd] text-[#0284c7]' : '',
                                                    child.badgeVariant === 'orange' ? 'bg-[#ffedd5] text-[#ea580c]' : '',
                                                    child.badgeVariant === 'peach' ? 'bg-[#fed7aa] text-[#ea580c]' : '',
                                                    child.badgeVariant === 'purple' ? 'bg-[#e9d5ff] text-[#9333ea]' : '',
                                                    !child.badgeVariant ? 'bg-slate-200 text-slate-600' : ''
                                                ]">{{ child.badge }}</span>
                                            </Link>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                </template>
                            </div>
                        </transition>
                    </template>

                    <!-- Regular item -->
                    <template v-else>
                        <SidebarMenuButton as-child :is-active="isActive(item.href, null)" :tooltip="item.title">
                            <Link :href="item.href" :preserve-state="true" :preserve-scroll="true" class="flex w-full justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <component :is="item.icon" />
                                    <span>{{ item.title }}</span>
                                </div>
                                <span v-if="item.badge" :class="[
                                    'px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider',
                                    item.badgeVariant === 'green' ? 'bg-[#c8e63a] text-[#5e7c00]' : '',
                                    item.badgeVariant === 'pink' ? 'bg-[#fecdd3] text-[#e11d48]' : '',
                                    item.badgeVariant === 'blue' ? 'bg-[#bae6fd] text-[#0284c7]' : '',
                                    item.badgeVariant === 'orange' ? 'bg-[#ffedd5] text-[#ea580c]' : '',
                                    item.badgeVariant === 'peach' ? 'bg-[#fed7aa] text-[#ea580c]' : '',
                                    item.badgeVariant === 'purple' ? 'bg-[#e9d5ff] text-[#9333ea]' : '',
                                    !item.badgeVariant ? 'bg-slate-200 text-slate-600' : ''
                                ]">{{ item.badge }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </template>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: height 0.3s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    height: 0;
    overflow: hidden;
}
</style>
