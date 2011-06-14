<?php
namespace jc\mvc\view\htmlresrc ;

use jc\util\IResourceManager;
use jc\util\UrlResourceManager;
use jc\ui\Object;

class HtmlResourcePoolFactory extends Object
{
	/**
	 * @return HtmlResourcePool
	 */
	public function create()
	{
		return new HtmlResourcePool(
			$this->javaScriptFileManager()
			, $this->cssFileManager()
		) ;
	}

	public function setJavaScriptFileManager(IResourceManager $aJsManager)
	{
		$this->aJsManager = $aJsManager ;
	}

	/**
	 * @return IResourceManager
	 */
	public function javaScriptFileManager()
	{
		if( !$this->aJsManager )
		{
			$this->aJsManager = new UrlResourceManager() ;
		}
		return $this->aJsManager ;
	}

	public function setCssFileManager(IResourceManager $aCssManager)
	{
		$this->aCssManager = $aCssManager ;
	}

	/**
	 * @return IResourceManager
	 */
	public function cssFileManager()
	{
		if( !$this->aCssManager )
		{
			$this->aCssManager = new UrlResourceManager() ;
		}
		return $this->aCssManager ;
	}

	private $aJsManager ;
	
	private $aCssManager ;
	
}

?>