<?php
namespace jc\resrc\htmlresrc ;

use jc\resrc\UrlResourceManager;
use jc\lang\Exception;
use jc\lang\Object;

class HtmlResourcePool extends Object
{
	const 	RESRC_JS = 1 ;
	const 	RESRC_CSS = 2 ;
	
	public function __construct(UrlResourceManager $aJsManager, UrlResourceManager $aCssManager)
	{
		parent::__construct() ;
		
		$this->aJsManager = $aJsManager ;
		$this->aCssManager = $aCssManager ;
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
	 * @return \Iterator
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
		
		return new \ArrayIterator($arrResrcUrls) ;
	}
	
	public function __toString()
	{
		$sRet = '' ;
	
		foreach($this->iterator(self::RESRC_JS) as $sUrl)
		{
			$sRet.= "<script type=\"text/javascript\" src=\"{$sUrl}\"></script>\r\n" ;
		}
		foreach($this->iterator(self::RESRC_CSS) as $sUrl)
		{
			$sRet.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$sUrl}\" />\r\n" ;
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