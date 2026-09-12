<script setup lang="ts">
import NavFooter from '@/components/admin/NavFooter.vue';
import NavMain from '@/components/admin/NavMain.vue';
import NavUser from '@/components/admin/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/admin/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    Bell,
    Calendar,
    CalendarCog,
    CalendarDays,
    CircleDollarSign,
    CircleUserRound,
    CogIcon,
    Copy,
    CreditCard,
    Dot,
    FileCode,
    FileText,
    FlagIcon,
    FlaskConical,
    Heart,
    LayoutGrid,
    Mail,
    MapPin,
    Newspaper,
    PoundSterling,
    ShoppingBag,
    ShoppingCart,
    Tv,
    User,
    User2Icon,
    Video,
    Wallet,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('admin.dashboard'),
        icon: LayoutGrid,
        children: [
            { title: 'Users', href: route('admin.dashboard'), icon: Dot, permission: 'view users' },
            { title: 'Wallet', href: route('admin.dashboard.wallet'), icon: Dot, permission: 'view wallet' },
            { title: 'Events', href: route('admin.dashboard.event'), icon: Dot, permission: 'view events' },
            { title: 'Fee Analytics', href: route('admin.dashboard.fee-analytics'), icon: Dot },
            { title: 'Live Analytics', href: route('admin.dashboard.live-analytics'), icon: Dot, permission: 'view events analytics' },
            { title: 'Moderation', href: route('admin.dashboard.moderation'), icon: Dot, permission: 'view moderation' },
        ],
    },
    {
        title: 'Users',
        href: route('admin.users.index'),
        icon: Dot,
        permission: 'view users',
    },
    {
        title: 'Users Bank Withdrawal',
        href: route('admin.bank-withdrawals.index'),
        icon: Dot,
        permission: 'view users',
    },
    {
        title: 'Seller Cash Out',
        href: route('admin.seller-cash-out.index'),
        icon: Dot,
        permission: 'view users',
    },
    {
        title: 'Flagged Users',
        href: route('admin.flagged'),
        icon: Dot,
        permission: 'view flagged users',
    },
    {
        title: 'KYC Users',
        href: route('admin.home'),
        icon: User2Icon,
        children: [
            { title: 'Organizer KYC', href: route('admin.kyc-organizers'), icon: Dot, permission: 'view kyc users' },
        ],
    },
    {
        title: 'Event Management',
        href: route('admin.home'),
        icon: CalendarDays,
        children: [
            { title: 'Events', href: route('admin.event.index'), icon: Dot, permission: 'view events' },
            { title: 'Categories', href: route('admin.categories.index'), icon: Dot, permission: 'view categories' },
            { title: 'Ticket Sale', href: route('admin.ticket.sale'), icon: Dot, permission: 'view ticket sales' },
            { title: 'Sponsors', href: route('admin.sponsors.index'), icon: Dot, permission: 'view sponsors' },
            { title: 'Coupons', href: route('admin.coupons.index'), icon: Dot, permission: 'view coupons' },
            { title: 'Tickets Report', href: route('admin.report.ticket.sale'), icon: Dot },
            { title: 'Cancel Tickets', href: route('admin.cancel-ticket.index'), icon: Dot },
            { title: 'Event Fee', href: route('admin.events.fee-settings'), icon: Dot },
        ],
        permission: 'view events',
    },
    {
        title: 'Event Organizer',
        href: route('admin.home'),
        icon: CalendarCog,
        children: [
            { title: 'List', href: route('admin.organizer.profile.index'), icon: Dot, permission: 'view event organizers' },
            { title: 'Scanners', href: route('admin.scanners.index'), icon: Dot, permission: 'view scanners' },
            { title: 'Payout List', href: route('admin.payouts.index'), icon: Dot, permission: 'view payouts' },
        ],
    },
    {
        title: 'E-Wallet',
        href: route('admin.home'),
        icon: Wallet,
        children: [
            { title: 'Dashboard', href: route('admin.e.wallet.dashboard'), icon: Dot, permission: 'view wallet dashboard' },
            { title: 'General Settings', href: route('admin.e.wallet.setting'), icon: Dot, permission: 'view wallet settings' },
            { title: 'Payment Methods', href: route('admin.gateway.index'), icon: Dot, permission: 'view payment methods' },
            { title: 'Wallet KYC', href: route('admin.user-wallet-kyc.index'), icon: Dot, permission: 'view wallet kyc' },
        ],
    },
    {
        title: 'Ad Management',
        href: route('admin.home'),
        icon: Copy,
        children: [
            { title: 'Dashboard', href: route('admin.ads.dashboard.index'), icon: Dot, permission: 'view ads dashboard' },
            { title: 'Analytics', href: route('admin.ads.analytics.index'), icon: Dot, permission: 'view ads analytics' },
            { title: 'Reports', href: route('admin.ads.reports.index'), icon: Dot, permission: 'view ads reports' },
            { title: 'All Ads', href: route('admin.ads.index'), icon: Dot, permission: 'view all ads list' },
            { title: 'Restaurants', href: route('admin.ads.restaurants.index'), icon: Dot, permission: 'view restaurants ads' },
            { title: 'Clubs & Fetes', href: route('admin.ads.clubs-fetes.index'), icon: Dot, permission: 'view clubs and fetes ads' },
            { title: 'Campaigns', href: route('admin.ads.campaigns.index'), icon: Dot, permission: 'view ads campaigns' },
            { title: 'Email Sponsor', href: route('admin.ads.email-sponsor.index'), icon: Dot, permission: 'view ads email sponsor' },
            { title: 'C360 News', href: route('admin.ads.c360-news.index'), icon: Dot, permission: 'view ads c360 news' },
        ],
    },
    {
        title: 'Swipes',
        href: route('admin.swipes.index'),
        icon: FileCode,
        permission: 'view swipes',
    },
    {
        title: 'Subscription',
        href: route('admin.subscriptions.index'),
        icon: PoundSterling,
        children: [
            { title: 'Dashboard', href: route('admin.subscriptions-dashboard'), icon: Dot, permission: 'view user plans' },
            { title: 'Users', href: route('admin.subscription.users.list'), icon: Dot, permission: 'view users' },
            { title: 'Swipes', href: route('admin.swipes.index'), icon: Dot, permission: 'view swipes' },
            { title: 'Subscriptions', href: route('admin.subscriptions.index'), icon: Dot, permission: 'subscription index' },
            { title: 'Revenue', href: route('admin.subscriptions.revenue'), icon: Dot, permission: 'view wallet' },
            { title: 'Go Live', href: route('admin.subscriptions.go-live'), icon: Dot, permission: 'view dashboard' },
            { title: 'Clubs & Restaurants', href: route('admin.subscriptions.club.restaurant'), icon: Dot, permission: 'view clubs' },
            { title: 'Marketplace', href: route('admin.subscriptions.marketplace'), icon: Dot, permission: 'view products' },
            { title: 'Events', href: route('admin.subscriptions.events'), icon: Dot, permission: 'view events' },
            { title: 'Lab', href: route('admin.subscriptions.lab'), icon: Dot, permission: 'taxes dashboard' },
        ],
    },
    {
        title: 'Live Streaming',
        href: route('admin.home'),
        icon: Tv,
        children: [
            { title: 'Dashboard', href: route('admin.live-streams.dashboard'), icon: Dot, permission: 'view dashboard' },
            { title: 'Messages', href: route('admin.messages.index'), icon: Dot, permission: 'view messages' },
            { title: 'Encounters', href: route('admin.encounters.index'), icon: Dot, permission: 'view encounters' },
            { title: 'Calls', href: route('admin.calls.index'), icon: Dot, permission: 'view calls' },
            { title: 'Live Streams', href: route('admin.live_streams.index'), icon: Dot, permission: 'view live streams' },
            { title: 'Live Gifts', href: route('admin.linkup-live-gifts.index'), icon: Dot, permission: 'view gifts' },
            { title: 'Payments', href: route('admin.payments.index'), icon: Dot, permission: 'view payments' },
            { title: 'Admin Fee Settings', href: route('admin.admin_fee.index'), icon: Dot, permission: 'view subscribed users' },
            { title: 'Fee Analytics', href: route('admin.live-streams.fee-analytics'), icon: Dot, permission: 'view fee analytics' },
            { title: 'Live Analytics', href: route('admin.live-streams.live-analytics'), icon: Dot, permission: 'view live analytics' },
            { title: 'Moderation', href: route('admin.live-streams.moderation'), icon: Dot, permission: 'view moderation' },
            { title: 'Categories', href: route('admin.live-streams.categories'), icon: Dot, permission: 'view categories' },
        ],
    },

    {
        title: 'Shop',
        href: route('admin.home'),
        icon: ShoppingCart,
        children: [
            { title: 'Dashboard', href: route('admin.dashboard.index'), icon: Dot, permission: 'view products' },
            { title: 'Products', href: route('admin.products.index'), icon: Dot, permission: 'view products' },
            { title: 'Categories', href: route('admin.product-categories.index'), icon: Dot, permission: 'view product categories' },
            { title: 'Fees', href: route('admin.products.fees'), icon: Dot, permission: 'fees' },
            { title: 'Orders', href: route('admin.orders.index'), icon: Dot, permission: 'view orders' },
            { title: 'Escrow & Releases', href: route('admin.orders.escrow-releases'), icon: Dot, permission: 'view escrow releases' },
            { title: 'Seller Transfers', href: route('admin.orders.seller-transfers'), icon: Dot, permission: 'view seller transfers' },
            { title: 'Wallets', href: route('admin.shop.wallets.index'), icon: Dot, permission: 'view wallets' },
            { title: 'Sellers (Store/Group)', href: route('admin.merchants.index'), icon: Dot, permission: 'view sellers (Store/Group)' },
        ],
    },
    {
        title: 'Reports',
        href: route('admin.e.wallet.transaction'),
        icon: FileText,
        children: [{ title: 'Wallet', href: route('admin.report.wallet'), icon: Dot }],
        permission: 'view reports',
    },
    {
        title: 'Push Notification',
        href: route('admin.push.notification.view'),
        icon: Bell,
        permission: 'view push notifications',
    },
    {
        title: 'Emails',
        href: route('admin.home'),
        icon: Mail,
        children: [
            { title: 'Send Emails', href: route('admin.send.emails'), icon: Dot, permission: 'send emails' },
            { title: 'Email Templates', href: route('admin.email-templates-index'), icon: Dot, permission: 'view emails' },
        ],
    },
    {
        title: 'Taxes',
        href: route('admin.taxes-index'),
        icon: FileText,
        permission: 'view taxes',
        children: [
            { title: 'Taxes', href: route('admin.taxes-index'), icon: Dot, permission: 'view taxes' },
            { title: 'Dashboard', href: route('admin.taxes-dashboard'), icon: Dot, permission: 'taxes dashboard' },
            { title: 'Tax Settings', href: route('admin.taxes-setting'), icon: Dot, permission: 'taxes setting' },
            { title: 'Tax Remittance Settings', href: route('admin.taxes-remittance-setting'), icon: Dot, permission: 'taxes remittance setting' },
            { title: 'Tax Remittance Center', href: route('admin.taxes-remittance-center'), icon: Dot, permission: 'taxes remittance center' },
        ],
    },


    {
        title: 'News Management',
        href: route('admin.home'),
        icon: Newspaper,
        children: [{ title: 'News List', href: route('admin.news.index'), icon: Dot, permission: 'view news' }],
    },
    {
        title: 'Admins',
        href: route('admin.home'),
        icon: CircleUserRound,
        children: [
            { title: 'Users List', href: route('admin.admins.index'), icon: Dot, permission: 'view admins' },
            { title: 'Roles', href: route('admin.roles.index'), icon: Dot, permission: 'view roles' },
        ],
    },
    {
        title: 'Settings',
        href: route('admin.home'),
        icon: CogIcon,
        children: [
            { title: 'App Settings', href: route('admin.appsetting'), icon: Dot, permission: 'view app settings' },
            { title: 'Ads Settings', href: route('admin.ads-setting.show', 'adsSettings'), icon: Dot, permission: 'view ads settings' },
            { title: 'SMTP Settings', href: route('admin.smtp-setting.show', 'smtpSettings'), icon: Dot, permission: 'view smtp settings' },
        ],
    },
];

</script>
<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('admin.dashboard')" class="bg-black">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
