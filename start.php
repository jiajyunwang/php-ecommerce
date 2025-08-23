<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;
use PHPSocketIO\SocketIO;

$io = new SocketIO(3120);
$io->on('connection', function($socket)use($io){
    $userId = $socket->handshake['query']['session_id'] ?? null;
    echo "new connection coming\n";
    $socket->on($userId, function($msg)use($io, $userId) {
        $io->emit($userId, [
            'message'=>$msg['message'], 
            'userId'=>$msg['userId'], 
            'messageId'=>$msg['messageId'], 
            'role'=>$msg['role']
        ]);
    });
});

Worker::runAll();