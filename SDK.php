<?php

class SDK {
    
  private $accessKey;
  private $secretKey;
  private $host;

  public function __construct($accessKey, $secretKey) {
    $this->accessKey = $accessKey;
    $this->secretKey = $secretKey;
    $this->host = "https://api.sonma.net";
  }

  /**
   * $sn 打印机编号,10位数字
   * $data 打印的数据, 需要和模板对应, e.g. data: {"message":"xxxxx"}
   * $template 模板编号, e.g. 10086 ,可以通过 templateURL: https://api.sonma.net/template/<id> 查看模板内容
   * HttpStatusCode 200 打印成功,
   * HttpStatusCode 202 打印机离线
   * HttpStatusCode > 202 根据 responseBody 中 message字段判断错误情况
   * link http://docs.sonma.net
   */

  public function print($sn,$data,$template) {
      return $this->request('POST','/v1/print',array('sn' => $sn,'content' => json_encode($data),'template' => $template));
  }
  
  private function request($method, $api, $content) {
      
    ksort($content); //sort by key asc
    $requestBody = http_build_query($content); //RFC3986 URL Encode
    $timeStamp = time();//unix timestamp
    $hashedQueryString = sha1($requestBody);//hex(sha1(*))
    $stringToSign = "{$timeStamp}\n".$hashedQueryString;//mixed
    $signature = hash_hmac('sha1',$stringToSign,$this->secretKey);//hex(hmac_sha1(*))
    $authorization = base64_encode("HMAC-SHA1 {$this->accessKey}:{$signature}");//base64_encode

    // echo "规范查询字符串:{$requestBody}<br>".
    //      "规范查询字符串哈希:{$hashedQueryString}<br>".
    //      "待签字符串:{$stringToSign}<br>".
    //      "签名:{$signature}<br>".
    //      "鉴权字符串:{$authorization}<br>";
    
    $options = array(
        'http' => array(
            'method' => $method,
            'ignore_errors' => true,
            'header' => "Content-type:application/x-www-form-urlencoded\r\n".
                        "Timestamp:{$timeStamp}\r\n".
                        "Authorization:{$authorization}",
            'content' => $requestBody,
            'timeout' => 15
        )
    );
    
    
    // var_dump($options);
    
    $context = stream_context_create($options);
    
    
    $result = file_get_contents($this->host.$api,false,$context);
    
    // var_dump($result);
    
    return $result;
  }
  
}
