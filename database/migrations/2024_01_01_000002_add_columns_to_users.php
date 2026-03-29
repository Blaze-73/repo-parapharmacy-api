<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add if they don't exist
            if (!Schema::hasColumn('users', 'nom')) {
                $table->string('nom')->after('id');
            }
            if (!Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->nullable()->after('nom');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('client')->after('email');
            }
            if (!Schema::hasColumn('users', 'actif')) {
                $table->boolean('actif')->default(true)->after('role');
            }
        });

        // Remove the default 'name' column Laravel adds if it exists
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nom', 'telephone', 'role', 'actif']);
        });
    }
};
