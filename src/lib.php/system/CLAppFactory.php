<?php
namespace jc\system ;


use jc\io\PrintSteam;

class CLAppFactory extends ApplicationFactory
{
	/**
	 * Enter description here ...
	 * 
	 * @return IRequest
	 */
	public function createRequest()
	{
		return $this->create( 'CLRequest', __NAMESPACE__ ) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return Response
	 */
	public function createResponse()
	{
		return $this->create( 'Response', __NAMESPACE__, array($this->create('PrintStream','jc\\io')) ) ;
	}
	
}
?>