<?php
namespace org\jecat\framework\system ;

class CLAppFactory extends ApplicationFactory
{
	public function createRequest(Application $aApp)
	{
		require_once \org\jecat\framework\PATH."/class/util/IHashTable.php" ;
		require_once \org\jecat\framework\PATH."/class/util/IDataSrc.php" ;
		require_once \org\jecat\framework\PATH."/class/util/HashTable.php" ;
		require_once \org\jecat\framework\PATH."/class/util/DataSrc.php" ;
		require_once __DIR__.'/Request.php' ;
		require_once __DIR__.'/CLRequest.php' ;
		
		$aReq = new CLRequest() ;
		$aReq->setApplication($aApp) ;
		
		return $aReq ;
	}	
	
	public function createResponseDevice()
	{
		return new \org\jecat\framework\io\ShellPrintStream() ;
	}
}

?>
