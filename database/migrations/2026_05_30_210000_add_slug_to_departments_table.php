<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('departments', 'slug')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('code');
            });
        }

        // Backfill existing departments safely, avoiding duplicate slug collisions
        $departments = DB::table('departments')->get();
        $assignedSlugs = [];
        foreach ($departments as $dept) {
            $baseSlug = str()->slug($dept->name) ?: (str()->slug($dept->code) ?: 'dept');
            $slug = $baseSlug;
            $counter = 1;
            
            while (in_array($slug, $assignedSlugs)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $assignedSlugs[] = $slug;

            DB::table('departments')
                ->where('id', $dept->id)
                ->update([
                    'slug' => $slug
                ]);
        }

        // Apply index after backfilling to ensure standard database compliance
        Schema::table('departments', function (Blueprint $table) {
            $table->index('slug', 'idx_departments_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex('idx_departments_slug');
            $table->dropColumn('slug');
        });
    }
};
