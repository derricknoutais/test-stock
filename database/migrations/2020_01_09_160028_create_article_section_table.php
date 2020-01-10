<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleSectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sectionnable', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('section_id');
            $table->string('sectionnable_id');
            $table->string('sectionnable_type');

            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade')->onUpdate('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_section');
    }
}
