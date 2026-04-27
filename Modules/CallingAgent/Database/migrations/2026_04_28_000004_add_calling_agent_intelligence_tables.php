<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calling_agent_caller_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->json('tags')->nullable();
            $table->json('preferences')->nullable();
            $table->json('last_outcome')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('calling_agent_call_outcomes', function (Blueprint $table) {
            $table->id();
            $table->string('call_sid')->nullable()->index();
            $table->string('intent')->default('unknown')->index();
            $table->string('urgency')->default('normal')->index();
            $table->string('lead_quality')->default('unknown')->index();
            $table->boolean('handoff_required')->default(false);
            $table->boolean('booking_requested')->default(false);
            $table->string('sentiment')->nullable();
            $table->json('entities')->nullable();
            $table->json('next_actions')->nullable();
            $table->text('summary')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });

        Schema::create('calling_agent_builder_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('industry')->default('front_desk');
            $table->json('schema')->nullable();
            $table->json('enabled_channels')->nullable();
            $table->json('routing_tree')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calling_agent_builder_presets');
        Schema::dropIfExists('calling_agent_call_outcomes');
        Schema::dropIfExists('calling_agent_caller_profiles');
    }
};
