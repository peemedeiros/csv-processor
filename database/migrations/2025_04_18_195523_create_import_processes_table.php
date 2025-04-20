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
        Schema::create('import_processes', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->smallInteger('status')->default('1'); // 1-pending, 2-processing, 3-done, 4-failed
            $table->integer('processed_rows')->default(0);
            $table->integer('users_created')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_processes');
    }
};
