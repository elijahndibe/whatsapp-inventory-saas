<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a third business status — 'closed' — for a business whose sole
 * Owner deleted their own account (see ProfileController::destroy()).
 * Unlike 'suspended' (admin-initiated, reversible), 'closed' is
 * owner-initiated and permanent: there's no user left on the business to
 * reactivate it. The business row and all its historical
 * products/orders/payments are deliberately kept, not deleted — deleting
 * them would retroactively shrink the platform's own already-recognized
 * GMV/commission history on the Admin dashboard, which the commission
 * snapshot design elsewhere in this app is specifically built to never
 * let happen.
 *
 * `status` moves from a native ENUM to a plain string, matching the same
 * add-copy-drop-rename approach already used for orders.order_status
 * (see 2026_08_27_080001_add_whatsapp_payment_link_flow_columns.php) —
 * ENUM-widening isn't portable SQL and this app has no doctrine/dbal
 * installed for Schema::change().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('status_new')->default('active')->after('status');
            $table->timestamp('closed_at')->nullable()->after('status_new');
        });

        DB::table('businesses')->update(['status_new' => DB::raw('status')]);

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('closed_at');
        });

        DB::table('businesses')->where('status', 'closed')->update(['status' => 'suspended']);

        // Reverting the string column back to the original two-value ENUM
        // is intentionally skipped — nothing downstream depends on it being
        // a native ENUM again, and doing so would risk truncation if any
        // 'closed' rows survived the update above under a race.
    }
};
