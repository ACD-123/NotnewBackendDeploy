<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\GuidHelper;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('category')->index();
            $table->string('name')->unique();
            $table->text('value')->comment('Serialized value of the setting');
            $table->boolean('user_editable')->index()->default(true)->comment('Whether this setting should be visible to the UI');
            $table->uuid('guid')->unique()->default(\App\Helpers\GuidHelper::getGuid());
            $table->boolean('system')->default(false);
            $table->boolean('active')->default(true);
            $table->string('created_by', 100);
            $table->string('updated_by', 100);
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
        Schema::dropIfExists('settings');
    }
}
