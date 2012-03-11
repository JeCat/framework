<?php
namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\oop\ClassLoader ;
use org\jecat\framework\fs\File ;

class ClassInfoLibrary extends Object{
	public function isParent($sBaseClassName , $sDividedClass){
		$aClassInfo = $this->classInfo($sDividedClass);
		
		foreach($aClassInfo->extendsIterator() as $sExtends){
			if( $sExtends === $sBaseClassName ){
				return true ;
			}
		}
		
		foreach($aClassInfo->implementsIterator() as $sInterface){
			if( $sInterface === $sBaseClassName ){
				return true ;
			}
		}
		
		return false;
	}
	
	public function classInfo($sClassName){
		$aClassInfo = $this->getClassInfo($sClassName) ;
		if( null === $aClassInfo ){
			$arrClassInfo = $this->generateClassInfo($sClassName) ;
			foreach($arrClassInfo as $aClassInfo){
				$this->saveClassInfo($sClassName , $aClassInfo );
			}
		}
		return $aClassInfo ;
	}
	
	private function getClassInfo($sClassName){
		if(isset($this->arrClassInfo[$sClassName]) ){
			return $this->arrClassInfo[$sClassName] ;
		}else{
			return null ;
		}
	}
	
	private function saveClassInfo($sClassName , ClassInfo $aClassInfo){
		$this->arrClassInfo[$sClassName] = $aClassInfo ;
	}
	
	private function generateClassInfo($sClassName){
		$aClassLoader = ClassLoader::singleton() ;
		
		$sFilePath = $aClassLoader->searchClass($sClassName) ;
		
		$aFile = new File($sFilePath);
		
		$aInheritInfoDetector = InheritInfoDetector::singleton() ;
		$aClassInfo = $aInheritInfoDetector->detect($aFile->openReader());
		
		return $aClassInfo ;
	}
	
	private $arrClassInfo = array() ;
}

