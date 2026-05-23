<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // How the owner wants to receive orders:
            //   whatsapp = customer is redirected to WhatsApp with the order pre-filled (no in-app dashboard tracking flow)
            //   web      = order goes to the in-app dashboard only — no WhatsApp redirect for the customer
            //   both     = saved in dashboard AND customer gets a WhatsApp link too
            $table->enum('orders_via', ['whatsapp', 'web', 'both'])
                ->default('whatsapp')
                ->after('delivery');

            // Same idea for bookings, since salons/clinics behave differently from restaurants
            $table->enum('bookings_via', ['whatsapp', 'web', 'both'])
                ->default('whatsapp')
                ->after('orders_via');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['orders_via', 'bookings_via']);
        });
    }
};
