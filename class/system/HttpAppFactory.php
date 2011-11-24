<?php
namespace jc\system ;

use jc\fs\FileSystem;

class HttpAppFactory extends ApplicationFactory
{
	public function createRequest()
	{
		require_once \jc\PATH."/class/util/IHashTable.php" ;
		require_once \jc\PATH."/class/util/IDataSrc.php" ;
		require_once \jc\PATH."/class/util/HashTable.php" ;
		require_once \jc\PATH."/class/util/DataSrc.php" ;
		require_once __DIR__.'/Request.php' ;
		require_once __DIR__.'/HttpRequest.php' ;
		
		$aReq = new HttpRequest() ;
		
		// 访问入口
		FileSystem::singleton()->find('/')->setHttpUrl( dirname($aReq->urlPath()) ) ;
		
		return $aReq ;
	}
	
	public function createResponse()
	{
		// 向客户端发送有效的编码
		header("Content-type: text/html; charset=UTF-8");
		$aRespn = parent::createResponse(new \jc\io\HtmlPrintStream()) ;
		
		return $aRespn ;
	}
}

?>
