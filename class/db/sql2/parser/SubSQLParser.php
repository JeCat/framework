<?php
namespace org\jecat\framework\db\sql2\parser ;


class SubSQLParser extends AbstractParserState
{
	public function __construct($sCommend)
	{
		$this->sCommend = strtoupper($sCommend) ;
		$this->sCommendLower = strtolower($sCommend) ;
	}

	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		if( $sToken===$this->sCommend )
		{
			return true ;	
		}
		else
		{
			return false ;
		}
	}
	
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		if($sToken!==$this->sCommend)
		{
			$aTokenTree->arrTree[] = $sToken ;
		}
	}
	
	public function active(& $sToken,TokenTree $aTokenTree)
	{
		$aTokenTree->arrTree[$this->sCommend] = array(
				'expr_type' => 'clause_' . $this->sCommendLower ,
				'subtree' => array() ,
		) ;
		
		$this->switchToSubTree($aTokenTree,$aTokenTree->arrTree[$this->sCommend]['subtree']) ;
	}
	public function finish(& $sToken,TokenTree $aTokenTree)
	{
		$this->restoreParentTree($aTokenTree) ;
	}
	
	private $sCommend ;
	private $sCommendLower ;
	
}

?>