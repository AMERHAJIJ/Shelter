<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gathering_points', function (Blueprint $table) {
            $table->id();

            // اسم نقطة التجمع (مثلاً: Kanarya Sahil Alanı)
            $table->string('name');

            // المنطقة الإدارية
            $table->string('district');      // Küçükçekmece
            $table->string('neighborhood');  // Kanarya

            // الموقع الجغرافي
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // سعة تقديرية (تجميع مؤقت)
            $table->integer('capacity_estimated')->nullable();

            // الحالة
            $table->enum('status', ['active', 'full', 'closed'])
                  ->default('active');

            // أي ملجأ يخدم هذه النقطة
            $table->foreignId('shelter_id')
                  ->constrained('shelters')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gathering_points');
    }
};
