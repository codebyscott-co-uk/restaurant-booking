<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->text('email_confirmation_content')->nullable()->after('cancellation_policy');
            $table->text('email_modification_content')->nullable()->after('email_confirmation_content');
            $table->text('email_cancellation_content')->nullable()->after('email_modification_content');
            $table->text('email_reminder_content')->nullable()->after('email_cancellation_content');
            $table->text('email_staff_alert_content')->nullable()->after('email_reminder_content');
            $table->text('email_footer_content')->nullable()->after('email_staff_alert_content');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn([
                'email_confirmation_content',
                'email_modification_content',
                'email_cancellation_content',
                'email_reminder_content',
                'email_staff_alert_content',
                'email_footer_content',
            ]);
        });
    }
};
