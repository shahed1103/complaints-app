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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('complaint_type_id')->constrained('complaint_types')->onDelete('cascade');
            $table->foreignId('complaint_department_id')->constrained('complaint_departments')->onDelete('cascade')->nullable();
            $table->foreignId('complaint_status_id')->constrained('complaint_statuses')->onDelete('cascade');
            $table->string('problem_description');
            $table->string('location');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
