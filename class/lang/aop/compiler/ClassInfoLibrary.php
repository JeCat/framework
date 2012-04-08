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
namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\lang\oop\Package;
use org\jecat\framework\fs\File;

class ClassInfoLibrary extends Object{
	public function isA($sDividedClass , $sBaseClassName ){
		if( $sBaseClassName === $sDividedClass ){
			return true;
		}
		$aClassInfo = $this->classInfo($sDividedClass);
		
		if(false === $aClassInfo){
			return false;
		}
		
		foreach($aClassInfo->extendsIterator() as $sExtends){
			if( $this->isA( $sExtends , $sBaseClassName ) ){
				return true ;
			}
		}
		
		foreach($aClassInfo->implementsIterator() as $sInterface){
			if( $this->isA( $sInterface , $sBaseClassName ) ){
				return true ;
			}
		}
		
		return false;
	}
	
	public function classInfo($sClassName){
		$aClassInfo = $this->getClassInfo($sClassName) ;
		if( false === $aClassInfo ){
			$arrClassInfo = $this->generateClassInfo($sClassName) ;
			foreach($arrClassInfo as $aClassInfo){
				$this->saveClassInfo($aClassInfo->fullName() , $aClassInfo );
			}
			
			$aClassInfo = $this->getClassInfo($sClassName);
			return $aClassInfo ;
		}else{
			return $aClassInfo ;
		}
	}
	
	private function getClassInfo($sClassName){
		if(isset($this->arrClassInfo[$sClassName]) ){
			return $this->arrClassInfo[$sClassName] ;
		}else{
			return false ;
		}
	}
	
	private function saveClassInfo($sClassName , ClassInfo $aClassInfo){
		$this->arrClassInfo[$sClassName] = $aClassInfo ;
	}
	
	private function generateClassInfo($sClassName){
		$aClassLoader = ClassLoader::singleton() ;
		
		$sFilePath = $aClassLoader->searchClass($sClassName,Package::nocompiled) ;
		if( empty($sFilePath)){
			return array();
		}
		
		$aFile = new File($sFilePath);
		
		$aInheritInfoDetector = InheritInfoDetector::singleton() ;
		$arrClassInfo = $aInheritInfoDetector->detect($aFile->openReader());
		
		return $arrClassInfo ;
	}
	
	private $arrClassInfo = array() ;
}


