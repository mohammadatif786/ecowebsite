<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Commissions earned from Vibes: which post drove the sale, what was sold (item or event
 * tickets) and for how much, so My Earnings can list them and split Vibes from Marketplace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_affiliate_earnings', function (Blueprint $table) {
            $table->unsignedBigInteger('vibe_id')->nullable()->index()->after('product_id');
            $table->unsignedBigInteger('ticket_sale_id')->nullable()->index()->after('vibe_id');
            $table->string('source', 20)->nullable()->after('ticket_sale_id'); // vibe | uvibe | marketplace
            $table->string('title')->nullable()->after('source');
            $table->decimal('sale_amount', 12, 2)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_affiliate_earnings', function (Blueprint $table) {
            $table->dropIndex(['vibe_id']);
            $table->dropIndex(['ticket_sale_id']);
            $table->dropColumn(['vibe_id', 'ticket_sale_id', 'source', 'title', 'sale_amount']);
        });
    }
};
