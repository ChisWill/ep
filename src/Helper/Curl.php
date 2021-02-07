<?php

namespace Ep\Helper;

use Closure;

/**
 * RESTful Api 的快捷调用助手类
 *
 * [options]:
 * -format: 返回数据类型，可选范围：json|xml
 * -timeout: 执行最大时间，默认为1秒，可输入小数
 * -header: 设置头信息参数
 * 
 * ps.其他 curl 的参数，通过 $options 参数直接按键值对方式传入即可
 * 
 * @author ChisWill
 */
class Curl
{
    /**
     * 模拟 get 请求
     * 
     * @param  string       $url     请求地址
     * @param  string|array $data    字符串格式为请求体数据，数组格式为curl选项
     * @param  array        $options curl选项
     * @return string                响应结果
     */
    public static function get(string $url, $data = '', array $options = []): string
    {
        if (is_array($data)) {
            $options = $data;
            $data = '';
        }

        $handle = new CurlHandle($options);

        curl_setopt($handle->ch, CURLOPT_URL, $url);
        curl_setopt($handle->ch, CURLOPT_CUSTOMREQUEST, 'GET');
        if ($data) {
            curl_setopt($handle->ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = $handle->exec();

        $handle->close();

        return $result;
    }

    /**
     * 模拟 post 请求
     * 
     * @param  string       $url     请求地址
     * @param  string|array $data    请求体数据
     * @param  array        $options curl选项
     * @return string                响应结果
     */
    public static function post(string $url, $data = [], array $options = []): string
    {
        $handle = new CurlHandle($options);

        curl_setopt($handle->ch, CURLOPT_URL, $url);
        curl_setopt($handle->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($handle->ch, CURLOPT_POSTFIELDS, $data);

        $result = $handle->exec();

        $handle->close();

        return $result;
    }

    /**
     * 模拟 put 请求
     * 
     * @param  string       $url     请求地址
     * @param  string|array $data    请求体数据
     * @param  array        $options curl选项
     * @return string                响应结果
     */
    public static function put(string $url, $data = [], array $options = []): string
    {
        $handle = new CurlHandle($options);

        curl_setopt($handle->ch, CURLOPT_URL, $url);
        curl_setopt($handle->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($handle->ch, CURLOPT_POSTFIELDS, $data);

        $result = $handle->exec();

        $handle->close();

        return $result;
    }

    /**
     * 模拟 delete 请求
     * 
     * @param  string  $url     请求地址
     * @param  array   $options curl选项
     * @return string           响应结果
     */
    public static function delete(string $url, array $options = []): string
    {
        $handle = new CurlHandle($options);

        curl_setopt($handle->ch, CURLOPT_URL, $url);
        curl_setopt($handle->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $result = $handle->exec();

        $handle->close();

        return $result;
    }

    /**
     * 模拟并发 post，支持一次执行多批次任务
     *
     * @param  array|string $urls     一维数组时，表示请求多个地址
     * @param  array        $data     二维数组时，表示使用多个请求参数
     * @param  array        $options  二维数组时，表示使用多个选项
     * @param  int          $batch    当设置值大于1时，表示直接使用以上参数值，此时以上参数必须为单个值，以发起批量请求
     * @return array
     */
    public static function postMulti($urls, array $data, array $options = [], int $batch = 1): array
    {
        $params = static::initParams($urls, $data, $options, $batch, function ($option, $url, $data) {
            $option[CURLOPT_URL] = $url;
            $option[CURLOPT_CUSTOMREQUEST] = 'POST';
            $option[CURLOPT_POSTFIELDS] = $data;
            return $option;
        });

        $handle = new CurlHandle($params, true);

        $handle->execMulti();

        $results = $handle->getResults();

        $handle->closeMulti();

        return $results;
    }

    /**
     * 模拟并发 get，支持一次执行多批次任务
     *
     * @param  array|string $urls     一维数组时，表示请求多个地址
     * @param  array        $options  二维数组时，表示使用多个选项
     * @param  int          $batch    当设置值大于1时，表示直接使用以上参数值，此时以上参数必须为单个值，以发起批量请求
     * @return mixed
     */
    public static function getMulti($urls, array $options = [], int $batch = 1): array
    {
        $params = static::initParams($urls, [], $options, $batch, function ($option, $url) {
            $option[CURLOPT_URL] = $url;
            $option[CURLOPT_CUSTOMREQUEST] = 'GET';
            return $option;
        });

        $handle = new CurlHandle($params, true);

        $handle->execMulti();

        $results = $handle->getResults();

        $handle->closeMulti();

        return $results;
    }

    private static function initParams($urls, array $data, array $options, int $batch, Closure $callback)
    {
        $params = [];
        $multiUrl = is_array($urls);
        $multiData = Arr::isIndexed($data);
        $multiOptions = Arr::isIndexed($options);
        if ($multiUrl) {
            $forData = &$urls;
        } elseif ($multiData) {
            $forData = &$data;
        } elseif ($multiOptions) {
            $forData = &$options;
        }
        if ($batch > 1 || !isset($forData)) {
            for ($i = 0; $i < $batch; $i++) {
                $params[$i] = call_user_func($callback, $options, $urls, $data);
            }
        } else {
            foreach ($forData as $k => $v) {
                $url = $multiUrl ? Arr::getValue($urls, $k, '') : $urls;
                $row = $multiData ? Arr::getValue($data, $k, []) : $data;
                $opt = $multiOptions ? Arr::getValue($options, $k, []) : $options;
                $params[$k] = call_user_func($callback, $opt, $url, $row);
            }
        }
        return $params;
    }
}

class CurlHandle
{
    public $ch;
    public $mch;
    protected $handles = [];

    //-------------------------------------------------
    //                以下为标准 Curl 方法
    //-------------------------------------------------
    public function __construct(array $options, bool $isMulti = false)
    {
        if ($isMulti === false) {
            $this->initCurl($options);
        } else {
            $this->initMultiCurl($options);
        }
    }

    private function initCurl(array $options): void
    {
        $this->ch = curl_init();
        $format = Arr::remove($options, 'format', 'json');
        $header = ['Accept:application/' . $format];
        $header = array_merge($header, (array) Arr::remove($options, 'header', []));
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);

        $timeout = Arr::remove($options, 'timeout', 10);
        if ($timeout >= 1) {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        } else {
            curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        }

        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko');
        curl_setopt($this->ch, CURLOPT_ENCODING, "gzip");

        curl_setopt_array($this->ch, $options);
    }

    public function close(): void
    {
        curl_close($this->ch);
    }

    public function exec()
    {
        return curl_exec($this->ch);
    }

    //-------------------------------------------------
    //                以下为 Curl_multi 相关方法
    //-------------------------------------------------
    protected function initMultiCurl(array $params): void
    {
        $this->mch = curl_multi_init();

        for ($i = 0; $i < count($params); $i++) {
            $handle = new CurlHandle($params[$i]);
            curl_multi_add_handle($this->mch, $handle->ch);
            $this->handles[] = $handle;
        }
    }

    public function closeMulti(): void
    {
        curl_multi_close($this->mch);
    }

    public function execMulti(): void
    {
        $active = false;
        do {
            $mrc = curl_multi_exec($this->mch, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->mch) != -1) {
                usleep(50);
            }
            do {
                $mrc = curl_multi_exec($this->mch, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    public function getResults(): array
    {
        $results = [];
        foreach ($this->handles as $k => $handle) {
            if (curl_error($handle->ch) == '') {
                $results[$k] = (string) curl_multi_getcontent($handle->ch);
            }
            curl_multi_remove_handle($this->mch, $handle->ch);
            $handle->close();
        }
        return $results;
    }
}
