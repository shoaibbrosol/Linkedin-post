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
        Schema::create('linkedin_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('linkedin_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('media_path')->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('posted_at')->nullable();
            $table->string('status')->default('draft')->index();
            $table->string('linkedin_post_id')->nullable();
            $table->json('api_response')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linkedin_posts');
    }
};
