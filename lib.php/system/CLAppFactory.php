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
		return $this->create( __NAMESPACE__.'\\CLRequest' ) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return Response
	 */
	public function createResponse()
	{
		$aRspn = $this->create( __NAMESPACE__.'\\Response' ) ;
		$aRspn->initialize( new PrintSteam() ) ;
		
		return $aRspn ;
	}
	
}
?>