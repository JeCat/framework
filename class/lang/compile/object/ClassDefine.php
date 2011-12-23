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
	
	public function setParentClassName($sParentClassName)
	{
		$this->sParentClassName = $sParentClassName ;
	}
	public function parentClassName()
	{
		return $this->sParentClassName ;
	}
	
	public function isAbstract(){
		return $this->bAbstract ;
	}
	public function setAbstract($bAbstract){
		$this->bAbstract = $bAbstract ;
	} 
	
	private $aTokenName ;
	private $sParentClassName ;
	private $aTokenBody ;
	private $bAbstract = false ;
}

?>
