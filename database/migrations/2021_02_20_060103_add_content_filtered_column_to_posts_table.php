<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentFilteredColumnToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'posts',
            function (Blueprint $table) {
                $table->longText('content_filtered')->after('content');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'posts',
            function (Blueprint $table) {
                $table->dropColumn('content_filtered');
            }
        );
    }
}
