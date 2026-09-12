var _lkMap = null, _lkLayer = null, _lkGeo = null, _lkMarkers = [];
var mapMetric = 'revenue';

function mapXY(lat, lng) { var x = (lng - (-130)) / 100 * 100; var y = (60 - lat) / 95 * 100; return [Math.max(2, Math.min(98, x)), Math.max(4, Math.min(96, y))]; }
function regionColor(r) { return r === 'Local' ? '#10b981' : r === 'Regional' ? '#0ea5e9' : '#8b5cf6'; }

function ourCountryRow(name) {
    var oc = MAP_NAME2OURS[name];
    if (!oc) return null;
    return countries.find(function (c) { return c.country === oc; }) || null;
}

function mapMetricVal(row) {
    return mapMetric === 'users' ? Math.round((row.users || 0) * scale()) : Math.round(countryFin(row).gross);
}

function mapAllRows() {
    const rf = document.getElementById('regionFilter')?.value || 'All';
    return countries.filter(function (c) { return (rf === 'All' || c.region === rf); });
}

function mapMax() { return Math.max.apply(null, mapAllRows().map(mapMetricVal).concat([1])); }

function mapLeaderCountry() {
    var best = null, bv = -1;
    mapAllRows().forEach(function (c) { var v = mapMetricVal(c); if (v > bv) { bv = v; best = c.country; } });
    return best;
}

function setMapMetric(m) {
    mapMetric = m;
    var t = document.getElementById('mapMetricTitle');
    if (t) t.textContent = (m === 'users' ? 'Users' : 'Revenue');
    var rb = document.getElementById('mapMetricRev'), ub = document.getElementById('mapMetricUsers');
    if (rb) rb.className = 'rounded-xl px-3 py-1.5 font-black ' + (m === 'revenue' ? 'bg-white shadow' : 'text-slate-500');
    if (ub) ub.className = 'rounded-xl px-3 py-1.5 font-black ' + (m === 'users' ? 'bg-white shadow' : 'text-slate-500');
    renderCountryMap();
}

function lkStyle(feature) {
    var row = ourCountryRow(feature.properties.name);
    if (!row) return { fillColor: '#13304d', fillOpacity: .4, color: '#1f3b57', weight: .5 };
    var reg = GEO_REGION[row.country] || 'Caribbean';
    const cf = document.getElementById('countryFilter')?.value || 'All Countries';
    var sel = (cf === row.country);
    var leader = (row.country === mapLeaderCountry());
    var intensity = 0.35 + 0.6 * (mapMetricVal(row) / mapMax());
    return { fillColor: GEO_COLOR[reg] || '#10b981', fillOpacity: Math.min(0.97, intensity), color: leader ? '#facc15' : (sel ? '#ffffff' : '#06121f'), weight: leader ? 3.5 : (sel ? 2.5 : 1) };
}

function lkTipHtml(row) {
    var fin = countryFin(row);
    var leader = (row.country === mapLeaderCountry());
    return '<div style="font-weight:900;font-size:14px">' + (leader ? '👑 ' : '') + row.country + '</div><div style="opacity:.65;font-size:11px;margin-bottom:4px">' + (GEO_REGION[row.country] || '') + (leader ? ' • Top ' + (mapMetric === 'users' ? 'by users' : 'by revenue') : '') + '</div>GTV <b>' + fmt(fin.gross) + '</b><br>LinkUp Rev. <b>' + fmt(fin.platform) + '</b><br>Users <b>' + num((row.users || 0) * scale()) + '</b><div style="font-size:10px;opacity:.6;margin-top:3px">Click to filter the platform</div>';
}

function addLeaderCrown() {
    if (window._lkLeaderMarker) { try { window._lkLeaderMarker.remove(); } catch (e) { } window._lkLeaderMarker = null; }
    if (typeof L === 'undefined' || !_lkMap) return;
    var lc = mapLeaderCountry();
    var co = lc && MAP_COORDS[lc];
    if (!co) return;
    var rowv = mapAllRows().find(function (c) { return c.country === lc; });
    var v = rowv ? mapMetricVal(rowv) : 0;
    var label = (mapMetric === 'users' ? num(v) + ' users' : fmt(v));
    window._lkLeaderMarker = L.marker([co[0], co[1]], { icon: L.divIcon({ className: '', html: '<div style="transform:translate(-50%,-150%);background:#facc15;color:#0b1f33;font-weight:900;font-size:11px;padding:3px 9px;border-radius:9999px;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.35)">👑 ' + lc + ' · ' + label + '</div>' }), interactive: false }).addTo(_lkMap);
}

