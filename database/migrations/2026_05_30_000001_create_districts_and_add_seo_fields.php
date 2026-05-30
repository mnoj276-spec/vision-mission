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
        // 1. Add slug column to states table
        if (!Schema::hasColumn('states', 'slug')) {
            Schema::table('states', function (Blueprint $table) {
                $table->string('slug')->nullable()->unique()->after('code');
            });
        }

        // 2. Create districts table
        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained('states')->onDelete('cascade');
                $table->string('name');
                $table->string('slug');
                $table->timestamps();

                $table->unique(['state_id', 'slug']);
            });
        }

        // 3. Add district_id column to job_posts table
        if (!Schema::hasColumn('job_posts', 'district_id')) {
            Schema::table('job_posts', function (Blueprint $table) {
                $table->foreignId('district_id')->nullable()->after('state_id')->constrained('districts')->onDelete('set null');
            });
        }

        // 4. Seed slugs for existing states
        $states = DB::table('states')->get();
        foreach ($states as $state) {
            $slug = str()->slug($state->name);
            DB::table('states')->where('id', $state->id)->update(['slug' => $slug]);
        }

        // 5. Seed districts for states
        $this->seedDistricts();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('job_posts', 'district_id')) {
            Schema::table('job_posts', function (Blueprint $table) {
                $table->dropForeign(['district_id']);
                $table->dropColumn('district_id');
            });
        }

        Schema::dropIfExists('districts');

        if (Schema::hasColumn('states', 'slug')) {
            Schema::table('states', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }

    /**
     * Helper to seed districts and link existing jobs.
     */
    protected function seedDistricts(): void
    {
        $stateMappings = [
            'UP' => ['Lucknow', 'Noida', 'Kanpur', 'Prayagraj'],
            'MH' => ['Mumbai', 'Pune', 'Nagpur', 'Thane'],
            'DL' => ['New Delhi', 'North Delhi', 'South Delhi'],
            'KA' => ['Bengaluru', 'Mysore', 'Hubli', 'Mangaluru'],
        ];

        foreach ($stateMappings as $code => $districtsList) {
            $state = DB::table('states')->where('code', $code)->first();
            if (!$state) {
                continue;
            }

            foreach ($districtsList as $districtName) {
                $districtSlug = str()->slug($districtName);
                
                // Insert district if not exists
                $districtId = DB::table('districts')->insertGetId([
                    'state_id' => $state->id,
                    'name' => $districtName,
                    'slug' => $districtSlug,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Map specific seeded jobs to this district to have test data
                if ($code === 'DL' && $districtName === 'New Delhi') {
                    // Map UPSC IAS Civil Services to New Delhi
                    DB::table('job_posts')
                        ->where('slug', 'like', 'upsc-ias-civil-services%')
                        ->update(['district_id' => $districtId]);
                }
                
                if ($code === 'MH' && $districtName === 'Mumbai') {
                    // Map RBI Grade B to Mumbai
                    DB::table('job_posts')
                        ->where('slug', 'like', 'rbi-grade-b%')
                        ->update(['district_id' => $districtId]);
                }

                if ($code === 'KA' && $districtName === 'Bengaluru') {
                    // Map SBI PO to Bengaluru
                    DB::table('job_posts')
                        ->where('slug', 'like', 'sbi-probationary-officer%')
                        ->update(['district_id' => $districtId]);
                }
            }
        }
    }
};
