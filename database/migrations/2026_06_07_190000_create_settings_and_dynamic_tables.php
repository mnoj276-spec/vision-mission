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
        // 1. Setting Groups Table
        Schema::create('setting_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Settings Table (Key-Value)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('setting_groups')->onDelete('set null');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, select, file, boolean, number, json
            $table->text('options')->nullable(); // JSON list for select options
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->boolean('is_secret')->default(false); // encrypt if secret
            $table->timestamps();
        });

        // 3. Menus Table
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->unique(); // e.g. header, footer_col1, footer_col2
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Menu Items Table
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->string('url');
            $table->string('icon')->nullable();
            $table->string('target', 10)->default('_self'); // _self, _blank
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
        });

        // 5. CMS Pages Table
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Social Links Table
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique(); // facebook, instagram, twitter, etc.
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        // 7. Theme Settings Table
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

        // 8. SEO Settings Table
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 9. Email Settings Table
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 10. API Settings Table
        Schema::create('api_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        // 11. Add Columns to Advertisements Table for image-based/campaign ads
        Schema::table('advertisements', function (Blueprint $table) {
            if (!Schema::hasColumn('advertisements', 'image_path')) {
                $table->string('image_path')->nullable()->after('ad_code');
            }
            if (!Schema::hasColumn('advertisements', 'url')) {
                $table->string('url')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('advertisements', 'start_date')) {
                $table->dateTime('start_date')->nullable()->after('url');
            }
            if (!Schema::hasColumn('advertisements', 'end_date')) {
                $table->dateTime('end_date')->nullable()->after('start_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'url', 'start_date', 'end_date']);
        });

        Schema::dropIfExists('api_settings');
        Schema::dropIfExists('email_settings');
        Schema::dropIfExists('seo_settings');
        Schema::dropIfExists('theme_settings');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('setting_groups');
    }
};
