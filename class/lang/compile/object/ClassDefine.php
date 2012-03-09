<?php
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

?>
