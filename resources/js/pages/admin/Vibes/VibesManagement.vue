<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch, onUnmounted } from 'vue';
import {
    Menu, HelpCircle, Star, Search, Coins, ArrowLeft, ArrowUpRight, PlusSquare, Gift, Heart, Home,
    PlusCircle, Megaphone, User, X, Image as ImageIcon, SlidersHorizontal, ShieldAlert, Code,
    PlayCircle, Music, Ticket, ShoppingBag, Repeat2, Send, Bookmark, MoreHorizontal, Flag,
    Upload, Link as LinkIcon, ChevronRight, CheckCircle2, AlertCircle, ShoppingCart, Repeat,
    CreditCard, Landmark, Wallet, Newspaper, Trash2, Ban, ThumbsUp, ThumbsDown, MessageCircle, FileText,
    Repeat2 as RepostIcon, Heart as HeartIcon, Share2 as ShareIcon, ImagePlus, Globe2, Activity,
    TrendingUp, Boxes, LayoutDashboard, Utensils, BadgeDollarSign, Radio, Sparkles, Flame, Terminal,
    Smartphone, HelpCircle as HelpIcon, Star as StarIcon, Image as ImageLucide
} from 'lucide-vue-next';

interface Props {
    tab?: string;
}
const props = defineProps<Props>();

// --- VERBATIM CONSTANTS & DATA FROM HTML ---
const SCOTIA_SHARE = 0.40;

const units = [
 {key:'tickets',name:'Ticket Sales',icon:'ticket',platformRate:.065,bankRate:0,costRate:.01,desc:'Tickets, VIP, drink tickets, event add-ons'},
 {key:'subscriptions',name:'Subscriptions',icon:'badge-dollar-sign',platformRate:1,bankRate:0,costRate:.04,desc:'Premium users and paid plans'},
 {key:'marketplace',name:'Marketplace',icon:'shopping-bag',platformRate:.05,bankRate:0,costRate:.01,desc:'Seller fees and commissions'},
 {key:'eats',name:'LinkUp Eats',icon:'utensils',platformRate:.075,bankRate:0,costRate:.025,desc:'QR menus, ordering, pickup, delivery'},
 {key:'merchantPay',name:'Merchant Pay',icon:'credit-card',platformRate:.02,bankRate:0,costRate:.007,desc:'QR payments, card, ACH, bill pay'},
 {key:'wallet',name:'Wallet & Money Movement',icon:'wallet',platformRate:.025,bankRate:.0175,costRate:.008,desc:'Cash-in (top-up), cash-out (withdrawal), transfers — the only bank/processing touchpoint'},
 {key:'live',name:'LinkUp Live',icon:'radio',platformRate:.5,bankRate:0,costRate:.08,desc:'Live coins, gifts, creators'},
 {key:'ads',name:'Advertising Revenue',icon:'megaphone',platformRate:1,bankRate:0,costRate:.12,desc:'Swipe ads, email ads, promoted posts'},
 {key:'wellness',name:'Wellness & Spa',icon:'sparkles',platformRate:.0675,bankRate:0,costRate:.015,desc:'Bookings and appointment marketplace'},
 {key:'cookouts',name:'Cookouts',icon:'flame',platformRate:.0675,bankRate:0,costRate:.015,desc:'Food events and vendor sales'},
 {key:'linkup360',name:'LinkUp 360 News Ads',icon:'newspaper',platformRate:1,bankRate:0,costRate:.15,desc:'Sponsored news and media placements'}
];

const countriesData = [
 {country:'Bahamas',region:'Local',users:100000,merchants:600,organizers:120,tickets:1000000,subscriptions:50000,marketplace:250000,eats:500000,merchantPay:2000000,wallet:1500000,live:100000,ads:25000,wellness:150000,cookouts:90000,linkup360:40000,coinsPurchased:250000,coinsRedeemed:90000},
 {country:'Jamaica',region:'Regional',users:280000,merchants:1200,organizers:250,tickets:720000,subscriptions:90000,marketplace:410000,eats:650000,merchantPay:1600000,wallet:950000,live:180000,ads:45000,wellness:190000,cookouts:140000,linkup360:55000,coinsPurchased:420000,coinsRedeemed:170000},
 {country:'Trinidad & Tobago',region:'Regional',users:190000,merchants:900,organizers:190,tickets:610000,subscriptions:70000,marketplace:380000,eats:520000,merchantPay:1350000,wallet:840000,live:160000,ads:39000,wellness:150000,cookouts:120000,linkup360:47000,coinsPurchased:360000,coinsRedeemed:140000},
 {country:'Barbados',region:'Regional',users:70000,merchants:380,organizers:95,tickets:330000,subscriptions:42000,marketplace:190000,eats:260000,merchantPay:720000,wallet:460000,live:85000,ads:21000,wellness:90000,cookouts:65000,linkup360:26000,coinsPurchased:150000,coinsRedeemed:60000},
 {country:'Guyana',region:'Regional',users:130000,merchants:620,organizers:140,tickets:420000,subscriptions:56000,marketplace:270000,eats:360000,merchantPay:980000,wallet:600000,live:120000,ads:28000,wellness:100000,cookouts:82000,linkup360:31000,coinsPurchased:210000,coinsRedeemed:85000},
 {country:'Dominican Republic',region:'Regional',users:220000,merchants:1000,organizers:210,tickets:520000,subscriptions:68000,marketplace:310000,eats:430000,merchantPay:1100000,wallet:710000,live:150000,ads:36000,wellness:130000,cookouts:99000,linkup360:41000,coinsPurchased:290000,coinsRedeemed:120000},
 {country:'United States',region:'International',users:450000,merchants:2400,organizers:410,tickets:1280000,subscriptions:180000,marketplace:820000,eats:0,merchantPay:2500000,wallet:1750000,live:420000,ads:160000,wellness:320000,cookouts:180000,linkup360:120000,coinsPurchased:950000,coinsRedeemed:410000},
 {country:'Canada',region:'International',users:260000,merchants:1400,organizers:260,tickets:840000,subscriptions:130000,marketplace:560000,eats:0,merchantPay:1600000,wallet:1100000,live:300000,ads:110000,wellness:210000,cookouts:120000,linkup360:85000,coinsPurchased:620000,coinsRedeemed:260000},
 {country:'Brazil',region:'International',users:370000,merchants:1900,organizers:350,tickets:960000,subscriptions:150000,marketplace:640000,eats:0,merchantPay:1850000,wallet:1300000,live:360000,ads:130000,wellness:260000,cookouts:140000,linkup360:97000,coinsPurchased:780000,coinsRedeemed:330000},
 {country:'Colombia',region:'International',users:240000,merchants:1300,organizers:240,tickets:690000,subscriptions:105000,marketplace:470000,eats:0,merchantPay:1250000,wallet:860000,live:240000,ads:90000,wellness:180000,cookouts:95000,linkup360:70000,coinsPurchased:520000,coinsRedeemed:210000}
];

