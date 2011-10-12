<?php 

namespace jc\db\sql ;


class Union extends Select
{
	public function __construct()
	{}
	
	/**
	 * 
	 * 可并两个 Select（或Union）sql语句为一个Union sql语句
	 * @param Select $aStatementsA
	 * @param Select $aStatementsB
	 * @param bool $bCreateUnionAlways=true
	 */
	static public function unionSelect(Select $aStatementsA,Select $aStatementsB,$bCreateUnionAlways=true)
	{
		$aUnion = null ;
		$arrStatements = array() ;
		if( $bCreateUnionAlways )
		{
			$aUnion = new Union() ;
			$arrStatements = array($aStatementsA,$aStatementsB) ;
		}
		
		else if( $aStatementsA instanceof self )
		{
			$aUnion = $aStatementsA ;
			$arrStatements = array($aStatementsB) ;
		}
		
		else if( $aStatementsB instanceof self )
		{
			$aUnion = $aStatementsB ;
			$arrStatements = array($aStatementsA) ;
		}
		
		else 
		{
			$aUnion = new Union() ;
			$arrStatements = array($aStatementsA,$aStatementsB) ;
		}
		
		foreach ($arrStatements as $aStatement)
		{
			$aUnion->add($aStatement) ;
		}
		
		return $aUnion ;
	}
	
	public function merge(Union $aStatements)
	{
		foreach($aStatements->iterator() as $aSelect)
		{
			$this->add($aSelect) ;
		}
	}
	
	/**
	 * 
	 * @return void
	 */
	public function add(Select $aStatement)
	{
		if( $aStatement instanceof self )
		{
			$this->merge($aStatement) ;
		}
		else 
		{
			if( !in_array($aStatement,$this->arrSelectStatements) )
			{
				$this->arrSelectStatements[] = $aStatement ;
			}
		}
	}
	
	/**
	 * 
	 * @return jc\pattern\iterate\INonlinearIterator
	 */
	public function iterator()
	{
		return new \jc\pattern\iterate\ArrayIterator($this->arrSelectStatements) ;
	}
	
	
	
	/**
	 * Enter description here ...
	 * 
	 * @var array
	 */
	private $arrSelectStatements = array() ;
}

?>