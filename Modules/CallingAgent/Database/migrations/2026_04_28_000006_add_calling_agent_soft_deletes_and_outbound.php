<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CallingAgentCallerProfile uses SoftDeletes but the table had no deleted_at.
        if (Schema::hasTable('calling_agent_caller_profiles')
            && !Schema::hasColumn('calling_agent_caller_profiles', 'deleted_at')) {
            Schema::table('calling_agent_caller_profiles', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Additional outbound call tracking columns for calling_agent_calls.
        if (Schema::hasTable('calling_agent_calls')) {
            Schema::table('calling_agent_calls', function (Blueprint $table) {
                if (!Schema::hasColumn('calling_agent_calls', 'outbound_call_sid')) {
                    $table->string('outbound_call_sid')->nullable()->index()->after('call_sid');
                }
                if (!Schema::hasColumn('calling_agent_calls', 'transfer_target')) {
                    $table->string('transfer_target')->nullable()->after('recording_url');
                }
                if (!Schema::hasColumn('calling_agent_calls', 'transfer_status')) {
                    $table->string('transfer_status')->nullable()->after('transfer_target');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('calling_agent_caller_profiles')
            && Schema::hasColumn('calling_agent_caller_profiles', 'deleted_at')) {
            Schema::table('calling_agent_caller_profiles', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('calling_agent_calls')) {
            Schema::table('calling_agent_calls', function (Blueprint $table) {
                $cols = ['outbound_call_sid', 'transfer_target', 'transfer_status'];
                $existing = array_filter($cols, fn ($c) => Schema::hasColumn('calling_agent_calls', $c));
                if ($existing) {
                    $table->dropColumn(array_values($existing));
                }
            });
        }
    }
};
