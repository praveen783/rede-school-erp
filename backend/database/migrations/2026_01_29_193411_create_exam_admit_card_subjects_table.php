<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_admit_card_subjects', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('exam_admit_card_id');
            $table->unsignedBigInteger('subject_id');

            $table->date('exam_date');

            $table->timestamps();

            // Prevent duplicate subjects per admit card
            $table->unique(
                ['exam_admit_card_id', 'subject_id'],
                'unique_subject_per_admit_card'
            );

            // Indexes
            $table->index('exam_admit_card_id');
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_admit_card_subjects');
    }
};
