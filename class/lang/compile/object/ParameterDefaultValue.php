<?php
namespace org\jecat\framework\lang\compile\object ;

class ParameterDefaultValue extends Token
{
	public function __construct (){
		parent::__construct(0,null);
	}
	
	public function addSubToken($aToken){
		$this->arrSubToken [] = $aToken ;
	}
	
	public function __toString(){
		$str = '' ;
		foreach($this->arrSubToken as $subToken){
			$str .= (string)$subToken;
		}
		return $str ;
	}
	
	private $arrSubToken = array();
}
