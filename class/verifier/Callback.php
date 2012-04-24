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
namespace org\jecat\framework\verifier;

use org\jecat\framework\lang\Type;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\lang\Object;

class Callback extends Object implements IVerifier ,IBean
{
	public function __construct($fnCallback=null,array $arrArgvs=null)
	{
		$this->fnCallback = $fnCallback ;
		$this->arrArgvs = $arrArgvs ;
	}
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	
	/**
	 * @wiki /MVC模式/数据交换和数据校验/数据校验
	 * ==Email格式校验==
	 * 验证输入的Email格式的正确性
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if( !is_callable($arrConfig['callback']) )
		{
			throw new BeanConfException("callback 类型的校验器的 callback 属性必须是一个回调函数") ;
		}
		$this->fnCallback = $arrConfig['callback'] ;
		
		if( array_key_exists('argvs',$arrConfig) )
		{
			$this->arrArgvs = Type::toArray($arrConfig['argvs'],Type::toArray_normal) ;
		}
		
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function verify($data,$bThrowException)
	{
		$arrArgvs = array($data) ;
		if($this->arrArgvs)
		{
			$arrArgvs = array_merge($arrArgvs,$this->arrArgvs) ;
		}

		$fnCallback = $this->fnCallback ;
		if(!call_user_func_array($fnCallback,$arrArgvs))
		{
			if ($bThrowException) {
				throw new VerifyFailed ( "数据经过回调函数校验失败" );
			}
			return false;
		}
		return true ;
	}
	
	private $arrBeanConfig = array();
	
	private $fnCallback ;
	
	private $arrArgvs ;
}

