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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("description");
            $table->integer("type");
            $table->string("logo_src")->nullable();
            $table->string("banner_image_src")->nullable();
            $table->unsignedBigInteger("rule_id");
            $table->integer("linked_school_id")->nullable();
            // $table->foreign("rule_id")->on("global_rules")->references("id")->onDelete("cascade");
            // $table->foreign("linked_school_id")->on("schools")->references("id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
