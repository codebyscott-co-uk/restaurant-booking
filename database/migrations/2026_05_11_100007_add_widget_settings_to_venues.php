<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->boolean('widget_enabled')->default(true)->after('email_footer_content');
            $table->string('widget_title')->nullable()->after('widget_enabled');
            $table->text('widget_intro')->nullable()->after('widget_title');
            $table->string('widget_button_text')->default('Book a table')->after('widget_intro');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn([
                'widget_enabled',
                'widget_title',
                'widget_intro',
                'widget_button_text',
            ]);
        });
    }
};
