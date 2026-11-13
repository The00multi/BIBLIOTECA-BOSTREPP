<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->string('isbn', 20)->nullable()->unique();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->timestamps();
            $table->index(['title', 'author']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('book_category', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['book_id', 'category_id']);
        });

        Schema::create('readers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('card_number')->unique();
            $table->string('document_number')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('location')->nullable();
            $table->string('status', 20)->default('available');
            $table->timestamps();
            $table->index(['book_id', 'status']);
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reader_id')->constrained()->restrictOnDelete();
            $table->foreignId('copy_id')->constrained()->restrictOnDelete();
            $table->date('borrowed_at');
            $table->date('due_at');
            $table->date('returned_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['status', 'due_at']);
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reader_id')->constrained()->restrictOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->date('reserved_at');
            $table->date('expires_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->index(['book_id', 'status']);
        });

        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->restrictOnDelete();
            $table->string('reason');
            $table->decimal('amount', 8, 2);
            $table->string('status', 20)->default('pending');
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('copies');
        Schema::dropIfExists('readers');
        Schema::dropIfExists('book_category');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('books');
    }
};
