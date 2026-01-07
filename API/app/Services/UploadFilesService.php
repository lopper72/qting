<?php
/**
 * 文件上传服务
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Services;

use Exception;
use App\Models\Config;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use OSS\OssClient;
use OSS\Core\OssException;

class UploadFilesService {

    private $config = [];

    public function __construct()
    {
        $this->config = Config::getConfig('upload');
        if (empty($this->config)){
            throw new Exception('上传配置错误');
        }
    }

    public function up(Request $request)
    {
        if (!$request->hasFile('file')) {
            throw new Exception('无法获取上传文件');
        }
        $file = $request->file('file');
        if (!$file->isValid()) {
            throw new Exception('文件未通过验证');
        }
        // 2.是否符合文件类型 getClientOriginalExtension 获得文件后缀名
        $fileExtension = strtolower($file->getClientOriginalExtension());
        if(! in_array($fileExtension, explode('|', $this->config['upload_file_ext']))) {
            throw new Exception('不支持' .$fileExtension. '文件格式');
        }
        // 3.判断大小是否符合 2M
        $filePath = $file->getRealPath();
        if (filesize($filePath) >= $this->config['upload_max_size'] * 1024 * 1000) {
            throw new Exception('文件大小超过限制');
        }
        $filename = genRequestSn() . '.' . $fileExtension;
        // 文件原名
        $originaName = $file->getClientOriginalName();
        if ($this->config['upload_service'] == 'qiniu') {
            $data = $this->qiniu_upload($filename, $filePath);
        } elseif ($this->config['upload_service'] == 'aliyun') {
            $data = $this->aliyun_upload($filename, $filePath);
        } else {
            $data = $this->local_upload($filename, $filePath);
        }
        $data['name'] = $originaName;
        return $data;
    }

    public function upVideo(Request $request)
    {
        if (!$request->hasFile('file')) {
            throw new Exception('无法获取上传文件');
        }
        $file = $request->file('file');
        if (!$file->isValid()) {
            throw new Exception('文件未通过验证');
        }
        // 2.是否符合文件类型 getClientOriginalExtension 获得文件后缀名
        $fileExtension = strtolower($file->getClientOriginalExtension());
        if(! in_array($fileExtension, explode('|', $this->config['upload_video_file_ext']))) {
            throw new Exception('不支持' .$fileExtension. '文件格式');
        }
        // 3.判断大小是否符合 2M
        $filePath = $file->getRealPath();
        if (filesize($filePath) >= $this->config['upload_video_max_size'] * 1024 * 1000) {
            throw new Exception('文件大小超过限制');
        }
        $filename = genRequestSn() . '.' . $fileExtension;
        // 文件原名
        $originaName = $file->getClientOriginalName();
        if ($this->config['upload_service'] == 'qiniu') {
            $data = $this->qiniu_upload($filename, $filePath, 'video');
        } elseif ($this->config['upload_service'] == 'aliyun') {
            $data = $this->aliyun_upload($filename, $filePath, 'video');
        } else {
            $data = $this->local_upload($filename, $filePath, 'video');
        }
        $data['name'] = $originaName;
        return $data;
    }

    public function qiniu_upload($filename, $filePath, $type = 'img')
    {
        $accessKey = $this->config['upload_qiniu_accessKey'];
        $secretKey = $this->config['upload_qiniu_secretKey'];
        $bucket = $this->config['upload_qiniu_bucket'];
        if (empty($accessKey) || empty($secretKey) || empty($bucket)) {
            throw new Exception('七牛云配置有误');
        }

        // Fix for Qiniu region issue - specify a default region
        $auth = new Auth($accessKey, $secretKey);
        $token = $auth->uploadToken($bucket);

        // Create UploadManager with proper region configuration
        $uploadMgr = new UploadManager();

        // Set up config with region
        $config = new \Qiniu\Config();
        // Use a default region (e.g., Zone::zonez0) if not specified
        $config->zone = \Qiniu\Zone::zonez0();

        $filename = 'qiniu_' . $filename;
        $result = $uploadMgr->putFile($token, $filename, $filePath, null, null, false, null, $config);
        unlink($filePath);
        $url = isset($result[0]['key']) ? $result[0]['key']:'';
        if ($type == 'video') {
            $img = $url ? $url . $this->config['upload_qiniu_video_thumb']:'';
            return [
                'img'       => $img,
                'img_url'   => dealUrl($img),
                'video_url' => $url,
                'url'       => dealUrl($url),
            ];
        } else {
            return [
                'img_url' => $url,
                'url' => dealUrl($url)
            ];
        }
    }

    public function aliyun_upload($filename, $filePath, $type = 'img')
    {
        $accessKey = $this->config['upload_aliyun_accessKey'];
        $secretKey = $this->config['upload_aliyun_secretKey'];
        $bucket = $this->config['upload_aliyun_bucket'];
        $endpoint = $this->config['upload_aliyun_endPoint'];
        if (empty($accessKey) || empty($secretKey) || empty($bucket)) {
            throw new Exception('阿里云配置有误');
        }
        try{
            $filename = 'aliyun_' . $filename;
            $ossClient = new OssClient($accessKey, $secretKey, $endpoint);
            $ossClient->uploadFile($bucket, $filename, $filePath);
        } catch(OssException $e) {
            throw new Exception($e->getMessage());
        }
        unlink($filePath);
        $url = $filename;
        if ($type == 'video') {
            $img = $url ? $url . $this->config['upload_aliyun_video_thumb']:'';
            return [
                'img'       => $img,
                'img_url'   => dealUrl($img),
                'video_url' => $url,
                'url'       => dealUrl($url),
            ];
        } else {
            return [
                'img_url' => $url,
                'url' => dealUrl($url)
            ];
        }
    }

    public function local_upload($filename, $filePath, $type = 'img')
    {
        $filename = date('Ymd') . '/' . $filename;
        // 使用uploads本地存款控件（目录）
        $bool = Storage::disk('local')->put($filename, file_get_contents($filePath));
        if($bool){
            $url = 'uploads/' . $filename;
            if ($type == 'video') {
                // 本地环境生成预览图
                $img = 'uploads/' . genRequestSn() . '.jpg';
                $logger = null;
                $ffmpeg = FFMpeg::create(array(
                    'ffmpeg.binaries'  => '/usr/ffmpeg/bin/ffmpeg',
                    'ffprobe.binaries' => '/usr/ffmpeg/bin/ffprobe',
                    'timeout'          => 3600, // The timeout for the underlying process
                    'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
                ), $logger);
                $video = $ffmpeg->open($filePath);
                $frame = $video->frame(TimeCode::fromSeconds(1));
                $frame->save($img);
                return [
                    'img'       => $img,
                    'img_url'   => dealUrl($img),
                    'video_url' => $url,
                    'url'       => dealUrl($url),
                ];
            } else {
                return [
                    'img_url' => $url,
                    'url' => dealUrl($url)
                ];
            }
        }else{
            throw new Exception('上传失败');
        }
    }
}
