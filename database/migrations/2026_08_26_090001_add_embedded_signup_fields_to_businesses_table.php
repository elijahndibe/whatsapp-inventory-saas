<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supports the WhatsApp Embedded Signup flow (see WhatsAppConnectController):
 *
 *  - whatsapp_business_account_id becomes a plain, indexed identifier
 *    instead of encrypted — same reasoning as whatsapp_phone_number_id
 *    (see the 2026_08_24_203903 migration): a WABA ID is an identifier
 *    Meta itself shows in the Business Settings UI, not a secret, and it
 *    needs to be queryable to check for cross-tenant reuse. The actual
 *    secret (whatsapp_access_token) stays encrypted.
 *  - whatsapp_phone_number_id gets a UNIQUE index: the hard multi-tenant
 *    guarantee that no two businesses can ever share a WhatsApp number,
 *    enforced at the database level rather than only in application code.
 *  - whatsapp_connected_via / whatsapp_display_phone_number /
 *    whatsapp_connected_at are new, all driving the settings UI's
 *    connected/disconnected state without needing to re-derive it from
 *    Meta on every page load.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('whatsapp_business_account_id');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('whatsapp_business_account_id')->nullable()->after('whatsapp_phone_number_id');
            $table->index('whatsapp_business_account_id');

            $table->string('whatsapp_connected_via')->nullable()->after('whatsapp_access_token'); // 'manual' | 'embedded_signup'
            $table->string('whatsapp_display_phone_number')->nullable()->after('whatsapp_connected_via');
            $table->timestamp('whatsapp_connected_at')->nullable()->after('whatsapp_display_phone_number');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->unique('whatsapp_phone_number_id');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropUnique(['whatsapp_phone_number_id']);
            $table->dropColumn(['whatsapp_business_account_id', 'whatsapp_connected_via', 'whatsapp_display_phone_number', 'whatsapp_connected_at']);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->text('whatsapp_business_account_id')->nullable();
        });
    }
};
