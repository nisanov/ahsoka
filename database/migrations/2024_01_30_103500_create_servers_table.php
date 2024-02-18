<?php

declare(strict_types=1);

use App\Enums\Models\Server\Type;
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
        Schema::create('servers', static function (Blueprint $table) {
            $table->id();
            $table->enum('type', Type::values());
            $table->string('name');
            $table->string('api');
            $table->text('token')->nullable();
            $table->string('processor')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
