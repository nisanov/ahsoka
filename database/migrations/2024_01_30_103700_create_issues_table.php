<?php

declare(strict_types=1);

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
        Schema::create('issues', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('developer_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('month');
            $table->string('ticket', 100);
            $table->string('summary');
            $table->integer('points')->default(0);
            $table->integer('coverage')->nullable();
            $table->index(['ticket', 'month']);
            $table->timestamps();
            $table->unique(['server_id', 'developer_id', 'ticket']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
