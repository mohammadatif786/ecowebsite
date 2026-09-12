export const DB = {
  get(k, def) {
    try {
      const v = localStorage.getItem(k);
      return v ? JSON.parse(v) : def;
    } catch (e) {
      return def;
    }
  },
  set(k, v) {
    try {
      localStorage.setItem(k, JSON.stringify(v));
    } catch (e) {}
  }
};

export const PIC = (id, w = 800, h = 600) => `https://picsum.photos/seed/lk${id}/${w}/${h}`;
export const AV = id => `https://i.pravatar.cc/120?img=${id}`;

export const DEFAULT_USER = {
  id: 1, handle: '@cassius', name: 'Cassius', avatar: AV(12),
  city: 'The Valley', country: 'Anguilla', flag: '🇦🇮', verified: true
};

export const DEFAULT_WALLET = { balance: 181.04, coins: 37010, currency: 'USD' };

export const SEED = {
  vibes_posts: [
    {id:1, handle:'@islandvibez', avatar:AV(15), location:'Jamaica', kind:'reel', media:PIC('vibe1',900,700), caption:'Carnival prep 🔥 Tickets in my post #fete #jamaica', sound:'Soca Anthem 2026', likes:48200, comments:1800, bigup:9800, shoppable:true, tag:{kind:'event', id:1, title:'Carnival Fete 2026', price:35, date:'2026-10-30', location:'The Valley, Anguilla', seller:'Soca Nation', image:PIC('ev1',800,500), commission:8}},
    {id:2, handle:'@gabis', avatar:AV(31), location:'Santo Domingo, DR', kind:'photo', media:PIC('vibe2',900,700), caption:'Sunset linkups 🌅', likes:12400, comments:342, bigup:1200},
    {id:3, handle:'@carlosmx', avatar:AV(52), location:'Cartagena, CO', kind:'reel', media:PIC('vibe3',900,700), caption:'Beach day with the crew 🏖️', sound:'Amapiano Mix', likes:22100, comments:889, bigup:3400},
    {id:4, handle:'@tanyab', avatar:AV(45), location:'Port of Spain, TT', kind:'photo', media:PIC('vibe4',900,700), caption:'Market finds — shop the look 🛍️', likes:8700, comments:210, bigup:640, shoppable:true, tag:{kind:'product', id:5, title:'Handmade Beaded Carnival Earrings', price:18, seller:'Kay Designs · Kingston, JM', image:PIC('pr5',700,500), commission:15}}
  ],
  stories: [
    {handle:'@tanyab', avatar:AV(45)},{handle:'@islandvibez', avatar:AV(15)},
    {handle:'@reneeb', avatar:AV(20)},{handle:'@carlosmx', avatar:AV(52)},
    {handle:'@gabis', avatar:AV(31)},{handle:'@drech', avatar:AV(60)}
  ],
  live_sessions: [
    {id:1, title:'Carnival Warmup', host:'@socaboss', category:'Carnival Mixers', viewers:4200, likes:312, gifts:18, thumb:PIC('live1',700,900), status:'live', products:[
      {id:6, title:'Carnival Costume — Frontline', price:399, origPrice:450, seller:'Carnival Collective · TT', image:PIC('pr6',700,500), stock:6, sold:14, commission:10},
      {id:5, title:'Beaded Carnival Earrings', price:15, origPrice:18, seller:'Kay Designs · JM', image:PIC('pr5',700,500), stock:24, sold:8, commission:15},
      {id:2, title:'D&G Sicily Handbag', price:1245, seller:'The Stop & Shop · BS', image:PIC('pr2',700,500), stock:3, sold:1, commission:8},
      {kind:'event', id:1, title:'Carnival Fete 2026', price:35, date:'2026-10-30', location:'The Valley, Anguilla', seller:'Soca Nation', image:PIC('ev1',800,500), stock:999, sold:37, commission:8}]},
    {id:2, title:'Caribbean Talk', host:'@island_news', category:'News · Sports', viewers:1800, likes:140, gifts:7, thumb:PIC('live2',700,900), status:'live'},
    {id:3, title:'Amapiano Sundays', host:'@djzone', category:'Afrobeats & Amapiano', viewers:980, likes:88, gifts:4, thumb:PIC('live3',700,900), status:'live', products:[
      {id:5, title:'Beaded Carnival Earrings', price:15, origPrice:18, seller:'Kay Designs · JM', image:PIC('pr5',700,500), stock:24, sold:5, commission:15},
      {id:1, title:'Utopia Bed Pillows (Set of 2)', price:24, origPrice:28, seller:'Individual Seller · BS', image:PIC('pr1',700,500), stock:9, sold:2, commission:12}]},
    {id:4, title:'Late Night Lime', host:'@limecrew', category:'Just Chatting', viewers:0, thumb:PIC('live4',700,900), status:'upcoming', when:'Tonight 9:00 PM'}
  ],
  events: [
    {id:1, title:'Carnival Fete 2026', category:'Party/Fete', date:'2026-10-30', price:35, location:'The Valley, Anguilla', organizer:'Soca Nation', desc:'The biggest fête of the season 🔥', image:PIC('ev1',800,500), commMode:'pct', commission:8},
    {id:2, title:'Polished Gem Wellness Retreat', category:'Wellness & Spa', date:'2026-06-29', price:35, location:'Nassau, BS', organizer:'Polished Gem', desc:'A full day of relaxation & detox', image:PIC('ev2',800,500), commMode:'flat', commFlat:5},
    {id:3, title:'Wax Moi — Spa Night', category:'Wellness & Spa', date:'2026-08-30', price:40, location:'Kingston, JM', organizer:'Wax Moi', desc:'Pamper night with the girls', image:PIC('ev3',800,500), commMode:'none'},
    {id:4, title:'Soca Brainwash', category:'Party/Fete', date:'2026-06-20', price:25, location:'Port of Spain, TT', organizer:'Fete Republic', desc:'All-inclusive soca experience', image:PIC('ev4',800,500), commMode:'pct', commission:12},
    {id:5, title:'Cookout & Cooler Fete', category:'Cookouts/Food', date:'2026-07-22', price:20, location:'Bridgetown, BB', organizer:'Cooler Crew', desc:'Bring your cooler, we bring the vibes', image:PIC('ev5',800,500), commMode:'flat', commFlat:3},
    {id:6, title:'Old School Vibes', category:'Party/Fete', date:'2026-10-30', price:30, location:'Castries, LC', organizer:'Retro Kingz', desc:'Throwback riddims all night', image:PIC('ev6',800,500), commMode:'pct', commission:10},
    {id:7, title:'Free Beach Bonfire Lime', category:'Party/Fete', date:'2026-07-05', price:0, location:'Grand Anse, GD', organizer:'Island Youth', desc:'Free entry · bonfire & drums', image:PIC('ev7',800,500), commMode:'none'},
    {id:8, title:'Steelpan Masterclass (Online)', category:'Arts', date:'2026-06-28', price:0, location:'Online', organizer:'Pan Institute', kind:'online', desc:'Live-streamed steelpan workshop', image:PIC('ev8',800,500)},
    {id:9, title:'Reggae Sundays', category:'Music', date:'2026-07-12', price:15, location:'Montego Bay, JM', organizer:'Irie Vibes', desc:'Roots, rockers & culture', image:PIC('ev9',800,500)}
  ],
  products: [
    {id:1, seller_type:'Individual', seller:'Individual Seller · Nassau, BS', title:'Utopia Bedding Bed Pillows (Set of 2)', price:28, category:'Home', stock:9, fulfil:'Delivery only', image:PIC('pr1',700,500), commission:12},
    {id:2, seller_type:'Store', seller:'The Stop & Shop · Nassau, BS', title:'Dolce & Gabbana My Sicily Handbag', price:1245, category:'Accessories', stock:10, fulfil:'Pickup available', image:PIC('pr2',700,500), commission:8},
    {id:3, seller_type:'Store', seller:'The Stop & Shop · Nassau, BS', title:'All-Weather HDPE Folding Adirondack Chair', price:99, category:'Outdoors', stock:11, fulfil:'Pickup available', image:PIC('pr3',700,500), commission:10},
    {id:4, seller_type:'Store', seller:'The Stop & Shop · Nassau, BS', title:'2025 Topps Museum Chris Sale #31/199', price:30, category:'Collectibles', stock:0, fulfil:'Pickup available', image:PIC('pr4',700,500), commission:10},
    {id:5, seller_type:'Individual', seller:'Kay Designs · Kingston, JM', title:'Handmade Beaded Carnival Earrings', price:18, category:'Accessories', stock:24, fulfil:'Delivery only', image:PIC('pr5',700,500), commission:15},
    {id:6, seller_type:'Group', seller:'Carnival Collective · TT', title:'Carnival Costume Deposit — Frontline', price:450, category:'Carnival', stock:6, fulfil:'Pickup available', image:PIC('pr6',700,500), commission:10}
  ],
  news: [
    {id:1, cat:'Entertainment', country:'Trinidad & Tobago', flag:'🇹🇹', top:true, title:'Soca stars confirm Carnival 2026 lineup', excerpt:'A star-studded lineup has been confirmed for next year’s carnival season.', feeds:5, image:PIC('nw1',900,500)},
    {id:2, cat:'Business', country:'Jamaica', flag:'🇯🇲', title:'Central Bank signals focus on economic stability', excerpt:'New measures aim to steady inflation across the region.', feeds:3, image:PIC('nw2',600,400)},
    {id:3, cat:'Sports', country:'Barbados', flag:'🇧🇧', title:'Regional T20 final set for the weekend', excerpt:'Two island rivals face off for the trophy.', feeds:4, image:PIC('nw3',600,400)},
    {id:4, cat:'Tech', country:'Dominican Republic', flag:'🇩🇴', title:'LatAm startups raise record funding in Q2', excerpt:'Fintech and commerce lead a strong quarter.', feeds:6, image:PIC('nw4',600,400)}
  ],
  restaurants: [
    {id:1, name:'Roti Hut', cuisine:'Trini · Roti · Curry', tags:['Trini'], rating:4.8, eta:'20–30 min', fee:2.5, km:2.1, image:PIC('rs1',700,450), menu:[
      {sec:'Popular', items:[{n:'Doubles',p:3.50,d:'Two bara with curried channa & pepper',stock:40},{n:'Chicken Roti',p:13.00,d:'Buss-up-shut with curried chicken',stock:25},{n:'Bake & Shark',p:16.00,d:'Fried shark, tamarind & chadon beni',stock:18}]},
      {sec:'Sides & Drinks', items:[{n:'Aloo Pie',p:4.00,d:'Fried pastry stuffed with potato',stock:30},{n:'Mauby',p:3.00,d:'Chilled bark drink'},{n:'Sorrel',p:3.00,d:'Spiced hibiscus cooler'}]}]},
    {id:2, name:'Jerk Centre', cuisine:'Jamaican · BBQ', tags:['Jamaican'], rating:4.7, eta:'25–35 min', fee:3.0, km:4.3, image:PIC('rs2',700,450), menu:[
      {sec:'Off the Grill', items:[{n:'Jerk Chicken',p:14.00,d:'Pimento-smoked ¼ chicken',stock:22},{n:'Oxtail',p:22.00,d:'Braised oxtail with butter beans',stock:12},{n:'Curry Goat',p:18.00,d:'Slow-cooked curried goat',stock:15}]},
      {sec:'Sides', items:[{n:'Festival',p:5.00,d:'Sweet fried dumpling'},{n:'Rice & Peas',p:6.00,d:'Coconut rice with kidney beans'},{n:'Fried Plantain',p:4.00,d:'Sweet ripe plantain'}]}]},
    {id:3, name:'Doubles Express', cuisine:'Street food · Vegan', tags:['Street food','Vegan'], rating:4.9, eta:'15–25 min', fee:1.5, km:1.2, image:PIC('rs3',700,450), menu:[
      {sec:'Street Eats', items:[{n:'Doubles (2)',p:3.00,d:'Classic pair, slight pepper',stock:60},{n:'Aloo Channa',p:6.50,d:'Potato & chickpea bowl'},{n:'Veggie Wrap',p:9.00,d:'Grilled veg, hummus, greens'}]},
      {sec:'Drinks', items:[{n:'Peanut Punch',p:4.50,d:'Creamy peanut shake'},{n:'Fresh Coconut',p:3.50,d:'Jelly coconut, chilled'}]}]},
    {id:4, name:'La Cevichería', cuisine:'Peruvian · Seafood', tags:['Seafood'], rating:4.6, eta:'30–40 min', fee:3.5, km:6.0, image:PIC('rs4',700,450), menu:[
      {sec:'Ceviche Bar', items:[{n:'Classic Ceviche',p:15.00,d:'Sea bass, lime, red onion, aji',stock:20},{n:'Mixto Ceviche',p:18.00,d:'Fish, squid & shrimp'},{n:'Leche de Tigre',p:8.00,d:'Citrus seafood shot'}]},
      {sec:'Mains', items:[{n:'Lomo Saltado',p:19.00,d:'Stir-fried beef, fries, rice'},{n:'Arroz con Mariscos',p:21.00,d:'Peruvian seafood rice'}]}]}
  ]
};

