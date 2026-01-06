<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetPass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resetPass {username?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置密码';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $username = $this->argument('username') ?: 'admin';
        $password = $this->argument('password') ?: '123456';

        if (!User::resetPass($username, $password)) {
            $this->error("重置密码失败");
        }

        $this->info("重置成功 账户：{$username}，密码：{$password}");
    }
}
