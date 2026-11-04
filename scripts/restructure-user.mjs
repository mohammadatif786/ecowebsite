#!/usr/bin/env node
/**
 * LinkUp - one "User" folder for the whole member app.
 *
 *   node scripts/restructure-user.mjs run             everything in one go: branch, route snapshot, plan, asks, apply, rebuild, checks
 *   node scripts/restructure-user.mjs plan            dry run: writes restructure-report.md + restructure-moves.csv, changes nothing
 *   node scripts/restructure-user.mjs apply           does it (needs a clean git tree)
 *   node scripts/restructure-user.mjs verify          re-checks the result on disk (imports, page names, leftovers)
 *   node scripts/restructure-user.mjs routes-compare before.json after.json
 *                                                     compares two `php artisan route:list --json` dumps
 *
 *   --root=<dir>   project root (default: current folder)
 *   --php=<path>   php executable for `run` (default: php)
 *
 * What it does
 *   1. Moves   pages/new_front/**, pages/User/**, pages/Profile/**, components/{new_frontend,front,ProfileTabComponents}/**,
 *              layouts/{new_front_layout,AuthenticatedLayout*}, app/Http/Controllers/NewFrontend/**, routes/web/member.php
 *              into one User tree (see FILE_RULES / DIR_RULES below). Nothing is deleted: every file keeps its content.
 *   2. Rewrites every import / @/ alias / url() / Inertia page name / route name / URL / PHP namespace that pointed at a moved file.
 *   3. Renames the web area new_frontend -> user (URL prefix + route-name prefix) and adds a permanent redirect
 *      from every old /new_frontend/... address, so emailed links, push links, Stripe returns and bookmarks keep working.
 *
 * No dependencies. Node 18+.
 */
import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';

const posix = path.posix;
const argv = process.argv.slice(2);
const cmd = argv[0] && !argv[0].startsWith('--') ? argv[0] : 'plan';
const opt = (n) => argv.find((a) => a.startsWith(`--${n}=`))?.slice(n.length + 3);
const has = (n) => argv.includes(`--${n}`);
const ROOT = path.resolve(opt('root') ?? process.cwd());

// ───────────────────────────────────────────────────────────── layout rules ──
const JS = 'resources/js/';
const P = `${JS}pages/`;
const C = `${JS}components/`;
const L = `${JS}layouts/`;
const U = `${P}User/`;

/** exact file -> exact file (checked first) */
const FILE_RULES = [
  // old member pages that are not a folder on their own
  [`${U}Wizard.vue`, `${U}Onboarding/Wizard.vue`],
  [`${U}Landing2.vue`, `${U}Landing/Index.vue`],
  [`${U}Landing.vue`, `${U}Landing/Classic/Landing.vue`],
  [`${U}Index.vue`, `${U}Home/Classic/Index.vue`],
  [`${U}Chat.vue`, `${U}Linkup/Classic/Chat/ChatPage.vue`],
  [`${U}VideoCall.vue`, `${U}Linkup/Classic/Chat/VideoCall.vue`],
  [`${U}Users.vue`, `${U}Linkup/Classic/Users.vue`],
  [`${U}TicketType.vue`, `${U}Events/Tickets/TicketType.vue`],
  [`${U}BuyTicket.vue`, `${U}Events/Tickets/BuyTicket.vue`],
  [`${U}Eticket.vue`, `${U}Events/Tickets/Eticket.vue`],
  [`${U}UpcommingEvent.vue`, `${U}Events/UpcomingEvent.vue`],
  [`${U}VerifyKyc.vue`, `${U}Kyc/VerifyKyc.vue`],
  [`${U}AppSetting.vue`, `${U}Settings/AppSetting.vue`],
  // new_front pages that are not a plain folder move
  [`${P}new_front/Settings.vue`, `${U}Settings/Index.vue`],
  [`${P}new_front/wallet/Index.vue`, `${U}Wallet/Index.vue`],
  [`${P}new_front/wallet/LinkUpCoinsCard.vue`, `${U}Wallet/components/LinkUpCoinsCard.vue`],
  [`${P}new_front/profile/Index.vue`, `${U}Profile/Index.vue`],
  [`${P}new_front/profile/EditProfileModal.vue`, `${U}Profile/modals/EditProfileModal.vue`],
  [`${P}new_front/profile/SettingsModal.vue`, `${U}Profile/modals/SettingsModal.vue`],
  // layouts
  [`${L}new_front_layout/MainLayout.vue`, `${L}User/MainLayout.vue`],
  [`${L}new_front_layout/new_front.css`, `${L}User/user.css`],
  [`${L}AuthenticatedLayout.vue`, `${L}User/Classic/AuthenticatedLayout.vue`],
  [`${L}AuthenticatedLayoutBackup.vue`, `${L}User/Classic/AuthenticatedLayoutBackup.vue`],
  // routes
  ['routes/web/member.php', 'routes/web/user.php'],
];