const feedCfg = ref({coinValue:0.01,creatorShare:50});
const feedCreators = ref([
 {id:'CR-01',name:'Tanya Baptiste',handle:'@tanyab',country:'Trinidad and Tobago',followers:184000,posts:312,coins:42000,verified:true,status:'Active'},
 {id:'CR-02',name:'Island Vibez',handle:'@islandvibez',country:'Jamaica',followers:298000,posts:540,coins:88000,verified:true,status:'Active'},
 {id:'CR-03',name:'Renee Bethel',handle:'@reneeb',country:'Bahamas',followers:76000,posts:201,coins:15500,verified:false,status:'Active'},
 {id:'CR-04',name:'Carlos Mendoza',handle:'@carlosmx',country:'Colombia',followers:142000,posts:288,coins:33500,verified:true,status:'Active'},
 {id:'CR-05',name:'Gabriela Silva',handle:'@gabis',country:'Brazil',followers:410000,posts:690,coins:121000,verified:true,status:'Active'},
 {id:'CR-06',name:'Sofía Hernández',handle:'@sofiह',country:'Mexico',followers:99000,posts:175,coins:21000,verified:false,status:'Active'},
 {id:'CR-07',name:'Andre Charles',handle:'@drech',country:'Jamaica',followers:54000,posts:140,coins:9800,verified:false,status:'Review'}
]);

const feedPosts = ref([
 {id:'PT-1001',creator:'Island Vibez',handle:'@islandvibez',country:'Jamaica',type:'Reel',caption:'Carnival prep 🔥 #fete #jamaica',likes:48200,comments:1820,shares:6400,views:512000,coins:9800,status:'Live',date:'2026-06-09', img:'https://picsum.photos/seed/PT-1001/600/600', liked: false, music: 'Soca Anthem 2026 — DJ Vibez', sponsored: false, tag: {kind:'Ticket',name:'Carnival Fete — VIP',price:120,ref:'EVT-CARN'}},
 {id:'PT-1002',creator:'Tanya Baptiste',handle:'@tanyab',country:'Trinidad and Tobago',type:'Photo',caption:'Sunset in Maracas 🌅',likes:22100,comments:640,shares:1200,views:140000,coins:3400,status:'Live',date:'2026-06-09', img:'https://picsum.photos/seed/PT-1002/600/600', liked: false, tag: {kind:'Product',name:'Maracas Beach Print',price:45,ref:'MKT-PRINT'}},
 {id:'PT-1003',creator:'Gabriela Silva',handle:'@gabis',country:'Brazil',type:'Reel',caption:'Açaí bowl recipe 🍇',likes:73400,comments:2950,shares:11200,views:880000,coins:15600,status:'Live',date:'2026-06-08', img:'https://picsum.photos/seed/PT-1003/600/600', liked: false, music: 'Samba Lo-Fi — Rio Beats', sponsored: true, tag: {kind:'Product',name:'Açaí Starter Kit',price:34.99,ref:'MKT-ACAI'}},
 {id:'PT-1004',creator:'Renee Bethel',handle:'@reneeb',country:'Bahamas',type:'Carousel',caption:'Craft market finds 🛍️ #shoplocal',likes:9800,comments:310,shares:540,views:61000,coins:1450,status:'Live',date:'2026-06-08', img:'https://picsum.photos/seed/PT-1004/600/600', liked: false, tag: {kind:'Product',name:'Handmade Straw Bag',price:108.11,ref:'MKT-STRAW'}},
 {id:'PT-1005',creator:'Carlos Mendoza',handle:'@carlosmx',country:'Colombia',type:'Reel',caption:'Bogotá street food tour',likes:31200,comments:980,shares:2600,views:240000,coins:5200,status:'Live',date:'2026-06-07', img:'https://picsum.photos/seed/PT-1005/600/600', liked: false, music:'Cumbia Mix — La Calle'},
 {id:'PT-1006',creator:'Sofía Hernández',handle:'@sofiह',country:'Mexico',type:'Story',caption:'Behind the scenes 🎬',likes:5400,comments:120,shares:90,views:38000,coins:600,status:'Live',date:'2026-06-07', img:'https://picsum.photos/seed/PT-1006/600/600', liked: false},
 {id:'PT-1007',creator:'Andre Charles',handle:'@drech',country:'Jamaica',type:'Photo',caption:'New drip 😎',likes:4100,comments:95,shares:60,views:22000,coins:300,status:'Flagged',date:'2026-06-06', img:'https://picsum.photos/seed/PT-1007/600/600', liked: false},
 {id:'PT-1008',creator:'Island Vibez',handle:'@islandvibez',country:'Jamaica',type:'Reel',caption:'Dance challenge 💃 #linkup',likes:60100,comments:2400,shares:8800,views:640000,coins:12400,status:'Live',date:'2026-06-06', img:'https://picsum.photos/seed/PT-1008/600/600', liked: false, music: 'Dancehall Riddim — Kingston'}
]);

const vibesAds = ref([
 {id:'AD-9001',advertiser:'Scotiabank',handle:'@scotiabank',logo:'🏦',caption:'Bank smarter across the Caribbean with the Scotiabank + LinkUp wallet. 💳 #linkup',img:'https://picsum.photos/seed/adbank/600/600',cta:'Learn More',url:'https://www.scotiabank.com',target:'Caribbean',status:'Active',likes:4820,comments:120,shares:340,followers:18200,liked:false, clicks: 840, mediaType: 'image'},
 {id:'AD-9002',advertiser:'Caribbean Travel Co.',handle:'@caribtravel',logo:'☀️',caption:'Escape to the islands ☀️ Exclusive LinkUp member rates on flights + resorts.',img:'https://picsum.photos/seed/adtravel/600/600',cta:'Book Now',url:'https://example.com/travel',target:'All',status:'Active',likes:9120,comments:430,shares:1200,followers:52000,liked:false, clicks: 1250, mediaType: 'image'},
 {id:'AD-9003',advertiser:'Digicel',handle:'@digicel',logo:'📱',caption:'Fastest Caribbean data plans. Stream LinkUp Vibes anywhere. 🔥',img:'https://picsum.photos/seed/addigicel/600/600',cta:'Get the Plan',url:'https://www.digicelgroup.com',target:'All',status:'Paused',likes:2100,comments:60,shares:140,followers:9800,liked:false, clicks: 310, mediaType: 'image'}
]);

const feedReports = ref([
 {id:'MR-01',post:'PT-1007',creator:'Andre Charles',country:'Jamaica',reason:'Spam / misleading',reports:14,risk:'Medium',status:'Open'},
 {id:'MR-02',post:'PT-2210',creator:'@quickcash',country:'United States',reason:'Scam / financial fraud',reports:38,risk:'High',status:'Open'},
 {id:'MR-03',post:'PT-2188',creator:'@nightlife242',country:'Bahamas',reason:'Nudity / adult content',reports:9,risk:'Medium',status:'Investigating'},
 {id:'MR-04',post:'PT-2055',creator:'@memequeen',country:'Mexico',reason:'Hate speech',reports:21,risk:'High',status:'Open'},
 {id:'MR-05',post:'PT-1990',creator:'@dealsdaily',country:'Colombia',reason:'Counterfeit goods',reports:6,risk:'Low',status:'Resolved'}
]);

