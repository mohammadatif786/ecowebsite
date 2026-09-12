<template>
    <aside class="sidebar">
        <ul>
            <li v-for="item in visibleNavItems" :key="item.title" class="relative group">
                <!-- Simple link without children -->
                <Link v-if="!item.children" :href="item.href" :class="[
                    'flex items-center gap-2 px-3 py-2 rounded-md transition',
                    isActive(item) ? 'bg-blue-600 text-white' : 'hover:bg-blue-300'
                ]">
                    <component :is="item.icon" class="w-4 h-4" />
                    <span>{{ item.title }}</span>
                </Link>

                <!-- Dropdown with children -->
                <div v-else class="dropdown relative group">
                    <button :class="[
                        'dropbtn flex items-center gap-2 px-3 py-2 rounded-md transition w-full text-left',
                        isDropdownActive(item) ? 'bg-blue-600 text-white' : 'hover:bg-blue-300'
                    ]">
                        <component :is="item.icon" class="w-4 h-4" />
                        <span class="flex-1">{{ item.title }}</span>
                        <span>▼</span>
                    </button>
                    <div
                        class="dropdown-content absolute left-full top-0 hidden group-hover:block shadow-lg rounded-md min-w-[180px] z-50 bg-white border border-gray-100">
                        <Link v-for="child in item.children" :key="child.title" :href="child.href" :class="[
                            'flex items-center gap-2 px-3 py-2 transition',
                            isActive(child) ? 'bg-blue-600 text-white' : 'hover:bg-blue-200 text-gray-700'
                        ]">
                            <component :is="child.icon" class="w-3 h-3" />
                            <span>{{ child.title }}</span>
                        </Link>
                    </div>
                </div>
            </li>
        </ul>
    </aside>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { Component } from 'vue';

import {
    Home,
    UserCircle,
    CalendarCheck,
    Smartphone,
    ShoppingCart,
    Star,
    DollarSign,
    CreditCard,
    FileHeart,
    BellRing,
    Settings,
    RefreshCcw,
    Wallet,
} from 'lucide-vue-next'

interface NavItem {
    title: string;
    href: string;
    icon?: Component;
    permission?: string;
    children?: NavItem[];
    activePaths?: string[];
}

const page = usePage();
const permissions = computed<string[]>(() => page.props.auth?.permissions || []);

const hasPermission = (permission?: string) => {
    return !permission || permissions.value.includes(permission);
};

/**
 * Normalizes any URL or path to a clean, standard format:
 * - Starts with /
 * - No trailing slash (except for / itself)
 * - No query parameters
 * - No domain
 */
const normalizePath = (url: string | undefined) => {
    if (!url || url === '#' || url === 'javascript:void(0)') return '';

    let path = url;

    // Extract path from full URL
    if (path.includes('://')) {
        try {
            path = new URL(path).pathname;
        } catch (e) { }
    }

    // Remove query string
    path = path.split('?')[0];

    // Remove trailing slash and ensure leading slash
    path = path.replace(/\/$/, '');
    if (!path.startsWith('/')) {
        path = '/' + path;
    }

    return path || '/';
};

// Reactively track the current path
const currentPath = computed(() => normalizePath(page.url));

const isActive = (item: NavItem) => {
    const current = currentPath.value;

    // 1. Check if current URL matches any of the activePaths patterns
    if (item.activePaths && item.activePaths.length > 0) {
        const isMatched = item.activePaths.some(p => {
            const pattern = normalizePath(p);
            if (pattern === '/') return current === '/';
            // Match exact or sub-path (e.g. /organizer/event matches /organizer/event/create)
            return current === pattern || current.startsWith(pattern + '/');
        });
        if (isMatched) return true;
    }

    // 2. Fallback to matching the item's own href
    const itemPath = normalizePath(item.href);
    if (!itemPath || itemPath === '#') return false;
    if (itemPath === '/') return current === '/';

    // Match exact or sub-path
    return current === itemPath || current.startsWith(itemPath + '/');
};

const isDropdownActive = (item: NavItem) => {
    // Dropdown is active if any child is active OR if the parent path itself is active
    const hasActiveChild = item.children?.some(child => isActive(child));
    if (hasActiveChild) return true;

    // Check if the current URL matches the parent's activePaths (if defined)
    if (item.activePaths?.length) {
        return item.activePaths.some(p => {
            const pattern = normalizePath(p);
            return currentPath.value === pattern || currentPath.value.startsWith(pattern + '/');
        });
    }

    return false;
};

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('organizer.dashboard'),
        icon: Home,
        activePaths: ['/organizer/dashboard'],
    },
    {
        title: 'My Organizer Profile',
        href: route('organizer.profile.index'),
        icon: UserCircle,
        activePaths: ['/organizer/profile'],
    },
    {
        title: 'My Events',
        href: route('organizer.event.index'),
        icon: CalendarCheck,
        permission: 'organizer events',
        activePaths: ['/organizer/event', '/organizer/ticket', '/organizer/packages'],
    },
    {
        title: 'Scanner App',
        href: '#',
        icon: Smartphone,
        activePaths: ['/organizer/scanner'],
        children: [
            { title: 'My Scanner', href: route('organizer.scanner.index'), permission: 'organizer scanners', activePaths: ['/organizer/scanner'] },
            { title: 'Scan Ticket', href: route('organizer.scanner.scan-view'), permission: 'organizer scan ticket', activePaths: ['/organizer/scanner/scan'] },
        ],
    },
    {
        title: 'Point Of Sale',
        href: route('organizer.pos.index'),
        icon: ShoppingCart,
        permission: 'organizer pos',
        activePaths: ['/organizer/pos'],
    },
    {
        title: 'Reviews',
        href: route('organizer.review.index'),
        icon: Star,
        permission: 'organizer reviews',
        activePaths: ['/organizer/review'],
    },
    {
        title: 'Payouts',
        href: '#',
        icon: DollarSign,
        activePaths: ['/organizer/payout'],
        children: [
            { title: 'Payout Request', href: route('organizer.payout.request'), permission: 'organizer payouts', activePaths: ['/organizer/payout/request'] },
            { title: 'Payout Method', href: route('organizer.payout.method'), permission: 'organizer payout methods', activePaths: ['/organizer/payout/method'] },
        ],
    },
    {
        title: 'Reports',
        href: route('organizer.report.statistics'),
        icon: FileHeart,
        permission: 'organizer reports',
        activePaths: ['/organizer/statistics'],
    },
    {
        title: 'Account',
        href: route('organizer.profile.index'),
        icon: Settings,
        activePaths: ['/organizer/profile'],
    },
]

const visibleNavItems = computed<NavItem[]>(() => {
    return mainNavItems
        .map((item) => {
            const children = item.children?.filter((child) => hasPermission(child.permission));

            if (item.children) {
                return children?.length ? { ...item, children } : null;
            }

            return hasPermission(item.permission) ? item : null;
        })
        .filter(Boolean) as NavItem[];
});
</script>
