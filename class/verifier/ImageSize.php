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
//  正在使用的这个版本是：0.8
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

class ImageSize extends Object implements IVerifier, IBean
{
	public function __construct($nMaxWidth = -1, $nMaxHeight = -1, $nMinWidth = -1, $nMinHeight = -1)
	{
		$this->nMaxWidth = ( int ) $nMaxWidth;
		$this->nMaxHeight = ( int ) $nMaxHeight;
		$this->nMinWidth = ( int ) $nMinWidth;
		$this->nMinHeight = ( int ) $nMinHeight;
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
	 * ==图片宽高校验器(ImageSize)==
	 * =Bean配置数组=
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |maxWidth
	 * |int
	 * |无
	 * |可选
	 * |宽度上限,单位字节(px),为空即不限
	 * |-- --
	 * |maxHeight
	 * |int
	 * |无
	 * |可选
	 * |高度上限,单位字节(px),为空即不限
	 * |-- --
	 * |minWidth
	 * |int
	 * |无
	 * |可选
	 * |宽度下限,单位字节(px),为空即不限
	 * |-- --
	 * |minHeight
	 * |int
	 * |无
	 * |可选
	 * |高度下限,单位字节(px),为空即不限
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if (! empty ( $arrConfig ['maxWidth'] ))
		{
			$this->nMaxWidth = ( int ) $arrConfig ['maxWidth'];
		}
		if (! empty ( $arrConfig ['maxHeight'] ))
		{
			$this->nMaxHeight = ( int ) $arrConfig ['maxHeight'];
		}
		if (! empty ( $arrConfig ['minWidth'] ))
		{
			$this->nMinWidth = ( int ) $arrConfig ['minWidth'];
		}
		if (! empty ( $arrConfig ['minHeight'] ))
		{
			$this->nMinHeight = ( int ) $arrConfig ['minHeight'];
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
		$nImageWidth = $nImageInfo [1];
		$nImageHeight = $nImageInfo [0];
		if ($this->nMinHeight != - 1 && $nImageHeight < $this->nMinHeight)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片太短了,应该大于:" . $this->nMinHeight . '像素,现在为:' . $nImageHeight . '像素' );
			}
			return false;
		}
		if ($this->nMaxHeight != - 1 && $nImageHeight > $this->nMaxHeight)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片太长了,应该小于:" . $this->nMaxHeight . '像素,现在为:' . $nImageHeight . '像素' );
			}
			return false;
		}
		if ($this->nMinWidth != - 1 && $nImageWidth < $this->nMinWidth)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片太窄了,应该大于:" . $this->nMinWidth . '像素,现在为:' . $nImageWidth . '像素' );
			}
			return false;
		}
		if ($this->nMaxWidth != - 1 && $nImageWidth > $this->nMaxWidth)
		{
			if ($bThrowException)
			{
				throw new VerifyFailed ( "图片太宽了,应该小于:" . $this->nMaxWidth . '像素,现在为:' . $nImageWidth . '像素' );
			}
			return false;
		}
		
		return true;
	}
	private $arrBeanConfig = array ();
	private $nMaxWidth = -1;
	private $nMaxHeight = -1;
	private $nMinWidth = -1;
	private $nMinHeight = -1;
}

