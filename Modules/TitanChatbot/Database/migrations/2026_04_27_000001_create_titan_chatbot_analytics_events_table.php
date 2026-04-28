<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('titan_chatbot_analytics_events')) {
            return;
        }

        Schema::create('titan_chatbot_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chatbot_id')->index();
            $table->string('tenant_id', 64)->nullable()->index();
            $table->string('session_id', 128)->nullable()->index();
            $table->string('event_type', 64)->index();
            $table->string('channel', 32)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_chatbot_analytics_events');
    }
};
