<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id');
            $table->string('ip_address', 45);
            $table->string('city')->default('Unknown');
            $table->string('country')->nullable();
            $table->string('device_type', 20);
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->text('page_url');
            $table->text('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index('visited_at');
            $table->index('city');
            $table->index(['visitor_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
