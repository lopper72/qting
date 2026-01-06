<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 50)->default('')->unique();
            $table->string('phone', 50)->default('')->unique();
            $table->string('email', 100)->default('')->unique();
            $table->string('password', 100)->default('');
            $table->string('nickname', 100)->default('');
            $table->string('avatar', 255)->default('');
            $table->string('refcode', 50)->default('');
            $table->string('truename', 100)->default('');
            $table->string('qq', 100)->default('');
            $table->tinyInteger('sex')->unsigned()->default(0);
            $table->string('birthday', 100)->default('');
            $table->decimal('amount', 10, 2)->unsigned()->default(0.00);
            $table->integer('integral')->unsigned()->default(0);
            $table->integer('gold')->unsigned()->default(0);
            $table->integer('heat')->unsigned()->default(0);
            $table->tinyInteger('grade')->unsigned()->default(0);
            $table->string('alipay_account_name', 255)->default('');
            $table->string('alipay_account', 255)->default('');
            $table->integer('vip_end_time')->unsigned()->default(0);
            $table->string('position', 255)->default('');
            $table->string('desc', 1000)->default('');
            $table->integer('pid')->unsigned()->default(0);
            $table->string('level', 500)->default('');
            $table->string('is_auth', 20)->default('NO');
            $table->tinyInteger('is_audio')->default(1);
            $table->string('api_token', 500)->default('');
            $table->string('wx_openid', 255)->default('');
            $table->string('qq_openid', 255)->default('');
            $table->string('wb_openid', 255)->default('');
            $table->string('device_id', 255)->default('');
            $table->tinyInteger('is_admin')->unsigned()->default(0);
            $table->tinyInteger('is_super')->unsigned()->default(0);
            $table->timestamp('last_login_time')->nullable();
            $table->string('tags', 255)->default('');
            $table->tinyInteger('can_live')->default(0);
            $table->tinyInteger('status')->unsigned()->default(1);
            $table->integer('updated_at')->unsigned()->default(0);
            $table->integer('created_at')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
