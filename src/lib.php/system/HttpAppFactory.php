<?php
namespace jc\system ;


use jc\io\HtmlPrintSteam;

class HttpAppFactory extends ApplicationFactory
{
	/**
	 * Enter description here ...
	 * 
	 * @return IRequest
	 */
	public function createRequest()
	{
		return $this->create( 'HttpRequest', __NAMESPACE__ ) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return Response
	 */
	public function createResponse()
	{
		return $this->create( 'Response', __NAMESPACE__, array($this->create('HtmlPrintSteam','jc\\io')) ) ;
	}
	
}
?>