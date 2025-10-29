<?php

use App\Models\BookType;
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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BookType::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('author')->nullable();
            $table->string('amazon_link')->nullable();
            $table->string('publication_year')->nullable();
            $table->text('description')->nullable();
            $table->string('isbn')->nullable();
            $table->string('image')->nullable();
            $table->string('price');
            $table->string('free_laws')->default(0);
            $table->string('pdf_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
