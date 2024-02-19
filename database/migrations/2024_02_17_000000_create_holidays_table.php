<?php

declare(strict_types=1);

use App\Enums\Models\Holiday\State;
use App\Enums\Models\Holiday\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('token');
            $table->enum('type', Type::values());
            $table->enum('state', State::values())->nullable();
            $table->date('date');
            $table->timestamps();
            $table->unique(['token', 'state', 'date']);
            $table->index(['type', 'state', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
