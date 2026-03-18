<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nom')->after('id');
            $table->enum('role', ['admin', 'client'])->default('client')->after('email');
            $table->boolean('actif')->default(true)->after('role');
            $table->string('telephone')->nullable()->after('nom');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nom', 'role', 'actif', 'telephone']);
        });
    }
};