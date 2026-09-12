<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $linkUpEventForeignKey = collect(Schema::getForeignKeys('tickets'))
            ->first(fn (array $foreignKey) => in_array('link_up_event_id', $foreignKey['columns'] ?? [], true));

        // 1) Drop old / unnecessary columns
        Schema::table('tickets', function (Blueprint $table) use ($linkUpEventForeignKey) {
            if ($linkUpEventForeignKey) {
                $table->dropForeign($linkUpEventForeignKey['name']);
            }

            if (Schema::hasColumn('tickets', 'firebase_id')) {
                $table->dropUnique('tickets_firebase_id_unique');
            }

            $dropCols = [
                'firebase_id',
                'title',
                'price_economy',
                'price_vip',
                'early_bird_economy_price',
                'early_bird_vip_price',
                'no_of_early_bird_economy',
                'no_of_tickets_available_economy',
                'no_of_early_bird_vip',
                'no_of_tickets_available_vip',
                'available_to',
                'available_from',
                'link_up_event_id',
                'qty',
                'qty_available',
                'qty_sold',
                'min_qty_per_order',
                'max_qty_per_order',
                'qrcode',
                'sales_start',
                'sales_end',
            ];

            foreach ($dropCols as $col) {
                if (Schema::hasColumn('tickets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // 2) Add required columns
        Schema::table('tickets', function (Blueprint $table) {
            // ✅ Event relation
            if (! Schema::hasColumn('tickets', 'event_id')) {
                $table->unsignedBigInteger('event_id')->nullable()->after('id');
                $table->foreign('event_id')->references('id')->on('link_up_events')->onDelete('cascade');
            }

            if (! Schema::hasColumn('tickets', 'type')) {
                $table->string('type')->nullable()->after('event_id');
            }

            if (! Schema::hasColumn('tickets', 'description')) {
                $table->text('description')->nullable()->after('type');
            }

            if (! Schema::hasColumn('tickets', 'has_table')) {
                $table->enum('has_table', ['yes', 'no'])->default('no')->after('description');
            }

            if (! Schema::hasColumn('tickets', 'table_price')) {
                $table->decimal('table_price', 8, 2)->default(0)->after('has_table');
            }

            if (! Schema::hasColumn('tickets', 'table_capacity')) {
                $table->unsignedInteger('table_capacity')->default(0)->after('table_price');
            }

            if (! Schema::hasColumn('tickets', 'sections')) {
                $table->json('sections')->nullable()->after('table_capacity');
            }

            if (! Schema::hasColumn('tickets', 'is_free')) {
                $table->enum('is_free', ['yes', 'no'])->default('no')->after('sections');
            }

            if (! Schema::hasColumn('tickets', 'price')) {
                $table->decimal('price', 8, 2)->default(0)->after('is_free');
            }

            if (! Schema::hasColumn('tickets', 'promo_price')) {
                $table->decimal('promo_price', 8, 2)->default(0)->after('price');
            }

            if (! Schema::hasColumn('tickets', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('promo_price');
            }

            if (! Schema::hasColumn('tickets', 'tickets_per_attendee')) {
                $table->unsignedInteger('tickets_per_attendee')->default(1)->after('quantity');
            }

            if (! Schema::hasColumn('tickets', 'sale_start')) {
                $table->timestamp('sale_start')->nullable()->after('tickets_per_attendee');
            }

            if (! Schema::hasColumn('tickets', 'sale_end')) {
                $table->timestamp('sale_end')->nullable()->after('sale_start');
            }

            if (! Schema::hasColumn('tickets', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('sale_end');
            } else {
                $table->enum('status', ['active', 'inactive'])->default('active')->change();
            }

            if (! Schema::hasColumn('tickets', 'drink_addons')) {
                $table->json('drink_addons')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $newCols = [
                'event_id',
                'type',
                'description',
                'has_table',
                'table_price',
                'table_capacity',
                'sections',
                'is_free',
                'price',
                'promo_price',
                'quantity',
                'tickets_per_attendee',
                'sale_start',
                'sale_end',
                'status',
                'drink_addons',
            ];

            foreach ($newCols as $col) {
                if (Schema::hasColumn('tickets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
