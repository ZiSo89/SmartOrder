<?php
/* Version 2.0 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Table: qr_restaurant_tables
Schema::create('restaurant_tables', function (Blueprint $table) {
    $table->integer('id', true);
    $table->integer('restaurant_id')->nullable()->index('restaurant_id');
    $table->integer('table_no')->nullable();
    $table->string('key')->nullable()->unique('key');

    $table->foreign(['restaurant_id'])->references(['id'])->on('posts')->onDelete('CASCADE');
});
