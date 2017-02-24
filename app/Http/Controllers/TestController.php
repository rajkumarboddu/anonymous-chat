<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZMQContext;
use ZMQ;

class TestController extends Controller
{
    public function testPush(){
        try{
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $entryData = array(
                'category' => 'kittensCategory'
            , 'title'    => 'Test Title'
            , 'article'  => 'Test Article'
            , 'when'     => time()
            );
            // This is our new stuff
            $context = new ZMQContext();
            $socket = $context->getSocket(ZMQ::SOCKET_PUSH);
            $socket->connect("tcp://127.0.0.1:5556");
            $socket->send(json_encode($entryData));
        } catch(\Exception $e){
            dd($e->getMessage());
        }
    }
}
