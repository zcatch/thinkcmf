<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\demo\controller;

use cmf\controller\HomeBaseController;

class IndexController extends HomeBaseController
{
    public function index()
    {
        phpinfo();
        die;
        $isPushed = \think\Queue::push("app\job\Test@fun", [
            'id' => 123,
        ], 'test_queue');
        dump($isPushed);
        die;
        return $this->fetch(':index');
    }

    public function block()
    {
        return $this->fetch();
    }

    public function ws()
    {
        return $this->fetch(':ws');
    }
}
