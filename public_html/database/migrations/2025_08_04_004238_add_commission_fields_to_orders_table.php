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
        Schema::table('orders', function (Blueprint $table) {
            // Campos para manejar comisiones y detalles de pago
            $table->decimal('sub_total', 10, 2)->default(0)->after('total')->comment('Subtotal sin comisión');
            $table->decimal('commission_rate', 8, 2)->default(0)->after('sub_total')->comment('Tarifa de comisión por servicio');
            $table->decimal('commission', 10, 2)->default(0)->after('commission_rate')->comment('Total de comisión');
            $table->text('payment_detail')->nullable()->after('commission')->comment('Detalles adicionales del pago en JSON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn(['sub_total', 'commission_rate', 'commission', 'payment_detail']);
        });
    }
};
