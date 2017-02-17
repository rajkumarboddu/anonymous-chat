<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatController extends Controller implements MessageComponentInterface
{
    protected $clients;
    private $subscriptions;
    private $users;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->subscriptions = [];
        $this->users = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->users[$conn->resourceId] = $conn;
    }

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        $data = json_decode($msg);
        switch ($data->command) {
            case "subscribe":
                $this->subscribe($conn,$data);
                break;
            case "message":
                if (isset($this->subscriptions[$conn->resourceId])) {
                    $data_obj = [
                        'type' => 'message',
                        'message' => '<strong>User '.$conn->resourceId.': </strong>'.$data->message
                    ];
                    $this->sendMessage($conn,$data_obj);
                }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $data_obj = [
            'type' => 'notification',
            'comes_under' => 'error',
            'message' => 'User: '.$conn->resourceId.' disconned!'
        ];
        $this->sendMessage($conn,$data_obj);
        $this->clients->detach($conn);
        unset($this->users[$conn->resourceId]);
        unset($this->subscriptions[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function subscribe($conn,$data){
        $this->subscriptions[$conn->resourceId] = $data->channel;
        $data_obj = [
            'type' => 'subscribed',
            'message' => $conn->resourceId
        ];
        $this->users[$conn->resourceId]->send(json_encode($data_obj));
        $data_obj = [
            'type' => 'notification',
            'comes_under' => 'success',
            'message' => 'New user: '.$conn->resourceId.' joined!'
        ];
        $this->sendMessage($conn,$data_obj);
    }

    public function sendMessage($conn,$data_obj){
        $data_obj = json_encode($data_obj);
        $target = $this->subscriptions[$conn->resourceId];
        foreach ($this->subscriptions as $id=>$channel) {
            if ($channel == $target && $id != $conn->resourceId) {
                $this->users[$id]->send($data_obj);
            }
        }
    }
}