export const NAV = [
  {id:'home', label:'Home', icon:'home'},
  {id:'vibes', label:'Vibes', icon:'sparkles'},
  {id:'uvibe', label:'U Vibe', icon:'graduation-cap'},
  {id:'eats', label:'Eats', icon:'utensils'},
  {id:'live', label:'Live', icon:'radio'},
  {id:'dating', label:'LinkUp', icon:'heart'},
  {id:'events', label:'Events', icon:'party-popper'},
  {id:'nightlife', label:'Night Life', icon:'moon'},
  {id:'news', label:'Caribbean 360', icon:'newspaper'},
  {id:'marketplace', label:'Marketplace', icon:'shopping-bag'},
  {id:'wallet', label:'Wallet', icon:'wallet'},
  {id:'profile', label:'Profile', icon:'user'}
];

export const getUser = () => DB.get('lk_user', DEFAULT_USER);
export const getWallet = () => DB.get('lk_wallet', DEFAULT_WALLET);
export const getEvents = () => DB.get('lk_events', SEED.events);
export const getVibes = () => DB.get('lk_vibes_posts', SEED.vibes_posts);
export const getRestaurants = () => DB.get('lk_restaurants', SEED.restaurants);
export const getProducts = () => DB.get('lk_products', SEED.products);
export const getNews = () => SEED.news;

