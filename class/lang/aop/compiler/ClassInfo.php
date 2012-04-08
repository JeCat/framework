<?php
namespace org\jecat\framework\lang\aop\compiler ;

class ClassInfo{
	public function name(){
		return $this->sName ;
	}
	
	public function ns(){
		return $this->sNs;
	}
	
	public function fullName(){
		return $this->ns().'\\'.$this->name() ;
	}
	
	const T_CLASS = 'class';
	const T_INTERFACE = 'interface';
	public function type(){
		return $this->sType ;
	}
	
	public function extendsIterator(){
		return new
			\org\jecat\framework\pattern\iterate\ArrayIterator(
				$this->arrExtends
			);
	}
	
	public function implementsIterator(){
		return new
			\org\jecat\framework\pattern\iterate\ArrayIterator(
				$this->arrImplements
			);
	}
	
	public function setName($sName){
		$this->sName = $sName ;
	}
	
	public function setNs($sNs){
		$this->sNs = $sNs ;
	}
	
	public function setType($sType){
		$this->sType = $sType ;
	}
	
	public function addExtends($sName){
		$this->arrExtends [] = $sName ;
	}
	
	public function addImplements($sName){
		$this->arrImplements [] = $sName ;
	}
	
	private $sName = '';
	private $sNs = '';
	private $sType = '';
	private $arrExtends = array();
	private $arrImplements = array();
}
