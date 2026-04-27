<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ext_chatbot_workflow_runs')) {
            return;
        }

        Schema::create('ext_chatbot_workflow_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chatbot_id');
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->string('workflow_key', 100);
            $table->string('status', 32)->default('proposed'); // proposed|confirmed|executing|completed|failed|canceled
            $table->json('input')->nullable();
            $table->json('result')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['chatbot_id', 'workflow_key'], 'ext_chatbot_workflow_runs_chatbot_workflow');
            $table->index(['conversation_id'], 'ext_chatbot_workflow_runs_conversation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_workflow_runs');
    }
};