const feedViewer = ref({
    name:'You',
    handle:'@you',
    coins:5000,
    following:{'@islandvibez':true} as any,
    saved:{} as any,
    stories: [] as any[],
    followingAds: {} as any
});

const postComments = ref({
    'PT-1001':[{user:'@dancehallqueen',text:'This is fire 🔥🔥',ts:'2026-06-09T10:00:00Z'},{user:'@trinikid',text:'See you at the fete!',ts:'2026-06-09T10:05:00Z'}],
} as any);

const feedTips = ref([] as any[]);

const feedEngineCfg = ref({
    rank:{recency:70,engagement:80,affinity:60,watch:75,diversity:40},
    types:{Photo:true,Reel:true,Story:true,Carousel:true},
    monet:{tips:true,subs:true,shoppable:true,ads:true,adRevShare:55},
    automod:{autoHideReports:25,aiConfidence:85}
});

const feedPolicy = ref([
 {cat:'Nudity / Adult',action:'Age-restrict'},
 {cat:'Violence / Graphic',action:'Auto-hide'},
 {cat:'Hate Speech',action:'Auto-remove'},
 {cat:'Harassment / Bullying',action:'Auto-hide'},
 {cat:'Spam / Misleading',action:'Auto-hide'},
 {cat:'Scam / Financial Fraud',action:'Auto-remove'},
 {cat:'Counterfeit Goods',action:'Auto-hide'},
 {cat:'Misinformation',action:'Age-restrict'},
 {cat:'Self-harm',action:'Auto-remove'},
 {cat:'Illegal / Regulated Goods',action:'Auto-remove'}
]);

const LINKUP_VIBES_GIFTS = [
 {id:'hola',name:'Hola',emoji:'👋',desc:'A sweet hello to start.',cost:0},
 {id:'smile',name:'Island Smile',emoji:'😊',desc:'A warm little signal.',cost:10},
 {id:'soca',name:'Soca Vibe',emoji:'🎶',desc:'Because the rhythm matters.',cost:35},
 {id:'dance',name:'Dance Move',emoji:'💃',desc:'Fun energy—no pressure.',cost:50},
 {id:'sunset',name:'Sunset',emoji:'🌅',desc:'Soft romantic mood.',cost:75},
 {id:'coconut',name:'Coconut Drink',emoji:'🥥',desc:'Cheers from the islands.',cost:90},
 {id:'rose',name:'Rose',emoji:'🌹',desc:'Classic, respectful romance.',cost:120},
 {id:'kiss',name:'Blown Kiss',emoji:'😘',desc:'Flirty, but still classy.',cost:150},
 {id:'date',name:'Date Night',emoji:'🕯️',desc:"A clear 'I'm interested.'",cost:220},
 {id:'beach',name:'Beach LinkUp',emoji:'🏝️',desc:"Let's link up by the water.",cost:250},
 {id:'heart',name:'Heart Glow',emoji:'💖',desc:"A louder like.",cost:300},
 {id:'crown',name:'Caribbean Crown',emoji:'👑',desc:'Big vibe, big respect.',cost:400}
];

const feedAPIContract = [
 ['GET','/feed?cursor&limit&country&type','Ranked For-You feed'],
 ['GET','/feed/stories','Stories rail (creators)'],
 ['GET','/feed/posts/:id','Single post'],
 ['POST','/feed/posts','Create post'],
 ['DELETE','/feed/posts/:id','Delete post'],
 ['GET','/feed/posts/:id/comments','List comments'],
 ['POST','/feed/posts/:id/comments','Add comment {text}'],
 ['POST/DELETE','/feed/posts/:id/like','Like / unlike'],
 ['POST','/feed/posts/:id/share','Share'],
 ['POST','/feed/posts/:id/save','Save toggle'],
 ['POST','/feed/posts/:id/tip','Tip coins {coins} → 50/50 split'],
 ['POST','/feed/posts/:id/buy','Shoppable checkout → {checkoutUrl,channel}'],
 ['POST/DELETE','/creators/:handle/follow','Follow / unfollow'],
 ['GET','/me/feed','Viewer (coin balance, following, saved)']
];

// --- APP STATE & REACTIVITY ---
const activeView = ref(props.tab || 'feedDashboardCommand');
const sidebarVisible = ref(true);
const regionFilter = ref('All');
const countryFilter = ref('All Countries');
const periodFilter = ref('Monthly');
const globalSearch = ref('');
const feedSearchQuery = ref('');
const toastMsg = ref('');
const toastVisible = ref(false);

watch(() => props.tab, (newTab) => {
    if (newTab) activeView.value = newTab;
});

// --- UTILS ---
const fmt = (n: number) => new Intl.NumberFormat('en-US',{style:'currency',currency:'USD',maximumFractionDigits:0}).format(n);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

const scale = computed(() => {
    const p = periodFilter.value;
    return p==='Today'?1/30:p==='Weekly'?.25:p==='Quarterly'?3:p==='Yearly'?12:p==='5-Year'?60:1;
});

const countryFin = (c: any) => {
    let gross=0,platform=0,bank=0,cost=0;
    units.forEach(u=>{
        const v=(c[u.key as keyof typeof c] as number || 0)*scale.value;
        gross+=v; platform+=v*u.platformRate; bank+=v*u.bankRate; cost+=v*u.costRate;
    });
    return {gross,platform,bank,cost,net:platform-cost};
};

const totals = computed(() => {
    const rs = countriesData.filter(c=>(regionFilter.value==='All'||c.region===regionFilter.value)&&(countryFilter.value==='All Countries'||c.country===countryFilter.value)).map(countryFin);
    const rawRows = countriesData.filter(c=>(regionFilter.value==='All'||c.region===regionFilter.value)&&(countryFilter.value==='All Countries'||c.country===countryFilter.value));
    return {
        gross:rs.reduce((s,x)=>s+x.gross,0),
        platform:rs.reduce((s,x)=>s+x.platform,0),
        bank:rs.reduce((s,x)=>s+x.bank,0),
        cost:rs.reduce((s,x)=>s+x.cost,0),
        net:rs.reduce((s,x)=>s+x.net,0),
        users:rawRows.reduce((s,c)=>s+c.users,0)*scale.value,
        merchants:rawRows.reduce((s,c)=>s+c.merchants,0),
        organizers:rawRows.reduce((s,c)=>s+c.organizers,0),
        countries:rawRows.length
    };
});

// --- FEED ALGORITHM (Verbatim logic) ---
const feedRankScore = (p: any) => {
    const r=feedEngineCfg.value.rank;
    const eng=(p.likes+p.comments*2+p.shares*3+p.coins*2);
    const rec=Math.max(0,30-((Date.now()-new Date(p.date).getTime())/864e5));
    const watch=(p.views||0)/1000;
    return eng*(r.engagement/100)+rec*1000*(r.recency/100)+watch*(r.watch/100)+(p.coins||0)*(r.affinity/100);
};

