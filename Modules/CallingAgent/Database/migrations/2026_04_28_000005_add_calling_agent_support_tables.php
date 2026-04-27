<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // calling_agent_caller_profiles is already created in migration 000004.
        // This migration adds only the tables not present in earlier migrations.

        Schema::create('calling_agent_recordings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calling_agent_call_id')->nullable()->index();
            $table->string('call_sid')->nullable()->index();
            $table->string('recording_sid')->nullable()->unique();
            $table->string('recording_url')->nullable();
            $table->integer('duration')->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->string('transcription_status')->nullable();
            $table->longText('transcription_text')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // calling_agent_builder_presets is already created in migration 000004.

        Schema::create('calling_agent_webhook_idempotency', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('source')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('payload_hash')->nullable();
            $table->timestamps();
        });

        Schema::create('calling_agent_transfer_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calling_agent_call_id')->nullable()->index();
            $table->string('call_sid')->nullable()->index();
            $table->string('target_number')->nullable();
            $table->string('target_department')->nullable();
            $table->string('status')->default('initiated');
            $table->string('reason')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('calling_agent_missed_call_recovery_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calling_agent_call_id')->nullable()->index();
            $table->string('call_sid')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('channel')->default('sms');
            $table->string('status')->default('pending');
            $table->integer('attempts')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calling_agent_missed_call_recovery_tasks');
        Schema::dropIfExists('calling_agent_transfer_attempts');
        Schema::dropIfExists('calling_agent_webhook_idempotency');
        Schema::dropIfExists('calling_agent_recordings');
    }
};
