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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            // $table->
            $table->string("name");
            $table->string("description");
            $table->unsignedBigInteger("community_id")->nullable();
            $table->unsignedBigInteger("school_id")->nullable();
            $table->integer("type");
            $table->string("logo_src")->nullable();
            $table->string("banner_image_src")->nullable();
            $table->unsignedBigInteger("rule_id");
            // $table->foreign("rule_id")->on("global_rules")->references("id")->onDelete("cascade");
            // $table->foreign("community_id")->on("communities")->references("id");
            // $table->foreign("school_id")->on("school")->references("id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
