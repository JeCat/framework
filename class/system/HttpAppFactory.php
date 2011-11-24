<?php
namespace org\jecat\framework\system ;

use org\jecat\framework\fs\FileSystem;

class HttpAppFactory extends ApplicationFactory
{
	public function createRequest()
	{
		require_once \org\jecat\framework\PATH."/class/util/IHashTable.php" ;
		require_once \org\jecat\framework\PATH."/class/util/IDataSrc.php" ;
		require_once \org\jecat\framework\PATH."/class/util/HashTable.php" ;
		require_once \org\jecat\framework\PATH."/class/util/DataSrc.php" ;
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
		$aRespn = parent::createResponse(new \org\jecat\framework\io\HtmlPrintStream()) ;
		
		return $aRespn ;
	}
}

?>