/** folder -> folder (longest matching source prefix wins) */
const DIR_RULES = [
  // new_front  (current member app)
  [`${P}new_front/home/`, `${U}Home/`],
  [`${P}new_front/notifications/`, `${U}Notifications/`],
  [`${P}new_front/vibes/`, `${U}Vibes/`],
  [`${P}new_front/u_vibe/`, `${U}UVibe/`],
  [`${P}new_front/eats/`, `${U}Eats/`],
  [`${P}new_front/linkup/`, `${U}Linkup/`],
  [`${P}new_front/events/`, `${U}Events/`],
  [`${P}new_front/night_life/`, `${U}NightLife/`],
  [`${P}new_front/caribbean360/`, `${U}Caribbean360/`],
  [`${P}new_front/marketplace/`, `${U}Marketplace/`],
  [`${P}new_front/wallet/`, `${U}Wallet/modals/`],
  [`${P}new_front/profile/settings/`, `${U}Profile/modals/settings/`],
  [`${P}new_front/live/`, `${U}Live/`],
  // old User pages that have a new_front twin  ->  <Feature>/Classic/...
  [`${U}Notification/`, `${U}Notifications/Classic/`],
  [`${U}FindMatch/`, `${U}Linkup/Classic/FindMatch/`],
  [`${U}Match/`, `${U}Linkup/Classic/Match/`],
  [`${U}Likes/`, `${U}Linkup/Classic/Likes/`],
  [`${U}Friend/`, `${U}Linkup/Classic/Friend/`],
  [`${U}FriendRequest/`, `${U}Linkup/Classic/FriendRequest/`],
  [`${U}Chat/`, `${U}Linkup/Classic/Chat/`],
  [`${U}Restaurants/`, `${U}Eats/Classic/Restaurants/`],
  [`${U}Event/`, `${U}Events/Classic/Event/`],
  [`${U}UserWallet/`, `${U}Wallet/Classic/UserWallet/`],
  [`${U}Wallet/`, `${U}Wallet/Classic/Wallet/`],
  [`${U}Profile/`, `${U}Profile/Classic/`],
  [`${U}GoLive/`, `${U}Live/Classic/GoLive/`],
  [`${U}LiveStream/`, `${U}Live/Classic/LiveStream/`],
  [`${U}LinkUpShop/`, `${U}Marketplace/Classic/LinkUpShop/`],
  // old User pages without a twin  ->  their own feature folder
  [`${U}MyEvent/`, `${U}Events/MyEvents/`],
  [`${U}Ticket/`, `${U}Events/Tickets/`],
  [`${U}Bookings/`, `${U}Events/Bookings/`],
  [`${U}Clubs/`, `${U}NightLife/Clubs/`],
  // starter-kit account page
  [`${P}Profile/`, `${U}Profile/Account/`],
  // components / layouts / php
  [`${C}new_frontend/`, `${C}User/`],
  [`${C}front/`, `${C}User/Classic/`],
  [`${C}ProfileTabComponents/`, `${C}User/Profile/Tabs/`],
  [`${L}new_front_layout/`, `${L}User/`],
  ['app/Http/Controllers/NewFrontend/', 'app/Http/Controllers/User/'],
];
// stays where it is: pages/User/{Auth,News,Gifts,Subscription}/**

const TYPO_FILES = {
  'GenralDialog.vue': 'GeneralDialog.vue',
  'AllLiveStrams.vue': 'AllLiveStreams.vue',
  'GiftCionModal.vue': 'GiftCoinModal.vue',
  'Upcomming.vue': 'Upcoming.vue',
  'UpcommingEvent.vue': 'UpcomingEvent.vue',
};

const OLD_AREA = 'new_frontend'; // url + route-name prefix
const NEW_AREA = 'user';
const WALLET_OLD = 'wallet'; // the new wallet routes clash with the old /user/wallet* routes, so they move to /user/wallet-hub*
const WALLET_NEW = 'wallet-hub';
const HOME_NAME_NEW = 'feed'; // user.home already exists (the public landing page), so new_frontend.home becomes user.feed

