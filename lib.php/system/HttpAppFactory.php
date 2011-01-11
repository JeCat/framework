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
		return $this->create( __NAMESPACE__.'\\HttpRequest' ) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return Response
	 */
	public function createResponse()
	{
		return $this->create( __NAMESPACE__.'\\Response', $this->create('jc\\io\\HtmlPrintSteam') ) ;
		return $aRspn ;
	}
	
}
?>