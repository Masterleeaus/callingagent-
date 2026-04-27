<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ext_chatbot_workflow_settings')) {
            return;
        }

        Schema::create('ext_chatbot_workflow_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chatbot_id');
            $table->string('workflow_key', 100);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['chatbot_id', 'workflow_key'], 'ext_chatbot_workflow_settings_unique');
            $table->index(['chatbot_id'], 'ext_chatbot_workflow_settings_chatbot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_workflow_settings');
    }
};
