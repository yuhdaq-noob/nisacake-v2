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
            // Kolom untuk menandai apakah admin sudah dinotifikasi via Telegram
            // Ditempatkan setelah scheduled_at untuk kemudahan pengaksesan
            $table->boolean('is_notified')
                ->default(false)
                ->after('scheduled_at')
                ->comment('Menandai apakah admin sudah dinotifikasi Telegram untuk pre-order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_notified');
        });
    }
};
