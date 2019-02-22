<?php
/**
 * 收到返回值
 * @param swoole_client $client
 * @param $data
 */
function receive(swoole_client $client, $string)
{
    var_dump(get_class($client));
    # 收到返回

    $data2 = decode($string);
    receive2($client, $data2);
}

/**
 * 返回信息
 * @param swoole_client $client
 * @param $value
 */
function receive2(swoole_client $client, $value)
{
    echo '代理器延迟: ' . (microtime(true) - $value['p'][2]) . " \n";
    $fd = $value['p'][1];
    $value['p'] = $value['p'][0];

    if ($client->swoole_server->exist($fd)) {
        $re = $client->swoole_server->push($fd, json_encode($value));
        echo "代理返回结果!:" . $re;
    } else {

    }
}


# 服务器
$server = new swoole_websocket_server("0.0.0.0", 9502);


$server->on('open', function (swoole_websocket_server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});

$server->on('message', function (swoole_websocket_server $server, $frame) {

    if ($frame->opcode == WEBSOCKET_OPCODE_TEXT) {
        $fd = $frame->fd;
        # 文字类型 JSON格式
        try {
            $data = json_decode($frame->data, true);
        } catch (\Error $e) {
            # 格式 不符合 规范
            $server->push($fd, json_encode([
                'e' => 400,
                'm' => '格式不符合规范'
            ]));
            return;
        }
        if (empty($data)) {
            # 不符合 规范
            $server->push($fd, json_encode([
                'e' => 400,
                'm' => '格式不符合规范'
            ]));
            return;
        }
        proxy_send($server, $data, $fd);

    } else {

    }
});
/**
 * 代理发送
 * @param swoole_websocket_server $server
 * @param $data
 * @param $fd
 */
function proxy_send(swoole_websocket_server $server, $data, $fd)
{
    if (isset($data['p'])) {
        $p = [
            $data['p'],
            $fd,
            microtime(true)
        ];
    } else {
        $p = [
            '',
            $fd,
            microtime(true)
        ];
    }
    $data['p'] = $p;
    echo "已转发 \n";
    $server->proxy_client->send(encode($data));
}

/**
 * 编码
 * @param array $data
 * @return string
 */
function encode(array $data)
{
    $msg_normal = \pms\Serialize::pack($data);
    $msg_length = pack("N", strlen($msg_normal)) . $msg_normal;
    return $msg_length;
}

/**
 * 解码
 * @param $string
 */
function decode($data)
{
    $length = unpack("N", $data)[1];
    $msg = substr($data, -$length);
    return \pms\Serialize::unpack($msg);
}

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});
/**
 * 服务器开启
 * @param swoole_server $server
 */
function WorkerStart(swoole_server $server)
{


# 客户端
    $client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

    $option = ['open_length_check' => true,
        'package_max_length' => 83886080,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,];
    $client->set($option);
    $client->on("connect", function (swoole_client $cli) {
        echo "代理器链接成功! \n";
    });
    $client->on("receive", 'receive');
    $client->swoole_server = $server;
    $client->on("error", function (swoole_client $client) {
        echo "代理器 error\n";
        echo $client->errCode;
        swoole_timer_after(1000, function ($client) {
            $client->connect(TCP_SERVER_HOST, TCP_SERVER_PORT);
        }, $client);
    });
    $client->on("close", function (swoole_client $client) {
        echo "代理器 close \n";
        swoole_timer_after(1000, function ($client) {
            $client->connect(TCP_SERVER_HOST, TCP_SERVER_PORT);
        }, $client);
    });
    $server->proxy_client = $client;
    $client->connect(TCP_SERVER_HOST, TCP_SERVER_PORT);
}

$server->on('WorkerStart', 'WorkerStart');

$server->start();
