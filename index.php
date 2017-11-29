<?php
require_once "SDK.php";
header("Content-type:application/json");

$sdk = new SDK("123456789","123456789");

$response = $sdk->print(123456789,array(
        message => "test"
    ),10086);

echo $response;


    


   