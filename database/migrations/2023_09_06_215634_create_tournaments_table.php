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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("excerpt");
            $table->text("full_description");
            $table->unsignedBigInteger("global_rule_id");
            $table->integer("organizer_id");
            $table->string("organizer_type");
            $table->unsignedBigInteger("tournament_type_id");
            // $table->foreign("global_rule_id")->references("id")->on("global_rules");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
