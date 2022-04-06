<?php
return [
    'queue' => [
        'connector'  => 'Redis',
        'expire'     => 60,// 任务的过期时间，默认为60秒; 若要禁用，则设置为 null
        'default'    => 'sync_queue',// 默认的队列名称
        'host'       => '175.178.35.151',// redis服务器地址
        'port'       => '6379',// redis端口
        'password'   => '123456',// redis密码
        'select'     => 1,// 使用哪一个 db，默认为 db0
        'timeout'    => 0,// redis连接的超时时间
        'persistent' => false,// 是否是长连接
    ],
];