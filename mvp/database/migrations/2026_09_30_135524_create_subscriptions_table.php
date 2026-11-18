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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('status', 30)->default('trialing');
            $table->timestampTz('started_at');
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('next_renewal_at')->nullable();
            $table->timestampTz('grace_period_started_at')->nullable();
            $table->timestampTz('grace_period_ends_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->text('status_reason')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
            $table->index('next_renewal_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
