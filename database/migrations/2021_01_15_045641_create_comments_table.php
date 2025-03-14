<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\GuidHelper;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->longText('comment');
            $table->integer('likes')->nullable()->comment('adding the count in observer');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('recursive id ');
            $table->uuid('guid')->unique()->default(\App\Helpers\GuidHelper::getGuid());
            $table->string('created_by', 100);
            $table->string('updated_by', 100);
            $table->timestamps();
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments_');
    }
}
