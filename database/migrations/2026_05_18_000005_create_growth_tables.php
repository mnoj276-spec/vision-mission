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
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('category_name')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('growth_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->index(); // page_view, subscribe, apply_click
            $table->string('page_path')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growth_analytics');
        Schema::dropIfExists('job_alerts');
    }
};
