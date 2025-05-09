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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('folder_name');
            $table->string('local_path')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('cascade');

            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
