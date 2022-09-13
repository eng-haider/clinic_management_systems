<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCasesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $table = 'cases';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('patients_id'); 
            $table->foreign('patients_id')->references('id')->on('patients')->onDelete('cascade');

          
            $table->unsignedBigInteger('case_categores_id');
            $table->foreign('case_categores_id')->references('id')->on('case_categories')->onDelete('cascade');



            $table->mediumText('notes')->nullable();

            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');



            $table->bigInteger('price')->nullable();
            $table->integer('upper_right')->nullable();
            $table->integer('upper_left')->nullable();
            $table->integer('lower_right')->nullable();
            $table->integer('lower_left')->nullable();
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
        Schema::dropIfExists($this->table);
    }
}
