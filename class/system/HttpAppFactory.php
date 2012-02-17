<?php
namespace org\jecat\framework\system ;

use org\jecat\framework\mvc\controller\HttpRequest;
use org\jecat\framework\fs\FileSystem;

class HttpAppFactory extends ApplicationFactory
{
	public function __construct()
	{
		// 向客户端发送有效的编码
		header("Content-type: text/html; charset=UTF-8");
	}
	
	public function createRequest(Application $aApp)
	{
		require_once \org\jecat\framework\PATH."/class/util/IHashTable.php" ;
		require_once \org\jecat\framework\PATH."/class/util/IDataSrc.php" ;
		require_once \org\jecat\framework\PATH."/class/util/HashTable.php" ;
		require_once \org\jecat\framework\PATH."/class/util/DataSrc.php" ;
		
		$aReq = new HttpRequest() ;
		
		// 访问入口
		FileSystem::singleton()->find('/')->setHttpUrl( dirname($aReq->urlPath()) ) ;
		
		return $aReq ;
	}
	
	public function createResponseDevice()
	{
		return new \org\jecat\framework\io\HtmlPrintStream() ;
	}
}

?>
