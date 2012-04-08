<?php
namespace org\jecat\framework\lang\compile\object ;

class Parameter extends Token
{
	public function __construct()
	{
	}
	
	public function type(){
		return (string)$this->aTypeToken ;
	}
	
	public function typeToken(){
		return $this->aTypeToken;
	}
	
	public function setTypeToken($aTypeToken){
		$this->aTypeToken = $aTypeToken ;
	}
	
	public function isReference(){
		return $this->bReference ;
	}
	
	public function setReference($bReference){
		$this->bReference = $bReference ;
	}
	
	public function name(){
		return (string)$this->aNameToken ;
	}
	
	public function nameToken(){
		return $this->aNameToken;
	}
	
	public function setNameToken($aNameToken){
		$this->aNameToken = $aNameToken ;
		$this->cloneOf($aNameToken);
	}
	
	public function defaultValueToken(){
		return $this->aDefaultValueToken;
	}
	
	public function defaultValue(){
		return (string)$this->aDefaultValueToken;
	}
	
	public function setDefaultValueToken($aDefaultValueToken){
		$this->aDefaultValueToken = $aDefaultValueToken;
	}
	
	public function belongsFunction(){
		return $this->aBelongsFunctionToken;
	}
	
	public function setBelongsFunction($aFunctionToken){
		$this->aBelongsFunctionToken = $aFunctionToken;
	}
	
	private $aTypeToken = null ;
	private $bReference = false ;
	private $aNameToken = null ;
	private $aDefaultValueToken = null ;
	private $aBelongsFunctionToken = null;
}
