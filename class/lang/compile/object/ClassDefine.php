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
	
	private $aTokenName ;
	private $aTokenBody ;
}

?>