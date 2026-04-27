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

        // calling_agent_call_outcomes was first created by migration 000003 with a
        // minimal schema. Here we ALTER it to add the richer intelligence columns.
        // We use hasColumn guards so this is safe on both fresh and existing installs.
        Schema::table('calling_agent_call_outcomes', function (Blueprint $table) {
            if (!Schema::hasColumn('calling_agent_call_outcomes', 'urgency')) {
                $table->string('urgency')->default('normal')->index()->after('intent');
            }
            if (!Schema::hasColumn('calling_agent_call_outcomes', 'lead_quality')) {
                $table->string('lead_quality')->default('unknown')->index()->after('urgency');
            }
            if (!Schema::hasColumn('calling_agent_call_outcomes', 'handoff_required')) {
                $table->boolean('handoff_required')->default(false)->after('lead_quality');
            }
            if (!Schema::hasColumn('calling_agent_call_outcomes', 'booking_requested')) {
                $table->boolean('booking_requested')->default(false)->after('handoff_required');
            }
            if (!Schema::hasColumn('calling_agent_call_outcomes', 'entities')) {
                $table->json('entities')->nullable()->after('sentiment');
            }
            if (!Schema::hasColumn('calling_agent_call_outcomes', 'next_actions')) {
                $table->json('next_actions')->nullable()->after('entities');
            }
            if (!Schema::hasColumn('calling_agent_call_outcomes', 'raw')) {
                $table->json('raw')->nullable()->after('summary');
            }
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
        // Revert the ALTER on calling_agent_call_outcomes
        Schema::table('calling_agent_call_outcomes', function (Blueprint $table) {
            $cols = ['urgency', 'lead_quality', 'handoff_required', 'booking_requested', 'entities', 'next_actions', 'raw'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('calling_agent_call_outcomes', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });

        Schema::dropIfExists('calling_agent_builder_presets');
        Schema::dropIfExists('calling_agent_caller_profiles');
    }
};
