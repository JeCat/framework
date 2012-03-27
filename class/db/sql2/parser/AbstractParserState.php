<?php
namespace org\jecat\framework\db\sql2\parser ;

use org\jecat\framework\lang\Object;

abstract class AbstractParserState extends Object
{
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		$aTokenTree->arrTree[] = $sToken ;
	}	
	
	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		return false ;
	}
	
	public function examineStateFinish(& $sToken,TokenTree $aTokenTree)
	{
		return false ;
	}
	
	public function active(& $sToken,TokenTree $aTokenTree)
	{}
	public function finish(& $sToken,TokenTree $aTokenTree)
	{}
	public function sleep(& $sToken,TokenTree $aTokenTree)
	{}
	public function wakeup(& $sToken,TokenTree $aTokenTree)
	{}
	
	public function setParentState(AbstractParserState $aParentState)
	{
		$this->aParentState = $aParentState ;
		return $this ;
	}
	
	/**
	 * @return AbstractParserState
	 */
	public function parentState()
	{
		return $this->aParentState ;
	}
	
	/**
	 * @return AbstractParserState
	 */
	public function addChildState(AbstractParserState $aState)
	{
		$aState->setParentState($this) ;
		$this->arrStates[] = $aState ;
		return $this ;
	}
	
	/**
	 * @return AbstractParserState
	 */
	public function childStates()
	{
		return $this->arrStates ;
	}
	
	/**
	 * @return AbstractParserState
	 */
	public function setDialect(Dialect $aDialect)
	{
		$this->aDialect = $aDialect ;
		return $this ;
	}
	/**
	 * @return Dialect
	 */
	public function dialect()
	{
		return $this->aDialect ;
	}
	
	public function switchToSubTree(TokenTree $aTokenTree,& $arrCurrentTree)
	{
		$arrCurrentTree['tmp_parent_tree'] =& $aTokenTree->arrTree ;
		$aTokenTree->arrTree =& $arrCurrentTree ;
	}
	public function restoreParentTree(TokenTree $aTokenTree)
	{
		$arrTree =& $aTokenTree->arrTree['tmp_parent_tree'] ;
		unset($aTokenTree->arrTree['tmp_parent_tree']) ;
		$aTokenTree->arrTree =& $arrTree ;
	}
	
	
	
	protected function parseName($sToken)
	{
		if( is_numeric($sToken) ) 
		{
			return null ;
		}
		
		if( substr($sToken,0,1)==='`' and substr($sToken,-1)==='`' )
		{
			return substr($sToken,1,-1) ;
		}
	
		if( preg_match('/^[_\\-\\w]+$/',$sToken) )
		{
			return $sToken ;
		}
	
		return null ;
	}
	
	private $aParentState ;
	/**
	 * @var Dialect
	 */
	protected $aDialect ;
	protected $arrStates = array() ;
	
}

?>