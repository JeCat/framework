<?php
namespace jc\system ;

class CLAppFactory extends AppFactory
{
	public function createRequest(CoreApplication $aApp)
	{
		require_once \jc\PATH."/src/lib.php/util/IHashTable.php" ;
		require_once \jc\PATH."/src/lib.php/util/IDataSrc.php" ;
		require_once \jc\PATH."/src/lib.php/util/HashTable.php" ;
		require_once \jc\PATH."/src/lib.php/util/DataSrc.php" ;
		require_once __DIR__.'/Request.php' ;
		require_once __DIR__.'/CLRequest.php' ;
		
		return $aApp->create( 'CLRequest', __NAMESPACE__ ) ;
	}
	
	public function createResponse(CoreApplication $aApp)
	{
		$aRespn = parent::createResponse($aApp,$aApp->create('PrintStream','jc\\io')) ;
		return $aRespn ;
	}
}

?>