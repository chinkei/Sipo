<?php
require_once __DIR__ . '/vendor/autoload.php';

// UTF-8编码
header("Content-Type: text/html; charset=utf-8");

$klein = new \Klein\Klein();

// 费用路由
$klein->respond('/fee/[:action]?.[xml|csv|json:format]?', function ($request, $response) use($klein) {
	
	// Handle exceptions => flash the message and redirect to the referrer
	$klein->onError(function ($klein, $err_msg) {
		$klein->service()->flash($err_msg);
		$klein->service()->back();
	});
	
    $controller = new \Sipo\Controller\FeeController();
    $action     = ( $request->action ? $request->action : 'index' ) .'Action';
    
    if ( is_callable(array($controller, $action) ) ) {
    	$controller->setRequest($request);
    	$controller->setResponse($response);
    	
    	$controller->preform();
    	$controller->$action();
    	$controller->postform();
    }
});

$klein->dispatch();