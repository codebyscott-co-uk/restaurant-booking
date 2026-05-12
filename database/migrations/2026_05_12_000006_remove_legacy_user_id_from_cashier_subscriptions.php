<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscriptions') || ! Schema::hasColumn('subscriptions', 'user_id')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });

            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::create('subscriptions_resora_tmp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['venue_id', 'stripe_status']);
        });

        DB::statement(<<<'SQL'
            INSERT INTO subscriptions_resora_tmp (
                id,
                venue_id,
                type,
                stripe_id,
                stripe_status,
                stripe_price,
                quantity,
                trial_ends_at,
                ends_at,
                created_at,
                updated_at
            )
            SELECT
                subscriptions.id,
                COALESCE(subscriptions.venue_id, users.venue_id),
                subscriptions.type,
                subscriptions.stripe_id,
                subscriptions.stripe_status,
                subscriptions.stripe_price,
                subscriptions.quantity,
                subscriptions.trial_ends_at,
                subscriptions.ends_at,
                subscriptions.created_at,
                subscriptions.updated_at
            FROM subscriptions
            LEFT JOIN users ON users.id = subscriptions.user_id
        SQL);

        Schema::drop('subscriptions');
        Schema::rename('subscriptions_resora_tmp', 'subscriptions');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        if (! Schema::hasTable('subscriptions') || Schema::hasColumn('subscriptions', 'user_id')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }
};
