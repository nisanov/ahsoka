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
        Schema::create('developer_server', static function (Blueprint $table) {
            $table->foreignId('developer_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('username');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developer_server');
    }
};
