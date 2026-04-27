<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calling_agent_realtime_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('provider')->nullable();
            $table->string('call_sid')->nullable()->index();
            $table->string('state')->default('listening');
            $table->json('context')->nullable();
            $table->timestamps();
        });
        Schema::create('calling_agent_call_outcomes', function (Blueprint $table) {
            $table->id();
            $table->string('call_sid')->nullable()->index();
            $table->string('intent')->nullable();
            $table->unsignedTinyInteger('lead_score')->nullable();
            $table->string('sentiment')->nullable();
            $table->text('summary')->nullable();
            $table->json('actions')->nullable();
            $table->timestamps();
        });
        Schema::create('calling_agent_booking_requests', function (Blueprint $table) {
            $table->id();
            $table->string('call_sid')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('service')->nullable();
            $table->dateTime('preferred_at')->nullable();
            $table->string('timezone')->nullable();
            $table->string('status')->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('calling_agent_booking_requests');
        Schema::dropIfExists('calling_agent_call_outcomes');
        Schema::dropIfExists('calling_agent_realtime_sessions');
    }
};
