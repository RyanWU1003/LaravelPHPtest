<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePunchRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('punch_records', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreign('user_id')->references('user_id')->on('members');
            $table->date('record_date');
            $table->timestamp('punch_in_time')->nullable();
            $table->timestamp('punch_out_time')->nullable();
            $table->string('remarks',50);
            $table->timestamps();
        });
        // Schema::dropIfExists('punch_records');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('punch_records');
    }
}
