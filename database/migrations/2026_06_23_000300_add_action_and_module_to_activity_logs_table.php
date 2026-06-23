<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('activity_logs', 'action')) {
                $table->string('action', 40)->default('unknown')->index()->after('event');
            }

            if (! Schema::hasColumn('activity_logs', 'module')) {
                $table->string('module', 80)->default('Unknown')->index()->after('action');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('activity_logs', 'module')) {
                $table->dropColumn('module');
            }

            if (Schema::hasColumn('activity_logs', 'action')) {
                $table->dropColumn('action');
            }
        });
    }
};