const getRankedFeed = computed(() => {
    const list = feedPosts.value.filter(p=>p.status!=='Removed'&&feedEngineCfg.value.types[p.type as keyof typeof feedEngineCfg.value.types]);
    return list.slice().sort((a,b)=>feedRankScore(b)-feedRankScore(a));
});

// --- INTERACTIONS ---
const showToast = (msg: string) => {
    toastMsg.value = msg;
    toastVisible.value = true;
    setTimeout(() => { toastVisible.value = false; }, 1800);
};

const fpLike = (id: string) => {
    const p = feedPosts.value.find(x=>x.id===id);
    if(p) { p.liked = !p.liked; p.likes += p.liked?1:-1; }
};

const fpFollow = (handle: string) => {
    feedViewer.value.following[handle] = !feedViewer.value.following[handle];
};

const fpSave = (id: string) => {
    feedViewer.value.saved[id] = !feedViewer.value.saved[id];
    const p = feedPosts.value.find(x=>x.id===id);
    if(p) p.saves += feedViewer.value.saved[id] ? 1 : -1;
};

// --- MODAL STATES ---
const showCoinGiftSheet = ref(false);
const coinGiftTargetId = ref<string | null>(null);
const selectedCoinGift = ref<any>(null);

const showCommentModal = ref(false);
const commentPostId = ref<string | null>(null);
const commentInput = ref('');

const showActivityModal = ref(false);
const activityTab = ref('all');

const showStoryViewer = ref(false);
const storyUserHandle = ref('');

const openCoinGift = (id: string) => {
    coinGiftTargetId.value = id;
    selectedCoinGift.value = null;
    showCoinGiftSheet.value = true;
};

const sendCoinGift = () => {
    if(!selectedCoinGift.value || !coinGiftTargetId.value) return;
    const coins = selectedCoinGift.value.cost;
    if(feedViewer.value.coins < coins) return alert('Not enough Coins');

    feedViewer.value.coins -= coins;
    const p = feedPosts.value.find(x=>x.id===coinGiftTargetId.value);
    if(p) {
        p.coins += coins;
        const cr = feedCreators.value.find(c=>c.handle===p.handle);
        if(cr) cr.coins += coins;
    }
    showCoinGiftSheet.value = false;
    showToast(`${selectedCoinGift.value.emoji} Gift sent!`);
};

const openComments = (id: string) => {
    commentPostId.value = id;
    showCommentModal.value = true;
};

const postComment = () => {
    if(!commentInput.value.trim() || !commentPostId.value) return;
    const id = commentPostId.value;
    if(!postComments.value[id]) postComments.value[id] = [];
    postComments.value[id].push({user: feedViewer.value.handle, text: commentInput.value, ts: new Date().toISOString()});
    const p = feedPosts.value.find(x=>x.id===id);
    if(p) p.comments += 1;
    commentInput.value = '';
};

const moderatePost = (id: string, action: string) => {
    const r = feedReports.value.find(x=>x.id===id);
    if(!r) return;
    r.status = action === 'remove' ? 'Removed' : action === 'approve' ? 'Resolved' : 'Escalated';
    const p = feedPosts.value.find(x=>x.id === r.post);
    if(p && action === 'remove') p.status = 'Removed';
};

const feedKpis = computed(() => {
    const posts = feedPosts.value;
    const totalCoins = posts.reduce((a,p)=>a+p.coins,0);
    const eng = posts.reduce((a,p)=>a+p.likes+p.comments+p.shares,0);
    const views = posts.reduce((a,p)=>a+p.views,0);
    return [
        {label: 'Creators', val: feedCreators.value.length},
        {label: 'Posts (period)', val: num(posts.length * scale.value)},
        {label: 'Engagement Rate', val: (views ? (eng/views*100).toFixed(1) : '0') + '%', color: 'text-sky-600'},
        {label: 'Coins Tipped', val: num(totalCoins * scale.value), sub: '$' + (totalCoins * feedCfg.value.coinValue * scale.value).toFixed(0), color: 'text-amber-500'},
        {label: 'LinkUp Coin Rev', val: '$' + (totalCoins * feedCfg.value.coinValue * (1 - feedCfg.value.creatorShare/100) * scale.value).toFixed(0), color: 'text-green-600'}
    ];
});

