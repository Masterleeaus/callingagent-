<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ext_chatbot_tool_settings')) {
            return;
        }

        Schema::create('ext_chatbot_tool_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chatbot_id');
            $table->string('tool_key', 100);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index(['chatbot_id', 'tool_key'], 'ext_chatbot_tool_settings_chatbot_tool_idx');
            $table->unique(['chatbot_id', 'tool_key'], 'ext_chatbot_tool_settings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_tool_settings');
    }
};
