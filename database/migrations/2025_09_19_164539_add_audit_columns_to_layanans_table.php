<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('layanans', function (Blueprint $t) {
            if (!Schema::hasColumn('layanans','created_by')) {
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('is_active');
            }
        });
    }

    public function down(): void {
        Schema::table('layanans', function (Blueprint $t) {
            if (Schema::hasColumn('created_by')) {
                $t->dropConstrainedForeignId('created_by');
            }
        });
    }
};
