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
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("game_id");
            $table->unsignedBigInteger("game_match_type_id");
            $table->unsignedBigInteger("tournament_id")->nullable();
            $table->json("individual_match_result_ids")->nullable();
            $table->integer("status");
            // $table->foreign("tournament_id")->references("id")->on("tournaments");
            // $table->foreign("game_match_type_id")->references("id")->on("game_match_types");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_matches');
    }
};
