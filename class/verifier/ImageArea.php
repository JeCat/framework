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

use org\jecat\framework\bean\IBean;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;
use org\jecat\framework\fs\File;

class ImageArea extends Object implements IVerifier, IBean
{
	public function __construct($nMaxArea = -1, $nMinArea = -1)
	{
		$this->nMaxArea = ( int ) $nMaxArea;
		$this->nMinArea = ( int ) $nMinArea;
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
	 * ==图片面积校验器(ImageArea)==
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
	 * |面积上限,单位字节(px),为空即不限
	 * |-- --
	 * |min
	 * |int
	 * |无
	 * |可选
	 * |面积下限,单位字节(px),为空即不限
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if (! empty ( $arrConfig ['max'] ))
		{
			$this->nMaxArea = ( int ) $arrConfig ['max'];
		}
		if (! empty ( $arrConfig ['min'] ))
		{
			$this->nMinArea = ( int ) $arrConfig ['min'];
		}
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function verify($data, $bThrowException)
	{
		return $this->verifyFile ( $data, $bThrowException );
	}
	
	public function verifyFile(File $file, $bThrowException)
	{
		if (! $file instanceof File)
		{
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是%s类型)", array (Type::detectType ( $file ) ) );
		}
		//  TODO 纠正文件类型
		$nImageInfo = getImageSize ( $file );
		$nImageArea = $nImageInfo [1] * $nImageInfo [0];
		
		if ($this->nMaxArea != - 1 && $nImageArea > $this->nMaxArea)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片面积太大了,应该小于:" . $this->nMaxArea . ',现在为:' . $nImageArea );
			}
			return false;
		}
		if ($this->nMinArea != - 1 && $nImageArea < $this->nMinArea)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片面积太小了,应该大于:" . $this->nMinArea . ',现在为:' . $nImageArea );
			}
			return false;
		}
		return true;
	}
	private $arrBeanConfig = array();
	private $nMaxArea;
	private $nMinArea;
}

