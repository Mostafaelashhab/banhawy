<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE businesses MODIFY bookings_via ENUM('whatsapp','web','both','walkin') NOT NULL DEFAULT 'whatsapp'");
    }

    public function down(): void
    {
        DB::statement("UPDATE businesses SET bookings_via = 'whatsapp' WHERE bookings_via = 'walkin'");
        DB::statement("ALTER TABLE businesses MODIFY bookings_via ENUM('whatsapp','web','both') NOT NULL DEFAULT 'whatsapp'");
    }
};
