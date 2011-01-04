<?php 
///////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JCAT PHP框架的一部，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008 JeCat.cn(http://team.JeCat.cn)
//
//
//  JCAT PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JCAT 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.6.0 / SVN信息: $Id: class._JCAT_MVCObjectContainer.php 2386 2010-12-03 07:33:55Z alee $
//
//
//
//  相关的链接：
//    [主页] http://jcat.JeCat.cn
//    [下载(HTTP)] http://code.google.com/p/jcat-php/downloads/list
//    [下载(svn)] svn checkout http://jcat-php.googlecode.com/svn/0.6/truck/ JCAT0.6
//    [在线文档] http://jcat.JeCat.cn/document
//    [社区] http://bbs.jecat.cn
//  不很相关：
//    [MP3] http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD] http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
///////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/



class CompositeObject extends ContainedableObject implements IContainer
{
	public function setChildTypes(array $arrTypes) {
		// TODO Auto-generated method stub
		
	}
	
	// implement for IContainer //////////////////
	public function addChild(IContainedable $aChild) {
		// TODO Auto-generated method stub
		
	}

	public function removeChild($Child) {
		// TODO Auto-generated method stub
		
	}

	public function clearChildren() {
		// TODO Auto-generated method stub
		
	}

	public function child($sName) {
		// TODO Auto-generated method stub
		
	}

	public function findChildInFamily($sName) {
		// TODO Auto-generated method stub
		
	}


	public function childrenIterator($Types = null) {
		// TODO Auto-generated method stub
		
	}
	
	

	private $arrChildren = array() ;
}

?>