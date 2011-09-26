<?php
namespace jc\system ;

class HttpAppFactory extends ApplicationFactory
{
	public function createRequest(CoreApplication $aApp)
	{
		require_once \jc\PATH."/src/lib.php/util/IHashTable.php" ;
		require_once \jc\PATH."/src/lib.php/util/IDataSrc.php" ;
		require_once \jc\PATH."/src/lib.php/util/HashTable.php" ;
		require_once \jc\PATH."/src/lib.php/util/DataSrc.php" ;
		require_once __DIR__.'/Request.php' ;
		require_once __DIR__.'/HttpRequest.php' ;
		
		$aReq = new HttpRequest($aApp) ;
		$aReq->setApplication($aApp) ;
		
		return $aReq ;
	}
	
	public function createResponse(CoreApplication $aApp)
	{
		// 向客户端发送有效的编码		
		header("Content-type: text/html; charset=UTF-8");

		$aPrinter = new \jc\io\HtmlPrintStream() ;
		$aPrinter->setApplication($aApp) ;
		
		$aRespn = parent::createResponse($aApp,$aPrinter) ;
		return $aRespn ;
	}
}

?>
