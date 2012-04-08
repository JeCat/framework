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
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\util\DataSrc;

class CLRequest extends Request
{
	public function __construct()
	{
		global $argv ;
		$this->addChild( new DataSrc($argv,true) ) ;
	}
	
	/**
	 * 定义命令行参数的名称
	 * 
	 * @param	$sName				string			参数名称
	 * @param	$bRequireValue	bool			
	 * @param	$aliases			string,array	参数别名
	 */
	public function defineParam($sName,$aliases=array(),$bRequireValue=true,$defaultValue=null)
	{
		$aliases  = (array)$aliases ;
		
		$this->arrParamNames[$sName] = array(
				$sName, $aliases, $bRequireValue, $defaultValue
		) ;
		
		$this->arrAliases[$sName] = & $this->arrParamNames[$sName] ;
		foreach($aliases as $sAlias)
		{
			$this->arrAliases[$sAlias] = & $this->arrParamNames[$sName] ;
		}
	}
	
	public function reparseParams()
	{
		if( empty($_SERVER['argv']) or !is_array($_SERVER['argv']) )
		{
			return ;
		}
		
		$this->clear() ;
		
		// set default value
		foreach($this->arrParamNames as $arrParam)
		{
			if($arrParam[3]!==null)
			{
				$this->set($arrParam[0], $arrParam[3]) ;
			}
		}
		
		// set input value
		$nUnnameParamIdx = 0 ;
		for( reset($_SERVER['argv']); $sParam=current($_SERVER['argv']); next($_SERVER['argv']) )
		{
			$sValue = null ;
			
			// like: --param=value
			if( preg_match('/^\-\-./',$sParam) )
			{
				if( strstr($sParam,'=')!==false )
				{
					list($sName,$sValue) = explode('=',$sParam,2) ;
				}
				
				else 
				{
					$sName = $sParam ;
						
					if( isset($this->arrAliases[$sName]) )
					{
						// 要求参数值
						if($this->arrAliases[$sName][2])
						{
							$sValue = next($_SERVER['argv']) ;
						}
						
						// 改参数不需要值
						else 
						{
							$sValue = $sName ; 
						}
					}
				}
				
				if( isset($this->arrAliases[$sName]) )
				{
					$this->set($this->arrAliases[$sName][0],$sValue) ;
				}
				else 
				{
					$this->set($sName,$sValue) ;
				}
			}
			
			// like: -p value
			else if( preg_match('/^\-./',$sParam) )
			{
				$sValue = $sParam ;
					
				if( isset($this->arrAliases[$sParam]) )
				{
					// 要求参数值
					if($this->arrAliases[$sParam][2])
					{
						$sValue = next($_SERVER['argv']) ;
					}
				}
				
				$this->set($sParam,$sValue) ;
			}
			
			// 普通
			else 
			{
				$this->set($nUnnameParamIdx++,$sParam) ;
			}
		}
	}
	
	
	private $arrParamNames = array() ; 
	
	private $arrAliases = array() ; 
}
