<?php
namespace jc\resrc ;

use jc\resrc\UrlResourceManager;
use jc\lang\Exception;
use jc\lang\Object;

class HtmlResourcePool extends Object
{
	const 	RESRC_JS = 1 ;
	const 	RESRC_CSS = 2 ;
	
	public function __construct(UrlResourceManager $aJsManager=null, UrlResourceManager $aCssManager=null)
	{
		parent::__construct() ;
		
		$this->aJsManager = $aJsManager? $aJsManager: new UrlResourceManager() ;
		$this->aCssManager = $aCssManager? $aCssManager: new UrlResourceManager() ;
	}

	public function addRequire($sResrcFileName,$nType)
	{
		if( !isset($this->arrResrcs[$nType]) )
		{
			throw new Exception("遇到以外的资源类型:%s",$nType) ;
		}
		
		if( !array_key_exists($sResrcFileName,$this->arrResrcs[$nType]) )
		{
			$this->arrResrcs[$nType][ $sResrcFileName ] = 0 ;
		}
		
		$this->arrResrcs[$nType][ $sResrcFileName ] ++ ;
	}
	
	public function removeRequire($sResrcFileName,$nType)
	{
		if( !isset($this->arrResrcs[$nType]) )
		{
			throw new Exception("遇到以外的资源类型:%s",$nType) ;
		}
		
		if( !array_key_exists($sResrcFileName,$this->arrResrcs[$nType]) )
		{
			return ;
		}
		
		$this->arrResrcs[$nType][ $sResrcFileName ] -- ;
		
		if( $this->arrResrcs[$nType][ $sResrcFileName ]<=0 )
		{
			unset($this->arrResrcs[$nType][ $sResrcFileName ]) ;
		}		
	}
	
	/**
	 * @return UrlResourceManager
	 */
	public function javaScriptFileManager()
	{
		return $this->aJsManager ;
	}
	public function setJavaScriptFileManager(UrlResourceManager $aJsManager)
	{
		$this->aJsManager = $aJsManager ;
	}
	/**
	 * @return UrlResourceManager
	 */
	public function cssFileManager()
	{
		return $this->aCssManager ;
	}
	public function setCssFileManager(UrlResourceManager $aCssManager)
	{
		$this->aCssManager = $aCssManager ;
	}
	
	/**
	 * @return jc\pattern\iterate\INonlinearIterator
	 */
	public function iterator($nType)
	{		
		$sFilePath = '' ;
		if( $nType==self::RESRC_JS )
		{
			$aResrcMgr = $this->aJsManager ;
		}
		else if( $nType==self::RESRC_CSS )
		{
			$aResrcMgr = $this->aCssManager ;
		}
		else 
		{
			throw new Exception("遇到以外的资源类型:%s",$nType) ;
		}
		
		$arrResrcUrls = array() ;
		foreach( $this->arrResrcs[$nType] as $sFileName=>$nBeRequiredCount )
		{
			$sResrcUrl = $aResrcMgr->find($sFileName) ;
			if(!$sResrcUrl)
			{
				throw new Exception("正在请求一个未知的资源：%s",$sFileName) ;
			}
			
			$arrResrcUrls[] = $sResrcUrl ; 
		}
		
		return new \jc\pattern\iterate\ArrayIterator($arrResrcUrls) ;
	}
	
	public function __toString()
	{
		$sRet = '' ;
	
		try {
			foreach($this->iterator(self::RESRC_JS) as $sUrl)
			{
				$sRet.= "<script type=\"text/javascript\" src=\"{$sUrl}\"></script>\r\n" ;
			}
			foreach($this->iterator(self::RESRC_CSS) as $sUrl)
			{
				$sRet.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$sUrl}\" />\r\n" ;
			}
		}
		catch (Exception $e)
		{
			$sRet.= $e->message() ;
		}
		catch (\Exception $e)
		{
			$sRet.= $e->getMessage() ;
		}
		
		return $sRet ;
	}
	
	private $arrResrcs = array(
				self::RESRC_JS => array() ,
				self::RESRC_CSS => array() ,
	) ;
	
	private $aJsManager ;
	
	private $aCssManager ;
}

?>