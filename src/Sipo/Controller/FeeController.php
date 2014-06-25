<?php
namespace Sipo\Controller;

use Goutte\Client;

class FeeController extends \Sipo\BaseController
{
	const STATUS_OK    = 200;
	const STATUS_ERROR = 300;
	
	protected $_indexParamMaps = array(
		'APN',
		'FEE_AMOUNT',
		'FEE_TYPE',
		'REGISTER_NUMBER',
		'FEE_SN',
		'PAYMENT_USER_INFO',
		'PAYMENT_STATUS',
		'PAYMENT_DATE'
	);
	
	public function indexAction()
	{
		$request = $this->getRequest();
		
		$apn  = $request->param('apn');
		$page = $request->param('page');
		$page = ( $page ? $page : 1);
		
		if ( $apn === null ) {
			$result = $this->_buildApiResult(self::STATUS_ERROR, array(), array(
				'ERROR_MESSAGE' => 'Invalid APN.'
			));
		} else {
			$result = $this->_buildFeeData($apn, $page);
		}
		
		$this->getResponse()->body($result);
	}
	
	private function _buildFeeData($apn = '', $page = 1)
	{
		$client = new Client();
		
		$crawler = $client->request('POST', $this->api('/searchfee/searchfee_action.jsp'), array(
			'sqh'  => $apn,
			'page' => $page,
		));
		
		$data = array();
		$crawler->filter('table table tr')->each(function ($node) use(&$data) {
			$tmp = array();
			$node->filter('td')->each(function ($n, $i) use(&$tmp) {
				
				// 使用友好的key
				$key = $i;
				if ( array_key_exists($i, $this->_indexParamMaps) ) {
					$key = $this->_indexParamMaps[$i];
				}
				// 清除特殊空格
				$tmp[$key] = trim($n->text(), '　');
			});
			$data[] = $tmp;
		});
		// 移除TITLE
		array_shift($data);
		
		$contents = array();
		$crawler->filter('.contents')->each(function ($node) use(&$contents) {
			$contents[] = trim($node->text());
		});
		
		$contents = array_slice($contents, -4, -1);
		$params = array(
			'CURRENT_PAGE' => isset($contents[0]) ? $this->_findNumber($contents[0]) : 0,
			'PAGES'  => isset($contents[1]) ? $this->_findNumber($contents[1]) : 0,
			'COUNTS' => isset($contents[2]) ? $this->_findNumber($contents[2]) : 0,
		);
		
		return $this->_buildApiResult(self::STATUS_OK, $data, $params);
	}
	
	/**
	 * 构造接口数据
	 * 
	 * @param string  $status
	 * @param array   $data
	 * @param array $params
	 * @return array
	 */
	public function _buildApiResult($status, $data, $params = array())
	{
		$request  = $this->getRequest();
		$response = $this->getResponse();
		
		$format  = strtolower($request->format);
		
		$data = array_merge(array(
			'STATUS' => $status,
			'DATA' 	 => $data,
		), $params);
		
		switch ( $format ) {
			
			case 'xml':
				$response->header('Content-Type', 'text/xml');
				$data = \Sipo\Lib\ArrayToXML::toXml($data, 'ROOT');
			break;
			
			case 'json':
			default:
				$response->header('Content-Type', 'application/json');
				$data = json_encode($data);
			break;
		}
		
		return $data;
	}
	
	/**
	 * 提取字符串中数字
	 * 
	 * @param string $str
	 * @param number $default
	 * @return number
	 */
	private function _findNumber($str = '', $default = 0)
	{
		$str = trim($str);
	
		if ( empty($str) ) {
			return $default;
		}
	
		$result = $default;
	
		for ( $i = 0; $i < strlen($str); $i++ ) {
			if ( is_numeric($str[$i]) ) {
				$result .= $str[$i];
			}
		}
		return (int)$result;
	}
}