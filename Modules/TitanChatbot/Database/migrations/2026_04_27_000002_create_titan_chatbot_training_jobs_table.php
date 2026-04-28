<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('titan_chatbot_training_jobs')) {
            return;
        }

        Schema::create('titan_chatbot_training_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chatbot_id')->index();
            $table->string('tenant_id', 64)->nullable()->index();
            $table->enum('source_type', ['text', 'qa', 'pdf', 'excel', 'url']);
            $table->string('title')->nullable();
            $table->longText('content');
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending')->index();
            $table->integer('chunks_created')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_chatbot_training_jobs');
    }
};
