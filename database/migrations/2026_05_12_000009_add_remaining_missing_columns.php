<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds columns that Request classes validate but no prior migration created:
 *
 *  payments:
 *    - details    : free-text note (PaymentRequest validates it)
 *    - cc_slip    : boolean "accepted" flag for Card Terminal payments
 *
 *  deliveries:
 *    - delivered  : boolean flag (DeliveryRequest validates it)
 *    - delivered_by : name of who delivered (DeliveryRequest validates it)
 *    - received_by  : name of who received (DeliveryRequest validates it)
 *
 *  return_orders:
 *    - surcharge  : additional charge on return (ReturnOrderRequest validates it)
 *    - type_ref   : original order reference string (ReturnOrderRequest validates it)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── payments ────────────────────────────────────────────────────
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $t) {
                if (! Schema::hasColumn('payments', 'details')) {
                    $t->text('details')->nullable();
                }
                if (! Schema::hasColumn('payments', 'cc_slip')) {
                    $t->boolean('cc_slip')->nullable()->default(0);
                }
            });
        }

        // ── deliveries ──────────────────────────────────────────────────
        if (Schema::hasTable('deliveries')) {
            Schema::table('deliveries', function (Blueprint $t) {
                if (! Schema::hasColumn('deliveries', 'delivered')) {
                    $t->boolean('delivered')->default(0)->after('details');
                }
                if (! Schema::hasColumn('deliveries', 'delivered_by')) {
                    $t->string('delivered_by')->nullable()->after('delivered');
                }
                if (! Schema::hasColumn('deliveries', 'received_by')) {
                    $t->string('received_by')->nullable()->after('delivered_by');
                }
            });
        }

        // ── return_orders ───────────────────────────────────────────────
        if (Schema::hasTable('return_orders')) {
            Schema::table('return_orders', function (Blueprint $t) {
                if (! Schema::hasColumn('return_orders', 'surcharge')) {
                    $t->decimal('surcharge', 25, 4)->nullable()->after('grand_total');
                }
                if (! Schema::hasColumn('return_orders', 'type_ref')) {
                    $t->string('type_ref')->nullable()->after('type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $t) {
                $t->dropColumn(['details', 'cc_slip']);
            });
        }
        if (Schema::hasTable('deliveries')) {
            Schema::table('deliveries', function (Blueprint $t) {
                $t->dropColumn(['delivered', 'delivered_by', 'received_by']);
            });
        }
        if (Schema::hasTable('return_orders')) {
            Schema::table('return_orders', function (Blueprint $t) {
                $t->dropColumn(['surcharge', 'type_ref']);
            });
        }
    }
};
