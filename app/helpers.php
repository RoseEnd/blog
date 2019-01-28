<?php

if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

/**
 * 遍历目录下的所有文件名,返回文件路径数组
 */
if (! function_exists('getAllFilename')) {
    function getAllFilename($path, array &$filename)
    {
        if(is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file!="." && $file!="..") {
                        if((is_dir($path."/".$file))) {
                            getAllFilename($path."/".$file, $filename);
                        } else {
                            $filename[] = $path."/".$file;
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
}
