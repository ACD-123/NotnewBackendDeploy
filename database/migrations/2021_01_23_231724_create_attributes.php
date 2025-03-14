<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Attribute;
use App\Helpers\GuidHelper;

class CreateAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', Attribute::typeKeys());
            $table->jsonb('options')->nullable()->comment('If attribute type is in (CHECKBOX_GROUP, RADIO_GROUP, SELECT), user must provide a list of options.');
            $table->boolean('active')->default(false);
            $table->uuid('guid')->unique()->default(\App\Helpers\GuidHelper::getGuid());
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
        Schema::dropIfExists('attributes');
    }
}
