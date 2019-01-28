<?php
namespace App;

/**
 * Class ReturnInfo
 * @Author roseEnd
 * @date 2017/9/22 13:21
 * @desc 用于repository返回
 * @package App
 */
class InfoReturn
{
    protected $array;

    public function __construct(array $array = ['status' => false, 'info' => null, 'data' => null])
    {
        $this->array['status'] = isset($array['status']) ? $array['status'] : false;
        $this->array['info'] = $array['info'] ?? null;
        $this->array['data'] = $array['data'] ?? null;
    }

    public function getAll()
    {
        return $this->array;
    }

    public function setAll(array $array = ['status' => false, 'info' => null, 'data' => null])
    {
        $this->array = $array;
        return $this;
    }

    public function getStatus()
    {
        return $this->array['status'];
    }

    public function getInfo()
    {
        return $this->array['info'];
    }

    public function getData()
    {
        return $this->array['data'];
    }

    public function setStatus(bool $status)
    {
        $this->array['status'] = $status;
        return $this;
    }

    public function setInfo(string $info)
    {
        $this->array['info'] = $info;
        return $this;
    }

    public function setData($data)
    {
        $this->array['data'] = $data;
        return $this;
    }

    public function toJson()
    {
        return json_encode($this->array);
    }
}