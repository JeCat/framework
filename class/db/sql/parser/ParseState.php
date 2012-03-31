<?php
namespace org\jecat\framework\db\sql\parser ;

class ParseState
{
	public $arrStatement ;
	public $arrTree ;
	public $arrTokenList ;
	public $aCurrentParser ;
	public $arrParserStack ;
	
	public function __construct(array & $arrTokenList,AbstractParser $aParser)
	{
		$this->arrStatement = array (
				'expr_type' => 'query' ,
				'subtree' => array()
		) ;
		$this->arrTree =& $this->arrStatement['subtree'] ;
		$this->arrTokenList =& $arrTokenList ;
		$this->pushParser($aParser) ;
	}
	
	/**
	 * @return AbstractParser
	 */
	public function currentParser()
	{
		return $this->aCurrentParser ;
	}
	
	public function pushParser(AbstractParser $aParser)
	{
		$this->arrParserStack[] = $aParser ;
		$this->aCurrentParser = $aParser ;
	}
	
	/**
	 * @return AbstractParser
	 */
	public function popParser()
	{
		$aParser = array_pop($this->arrParserStack) ;
		$this->aCurrentParser = end($this->arrParserStack) ;
		return $aParser ;
	}
}

?>