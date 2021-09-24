<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('member');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('member', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('signin')->nullable();
            $table->timestamp('signout')->nullable();
            $table->text('remark');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }
}
