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
        Schema::create('import_process_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_process_id')->constrained()->cascadeOnDelete();
            $table->integer('row_index')->nullable();
            $table->json('row_data');
            $table->text('error_message');
            $table->string('error_type');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_process_errors', function (Blueprint $table) {
            $table->dropForeign(['import_process_id']);
            $table->dropIfExists();
        });
    }
};
