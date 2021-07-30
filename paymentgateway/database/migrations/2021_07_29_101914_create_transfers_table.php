<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(! Schema::hasTable('transfers')){

            Schema::create('transfers', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->string('transfer_id');
                $table->string('transfer_code');
                $table->string('amount');
                $table->text('reason');
                $table->string('currency');
                $table->string('reference');
                $table->string('status');
                $table->string('transfer_created_at');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfers');
    }
}
