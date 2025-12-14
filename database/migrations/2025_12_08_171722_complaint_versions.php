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
                Schema::create('complaint_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('editor_id')->constrained('users')->onDelete('cascade');

            $table->foreignId('complaint_type_id')->constrained('complaint_types')->onDelete('cascade');
            $table->foreignId('complaint_department_id')->constrained('complaint_departments')->onDelete('cascade');
            $table->foreignId('complaint_status_id')->constrained('complaint_statuses')->onDelete('cascade');
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('problem_description');
            $table->string('location');
            $table->string('editor_name');
            $table->string('editor_role');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
