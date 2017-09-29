<?php

namespace App\Http\Controllers;

use App\Jobs\MakeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use stdClass;

class TestController extends Controller
{
    //
    public function index()
    {
        for($i=0;$i<10;$i++){
            $num[] = $i;
        }
        Redis::lpush('num',$num);
        $mykey = Redis::lrange('num',2,3);
        dd($mykey);
        echo "ssadfsd";
    }

    public function testQueue(){
        Log::info('调用时间：'.date('Y-m-d H:i:s'));
        $this->dispatch((new MakeLog())->delay(60));
    }
}