function renderCountryMap() {
    var box = document.getElementById('worldMap'); if (!box) return;
    if (typeof L === 'undefined') { return renderCountryMapBubbles(); }
    if (box.offsetParent === null && _lkGeo) { return; }
    if (!_lkMap) { box.innerHTML = ''; _lkMap = L.map(box, { zoomControl: true, attributionControl: false, scrollWheelZoom: false, minZoom: 2, maxZoom: 6 }); _lkMap.setView([8, -68], 3); }

    function present() { var s = {}; if (_lkGeo) { _lkGeo.features.forEach(function (f) { var r = ourCountryRow(f.properties.name); if (r) s[r.country] = true; }); } return s; }

    function addMarkers() {
        _lkMarkers.forEach(function (m) { m.remove(); }); _lkMarkers = [];
        var pres = present();
        countries.forEach(function (c) {
            if (pres[c.country]) return;
            var co = MAP_COORDS[c.country];
            if (!co) return;
            var reg = GEO_REGION[c.country] || 'Caribbean';
            var mk = L.circleMarker([co[0], co[1]], { radius: 7, fillColor: GEO_COLOR[reg] || '#10b981', color: '#fff', weight: 1.5, fillOpacity: .9 }).addTo(_lkMap);
            mk.bindTooltip(lkTipHtml(c), { className: 'mapcountry-tip', sticky: true });
            mk.on('click', function () { mapSelect(encodeURIComponent(c.country)); });
            _lkMarkers.push(mk);
        });
    }

    function applyGeo(geo) {
        _lkGeo = geo; if (_lkLayer) _lkLayer.remove();
        _lkLayer = L.geoJSON(geo, {
            style: lkStyle, onEachFeature: function (f, layer) {
                var row = ourCountryRow(f.properties.name); if (!row) return;
                layer.bindTooltip(lkTipHtml(row), { className: 'mapcountry-tip', sticky: true });
                layer.on('mouseover', function () { layer.setStyle({ weight: 2.5, color: '#fff', fillOpacity: .95 }); });
                layer.on('mouseout', function () { layer.setStyle(lkStyle(f)); });
                layer.on('click', function () { mapSelect(encodeURIComponent(row.country)); });
            }
        }).addTo(_lkMap);
        addMarkers(); addLeaderCrown();
        try { _lkMap.fitBounds(L.latLngBounds([[-34, -118], [55, -34]])); } catch (e) { }
        setTimeout(function () { try { _lkMap.invalidateSize(); } catch (e) { } }, 80);
    }

    if (_lkGeo) {
        if (_lkLayer) {
            _lkLayer.setStyle(lkStyle);
            _lkLayer.eachLayer(function (layer) { var row = ourCountryRow(layer.feature.properties.name); if (row) layer.setTooltipContent(lkTipHtml(row)); });
            addMarkers(); addLeaderCrown();
        } else applyGeo(_lkGeo);
        setTimeout(function () { try { _lkMap.invalidateSize(); } catch (e) { } }, 80); return;
    }
    fetch('https://cdn.jsdelivr.net/gh/johan/world.geo.json@master/countries.geo.json').then(function (r) { return r.json(); }).then(applyGeo).catch(function () { renderCountryMapBubbles(); });
}

function renderCountryMapBubbles() {
    var box = document.getElementById('worldMap'); if (!box) return;
    var rs = rows();
    var vals = rs.map(function (c) { return countryFin(c).gross; }); var maxV = Math.max.apply(null, vals.concat([1]));
    const cf = document.getElementById('countryFilter')?.value || 'All Countries';
    var sel = cf;
    var dots = rs.map(function (c) {
        var co = MAP_COORDS[c.country]; if (!co) return ''; var p = mapXY(co[0], co[1]); var g = countryFin(c).gross; var size = 22 + Math.sqrt(g / maxV) * 54; var col = regionColor(c.region); var active = (sel === c.country);
        return '<button class="mapdot" data-country="' + encodeURIComponent(c.country) + '" style="position:absolute;left:' + p[0] + '%;top:' + p[1] + '%;width:' + size + 'px;height:' + size + 'px;transform:translate(-50%,-50%);border-radius:9999px;border:' + (active ? '3px solid #fff' : '2px solid rgba(255,255,255,.55)') + ';background:' + col + ';opacity:.82;box-shadow:0 0 ' + (size / 2) + 'px ' + col + ';cursor:pointer;display:grid;place-items:center;" ' +
            'onmouseenter="mapHover(event,\'' + encodeURIComponent(c.country) + '\')" onmousemove="mapHover(event,\'' + encodeURIComponent(c.country) + '\')" onmouseleave="mapHoverOut()" onclick="mapSelect(\'' + encodeURIComponent(c.country) + '\')">' +
            '<span style="font-size:10px;font-weight:900;color:#fff;text-shadow:0 1px 2px rgba(0,0,0,.6);white-space:nowrap;pointer-events:none">' + c.country.split(' ')[0] + '</span></button>';
    }).join('');
    box.innerHTML = '<div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:40px 40px;"></div>' +
        '<div style="position:absolute;left:10px;bottom:10px;color:#93c5fd;font-size:11px;font-weight:800">LinkUp Operating Countries</div>' + dots;
    if (window.lucide) lucide.createIcons();
}

function mapHover(e, enc) {
    var c = decodeURIComponent(enc);
    var row = countries.find(function (x) { return x.country === c; });
    if (!row) return;
    var f = countryFin(row);
    var tip = document.getElementById('mapTooltip'); if (!tip) return;
    tip.innerHTML = '<div class="font-black text-base">' + c + '</div><div class="text-slate-300 text-xs mb-1">' + row.region + '</div><div class="flex justify-between gap-6"><span class="text-slate-400">GTV</span><b>' + fmt(f.gross) + '</b></div><div class="flex justify-between gap-6"><span class="text-slate-400">LinkUp Rev.</span><b class="text-emerald-300">' + fmt(f.platform) + '</b></div><div class="flex justify-between gap-6"><span class="text-slate-400">Users</span><b>' + num(row.users * scale()) + '</b></div><div class="text-[10px] text-slate-400 mt-1">Click to filter the whole platform</div>';
    tip.classList.remove('hidden'); tip.style.left = (e.clientX + 16) + 'px'; tip.style.top = (e.clientY + 16) + 'px';
}

function mapHoverOut() { var tip = document.getElementById('mapTooltip'); if (tip) tip.classList.add('hidden'); }

function mapSelect(enc) {
    var c = decodeURIComponent(enc);
    var cf = document.getElementById('countryFilter');
    if (cf) {
        cf.value = (cf.value === c ? 'All Countries' : c);
        renderAll();
    }
    mapHoverOut();
    renderCountryMap();
}
