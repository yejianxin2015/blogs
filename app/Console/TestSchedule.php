<?php

namespace App\Console;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Log;

class TestSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test_schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时关闭已经过期的轮播图。';

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
       Log::info('执行成功时间：'.date('Y-m-d H:i:s'));
    }
}
