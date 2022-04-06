<?php

namespace api\common\lib\dingtalk;

class Robot
{
    private static $instance = null;
    private $msgtype;
    private $title;
    private $content;
    private $phones;
    private $isAtAll = false;
    private $picUrl;
    private $msgUrl;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        //查找内容中是否包含@电话
        preg_match_all("/(@1)\d{10}/", $content, $arr);
        if (!empty($arr[0])) {
            foreach ($arr[0] as &$tel) {
                $tel = substr($tel, 1);
            }
            $this->phones = $arr[0];
        } else {
            $this->phones = "";
        }
        return $this;
    }

    public function setMsgType($msgtype)
    {
        $this->msgtype = $msgtype;
        return $this;
    }

    public function setMsgUrl($msgUrl)
    {
        $this->msgUrl = $msgUrl;
        return $this;
    }

    /**
     * @param $phones  被@的手机号
     * @param $isAtAll 是否@所有人
     *
     * @return $this
     */
    public function setAtAll($isAtAll = true)
    {
        if ($isAtAll) {
            $this->isAtAll = $isAtAll;
        }
        return $this;
    }

    public function getAt()
    {
        $at = [];
        if ($this->phones) {
            $at["atMobiles"] = $this->phones;
        }
        if ($this->isAtAll) {
            $at["isAtAll"] = true;
        }
        return $at;
    }

    public function requestData()
    {
        $data            = [];
        $data['msgtype'] = $this->msgtype ? $this->msgtype : "text";
        if (empty($this->content)) {
            return [0, "内容缺省"];
        }
        switch ($data['msgtype']) {
            case "text":
                $data["text"] = ["content" => $this->content];
                if ($this->phones || $this->isAtAll) {
                    $data['at'] = $this->getAt();
                }
                break;
            case "link":
                if (empty($this->title)) {
                    return [0, "标题缺省"];
                } elseif (empty($this->msgUrl)) {
                    return [0, "链接URL缺省"];
                }
                $data["link"] = [
                    "title"      => $this->title,
                    "text"       => $this->content,
                    "picUrl"     => $this->picUrl,
                    "messageUrl" => $this->msgUrl,
                ];
                break;
            case "markdown":
                if (empty($this->title)) {
                    return [0, "标题缺省"];
                }
                $data["markdown"] = [
                    "title" => $this->title,
                    "text"  => $this->content,
                ];
                if ($this->phones || $this->isAtAll) {
                    $data['at'] = $this->getAt();
                }
                break;
            default:
                return [0, "参数错误"];
        }
        return $data;
    }

    /**
     * 发送到系统配置中的群组
     *
     * @param string $name
     * @param        $optionKey
     *
     * @return bool|string
     * @throws \Exception
     */
    public function sendToOptionGroup($optionKey = "test", $name = "dingtalk")
    {
        $options = cmf_get_option_no_cache($name);
        $url     = $options[$optionKey];
        if (empty($url)) {
            throw new \Exception("钉钉：未配置{$optionKey}的群地址");
        }
        return $this->send($url);
    }

    public function send($url)
    {
        if (empty($url)) {
            throw new \Exception("钉钉：群地址不能为空");
        }
        //完善发送机器人信息代码
        $data = $this->requestData();
        $res  = $this->post($url, json_encode($data));
        return $res;
    }

    protected function post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json;charset=utf-8']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}