function mapPath(rel) {
  let out = null;
  const exact = FILE_RULES.find(([from]) => from === rel);
  if (exact) out = exact[1];
  else {
    let best = null;
    for (const [from, to] of DIR_RULES) if (rel.startsWith(from) && (!best || from.length > best[0].length)) best = [from, to];
    if (best) out = best[1] + rel.slice(best[0].length);
  }
  if (!out && /^(app|tests|database|config)\//.test(rel) && rel.split('/').includes('NewFrontend')) out = rel.split('/').map((seg) => (seg === 'NewFrontend' ? 'User' : seg)).join('/');
  if (!out) return null;
  if (out.startsWith(U)) out = out.split('/').map((s) => (s === 'Components' ? 'components' : s === 'Modals' ? 'modals' : s)).join('/');
  const base = posix.basename(out);
  if (TYPO_FILES[base]) out = posix.join(posix.dirname(out), TYPO_FILES[base]);
  return out;
}

// ───────────────────────────────────────────────────────────────── helpers ──
const IGNORE_DIRS = new Set(['node_modules', 'vendor', '.git', 'storage', '.restructure-tmp', '.idea', '.vscode', '.fleet']);
const IGNORE_PREFIX = ['public/build/', 'public/hot', 'bootstrap/cache/'];
const SELF = new Set(['scripts/restructure-user.mjs', 'restructure-report.md', 'restructure-moves.csv', 'restructure-baseline.json']);
const TEXT_EXT = new Set(['.php', '.vue', '.ts', '.tsx', '.js', '.mjs', '.cjs', '.css', '.scss', '.md', '.html', '.xml', '.yml', '.yaml', '.blade']);
const JS_EXT = new Set(['.vue', '.ts', '.tsx', '.js', '.mjs', '.cjs', '.css', '.scss']);
const SKIP_CONTENT = new Set(['resources/js/ziggy.js', 'package-lock.json', 'composer.lock']);

function walk(dir = '') {
  const out = [];
  for (const e of fs.readdirSync(path.join(ROOT, dir), { withFileTypes: true })) {
    const rel = dir ? `${dir}/${e.name}` : e.name;
    if (e.isDirectory()) {
      if (IGNORE_DIRS.has(e.name) || IGNORE_PREFIX.some((p) => `${rel}/`.startsWith(p))) continue;
      out.push(...walk(rel));
    } else if (e.isFile()) {
      if (IGNORE_PREFIX.some((p) => rel.startsWith(p)) || SELF.has(rel)) continue;
      if (/\.(min\.js|min\.css|map)$/.test(rel) || fs.statSync(abs(rel)).size > 2_000_000) continue; // vendored / generated bundles
      out.push(rel);
    }
  }
  return out;
}
const abs = (rel) => path.join(ROOT, ...rel.split('/'));
const ext = (rel) => posix.extname(rel).toLowerCase();
const isText = (rel) => TEXT_EXT.has(ext(rel)) || rel.endsWith('.blade.php');
const readText = (rel) => fs.readFileSync(abs(rel), 'utf8');
const lineOf = (text, idx) => text.slice(0, idx).split('\n').length;
const fullLine = (text, idx) => {
  const s = text.lastIndexOf('\n', idx) + 1;
  const e = text.indexOf('\n', idx);
  return text.slice(s, e === -1 ? undefined : e);
};
const snippet = (text, idx) => {
  const s = text.lastIndexOf('\n', idx) + 1;
  const e = text.indexOf('\n', idx);
  return text.slice(s, e === -1 ? undefined : e).trim().slice(0, 140);
};
const INTENDED = /legacy|used to live|\{path\?\}|redirect|stripe_cart/i;
const esc = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

// ───────────────────────────────────────────────── import / path resolution ──
const RESOLVE_EXTS = ['.vue', '.ts', '.js', '.mjs', '.tsx', '.jsx', '.json', '.css', '.scss'];
const INDEXES = ['/index.ts', '/index.js', '/index.vue', '/index.mjs'];

/** resolve an import-like string against the OLD file set. returns {target, how} or null */
function resolveSpec(fromRel, rawSpec, fileSet) {
  const q = rawSpec.search(/[?#]/);
  const spec = q === -1 ? rawSpec : rawSpec.slice(0, q);
  let base;
  if (spec.startsWith('@/')) base = JS + spec.slice(2);
  else if (spec.startsWith('./') || spec.startsWith('../')) base = posix.normalize(posix.join(posix.dirname(fromRel), spec));
  else return null;
  if (fileSet.has(base)) return { target: base, how: 'as-is', tail: q === -1 ? '' : rawSpec.slice(q) };
  for (const e of RESOLVE_EXTS) if (fileSet.has(base + e)) return { target: base + e, how: 'ext-omitted', omitted: e, tail: q === -1 ? '' : rawSpec.slice(q) };
  for (const i of INDEXES) if (fileSet.has(base + i)) return { target: base + i, how: 'index', tail: q === -1 ? '' : rawSpec.slice(q) };
  return null;
}

function emitSpec(origSpec, r, fromNew, targetNew) {
  let t = targetNew;
  if (r.how === 'ext-omitted' && t.endsWith(r.omitted)) t = t.slice(0, -r.omitted.length);
  if (r.how === 'index') t = posix.dirname(t);
  if (origSpec.startsWith('@/') && t.startsWith(JS)) return `@/${t.slice(JS.length)}${r.tail}`;
  let rel = posix.relative(posix.dirname(fromNew), t);
  if (!rel.startsWith('.')) rel = `./${rel}`;
  return rel + r.tail;
}

const SPEC_RE = /(['"`])((?:@\/|\.{1,2}\/)[^'"`\n\r$\\]*?)\1/g;
const CSS_URL_RE = /url\(\s*(\.{1,2}\/[^)'"\s]+)\s*\)/g;

function rewriteSpecs(fromOld, fromNew, text, fileSet, moveMap, stats) {
  const swap = (orig) => {
    const r = resolveSpec(fromOld, orig, fileSet);
    if (!r) return { out: orig, unresolved: true };
    const tNew = moveMap.get(r.target) ?? r.target;
    if (tNew === r.target && fromNew === fromOld) return { out: orig };
    const out = emitSpec(orig, r, fromNew, tNew);
    if (out !== orig) stats.specs++;
    return { out };
  };
  let res = text.replace(SPEC_RE, (m, q, spec) => {
    const { out } = swap(spec);
    return out === spec ? m : `${q}${out}${q}`;
  });
  res = res.replace(CSS_URL_RE, (m, spec) => {
    const { out } = swap(spec);
    return out === spec ? m : `url(${out})`;
  });
  return res;
}

/** every relative/alias string in a file that does not resolve (used for before/after comparison) */
function unresolvedSpecs(rel, text, fileSet) {
  const bad = [];
  if (!JS_EXT.has(ext(rel))) return bad;
  for (const m of text.matchAll(SPEC_RE)) {
    const spec = m[2];
    if (/[*{]/.test(spec)) continue; // globs
    if (!resolveSpec(rel, spec, fileSet)) bad.push(spec);
  }
  return bad;
}

// ───────────────────────────────────────────────────────── content rewriting ──
function buildNameRegex(map) {
  const names = [...map.keys()].sort((a, b) => b.length - a.length).map(esc);
  return names.length ? new RegExp(`(['"])(${names.join('|')})\\1`, 'g') : null;
}

const RE_ROUTE_NAME = /(['"`])new_frontend\.([\w.\-*]*)/g;
const RE_URL_SLASH = /(?<![\w.\-])\/new_frontend(?![\w-])(\/wallet(?![\w-]))?/g;
const RE_URL_QUOTED = /(['"`])new_frontend\/(wallet(?![\w-]))?/g;
const RE_PREFIX = /prefix\((['"])new_frontend\1\)/g;
const RE_NAMESPACE = /(\w)(\\{1,2})NewFrontend(?![\w])/g;
const RE_MEMBER_PHP = /web\/member\.php/g;
const PATH_WORDS = [[/components\/new_frontend\//g, 'components/User/'], [/layouts\/new_front_layout\//g, 'layouts/User/'], [/Controllers\/NewFrontend\//g, 'Controllers/User/']];

function rewriteContent({ fromOld, fromNew, text, fileSet, moveMap, nameRe, nameMap, stats, warnings }) {
  let t = text;
  if (SKIP_CONTENT.has(fromOld)) return t;
  if (JS_EXT.has(ext(fromOld))) t = rewriteSpecs(fromOld, fromNew, t, fileSet, moveMap, stats);
  if (nameRe) t = t.replace(nameRe, (m, q, name) => { stats.names++; return `${q}${nameMap.get(name)}${q}`; });
  t = t.replace(RE_ROUTE_NAME, (m, q, rest) => { stats.routeNames++; return `${q}${NEW_AREA}.${rest === 'home' ? HOME_NAME_NEW : rest}`; });
  t = t.replace(RE_URL_SLASH, (m, w) => { stats.urls++; return `/${NEW_AREA}${w ? `/${WALLET_NEW}` : ''}`; });
  t = t.replace(RE_URL_QUOTED, (m, q, w) => { stats.urls++; return `${q}${NEW_AREA}/${w ? WALLET_NEW : ''}`; });
  t = t.replace(RE_PREFIX, (m, q) => { stats.urls++; return `prefix(${q}${NEW_AREA}${q})`; });
  t = t.replace(RE_NAMESPACE, (m, a, b) => { stats.ns++; return `${a}${b}User`; });
  t = t.replace(RE_MEMBER_PHP, 'web/user.php');
  for (const [re, to] of PATH_WORDS) t = t.replace(re, to);
  t = patchSpecific(fromNew, t, warnings);
  return t;
}

// one-off edits that need a human decision, each guarded by an exact-match assertion
const LEGACY_CONTROLLER = 'app/Http/Controllers/LegacyUrlRedirectController.php';
function patchSpecific(newRel, t, warnings) {
  if (newRel === 'routes/web/user.php') {
    let n = 0;
    t = t.replace(/(Route::(?:get|post|put|patch|delete|any)\(\s*)(['"])\/wallet(?=[\/'"])/g, (m, a, q) => { n++; return `${a}${q}/${WALLET_NEW}`; });
    if (n !== 13) warnings.push(`routes/web/user.php: expected 13 wallet routes to move to /${WALLET_NEW}, found ${n} - check the file by hand`);
    const before = t;
    t = t.replace("Route::get('/home', [DashboardController::class, 'home'])->name('home');", `Route::get('/home', [DashboardController::class, 'home'])->name('${HOME_NAME_NEW}');`);
    if (t === before) warnings.push(`routes/web/user.php: the '/home' route line was not found, so it keeps the name 'home' (clashes with user.home)`);
    if (!t.includes('LegacyUrlRedirectController')) {
      t = t.replace('use Illuminate\\Support\\Facades\\Route;', 'use App\\Http\\Controllers\\LegacyUrlRedirectController;\nuse Illuminate\\Support\\Facades\\Route;');
      t = `${t.replace(/\s*$/, '')}\n\n// The member app used to live under /new_frontend. Every old address is redirected for good, so emailed links,\n// push-notification links, Stripe returns and bookmarks keep working. Safe to delete once old links have aged out.\nRoute::any('${OLD_AREA}/{path?}', LegacyUrlRedirectController::class)->where('path', '.*')->name('legacy.${OLD_AREA}');\n`;
    }
  }
  if (newRel === `${L}User/MainLayout.vue`) {
    const before = t;
    t = t.replace("route('user.' + item.id)", `route(item.id === 'home' ? 'user.${HOME_NAME_NEW}' : 'user.' + item.id)`);
    if (t === before) warnings.push(`${newRel}: the dynamic nav link route('user.' + item.id) was not found - check that the Home link goes to user.${HOME_NAME_NEW}`);
  }
  if (newRel === 'WARP.md' && !t.includes('resources/js/pages/User/')) {
    t = t.replace(
      '- `resources/js/lib/` - Utility libraries',
      '- `resources/js/lib/` - Utility libraries\n\n**Member app (`User`):** one folder per area. Pages live in `resources/js/pages/User/<Feature>/` (`Home`, `Linkup`, `Wallet`, `Events`, `Live`, `Marketplace`, ...), their page-only pieces in `<Feature>/components/` and `<Feature>/modals/`, and the original screens that a newer page replaced in `<Feature>/Classic/` (still routed; retire one feature at a time). Shared member components are in `resources/js/components/User/`, the layout in `resources/js/layouts/User/`, PHP controllers in `app/Http/Controllers/User/`, routes in `routes/web/user.php` (URL prefix `/user`, route names `user.*`; old `/new_frontend/*` addresses redirect).',
    );
  }
  return t;
}

const LEGACY_CONTROLLER_SRC = `<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;

/**
 * The member app used to live under /new_frontend. This sends every old address to its new /user address for good
 * (301 for reads, 308 for writes so the method and body survive), keeping the query string.
 */
final class LegacyUrlRedirectController extends Controller
{
    public function __invoke(Request $request, ?string $path = null): RedirectResponse
    {
        $path = trim((string) $path, '/');
        $parts = explode('/', $path, 2);

        // the new wallet routes moved to /user/${WALLET_NEW} because /user/${WALLET_OLD}* already belongs to the original wallet
        if ($parts[0] === '${WALLET_OLD}') {
            $path = '${WALLET_NEW}'.(isset($parts[1]) ? '/'.$parts[1] : '');
        }

        $target = '/${NEW_AREA}'.($path !== '' ? '/'.$path : '');
        if (($query = $request->getQueryString()) !== null && $query !== '') {
            $target .= '?'.$query;
        }

        return redirect($target, $request->isMethodSafe() ? 301 : 308);
    }
}
`;

// ───────────────────────────────────────────────────────────────────── plan ──
function buildPlan() {
  const files = walk();
  const fileSet = new Set(files);

  if (!fileSet.has('artisan') || !fs.existsSync(abs('resources/js'))) throw new Error(`${ROOT} does not look like the LinkUp project root (no artisan / resources/js).`);
  if (!files.some((f) => f.startsWith(`${P}new_front/`))) throw new Error('resources/js/pages/new_front is already gone - looks like this has been applied already. Nothing to do.');

  const moveMap = new Map();
  for (const f of files) {
    const n = mapPath(f);
    if (n && n !== f) moveMap.set(f, n);
  }

  const problems = [];
  const dest = new Map();
  for (const [o, n] of moveMap) {
    const key = n.toLowerCase();
    if (dest.has(key)) problems.push(`two files would land on the same path: ${dest.get(key)} and ${o} -> ${n}`);
    dest.set(key, o);
    if (fileSet.has(n) && !moveMap.has(n)) problems.push(`destination already exists and is not moving: ${n} (from ${o})`);
  }

  // anything else still carrying the old name
  const unmapped = files.filter((f) => !moveMap.has(f) && /new_?front/i.test(f) && !SELF.has(f));

  // inertia page names
  const nameMap = new Map();
  for (const [o, n] of moveMap) {
    if (o.startsWith(P) && o.endsWith('.vue') && n.startsWith(P)) nameMap.set(o.slice(P.length, -4), n.slice(P.length, -4));
  }
  const nameRe = buildNameRegex(nameMap);

  const stats = { specs: 0, names: 0, routeNames: 0, urls: 0, ns: 0 };
  const warnings = [];
  const changed = new Map(); // old path -> new text (text files whose content changes or that move)
  const baselineBad = [];
  for (const f of files) {
    if (!isText(f)) continue;
    const text = readText(f);
    for (const spec of unresolvedSpecs(f, text, fileSet)) baselineBad.push({ file: f, spec });
    const fromNew = moveMap.get(f) ?? f;
    const out = rewriteContent({ fromOld: f, fromNew, text, fileSet, moveMap, nameRe, nameMap, stats, warnings });
    if (out !== text) changed.set(f, out);
  }
  // leftovers in the would-be result
  const leftovers = [];
  const dynamic = [];
  for (const f of files) {
    if (!isText(f) || SKIP_CONTENT.has(f)) continue;
    const text = changed.get(f) ?? readText(f);
    for (const m of text.matchAll(/new_?front|NewFrontend/gi)) if (!INTENDED.test(fullLine(text, m.index)) || /stripe_cart/.test(fullLine(text, m.index))) leftovers.push({ file: moveMap.get(f) ?? f, line: lineOf(text, m.index), text: snippet(text, m.index) });
    for (const m of text.matchAll(/route\(\s*['"`]user\.['"`]\s*\+|`user\.\$\{|(?:Inertia::render|inertia)\(\s*['"][^'"]*['"]\s*\.|['"`]user(?:\.|\/)\*/g)) dynamic.push({ file: moveMap.get(f) ?? f, line: lineOf(text, m.index), text: snippet(text, m.index) });
  }

  // inertia render names that point at a page file that does not exist (before)
  const renderRe = /(?:Inertia::render|inertia)\(\s*(['"])([^'"]+)\1/g;
  const missingPages = [];
  for (const f of files) {
    if (!f.endsWith('.php') || !(f.startsWith('app/') || f.startsWith('routes/'))) continue;
    for (const m of readText(f).matchAll(renderRe)) if (!fileSet.has(`${P}${m[2]}.vue`)) missingPages.push({ file: f, name: m[2] });
  }

  // who imports the shared files that are moving (so you can see what else depends on them)
  const outsideImporters = new Map();
  const movedTargets = new Set(moveMap.keys());
  for (const f of files) {
    if (!JS_EXT.has(ext(f)) || !isText(f)) continue;
    const area = f.startsWith(`${P}admin/`) ? 'pages/admin' : f.startsWith(`${P}organizer/`) ? 'pages/organizer' : f.startsWith(`${P}Legal/`) ? 'pages/Legal' : f.startsWith(`${C}admin/`) || f.startsWith(`${C}organizer/`) || f.startsWith(`${L}admin/`) || f.startsWith(`${L}organizer/`) ? 'admin/organizer shared' : null;
    if (!area) continue;
    for (const m of readText(f).matchAll(SPEC_RE)) {
      const r = resolveSpec(f, m[2], fileSet);
      if (r && movedTargets.has(r.target)) {
        const k = `${area} -> ${r.target}`;
        outsideImporters.set(k, (outsideImporters.get(k) ?? 0) + 1);
      }
    }
  }

  return { files, fileSet, moveMap, problems, unmapped, nameMap, nameRe, stats, warnings, changed, baselineBad, leftovers, dynamic, missingPages, outsideImporters };
}

function area(rel) {
  if (rel.startsWith(P)) return 'pages';
  if (rel.startsWith(C)) return 'components';
  if (rel.startsWith(L)) return 'layouts';
  if (rel.startsWith('app/')) return 'php controllers';
  if (rel.startsWith('routes/')) return 'routes';
  return 'other';
}

function writeReport(plan) {
  const { moveMap, problems, unmapped, stats, warnings, changed, baselineBad, leftovers, dynamic, missingPages, outsideImporters, nameMap } = plan;
  const byArea = {};
  for (const n of moveMap.values()) byArea[area(n)] = (byArea[area(n)] ?? 0) + 1;
  const trees = [...new Set([...moveMap.values()].filter((n) => n.startsWith(U)).map((n) => n.slice(U.length).split('/').slice(0, 2).join('/')))].sort();
  const L2 = [];
  L2.push('# User folder restructure - dry run', '');
  L2.push(`- files that move: **${moveMap.size}** (${Object.entries(byArea).map(([k, v]) => `${k} ${v}`).join(', ')})`);
  L2.push(`- files whose text changes (imports, page names, route names, URLs, namespaces): **${changed.size}**`);
  L2.push(`- rewritten: ${stats.specs} imports/paths, ${stats.names} Inertia page names, ${stats.routeNames} route names, ${stats.urls} URLs, ${stats.ns} PHP namespaces`);
  L2.push(`- Inertia page names that change: ${nameMap.size}`);
  L2.push('', '## Must fix before apply', problems.length || unmapped.length ? '' : '_nothing - clean_');
  for (const p of problems) L2.push(`- ${p}`);
  for (const u of unmapped) L2.push(`- still named new_front*, has no move rule: ${u}`);
  L2.push('', '## Warnings', warnings.length ? '' : '_none_');
  for (const w of warnings) L2.push(`- ${w}`);
  L2.push('', '## Shared files that are moving but are also imported from admin / organizer / legal code', '', outsideImporters.size ? 'These imports are rewritten too, but check the file really belongs in User:' : '_none_', '');
  for (const [k, v] of [...outsideImporters].sort()) L2.push(`- ${k}  (${v})`);
  L2.push('', '## Pre-existing problems (not caused by this change)', '');
  L2.push(`- relative/alias imports that already do not resolve today: **${baselineBad.length}**${baselineBad.length ? ' (see below, first 60)' : ''}`);
  for (const b of baselineBad.slice(0, 60)) L2.push(`  - ${b.file}: ${b.spec}`);
  L2.push(`- controllers that render a page file that does not exist: **${missingPages.length}**`);
  for (const m of missingPages) L2.push(`  - ${m.file}: ${m.name}`);
  L2.push('', '## Dynamic names to review by hand', dynamic.length ? '' : '_none_');
  for (const d of dynamic) L2.push(`- ${d.file}:${d.line}  ${d.text}`);
  L2.push('', `## Still mentions new_front / NewFrontend after the rewrite (${leftovers.length})`, leftovers.length ? 'Comments, labels or things the rules deliberately do not touch:' : '_none_', '');
  for (const l of leftovers.slice(0, 200)) L2.push(`- ${l.file}:${l.line}  ${l.text}`);
  if (leftovers.length > 200) L2.push(`- ... ${leftovers.length - 200} more`);
  L2.push('', '## New layout of resources/js/pages/User', '', '```');
  for (const t of trees) L2.push(t);
  L2.push('```', '', 'Full old -> new list: restructure-moves.csv');
  fs.writeFileSync(abs('restructure-report.md'), `${L2.join('\n')}\n`);
  fs.writeFileSync(abs('restructure-moves.csv'), `old,new\n${[...moveMap].sort().map(([o, n]) => `${o},${n}`).join('\n')}\n`);
}

// ──────────────────────────────────────────────────────────────────── apply ──
function git(args) {
  return execSync(`git ${args}`, { cwd: ROOT, stdio: ['ignore', 'pipe', 'pipe'] }).toString();
}

function apply(quiet = false) {
  if (!has('force')) {
    let dirty;
    try { dirty = git('status --porcelain').split('\n').filter((l) => l && !/restructure-(report\.md|moves\.csv|baseline\.json)|scripts\/restructure-user\.mjs|route-(before|after)\.json/.test(l)); }
    catch { throw new Error('git is not available here. Run it from the project folder, or pass --force if you have your own backup.'); }
    if (dirty.length) throw new Error(`Working tree is not clean (${dirty.length} changes). Commit or stash first, then run apply on a fresh branch:\n  git switch -c restructure-user-folder\n${dirty.slice(0, 8).join('\n')}`);
  }
  const plan = buildPlan();
  if (plan.problems.length || plan.unmapped.length) throw new Error(`Refusing to apply - see "Must fix" in restructure-report.md (run plan first).`);
  writeReport(plan);
  const { moveMap, changed, fileSet } = plan;
  const tmp = abs('.restructure-tmp');
  if (fs.existsSync(tmp)) throw new Error('.restructure-tmp already exists from an earlier run - delete it (git has the originals) and retry.');

  // 1. write every final file into a mirror folder first (nothing in the project is touched yet)
  const queue = new Map(); // final rel -> source (Buffer | string)
  for (const f of plan.files) {
    const n = moveMap.get(f) ?? f;
    if (changed.has(f)) queue.set(n, changed.get(f));
    else if (moveMap.has(f)) queue.set(n, fs.readFileSync(abs(f)));
  }
  queue.set(LEGACY_CONTROLLER, LEGACY_CONTROLLER_SRC);
  for (const [rel, data] of queue) {
    const p = path.join(tmp, ...rel.split('/'));
    fs.mkdirSync(path.dirname(p), { recursive: true });
    fs.writeFileSync(p, data);
  }
  // 2. take the old copies away (git still has them)
  for (const f of moveMap.keys()) fs.unlinkSync(abs(f));
  // 3. put the mirror in place
  for (const rel of queue.keys()) {
    fs.mkdirSync(path.dirname(abs(rel)), { recursive: true });
    fs.copyFileSync(path.join(tmp, ...rel.split('/')), abs(rel));
  }
  fs.rmSync(tmp, { recursive: true, force: true });
  // 4. clean up folders that are now empty
  const dirs = new Set();
  for (const f of moveMap.keys()) for (let d = posix.dirname(f); d && d !== '.'; d = posix.dirname(d)) dirs.add(d);
  for (const d of [...dirs].sort((a, b) => b.length - a.length)) { try { fs.rmdirSync(abs(d)); } catch { /* not empty */ } }

  fs.writeFileSync(abs('restructure-baseline.json'), JSON.stringify({ moves: Object.fromEntries(moveMap), badBefore: plan.baselineBad, missingPages: plan.missingPages, nameMap: Object.fromEntries(plan.nameMap) }, null, 1));
  console.log(`\nApplied: ${moveMap.size} files moved, ${changed.size} files rewritten, 1 file added (${LEGACY_CONTROLLER}).`);
  verify();
  if (quiet) return;
  console.log(`\nNext:
  php artisan optimize:clear
  php artisan ziggy:generate          (resources/js/ziggy.js is generated - this refreshes the route names)
  php artisan route:list --json > route-after.json
  node scripts/restructure-user.mjs routes-compare route-before.json route-after.json
  npm run build
  git add -A && git status           (git shows the moves as renames)`);
}

// ─────────────────────────────────────────────────────────────────── verify ──
function verify() {
  const files = walk();
  const fileSet = new Set(files);
  let bad = 0;
  const baseline = fs.existsSync(abs('restructure-baseline.json')) ? JSON.parse(fs.readFileSync(abs('restructure-baseline.json'), 'utf8')) : null;
  const moves = baseline?.moves ?? {};
  const was = new Set((baseline?.badBefore ?? []).map((b) => `${moves[b.file] ?? b.file}|${b.spec}`));

  const nowBad = [];
  for (const f of files) {
    if (!isText(f)) continue;
    for (const spec of unresolvedSpecs(f, readText(f), fileSet)) if (!was.has(`${f}|${spec}`)) nowBad.push(`${f}: ${spec}`);
  }
  console.log(`\nverify: imports       ${nowBad.length ? `FAIL - ${nowBad.length} import(s) that resolved before no longer resolve` : 'ok - every import that worked before still resolves'}`);
  for (const b of nowBad.slice(0, 50)) console.log(`    ${b}`);
  bad += nowBad.length;

  const oldMissing = new Set((baseline?.missingPages ?? []).map((m) => `${m.file}|${baseline.nameMap?.[m.name] ?? m.name}`));
  const renderRe = /(?:Inertia::render|inertia)\(\s*(['"])([^'"]+)\1/g;
  const missing = [];
  for (const f of files) {
    if (!f.endsWith('.php') || !(f.startsWith('app/') || f.startsWith('routes/'))) continue;
    for (const m of readText(f).matchAll(renderRe)) if (!fileSet.has(`${P}${m[2]}.vue`) && !oldMissing.has(`${f}|${m[2]}`)) missing.push(`${f}: ${m[2]}`);
  }
  console.log(`verify: page names    ${missing.length ? `FAIL - ${missing.length} Inertia page name(s) with no file` : 'ok - every Inertia page name still points at a file'}`);
  for (const b of missing) console.log(`    ${b}`);
  bad += missing.length;

  const left = [];
  for (const f of files) {
    if (!isText(f) || SKIP_CONTENT.has(f)) continue;
    const text = readText(f);
    for (const m of text.matchAll(/new_frontend|NewFrontend|new_front_layout|pages\/new_front/g)) if (!INTENDED.test(fullLine(text, m.index)) || /stripe_cart/.test(fullLine(text, m.index))) left.push(`${f}:${lineOf(text, m.index)}  ${snippet(text, m.index)}`);
  }
  console.log(`verify: old name      ${left.length ? `${left.length} mention(s) left (comments/labels?) - review` : 'ok - no new_frontend / NewFrontend left'}`);
  for (const l of left.slice(0, 40)) console.log(`    ${l}`);

  const oldDirs = [`${P}new_front`, `${C}new_frontend`, `${L}new_front_layout`, 'app/Http/Controllers/NewFrontend'].filter((d) => fs.existsSync(abs(d)));
  console.log(`verify: old folders   ${oldDirs.length ? `FAIL - still there: ${oldDirs.join(', ')}` : 'ok - gone'}`);
  bad += oldDirs.length;
  if (bad) process.exitCode = 1;
}

// ──────────────────────────────────────────────────────────── routes-compare ──
function routesCompare(beforeFile, afterFile) {
  const read = (f) => { const j = JSON.parse(fs.readFileSync(f, 'utf8')); return Array.isArray(j) ? j : Object.values(j); };
  const before = read(beforeFile);
  const after = read(afterFile);
  const mapUri = (u) => {
    if (u !== OLD_AREA && !u.startsWith(`${OLD_AREA}/`)) return u;
    let rest = u.slice(OLD_AREA.length);
    if (rest === `/${WALLET_OLD}` || rest.startsWith(`/${WALLET_OLD}/`)) rest = `/${WALLET_NEW}${rest.slice(WALLET_OLD.length + 1)}`;
    return NEW_AREA + rest;
  };
  const mapName = (n) => (n && n.startsWith(`${OLD_AREA}.`) ? `${NEW_AREA}.${n === `${OLD_AREA}.home` ? HOME_NAME_NEW : n.slice(OLD_AREA.length + 1)}` : n);
  const mapAction = (a) => (typeof a === 'string' ? a.replace(/\\Controllers\\NewFrontend\\/g, '\\Controllers\\User\\') : a);
  const canon = (r) => JSON.stringify([r.domain ?? null, r.method, r.uri, r.name ?? null, r.action, [...(r.middleware ?? [])]]);
  const expected = before.map((r) => canon({ ...r, uri: mapUri(r.uri), name: mapName(r.name), action: mapAction(r.action) }));
  const got = after.map(canon);
  const count = (arr) => arr.reduce((m, x) => m.set(x, (m.get(x) ?? 0) + 1), new Map());
  const e = count(expected);
  const g = count(got);
  const missing = [];
  const extra = [];
  for (const [k, v] of e) if ((g.get(k) ?? 0) < v) missing.push(k);
  for (const [k, v] of g) if ((e.get(k) ?? 0) < v) extra.push(k);
  console.log(`routes before: ${before.length}   routes after: ${after.length}`);
  console.log(`expected change: every new_frontend route moves to /${NEW_AREA} (wallet -> /${NEW_AREA}/${WALLET_NEW}), name prefix ${OLD_AREA}. -> ${NEW_AREA}., class namespace NewFrontend -> User, plus one redirect route.`);
  console.log(missing.length ? `FAIL - ${missing.length} route(s) lost or changed:` : 'ok - no route lost or altered');
  for (const m of missing.slice(0, 30)) console.log(`   - ${m}`);
  const unexpected = extra.filter((x) => !x.includes(`"legacy.${OLD_AREA}"`));
  console.log(unexpected.length ? `FAIL - ${unexpected.length} unexpected new route(s):` : 'ok - only the redirect route was added');
  for (const m of unexpected.slice(0, 30)) console.log(`   + ${m}`);
  // nothing may now share a method+uri that did not before
  const pairs = (rows) => rows.reduce((m, r) => { for (const meth of String(r.method).split('|').filter((x) => x !== 'HEAD')) { const k = `${meth} ${r.uri}`; m.set(k, (m.get(k) ?? 0) + 1); } return m; }, new Map());
  const pb = pairs(before);
  const pa = pairs(after);
  const dup = [...pa].filter(([k, v]) => v > (pb.get(k) ?? 0) && v > 1 && !k.includes(`${OLD_AREA}/{path?}`));
  console.log(dup.length ? `FAIL - ${dup.length} address(es) now served by two routes:` : 'ok - no address is claimed by two routes');
  for (const [k] of dup.slice(0, 20)) console.log(`   ! ${k}`);
  if (missing.length || unexpected.length || dup.length) process.exitCode = 1;
}


// ───────────────────────────────────────────────────────────────────── run ──
async function run() {
  const { createInterface } = await import('node:readline/promises');
  const php = opt('php') ?? 'php';
  const sh = (c) => execSync(c, { cwd: ROOT, stdio: 'inherit' });
  const routeList = (file) => {
    const out = execSync(`${php} artisan route:list --json`, { cwd: ROOT, maxBuffer: 1 << 28, stdio: ['ignore', 'pipe', 'inherit'] }).toString();
    const line = out.split('\n').reverse().find((l) => l.trim().startsWith('['));
    if (!line) throw new Error('php artisan route:list --json did not return JSON');
    fs.writeFileSync(abs(file), line.trim());
    console.log(`   saved ${file} (${JSON.parse(line).length} routes)`);
  };
  const step = (n, t) => console.log(`\n[${n}] ${t}`);

  step(1, 'Branch');
  if (!has('skip-git')) {
    const dirty = git('status --porcelain').split('\n').filter((l) => l && !/restructure-|scripts\/restructure-user\.mjs|route-(before|after)\.json/.test(l));
    if (dirty.length) throw new Error(`Working tree is not clean (${dirty.length} changes). Commit or stash first, then run again.\n${dirty.slice(0, 8).join('\n')}`);
    const branch = opt('branch') ?? 'restructure-user-folder';
    git(`switch -c ${branch}`);
    console.log(`   on new branch ${branch}`);
  }

  step(2, 'Snapshot the routes before');
  if (!has('skip-php')) routeList('route-before.json'); else console.log('   skipped (--skip-php)');

  step(3, 'Plan (nothing is changed yet)');
  const plan = buildPlan();
  writeReport(plan);
  console.log(`   ${plan.moveMap.size} files move, ${plan.changed.size} files are rewritten, ${plan.warnings.length} warnings, ${plan.leftovers.length} leftover mentions`);
  for (const w of plan.warnings) console.log(`   warning: ${w}`);
  if (plan.problems.length || plan.unmapped.length) {
    for (const x of [...plan.problems, ...plan.unmapped.map((u) => `still named new_front*, no move rule: ${u}`)]) console.log(`   BLOCKING: ${x}`);
    throw new Error('Stopped before changing anything. Fix the blocking items above (or send them to Claude), then run again. The new branch is empty: `git switch -` and `git branch -D restructure-user-folder` to drop it.');
  }
  console.log('   full list: restructure-report.md   old -> new: restructure-moves.csv');
  if (!has('yes')) {
    const rl = createInterface({ input: process.stdin, output: process.stdout });
    const answer = (await rl.question('\nType APPLY and press Enter to move the files (anything else cancels): ')).trim();
    rl.close();
    if (answer !== 'APPLY') throw new Error('Cancelled. Nothing was changed.');
  }

  step(4, 'Apply');
  apply(true);

  step(5, 'Refresh Laravel and check the routes');
  if (!has('skip-php')) {
    sh(`${php} artisan optimize:clear`);
    try { sh(`${php} artisan ziggy:generate`); } catch { console.log('   ziggy:generate failed - run it by hand (resources/js/ziggy.js is generated)'); }
    routeList('route-after.json');
    routesCompare(abs('route-before.json'), abs('route-after.json'));
    if (process.exitCode) throw new Error('The route check failed - do not commit. Revert with: git reset --hard && git clean -fd');
  } else console.log('   skipped (--skip-php)');

  step(6, 'Build the front end');
  if (!has('skip-build')) {
    try { sh('npm run build'); } catch { throw new Error('npm run build failed - the move broke something. Revert with: git reset --hard && git clean -fd  (then send the first error to Claude)'); }
  } else console.log('   skipped (--skip-build)');

  if (!has('skip-git')) git('add -A');
  console.log('\nDone. Everything is staged but not committed:\n  git status        (moves show as renames)\n  git commit -m "Move member app into one User folder"\nTry the site (home, wallet, a live page, an old /new_frontend link) before merging.');
}

// ───────────────────────────────────────────────────────────────────── main ──
try {
  if (cmd === 'plan') {
    const plan = buildPlan();
    writeReport(plan);
    console.log(`plan: ${plan.moveMap.size} files move, ${plan.changed.size} files are rewritten.`);
    console.log(`      ${plan.problems.length + plan.unmapped.length} blocking problems, ${plan.warnings.length} warnings, ${plan.leftovers.length} leftover mentions.`);
    console.log(`
Before apply:
  1. git switch -c restructure-user-folder        (commit or stash everything else first)
  2. stop 'npm run dev' / 'composer run dev'      (open files block renames on Windows)
  3. php artisan route:list --json > route-before.json
  4. read restructure-report.md
then:  node scripts/restructure-user.mjs apply`);
    if (plan.problems.length || plan.unmapped.length) process.exitCode = 1;
  } else if (cmd === 'run') await run();
  else if (cmd === 'apply') apply();
  else if (cmd === 'verify') verify();
  else if (cmd === 'routes-compare') routesCompare(argv[1], argv[2]);
  else throw new Error(`unknown command "${cmd}" (run | plan | apply | verify | routes-compare)`);
} catch (e) {
  console.error(`\n${e.message}`);
  process.exitCode = 1;
}
