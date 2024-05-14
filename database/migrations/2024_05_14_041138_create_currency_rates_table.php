<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('num_code')->unique();
            $table->string('currency_code', 10);
            $table->string('name', 120);
            $table->tinyInteger('nominal');
            $table->decimal('value', 10, 4);
            $table->decimal('v_unit_rate', 10, 4);
            $table->date('on_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('currency_rates');
    }
};