onMounted(() => {
    document.body.classList.add('new-admin-body');
});
onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="LinkUp — LinkUp Vibes (standalone)" />
    <div class="flex min-h-screen text-slate-900 bg-[#f8fbff]" style="font-family:'Inter',system-ui,sans-serif">

        <NewAppSidebar :active-id="activeView" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Executive Command Center"
                :countries="[]"
                :metrics="{
                    gtv: fmt(totals.gross),
                    revenue: fmt(totals.platform),
                    bank: fmt(totals.bank),
                    net: fmt(totals.net + totals.bank * (1-SCOTIA_SHARE)),
                    users: num(totals.users),
                    merchants: num(totals.merchants),
                    organizers: num(totals.organizers),
                    countries: String(totals.countries)
                }"
                @toggle-sidebar="toggleSidebar"
                @filter-change="(f:any) => { regionFilter = f.region; countryFilter = f.country; periodFilter = f.period; }"
            />

            <!-- Financial Ribbon (Literal Match) -->
            <div class="px-5 lg:px-8 py-2">
                <div class="ribbon rounded-3xl p-4 grid grid-cols-2 md:grid-cols-4 2xl:grid-cols-8 gap-3 text-white shadow-xl">
                    <div><p class="text-xs opacity-60 uppercase font-black">GTV</p><b class="text-lg">{{ fmt(totals.gross) }}</b></div>
                    <div><p class="text-xs opacity-60 uppercase font-black">LinkUp Rev.</p><b class="text-lg">{{ fmt(totals.platform) }}</b></div>
                    <div><p class="text-xs opacity-60 uppercase font-black">Proc. Pool</p><b class="text-lg">{{ fmt(totals.bank) }}</b></div>
                    <div><p class="text-xs opacity-60 uppercase font-black">Net Profit</p><b class="text-lg">{{ fmt(totals.net + totals.bank * (1-SCOTIA_SHARE)) }}</b></div>
                    <div><p class="text-xs opacity-60 uppercase font-black">Users</p><b class="text-lg">{{ num(totals.users) }}</b></div>
                    <div><p class="text-xs opacity-60 uppercase font-black">Merchants</p><b class="text-lg">{{ num(totals.merchants) }}</b></div>
                    <div><p class="text-xs opacity-60 uppercase font-black">Organizers</p><b class="text-lg">{{ num(totals.organizers) }}</b></div>
                    <div><p class="text-xs opacity-60 uppercase font-black">Countries</p><b class="text-lg">{{ totals.countries }}</b></div>
                </div>
            </div>

            <section class="p-5 lg:p-8 space-y-6">

                <!-- 1. FEED DASHBOARD (SUB-COMPONENT) -->
                <div v-if="activeView === 'feedDashboardCommand'" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div><h3 class="text-3xl font-black text-slate-950">LinkUp Vibes</h3><p class="text-slate-500">Social content — photos, reels & stories — where fans don't just like & comment, they <b>send creators LinkUp Coins</b>. Back-office ready; public feed plugs in later. Period & region reactive.</p></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                        <div v-for="k in feedKpis" :key="k.label" class="card rounded-3xl p-5">
                            <p class="text-slate-500 font-bold uppercase text-[10px] tracking-widest">{{ k.label }}</p>
                            <h3 class="text-4xl font-black mt-1" :class="k.color || 'text-slate-900'">{{ k.val }}</h3>
                            <p v-if="k.sub" class="text-xs text-slate-400 font-bold mt-1">{{ k.sub }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6 font-bold">
                        <div class="card rounded-3xl p-6 2xl:col-span-1">
                            <div class="flex items-center gap-2 mb-2"><div class="h-9 w-9 rounded-xl bg-gradient-to-br from-pink-500 to-amber-400 grid place-items-center text-white font-black"><Coins class="w-4 h-4"/></div><h3 class="text-xl font-black">Coin Tipping Economy</h3></div>
                            <p class="text-sm text-slate-500 mb-3 leading-relaxed">When someone loves your post, they tip Coins. Split {{feedCfg.creatorShare}}% creator / {{100-feedCfg.creatorShare}}% LinkUp — the LinkUp Feed twist.</p>
                            <div class="rounded-2xl bg-slate-50 p-4 space-y-2 text-sm border border-slate-100 shadow-inner">
                                <div class="flex justify-between text-slate-500"><span>Estimated Gross (Period)</span><span class="text-slate-900 font-black">{{ fmt(totals.gross * 0.02) }}</span></div>
                                <div class="flex justify-between text-pink-600"><span>Creators share ({{feedCfg.creatorShare}}%)</span><span>{{ fmt(totals.gross * 0.02 * (feedCfg.creatorShare/100)) }}</span></div>
                                <div class="flex justify-between border-t border-slate-200 pt-2 text-green-600"><span>LinkUp revenue ({{100-feedCfg.creatorShare}}%)</span><span>{{ fmt(totals.gross * 0.02 * (1 - feedCfg.creatorShare/100)) }}</span></div>
                            </div>
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest mt-5 block ml-1">Coin value ($ each)</label>
                            <input v-model="feedCfg.coinValue" type="number" step="0.01" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition-all font-black text-slate-950">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest mt-3 block ml-1">Creator share (%)</label>
                            <input v-model="feedCfg.creatorShare" type="number" step="1" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 transition-all font-black text-slate-950">
                        </div>

                        <div class="card rounded-3xl p-6 2xl:col-span-2 overflow-hidden">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                                <h3 class="text-xl font-black text-slate-950">Content Monitor</h3>
                                <div class="relative w-full sm:w-72">
                                    <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                                    <input v-model="feedSearchQuery" class="rounded-2xl border border-slate-200 px-4 py-2.5 pl-10 text-sm w-full font-bold outline-none focus:border-indigo-400 transition-all bg-slate-50 focus:bg-white" placeholder="Search creators...">
                                </div>
                            </div>
                            <div class="overflow-x-auto scrollbar">
                                <table class="w-full text-left text-sm font-bold border-collapse">
                                    <thead class="text-xs uppercase text-slate-400 tracking-widest bg-slate-50/80 sticky top-0 z-10">
                                        <tr>
                                            <th class="py-4 px-4 border-b border-slate-100">Post</th>
                                            <th class="py-4 px-4 border-b border-slate-100">Creator</th>
                                            <th class="py-4 px-4 text-center border-b border-slate-100">Type</th>
                                            <th class="py-4 px-4 text-center border-b border-slate-100">Interactions</th>
                                            <th class="py-4 px-4 text-center border-b border-slate-100">Coins</th>
                                            <th class="py-4 px-4 text-center border-b border-slate-100">Status</th>
                                            <th class="py-4 px-4 text-right border-b border-slate-100">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="p in feedPosts.filter(x => !feedSearchQuery || x.handle.includes(feedSearchQuery) || x.caption.toLowerCase().includes(feedSearchQuery.toLowerCase()))" :key="p.id" class="hover:bg-slate-50 transition group">
                                            <td class="py-5 px-4"><div class="flex items-center gap-3">
                                                <img :src="p.img" class="h-10 w-10 rounded-lg object-cover border border-slate-200 shadow-sm flex-shrink-0 group-hover:scale-110 transition duration-300">
                                                <div class="flex flex-col min-w-0">
                                                    <span class="truncate max-w-[150px] text-slate-950 font-black">{{ p.id }}</span>
                                                    <span class="truncate max-w-[150px] text-[11px] text-slate-400 font-normal">{{ p.caption }}</span>
                                                </div>
                                            </div></td>
                                            <td class="py-5 px-4 text-slate-500 font-black uppercase text-[10px] tracking-tight">{{ p.creator }}<div class="text-[9px] text-slate-400 font-normal lowercase tracking-tighter">{{ p.handle }}</div></td>
                                            <td class="py-5 px-4 text-center"><span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-black uppercase">{{ p.type }}</span></td>
                                            <td class="py-5 px-4 text-center text-slate-900 leading-none">
                                                <div class="flex flex-col">
                                                    <span class="font-black">{{ num(p.likes) }} L</span>
                                                    <span class="text-[9px] text-slate-400 uppercase tracking-tighter mt-1">{{ num(p.comments) }} C / {{ num(p.shares) }} S</span>
                                                </div>
                                            </td>
                                            <td class="py-5 px-4 text-center text-amber-600 font-black">{{ num(p.coins) }} 🪙</td>
                                            <td class="py-5 px-4 text-center"><span class="px-2.5 py-1.5 rounded-full text-[9px] font-black uppercase shadow-sm border" :class="p.status==='Live' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ p.status }}</span></td>
                                            <td class="py-5 px-4 text-right">
                                                <div class="flex gap-1 justify-end">
                                                    <button @click="openCoinGift(p.id)" class="px-3 py-1.5 rounded-xl bg-slate-950 text-white text-[10px] font-black uppercase shadow-lg active:scale-95 transition-all">Tip</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. APP PREVIEW (SUB-COMPONENT) -->
                <div v-if="activeView === 'feedPreviewCommand'" class="space-y-6 flex flex-wrap gap-12 items-start justify-center xl:justify-start animate-in fade-in duration-300">
                    <div class="rounded-[3.5rem] bg-slate-950 p-4 shadow-2xl border-4 border-slate-800 ring-1 ring-white/10" style="width: 390px;">
                        <div class="rounded-[2.4rem] bg-white overflow-hidden flex flex-col relative shadow-inner" style="height: 780px;">
                            <div class="px-6 py-6 flex items-center justify-between border-b border-slate-50 shrink-0 bg-white/80 backdrop-blur-md sticky top-0 z-10">
                                <span class="text-2xl font-black italic text-slate-950" style="font-family: cursive;">Vibes</span>
                                <div class="flex items-center gap-4 text-slate-700">
                                    <div class="bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black border border-amber-100 shadow-sm"><Coins class="w-3.5 h-3.5 inline mr-1" />{{ num(feedViewer.coins) }}</div>
                                    <PlusSquare class="w-6 h-6" />
                                    <div class="relative"><Heart class="w-6 h-6 text-slate-700" /><span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-rose-500 text-white text-[8px] font-black grid place-items-center ring-2 ring-white">8</span></div>
                                </div>
                            </div>
                            <div class="flex-1 overflow-y-auto scrollbar-hide bg-slate-50/50">
                                <template v-for="(p, i) in getRankedFeed" :key="p.id">
                                    <div class="bg-white border-b border-slate-100 mb-2 last:mb-0 shadow-sm">
                                        <div class="px-5 py-4 flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 grid place-items-center font-black text-xs text-white uppercase border border-white shadow-sm">{{ p.handle.slice(1,2) }}</div>
                                                <div><p class="text-sm font-black text-slate-900 leading-tight">{{ p.handle }}</p><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">{{ p.country }}</p></div>
                                            </div>
                                            <button @click="fpFollow(p.handle)" class="text-[11px] font-black uppercase tracking-widest px-4 py-1.5 rounded-xl transition shadow-sm active:scale-95" :class="feedViewer.following[p.handle] ? 'bg-slate-100 text-slate-400 border border-slate-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-100'">{{ feedViewer.following[p.handle] ? 'Following' : 'Follow' }}</button>
                                        </div>
                                        <div class="relative aspect-square bg-slate-100 overflow-hidden" @dblclick="fpLike(p.id)"><img :src="p.img" class="w-full h-full object-cover transition duration-500 hover:scale-105"></div>
                                        <div class="px-5 py-4 flex items-center justify-between text-slate-900">
                                            <div class="flex items-center gap-6">
                                                <button @click="fpLike(p.id)" class="transition active:scale-150 transform" :class="p.liked ? 'text-rose-500' : ''"><Heart class="w-6 h-6" :fill="p.liked ? 'currentColor' : 'none'" /></button>
                                                <MessageCircle @click="openComments(p.id)" class="w-6 h-6 hover:text-indigo-600 transition cursor-pointer" />
                                                <Repeat2 class="w-6 h-6" />
                                                <Send class="w-6 h-6" />
                                            </div>
                                            <div class="flex items-center gap-4"><Coins @click="openCoinGift(p.id)" class="w-7 h-7 text-amber-500 cursor-pointer transition active:scale-110 hover:rotate-12" /><Bookmark @click="fpSave(p.id)" class="w-6 h-6 transition" :class="feedViewer.saved[p.id] ? 'text-indigo-600' : ''" :fill="feedViewer.saved[p.id] ? 'currentColor' : 'none'" /></div>
                                        </div>
                                        <div class="px-5 pb-6">
                                            <p class="text-xs font-black">{{ num(p.likes) }} likes</p>
                                            <p class="text-[13px] leading-snug mt-1 font-medium text-slate-800"><span class="font-black mr-2 text-slate-950">{{ p.handle }}</span> {{ p.caption }}</p>
                                            <div v-if="p.tag" class="mt-4"><button class="w-full bg-slate-950 text-white px-5 py-4 rounded-2xl text-sm font-black flex items-center justify-between transition active:scale-95 shadow-xl"><div class="flex items-center gap-2 uppercase tracking-widest text-[10px]"><ShoppingCart class="w-4 h-4"/> Buy {{p.tag.name}} · ${{p.tag.price}}</div> <ChevronRight class="w-4 h-4" /></button></div>
                                        </div>
                                    </div>
                                    <!-- Interleaved Ad View Mock -->
                                    <div v-if="(i+1) % 2 === 0 && vibesAds[Math.floor(i/2)]" class="bg-white border-b border-slate-100 mb-2 shadow-sm">
                                        <div class="px-5 py-4 flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-xl bg-amber-50 grid place-items-center text-lg shadow-sm border border-amber-100">{{ vibesAds[Math.floor(i/2)].logo }}</div>
                                                <div><p class="text-sm font-black text-slate-900 leading-tight">{{ vibesAds[Math.floor(i/2)].handle }}</p><p class="text-[10px] font-black uppercase text-amber-600 tracking-widest">Sponsored Partner</p></div>
                                            </div>
                                        </div>
                                        <div class="aspect-square bg-slate-50 overflow-hidden"><img :src="vibesAds[Math.floor(i/2)].img" class="w-full h-full object-cover"></div>
                                        <div class="p-5">
                                            <button class="w-full bg-slate-950 text-white px-5 py-4 rounded-2xl text-sm font-black flex items-center justify-between border border-slate-200 shadow-xl">{{ vibesAds[Math.floor(i/2)].cta }} <ChevronRight class="w-4 h-4" /></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="px-8 py-5 border-t border-slate-100 flex items-center justify-between text-slate-400 shrink-0 bg-white/95 backdrop-blur-md">
                                <Home class="w-7 h-7 text-slate-950" />
                                <Search class="w-7 h-7" />
                                <PlusCircle class="w-10 h-10 text-indigo-600 shadow-lg active:scale-95 transition" />
                                <Megaphone class="w-7 h-7" />
                                <User class="w-7 h-7" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. CREATORS (SUB-COMPONENT) -->
                <div v-if="activeView === 'feedCreatorsCommand'" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex justify-between items-center"><h3 class="text-3xl font-black text-slate-950">Creators</h3><button class="px-8 py-3.5 rounded-2xl bg-slate-950 text-white font-black text-sm shadow-xl active:scale-95 transition">+ Add Creator</button></div>
                    <div class="card rounded-[32px] bg-white border border-slate-100 shadow-sm overflow-hidden font-bold">
                        <table class="w-full text-left text-sm font-bold border-collapse">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100">
                                <tr><th class="py-4 px-6">Creator Profile</th><th class="py-4 px-6 text-center">Followers</th><th class="py-4 px-6 text-center">Coins Earned</th><th class="py-4 px-6 text-center">Status</th><th class="py-4 px-6 text-right">Action</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="c in feedCreators" :key="c.id" class="hover:bg-slate-50 transition">
                                    <td class="py-5 px-6"><div class="flex items-center gap-3"><div><p class="text-slate-900 font-black">{{c.name}} <CheckCircle2 v-if="c.verified" class="w-4 h-4 inline text-sky-500 ml-1"/></p><p class="text-xs text-slate-400 uppercase tracking-tight">{{c.handle}}</p></div></div></td>
                                    <td class="py-5 px-6 text-center text-slate-950 leading-none">{{ num(c.followers) }}</td>
                                    <td class="py-5 px-6 text-center text-amber-600 font-black leading-none">{{ num(c.coins) }} 🪙</td>
                                    <td class="py-5 px-6 text-center"><span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase shadow-sm border" :class="c.status==='Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200'">{{c.status}}</span></td>
                                    <td class="py-5 px-6 text-right"><button class="px-4 py-2 rounded-xl bg-slate-950 text-white text-[10px] uppercase font-black shadow-sm active:scale-95 transition">Pay Wallet</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. VIBES ADS (SUB-COMPONENT) -->
                <div v-if="activeView === 'feedAdsCommand'" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex justify-between items-center"><h3 class="text-3xl font-black text-slate-950">Vibes Ads</h3><button class="px-6 py-3 rounded-2xl bg-indigo-600 text-white font-black text-sm shadow-lg hover:bg-indigo-700 active:scale-95 transition">+ New Ad Unit</button></div>
                    <div class="card rounded-[32px] bg-white border border-slate-100 shadow-sm overflow-hidden">
                        <table class="w-full text-left text-sm font-bold border-collapse">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100">
                                <tr><th class="py-5 px-6">Advertiser</th><th class="py-5 px-6 text-center">Likes</th><th class="py-5 px-6 text-center">CTR</th><th class="py-5 px-6 text-center">Status</th><th class="py-5 px-6 text-right">Action</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-bold">
                                <tr v-for="a in vibesAds" :key="a.id" class="hover:bg-slate-50 transition">
                                    <td class="py-5 px-6"><div class="flex items-center gap-3"><span class="text-lg">{{ a.logo }}</span><div><p class="text-slate-900">{{ a.advertiser }}</p><p class="text-xs text-slate-400 uppercase tracking-widest">{{ a.handle }}</p></div></div></td>
                                    <td class="py-5 px-6 text-center text-slate-900 font-black leading-none">{{ num(a.likes) }}</td>
                                    <td class="py-5 px-6 text-center text-indigo-600 font-black leading-none">1.4%</td>
                                    <td class="py-5 px-6 text-center"><span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase shadow-sm border border-emerald-100" :class="a.status==='Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">{{a.status}}</span></td>
                                    <td class="py-5 px-6 text-right font-black"><button class="text-xs text-indigo-600 hover:underline">Edit Content</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 5. FEED ENGINE (SUB-COMPONENT) -->
                <div v-if="activeView === 'feedEngineCommand'" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex items-center justify-between mb-8">
                        <div><h3 class="text-3xl font-black text-slate-950">Algorithm Hub</h3><p class="text-slate-500 font-medium">Fine-tune ranking signals and safety protocols.</p></div>
                        <button class="rounded-2xl bg-indigo-600 text-white px-10 py-4 font-black text-sm shadow-xl active:scale-95 transition">Apply Global Tuning</button>
                    </div>
                    <div class="grid grid-cols-1 2xl:grid-cols-2 gap-8">
                        <div class="card rounded-[32px] p-8 bg-white border-slate-100 shadow-sm">
                            <h3 class="text-xl font-black mb-10 flex items-center gap-3"><SlidersHorizontal class="w-6 h-6 text-indigo-600" /> Signal Weights</h3>
                            <div class="space-y-12">
                                <div v-for="(v, k) in feedEngineCfg.rank" :key="k">
                                    <div class="flex justify-between items-center mb-4 px-1 font-black text-xs text-slate-400 uppercase tracking-widest"><span>{{ k }}</span><span class="text-indigo-700 text-sm font-black">{{ v }}%</span></div>
                                    <input type="range" min="0" max="100" v-model="(feedEngineCfg.rank as any)[k]" class="w-full accent-indigo-600 h-2 bg-slate-100 rounded-full appearance-none cursor-pointer transition active:scale-[1.01]">
                                </div>
                            </div>
                        </div>
                        <div class="space-y-8">
                            <div class="card rounded-[32px] p-8 bg-slate-900 text-white shadow-2xl relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 opacity-10"><ShieldAlert class="w-32 h-32 text-rose-500" /></div>
                                <h3 class="text-xl font-black mb-10 flex items-center gap-3 text-rose-400"><ShieldAlert class="w-6 h-6" /> AI Moderation</h3>
                                <div class="space-y-6 font-black">
                                    <div><label class="text-[10px] uppercase text-slate-500 tracking-widest block mb-3 ml-1">Auto-hide threshold (Reports)</label><div class="flex items-center justify-between bg-white/5 border border-white/10 rounded-2xl p-4 shadow-inner"><input v-model="feedEngineCfg.automod.autoHideReports" type="number" class="bg-transparent border-0 text-white font-black text-3xl p-0 focus:ring-0 w-24"><span class="text-xs text-slate-500 uppercase">Alerts</span></div></div>
                                    <div><label class="text-[10px] uppercase text-slate-500 tracking-widest block mb-3 ml-1">AI Confidence (%)</label><div class="flex items-center justify-between bg-white/5 border border-white/10 rounded-2xl p-4 shadow-inner"><input v-model="feedEngineCfg.automod.aiConfidence" type="number" class="bg-transparent border-0 text-white font-black text-3xl p-0 focus:ring-0 w-24"><span class="text-xs text-slate-500 uppercase">Certainty</span></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. MODERATION (SUB-COMPONENT) -->
                <div v-if="activeView === 'feedModerationCommand'" class="space-y-6 animate-in fade-in duration-300">
                    <h3 class="text-3xl font-black text-slate-950">Safety Hub</h3>
                    <div class="card rounded-[32px] bg-white border border-slate-100 shadow-sm overflow-hidden font-bold">
                        <table class="w-full text-left text-sm font-bold border-collapse">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100">
                                <tr><th class="py-5 px-6">Post ID</th><th class="py-5 px-6">Creator</th><th class="py-5 px-6">Reason</th><th class="py-5 px-6 text-center">Risk Level</th><th class="py-5 px-6 text-right">Moderation</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="r in feedReports" :key="r.id">
                                    <td class="py-6 px-6 font-black text-indigo-600 shadow-sm">#{{ r.post }}</td>
                                    <td class="py-6 px-6 font-black text-slate-950 leading-none">{{ r.creator }}</td>
                                    <td class="py-6 px-6 text-slate-500 font-bold leading-tight uppercase text-[10px]">{{ r.reason }}</td>
                                    <td class="py-6 px-6 text-center"><span class="px-4 py-2 rounded-full text-[9px] font-black uppercase shadow-sm border" :class="r.risk==='High'?'bg-rose-50 text-rose-700 border-rose-100':'bg-amber-50 text-amber-700 border-amber-100'">{{ r.risk }} Risk</span></td>
                                    <td class="py-6 px-6 text-right font-black"><div class="flex gap-2 justify-end"><button @click="moderatePost(r.id, 'approve')" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-[10px] uppercase shadow-lg active:scale-95 transition">Allow</button><button @click="moderatePost(r.id, 'remove')" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-[10px] uppercase shadow-lg active:scale-95 transition">Take Down</button></div></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 7. DEV EXPORT (SUB-COMPONENT) -->
                <div v-if="activeView === 'feedDevCommand'" class="space-y-6 animate-in fade-in duration-300">
                    <h3 class="text-3xl font-black text-slate-950">Developer Suite</h3>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 font-bold">
                        <div class="card rounded-[32px] p-8 bg-white border-slate-100 shadow-sm overflow-hidden">
                            <h3 class="text-xl font-black mb-10 flex items-center gap-3 font-['Inter']"><Code class="w-8 h-8 text-indigo-600" /> REST API Contract</h3>
                            <div class="space-y-4">
                                <div v-for="ep in feedAPIContract" :key="ep[1]" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-indigo-300 transition-all shadow-sm">
                                    <div class="flex items-center gap-5 mb-2 font-black"><span class="text-[10px] px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white uppercase tracking-tighter">{{ ep[0] }}</span><span class="font-mono text-sm text-slate-950 tracking-tight">{{ ep[1] }}</span></div>
                                    <p class="text-xs text-slate-400 font-black uppercase ml-16">{{ ep[2] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card rounded-[32px] p-8 bg-slate-950 text-white shadow-2xl relative overflow-hidden flex flex-col">
                            <div class="absolute -right-6 -top-6 opacity-10"><Terminal class="w-48 h-48" /></div>
                            <h3 class="text-2xl font-black mb-8 leading-tight uppercase tracking-tighter">Data Schema (Social)</h3>
                            <div class="flex-1 p-6 rounded-3xl bg-white/5 font-mono text-[11px] leading-relaxed text-indigo-300 border border-white/10 overflow-x-auto shadow-inner"><pre>{
  "post_id": "string",
  "creator_handle": "string",
  "likes_count": "number",
  "coins_tipped": "number",
  "media_url": "string"
}</pre></div>
                            <button class="mt-10 w-full py-5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-black text-sm border border-white/10 transition uppercase tracking-widest">Download Integration Bundle</button>
                        </div>
                    </div>
                </div>

            </section>
        </main>

        <!-- VERBATIM MODALS (Reactively controlled) -->
        <div v-if="showCoinGiftSheet" class="fixed inset-0 z-[1000] bg-black/60 backdrop-blur-sm flex items-end justify-center p-0 md:p-5 animate-in slide-in-from-bottom-full duration-300">
            <div class="bg-white rounded-t-[56px] md:rounded-[56px] max-w-sm w-full shadow-2xl overflow-hidden border border-white/20">
                <div class="p-8 bg-gradient-to-br from-amber-50 to-rose-50 border-b border-orange-100 flex justify-between items-center">
                    <div><h3 class="text-2xl font-black text-orange-950 tracking-tighter">Big Up! 🪙</h3><p class="text-[11px] text-orange-600 font-black uppercase mt-1 tracking-widest">Send a vibe to creator</p></div>
                    <button @click="showCoinGiftSheet = false" class="h-12 w-12 rounded-full bg-white grid place-items-center shadow-xl text-slate-400 active:scale-90 transition border border-orange-50"><X class="w-6 h-6" /></button>
                </div>
                <div class="p-8">
                    <div class="bg-blue-50 text-blue-700 px-6 py-3 rounded-full text-sm font-black border border-blue-100 mb-10 w-fit shadow-md">🔷 {{ num(feedViewer.coins) }} Balance</div>
                    <div class="grid grid-cols-4 gap-4 max-h-[340px] overflow-y-auto pr-1">
                        <button v-for="g in LINKUP_VIBES_GIFTS" :key="g.id" @click="selectedCoinGift = g; sendCoinGift()" class="flex flex-col items-center gap-2 p-3 rounded-3xl border-2 border-transparent bg-slate-50 hover:bg-white hover:border-indigo-200 transition group active:scale-95 shadow-sm hover:shadow-lg"><span class="text-3xl group-hover:scale-110 transition duration-300">{{ g.emoji }}</span><span class="text-[9px] font-black uppercase text-slate-400">{{ g.cost || 'Free' }}</span></button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showCommentModal" class="fixed inset-0 z-[1000] bg-black/60 backdrop-blur-sm flex items-end justify-center p-0 md:p-5 animate-in slide-in-from-bottom-full duration-300">
            <div class="bg-white rounded-t-[56px] md:rounded-[56px] max-w-md w-full shadow-2xl overflow-hidden flex flex-col border border-white/20" style="max-height:85vh">
                <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0"><h3 class="text-xl font-black tracking-tight text-slate-950 uppercase text-xs tracking-[0.2em]">Social Discussion</h3><button @click="showCommentModal = false" class="h-10 w-10 rounded-full bg-white shadow-sm grid place-items-center"><X class="w-6 h-6"/></button></div>
                <div class="flex-1 overflow-y-auto p-8 space-y-6 scrollbar-hide">
                    <div v-for="c in (commentPostId ? postComments[commentPostId] : [])" :key="c.ts" class="flex gap-4 items-start animate-in fade-in duration-300">
                        <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 grid place-items-center font-black text-xs uppercase text-white border border-white shadow-sm flex-shrink-0">{{ c.user.slice(1,2) }}</div>
                        <div class="rounded-3xl bg-slate-100 px-6 py-4 shadow-sm border border-slate-200 flex-1 transition hover:shadow-md"><p class="text-xs font-black text-indigo-600 mb-1 tracking-tight">{{ c.user }}</p><p class="text-sm font-bold leading-relaxed text-slate-800">{{ c.text }}</p></div>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 flex gap-3 bg-white shadow-2xl shrink-0"><input v-model="commentInput" @keydown.enter="postComment" class="flex-1 rounded-[24px] border-slate-200 px-6 py-4 font-bold text-sm focus:ring-0 focus:border-indigo-400 outline-none bg-slate-50 transition" placeholder="Add a comment..."><button @click="postComment" class="rounded-[24px] bg-slate-950 text-white px-8 py-4 font-black text-sm transition active:scale-95 shadow-xl uppercase tracking-widest">Send</button></div>
            </div>
        </div>

        <transition enter-active-class="duration-300" enter-from-class="translate-y-4 opacity-0" leave-to-class="opacity-0">
            <div v-if="toastVisible" class="fixed left-1/2 -translate-x-1/2 bottom-10 z-[1100] bg-slate-950 text-white px-8 py-4 rounded-3xl font-black shadow-2xl uppercase tracking-widest text-[11px] border border-white/10">{{ toastMsg }}</div>
        </transition>

    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.nav-active { background: linear-gradient(90deg, rgba(40, 168, 255, 0.1), rgba(217, 236, 16, 0.1)); border-right: 4px solid #28A8FF; color: #07111f; }
.ribbon { background: linear-gradient(90deg, #07111f, #0f2745, #07111f); }
.glass { background: rgba(255,255,255,.9); backdrop-filter: blur(18px); border-bottom: 1px solid rgba(226,232,240,.85); }
.gradient-title { background: linear-gradient(90deg,#07111f,#28A8FF,#00C853); -webkit-background-clip: text; color: transparent; }
input[type="range"]::-webkit-slider-thumb { appearance: none; width: 24px; height: 24px; background: #4f46e5; border-radius: 50%; cursor: pointer; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.15); transition: transform 0.2s; }
input[type="range"]::-webkit-slider-thumb:active { transform: scale(1.2); }
</style>
