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
namespace org\jecat\framework\lang\compile\object ;

class ClassDefine extends StructDefine
{
	public function __construct(Token $aToken, $aTokenName=null, Token $aTokenBody=null)
	{
		parent::__construct($aToken,$aTokenName,$aTokenBody) ;
		
		$this->setBelongsClass($this) ;
	}
	/**
	 * 返回正在定义的class的包括命名控件的完整名称
	 */
	public function fullName()
	{
		$aNamespace = $this->belongsNamespace() ;
		if($aNamespace)
		{
			return $aNamespace->name() . '\\' . $this->name() ;
		}
		else 
		{
			return $this->name() ;
		}
	}
	
	public function addParentClassName($sName){
		$this->arrParentClassNameList [] = $sName ;
	}
	public function parentClassNameIterator(){
		return
			new \org\jecat\framework\pattern\iterate\ArrayIterator(
				$this->arrParentClassNameList
			);
	}
	
	public function addImplementsInterfaceName($sName){
		$this->arrImplementsInterfaceNameList [] = $sName ;
	}
	public function implementsInterfaceNameIterator(){
		return
			new \org\jecat\framework\pattern\iterate\ArrayIterator(
				$this->arrImplementsInterfaceNameList
			);
	}
	
	public function isAbstract(){
		return $this->bAbstract ;
	}
	public function setAbstract($bAbstract){
		$this->bAbstract = $bAbstract ;
	}
	
	public function isInterface(){
		return $this->tokenType() === T_INTERFACE ;
	}
	public function isClass(){
		return $this->tokenType() === T_CLASS ;
	}
	
	private $aTokenName ;
	private $arrParentClassNameList=array() ;
	private $arrImplementsInterfaceNameList=array() ;
	private $aTokenBody ;
	private $bAbstract = false ;
}

