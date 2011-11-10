<?php
namespace jc\system ;

class CLAppFactory extends ApplicationFactory
{
	public function createRequest(Application $aApp)
	{
		require_once \jc\PATH."/src/lib.php/util/IHashTable.php" ;
		require_once \jc\PATH."/src/lib.php/util/IDataSrc.php" ;
		require_once \jc\PATH."/src/lib.php/util/HashTable.php" ;
		require_once \jc\PATH."/src/lib.php/util/DataSrc.php" ;
		require_once __DIR__.'/Request.php' ;
		require_once __DIR__.'/CLRequest.php' ;
		
		$aReq = new CLRequest() ;
		$aReq->setApplication($aApp) ;
		
		return $aReq ;
	}
	
	public function createResponse(CoreApplication $aApp)
	{
		$aPrinter = new \jc\io\ShellPrintStream() ;
		$aPrinter->setApplication($aApp) ;
		
		$aRespn = parent::createResponse($aApp,$aPrinter) ;
		return $aRespn ;
	}
}

?>
