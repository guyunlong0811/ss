<?php
return array(

    'PROTOCOL' => array(

        'Exec' => array(
            'index' => array(
                'verify' => false,
                'log_path' => 'server/repeat/',
                'type' => 1,//文件名类型
                'respond' => false,
            ),
        ),

        'ExecOpen' => array(
            'index' => array(
                'verify' => false,
                'log_path' => 'server/unique/',
                'type' => 2,//文件名类型
            ),
        ),

        'ExecDaily' => array(
            //负责每天固定时间更新游戏内容
            'index' => array(
                'verify' => false,
                'log_path' => 'server/unique/',
                'type' => 2,//文件名类型
            ),
        ),

        'ExecDaily21' => array(
            //负责每天固定时间更新游戏内容
            'index' => array(
                'verify' => false,
                'log_path' => 'server/unique/',
                'type' => 2,//文件名类型
            ),
        ),

        'ExecMin10' => array(
            //负责每10分钟执行一次
            'index' => array(
                'verify' => false,
                'log_path' => 'server/repeat/',
                'type' => 1,//文件名类型
            ),

        ),

        'ExecMin1' => array(
            //负责每1分钟执行一次
            'index' => array(
                'verify' => false,
                'log_path' => 'server/repeat/',
                'type' => 1,//文件名类型
            ),

        ),

        'ExecHeart' => array(
            //负责心跳执行
            'index' => array(
                'verify' => false,
                'log_path' => 'server/repeat/',
                'type' => 1,//文件名类型
            ),

        ),

        'ExecHourly' => array(
            //负责每小时执行一次
            'index' => array(
                'verify' => false,
                'log_path' => 'server/repeat/',
                'type' => 1,//文件名类型
            ),

        ),

        'Notice' => array(

            'send' => array(
                'verify' => true,
                'log_path' => 'server/api/',
                'type' => 1,//文件名类型
                'params' => array(
                    'gid' => array('type' => 'number',),
                    'sid' => array('type' => 'number',),
                    'send_tid' => array('type' => 'number',),
                    'content' => array('type' => 'string',),
                    'level' => array('type' => 'number',),
                    'endtime' => array('type' => 'number',),
                    'interval' => array('type' => 'number',),
                ),
            ),

            'cancel' => array(
                'verify' => true,
                'log_path' => 'server/api/',
                'type' => 1,//文件名类型
                'params' => array(
                    'gid' => array('type' => 'number',),
                    'sid' => array('type' => 'number',),
                    'send_tid' => array('type' => 'number',),
                    'content' => array('type' => 'string',),
                    'level' => array('type' => 'number',),
                    'endtime' => array('type' => 'number',),
                    'interval' => array('type' => 'number',),
                ),
            ),

        ),

    ),

);
