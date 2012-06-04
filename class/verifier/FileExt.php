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

class FileExt extends Object implements IVerifier,IBean {
	/**
	 * $arrExt 扩展名列表
	 * $bAllow 为true时arrExt意为允许上传的扩展名列表,false时arrExt意为不允许上传的扩展名列表
	 * @param array $arrExt
	 * @param boolean $bAllow
	 */
	public function __construct($arrExt,$bAllow = true) {
		$this->setExt($arrExt);
		$this->setAllow($bAllow);
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
	 * ==文件类型校验器(FileExt)==
	 * =Bean配置数组=
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |exts
	 * |array
	 * |无
	 * |必须
	 * |扩展名名单
	 * |-- --
	 * |allow
	 * |boolean
	 * |true
	 * |可选
	 * |为true时arrExt意为允许上传的扩展名列表,false时arrExt意为不允许上传的扩展名列表
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if (! empty ( $arrConfig ['exts'] ))
		{
			$this->setExt ( ( array ) $arrConfig ['exts'] );
		}
		if (! empty ( $arrConfig ['allow'] ))
		{
			$this->setAllow ( $arrConfig ['allow'] );
		}
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function setExt($arrExt){
		if( !is_array($arrExt)){
			array_push($this->arrExt , $arrExt);
		}else{
			$this->arrExt = $arrExt;
		}
	}
	
	public function setAllow($bAllow){
		$this->bAllow = (boolean)$bAllow;
	}
	
	public function verify($data, $bThrowException) {
		return $this->verifyFile($data, $bThrowException);
	}
	
	public function verifyFile(File $file, $bThrowException) {
		if (! $file instanceof File) {
			throw new Exception ( __CLASS__ . "的" . __METHOD__ . "传入了错误的data参数(得到的参数是%s类型)", array ( Type::detectType($file) ) );
		}
		$nFileExt = $file->extname() ;
		if ( ($this->bAllow && !in_array($nFileExt, $this->arrExt)) || (!$this->bAllow && in_array($nFileExt, $this->arrExt)) ) {
			if ($bThrowException) {
				throw new VerifyFailed ( "不允许上传的文件类型:".$nFileExt );
			}
			return false;
		}
		return true;
	}
	private $arrBeanConfig = array();
	private $arrExt = array();
	private $bAllow = true;
}

