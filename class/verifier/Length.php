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
namespace org\jecat\framework\verifier ;

use org\jecat\framework\util\String;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\lang\Object;

class Length extends Object implements IVerifier, IBean
{
	public function __construct($nMinLen=-1,$nMaxLen=-1,$bByByte=true)
	{
		$this->nMinLen = $nMinLen ;
		$this->nMaxLen = $nMaxLen ;
		$this->bByByte = $bByByte;
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
	 * ==字符长度校验器(Length)==
	 * =Bean配置数组=
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |max
	 * |int
	 * |无
	 * |可选
	 * |字符个数上限,为空即不限
	 * |-- --
	 * |min
	 * |int
	 * |无
	 * |可选
	 * |字符个数下限,单位字节(px),为空即不限
	 * |-- --
	 * |byte
	 * |bool
	 * |true
	 * |可选
	 * |字符个数按照字节计算还是字符计算,主要用来解决中文长度问题.为true时按字节计算(中文算3个字符),false时按照字符计算(中文算1个字符)
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if( !empty($arrConfig['min']) )
		{
			$this->nMinLen = (int)$arrConfig['min'] ;
		}
		if( !empty($arrConfig['max']) )
		{
			$this->nMaxLen = (int)$arrConfig['max'] ;
		}
		if( !empty($arrConfig['byte']) )
		{
			$this->bByByte = (bool)$arrConfig['byte'] ;
		}
		$this->arrConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrConfig;
	}
	
	public function verify($data,$bThrowException)
	{
		if( is_array($data) )
		{
			$nLen = count($data) ;
		}
		
		else 
		{
			if($this->bByByte){
				$nLen = strlen($data) ;
			}else{
				$sData = new String($data);
				$nLen = $sData->length(false);
			}
		}
		
		if( $this->nMinLen>=0 and $this->nMinLen>$nLen )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能小于%d",array($this->nMinLen)) ;
			}
			return false ;
		}
		if( $this->nMaxLen>=0 and $this->nMaxLen<$nLen )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能大于%d",array($this->nMaxLen)) ;
			}
			return false ;
		}
		if( !$this->bAllowEmpty and $nLen<=0 )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能为空") ;
			}
			return false ;
		}
		
		return true ;
	}

	private $arrConfig = array();
	private $bAllowEmpty = true ;
	private $nMaxLen = -1 ;
	private $nMinLen = -1 ;
	private $bByByte = true ;
}
