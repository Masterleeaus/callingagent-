<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('connector_client_pack_templates')) {
            return;
        }

        Schema::create('connector_client_pack_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('name', 120);
            $table->boolean('is_default')->default(false)->index();
            $table->longText('template_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connector_client_pack_templates');
    }
};
