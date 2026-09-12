<?php

namespace App\Repositories;

use App\Models\EventFeeSetting;
use App\Models\LinkUpEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsRepository
{
    /**
     * Event-specific fee settings, falling back to the global default.
     *
     * The Schema::hasColumn() check exists for backward-compatibility
     * with environments that haven't migrated the link_up_event_id
     * column yet. Running a schema introspection query on every single
     * page load is wasteful — it's cached here for the process lifetime.
     * Once every environment is confirmed migrated, delete this whole
     * check and just query EventFeeSetting directly.
     */
    public function getFeeSettingsForEvent(LinkUpEvent $event): ?EventFeeSetting
    {
        $hasEventColumn = Cache::rememberForever(
            'event_fee_settings_has_link_up_event_id_column',
            fn() => Schema::hasColumn('event_fee_settings', 'link_up_event_id')
        );

        $settings = $hasEventColumn
            ? EventFeeSetting::where('link_up_event_id', $event->id)->first()
            : null;

        return $settings ?? EventFeeSetting::first();
    }

    /**
     * NOTE — verify this column name. A generic key/value "settings"
     * table normally stores its payload in a `value` column, not a
     * column literally named after the key. If tax rules ever come
     * back null unexpectedly, this is the first place to check —
     * it may need to be ->value instead of ->tax_rules.
     */
    public function getTaxRules(): ?array
    {
        $row = DB::table('settings')->where('key', 'tax_rules')->first();

        if (! $row) {
            return null;
        }

        return json_decode($row->tax_rules, true);
    }
}
