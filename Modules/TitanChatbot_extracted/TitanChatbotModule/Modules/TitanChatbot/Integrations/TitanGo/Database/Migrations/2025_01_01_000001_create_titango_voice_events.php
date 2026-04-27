<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titango_voice_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('event_type', 50); // preview, confirm, denied, error
            $table->string('action', 100)->nullable();
            $table->string('record_type', 50)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('transcript')->nullable();
            $table->string('transcript_hash', 64)->nullable();
            $table->json('payload')->nullable();
            $table->string('status', 30)->default('ok');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id']);
            $table->index(['event_type']);
            $table->index(['action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titango_voice_events');
    }
};
