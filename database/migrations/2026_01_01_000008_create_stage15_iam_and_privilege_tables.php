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
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique(); // superadmin, rector, dean, kaprodi, baak, bauk, lecturer, student, applicant
            $table->string('display_name');
            $table->integer('level')->default(10); // 100 for superadmin, 90 for rector, ..., 10 for applicant
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique(); // e.g. krs.submit, grades.lock
            $table->string('name');
            $table->string('module'); // KRS, Attendance, Grading, Finance, Curriculum, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Role-Permission pivot table
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->unique(['role_id', 'permission_id']);
        });

        // 4. User Custom Permission Overrides (Grant or Revoke)
        Schema::create('user_custom_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('permission_id');
            $table->boolean('is_granted')->default(true); // true = explicit grant, false = explicit revoke
            $table->uuid('granted_by')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['user_id', 'permission_id']);
        });

        // 5. Add role_id to users if not present
        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('role_id')->nullable()->after('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        }
        Schema::dropIfExists('user_custom_permissions');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
