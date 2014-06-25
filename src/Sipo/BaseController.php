<?php
namespace Sipo;

class BaseController
{
	/**
	 * 
	 * @var \Klein\Request
	 */
	protected $_request;
	
	/**
	 * 
	 * @var \Klein\Response
	 */
	protected $_response;
	
	protected $_api = 'http://app.sipo.gov.cn:8080/%s';
	
	public function setRequest(\Klein\Request $request)
	{
		$this->_request = $request;
	}
	
	public function setResponse(\Klein\Response $response)
	{
		$this->_response = $response;
	}
	
	public function getRequest()
	{
		return $this->_request;
	}
	
	public function getResponse()
	{
		return $this->_response;
	}
	
	public function api($path)
	{
		return sprintf($this->_api, $path);
	}
	
	public function preform()
	{
		
	}
	
	public function postform()
	{
		
	}
}