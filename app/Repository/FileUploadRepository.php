<?php
namespace App\Repository;

use App\InfoReturn;

/**
 * Class FileUploadRepository
 * @Author roseEnd
 * @date 2017/9/25 14:35
 * @desc 文件上传库
 * @package App\Repository
 */
class FileUploadRepository
{
    /*允许的文件类型*/
    protected $allowTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/jpg'];

    /*图片上传允许最大10M*/
    protected $allowSize = 1000 * 1000 * 10;

    protected $rtn;

    public function __construct()
    {
        $this->rtn = new InfoReturn();
    }

    /**
     * @Author roseEnd
     * @date 2017/9/25 14:52
     * @desc 设置允许的文件类型
     * @param array $type
     * @return $this
     */
    public function setAllowType(array $type)
    {
        $this->allowTypes = $type;
        return $this;
    }

    /**
     * @Author roseEnd
     * @date 2017/9/25 14:53
     * @desc 设置允许的大小
     * @param int $size
     * @return $this
     */
    public function setAllowSize(int $size)
    {
        $this->allowSize = $size;
        return $this;
    }

    /**
     * @Author roseEnd
     * @date 2017/9/25 15:00
     * @desc 上传前校验
     * @param string $type
     * @param int $size
     * @return InfoReturn
     */
    public function check(string $type, int $size):InfoReturn
    {
        if (!in_array($type, $this->allowTypes, true)) {
            return $this->rtn->setAll(['status' => false, 'info' => '不允许的文件类型', 'data' => '']);
        }

        if ($size > $this->allowSize) {
            return $this->rtn->setAll(['status' => false, 'info' => '文件太大', 'data' => '']);
        }
        return $this->rtn->setAll(['status' => true, 'info' => '成功', 'data' => '']);
    }

    /**
     * @Author roseEnd
     * @date 2017/9/25 15:18
     * @desc base64格式的图片上传,支持将图片进行缩放
     * @param string $file
     * @param int $height
     * @param int $width
     * @return InfoReturn
     */
    public function uploadForBase64(string $file, int $width = 320, int $height = 240):InfoReturn
    {
        if (preg_match('/^(data:\s*(image\/(\w+));base64,)/', $file, $result)) {
            $type = $result[2];
            $extension = $result[3];
            //进行图片类型验证
            if (! in_array($type, $this->allowTypes)) {
                return $this->rtn->setAll(['status' => false, 'info' => '不支持的图片类型', 'data' => '']);
            }
            /*解码*/
            $filename = 'image/'.(time() . rand(1, 99)) . '.' .$extension;
            $decode = base64_decode(str_replace($result[1], '', $file));

            /*保存图片*/
            if (!OSSRepository::publicUploadContent('rose-blog', $filename, $decode)) {
                return $this->rtn->setAll([
                    'status' => false,
                    'data' => '',
                    'info' => '上传失败'
                ]);
            }
            $data['fileName'] = 'base64';  //文件原来的名字
            $data['filePath'] = OSSRepository::getPublicObjectURL('rose-blog', $filename);  //需要保存到数据库的路径
            $data['fileType'] = $extension;   //文件类型

            return $this->rtn->setAll([
                'status' => true,
                'data' => $data,
                'info' => '上传成功'
            ]);
        } else {
            return $this->rtn->setAll(['status' => false, 'info' => '文件错误', 'data' => '']);
        }
    }
}