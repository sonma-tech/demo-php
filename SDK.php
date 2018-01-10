<?php
class SDK {
    
  public $accessKey;
  private $secretKey;
  private $host;

  public function __construct($accessKey, $secretKey) {
    $this->accessKey = $accessKey;
    $this->secretKey = $secretKey;
    $this->host = "http://api-beta.sonma.net";
    // $this->host = "http://localhost:8080";
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

  public function print1($sn,$data,$template) {
      return $this->curl('POST','/v1/print',array('sn' => $sn,'content' => $data,'template' => $template));
  }
  
  /**
   * 清空打印队列
   * http://api.sonma.net/printer/<sn>/queue
   */
  public function clearQueue($sn) {
      return $this->curl('DELETE','/printer/'.$sn.'/queue',null);
    
  }
  
  /**
   * 查询模板
   * http://api.sonma.net/template/<id>
   */
  public function template($id) {
      return $this->curl('GET','/template/'.$id,null);
  }
  
  /**
   * 创建模板
   * http://api.sonma.net/template  POST
   */
  public function createTemplate($template) {
      return $this->curl('POST', '/template', array('template' => $template));
  }
  
  /**
   * 修改模板
   * http://api.sonma.net/template  PUT
   */
  public function editTemplate($id, $template) {
      return  $this->curl('PUT', '/template', array('id' => $id, 'template' => $template));
  }
  
  public function register($sn) {
      return $this->curl('POST', '/printer/mfg', array('sn' => $sn));
  }
  
  public function record($sn,$start,$end) {
    $sn = urlencode($sn);
    $start = urlencode($start);
    $end = urlencode($end);
      return $this->get('GET', '/record?sn='.$sn.'&start='.$start.'&end='.$end);
  }

  private function get($method, $api) {
    @ksort($content); //sort by key asc
    //$requestBody = http_build_query($content,null,'&',PHP_QUERY_RFC3986);//RFC3986 URL Encode
    $requestBody  = str_replace('+', '%20', @http_build_query($content,null,'&'));
    $timeStamp = 0;//time();//unix timestamp
    $hashedQueryString = sha1($requestBody);//hex(sha1(*))
    $stringToSign = "{$timeStamp}\n".$hashedQueryString;//mixed
    $signature = hash_hmac('sha1',$stringToSign,$this->secretKey);//hex(hmac_sha1(*))
    $authorization = base64_encode("HMAC-SHA1 {$this->accessKey}:{$signature}");//base64_encode
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->host.$api,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => array(
        "Content-type:application/x-www-form-urlencoded",
      ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
      return $err;
    } else {
      return $response;
    }
  }
  
  private function curl($method, $api, $content) {
      
    ksort($content); //sort by key asc
    // var_dump($content);
    // echo('<BR>');
    //$requestBody = http_build_query($content,null,'&',PHP_QUERY_RFC3986);//RFC3986 URL Encode
    $requestBody  = str_replace('+', '%20', http_build_query($content,null,'&'));
    $timeStamp = 0;//time();//unix timestamp
    $hashedQueryString = sha1($requestBody);//hex(sha1(*))
    $stringToSign = "{$timeStamp}\n".$hashedQueryString;//mixed
    $signature = hash_hmac('sha1',$stringToSign,$this->secretKey);//hex(hmac_sha1(*))
    $authorization = base64_encode("HMAC-SHA1 {$this->accessKey}:{$signature}");//base64_encode

    // echo "规范查询字符串:{$requestBody}<br>".
    //      "规范查询字符串哈希:{$hashedQueryString}<br>".
    //      "待签字符串:{$stringToSign}<br>".
    //      "签名:{$signature}<br>".
    //      "鉴权字符串:{$authorization}<br>";
    
    
      $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->host.$api,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POSTFIELDS => $requestBody,
      CURLOPT_HTTPHEADER => array(
        "Content-type:application/x-www-form-urlencoded",
        "Timestamp:{$timeStamp}",  
        "Authorization:{$authorization}"
      ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
      return $err;
    } else {
      return $response;
    }
  }
}

// $sdk = new SDK('zfl','zfl8888');
$sdk = new SDK('lSHkjGqYSCgXxwEr','xMwNeFcpsHzaDHPR');

//模板测试
$sn1 = $_POST['sn1'];
$content = $_POST['content'];
$template = $_POST['template'];
if($sn1 != null) {
    $retrun = $sdk->print1($sn1,$content,$template);
    echo($retrun);
}

//清空缓存
$sn2 = $_POST['sn2'];
if($sn2 != null) {
    $retrun = $sdk->clearQueue($sn2);
    echo($retrun);
}

//查询打印任务
$sn3 = $_POST['sn3'];
$start = $_POST['start'];
$end = $_POST['end'];
if($sn3 != null) {
    $retrun = $sdk->record($sn3,$start,$end);
    echo($retrun);
}
//打印机注册
$sn4 = $_POST['sn4'];
if($sn4 != null) {
    $retrun = $sdk->register($sn4);
    echo($retrun);
}
//获取模板
$templateId = $_POST['templateId'];
if($templateId != null) {
    $retrun = $sdk->template($templateId);
    echo($retrun);
}

//保存模板
$savetemplateId = $_POST['savetemplateId'];
$edittemplate = $_POST['edittemplate'];
if($savetemplateId != null) {
    $retrun = $sdk->editTemplate($savetemplateId,$edittemplate);
    echo($retrun);
}

//创建模板
$savetemplate = $_POST['savetemplate'];
if($savetemplate != null) {
    $retrun = $sdk->createTemplate($savetemplate);
    echo($retrun);
}
?>
