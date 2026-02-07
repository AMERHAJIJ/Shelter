<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('card_id')
                  ->constrained('cards')
                  ->cascadeOnDelete();

            $table->string('name');

            $table->enum('status', ['inside', 'outside'])
                  ->default('inside');

            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('health_status')->nullable();
            $table->string('last_location')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_members');
    }
};
