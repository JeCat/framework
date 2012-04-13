<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\resrc ;

use org\jecat\framework\system\Application;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class HtmlResourcePool extends Object
{
	const 	RESRC_JS = 1 ;
	const 	RESRC_CSS = 2 ;
	
	public function __construct(ResourceManager $aResourceManager=null)
	{
		parent::__construct() ;
		
		$this->aResourceManager = $aResourceManager? $aResourceManager: Application::singleton()->publicFolders() ;

		$this->addRequire('org.jecat.framework:style/style.css',self::RESRC_CSS) ;
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
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
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
		
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($arrResrcUrls) ;
	}
	
	public function __toString()
	{
		$sRet = '' ;
	
		try {
			foreach($this->iterator(self::RESRC_JS,false) as $sFilename)
			{
				$sUrl = $this->aResourceManager->find($sFilename,'*',true) ;
				if( $sUrl )
				{
					$sRet.= "<script type=\"text/javascript\" src=\"{$sUrl}\"></script>\r\n" ;
				}
				else 
				{
					$sRet.= "<script type=\"text/javascript\" comment=\"正在请求一个未知的JavaScript文件：{$sFilename}\"></script>\r\n" ;
				}
			}
			foreach($this->iterator(self::RESRC_CSS,false) as $sFilename)
			{
				$sUrl = $this->aResourceManager->find($sFilename,'*',true) ;
				if( $sUrl )
				{
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
