<?php
namespace jc\resrc ;

use jc\system\Application;
use jc\lang\Exception;
use jc\lang\Object;

class HtmlResourcePool extends Object
{
	const 	RESRC_JS = 1 ;
	const 	RESRC_CSS = 2 ;
	
	public function __construct(ResourceManager $aResourceManager=null)
	{
		parent::__construct() ;
		
		$this->aResourceManager = $aResourceManager? $aResourceManager: Application::singleton()->publicFolders() ;

		$this->addRequire('jc:style/style.css',self::RESRC_CSS) ;
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
	 * @return ResourceManager
	 */
	public function resourceManager()
	{
		return $this->aResourceManager ;
	}
	public function setResourceManager(ResourceManager $aResourceManager)
	{
		$this->aResourceManager = $aResourceManager ;
	}
	
	/**
	 * @return jc\pattern\iterate\INonlinearIterator
	 */
	public function iterator($nType,$bUrl=true)
	{		
		$sFilePath = '' ;
				
		$arrResrcUrls = array() ;
		foreach( $this->arrResrcs[$nType] as $sFileName=>$nBeRequiredCount )
		{
			if(!$nBeRequiredCount)
			{
				continue ;
			}
			
			if( $bUrl )
			{
				$sResrcUrl = $this->aResourceManager->find($sFileName) ;
				if(!$sResrcUrl)
				{
					throw new Exception("正在请求一个未知的资源：%s",$sFileName) ;
				}
				
				$arrResrcUrls[] = $sResrcUrl ; 
			}
			else 
			{
				$arrResrcUrls[] = $sFileName ; 
			}
		}
		
		return new \jc\pattern\iterate\ArrayIterator($arrResrcUrls) ;
	}
	
	public function __toString()
	{
		$sRet = '' ;
	
		try {
			foreach($this->iterator(self::RESRC_JS,false) as $sFilename)
			{
				if( $aFile = $this->aResourceManager->find($sFilename) )
				{
					$sUrl = $aFile->httpUrl() ;
					$sRet.= "<script type=\"text/javascript\" src=\"{$sUrl}\"></script>\r\n" ;
				}
				else 
				{
					$sRet.= "<script type=\"text/javascript\" comment=\"正在请求一个未知的JavaScript文件：{$sFilename}\"></script>\r\n" ;
				}
			}
			foreach($this->iterator(self::RESRC_CSS,false) as $sFilename)
			{
				if( $aFile = $this->aResourceManager->find($sFilename) )
				{
					$sUrl = $aFile->httpUrl() ;
					$sRet.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$sUrl}\" />\r\n" ;
				}
				else 
				{
					$sRet.= "<link rel=\"stylesheet\" type=\"text/css\" comment=\"正在请求一个未知的CSS文件：{$sFilename}\" />\r\n" ;
				}
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
	
	private $aResourceManager ;
	
	private $aJsManager ;
	
	private $aCssManager ;
}

?>