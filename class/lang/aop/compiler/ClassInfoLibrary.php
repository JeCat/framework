<?php
namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\oop\ClassLoader ;
use org\jecat\framework\lang\oop\Package ;
use org\jecat\framework\fs\File ;

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