export const UVIBE_CAMPUSES = [['UWI Mona','🇯🇲'],['UWI St. Augustine','🇹🇹'],['UWI Cave Hill','🇧🇧'],['Univ. of The Bahamas','🇧🇸'],['Univ. of Guyana','🇬🇾']];
export const UVIBE_CATS = [['all','All','grid-3x3'],['events','Events','calendar'],['parties','Parties','party-popper'],['campus-life','Campus Life','building-2'],['clubs','Clubs','users'],['food','Food','utensils']];
export const UVIBE_POSTS_SEED = [
  {id:1, org:'UWI St. Augustine', verified:true, group:'Carnival Committee', campus:'UWI St. Augustine', time:'2h ago', category:'parties', tagLabel:'Parties', image:PIC('uv1',800,600), title:"Campus Carnival Fete 🎭", price:30, desc:"J'ouvert vibes, soca trucks & a steelpan set this Friday at the SAC quad. All campuses welcome!", likes:214, comments:28, onVibes:true},
  {id:2, org:'Univ. Of The Bahamas', verified:true, group:'UB Student Union', campus:'Univ. of The Bahamas', time:'4h ago', category:'campus-life', tagLabel:'Student Union', image:null, title:'Student Union elections — meet the candidates', price:0, desc:'Town hall Thursday 5pm in the Harry C. Moore Library. Bring your questions on tuition, housing & campus Wi-Fi.', likes:88, comments:12, onVibes:false},
  {id:3, org:'UWI Mona', verified:true, group:'Caribbean Students Assoc.', campus:'UWI Mona', time:'7h ago', category:'clubs', tagLabel:'Clubs', image:PIC('uv3',800,600), title:'Club fair on the lawns', price:0, desc:'Meet 30+ clubs, grab free merch, and sign up for semester projects. Food vendors on site.', likes:156, comments:19, onVibes:true},
  {id:4, org:'UWI Cave Hill', verified:true, group:'Hospitality Society', campus:'UWI Cave Hill', time:'1d ago', category:'food', tagLabel:'Food', image:PIC('uv4',800,600), title:'Free ice cream on the quad 🍦', price:0, desc:'Cool down between finals! First 200 students get a free scoop.', likes:302, comments:41, onVibes:false},
  {id:5, org:'Univ. Of The Bahamas', verified:true, group:'UB Events Board', campus:'Univ. of The Bahamas', time:'1d ago', category:'events', tagLabel:'Events', image:null, title:'Career Fair 2026', price:0, desc:'50+ employers on campus Thursday 10am–3pm at the Gym. Bring your resume!', likes:64, comments:7, onVibes:false}
];
export const getUVibes = () => DB.get('lk_uvibe_posts', UVIBE_POSTS_SEED);
