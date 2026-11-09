<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pivot table: many-to-many between roles and permissions.
        // A role can have multiple permissions; a permission can belong to multiple roles.
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')
                  ->constrained('permissions')
                  ->cascadeOnDelete();
            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
