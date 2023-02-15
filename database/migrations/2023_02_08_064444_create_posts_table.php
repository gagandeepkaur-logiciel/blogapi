<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userid')->constrained('users');
            $table->foreignId('categoryid')->constrained('categories');
            $table->string('title');
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('facebook_post_id')->nullable();
            $table->string('facebook_msg_id')->nullable();
            $table->string('pageid')->nullable();
            $table->string('created_by');
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
        Schema::dropIfExists('posts');
    }
};