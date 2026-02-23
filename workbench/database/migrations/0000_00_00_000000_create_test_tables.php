<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('some_models', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
        Schema::create('some_other_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('some_model_id')->constrained();
            $table->timestamps();
        });

    }
};
