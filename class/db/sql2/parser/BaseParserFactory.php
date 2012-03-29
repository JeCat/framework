<?php
namespace org\jecat\framework\db\sql2\parser ;


use org\jecat\framework\lang\Object;

/**
 * @return Parser
 */
class BaseParserFactory extends Object
{	
	/**
	 * @return AbstractParser
	 */
	public function create($bShare=true,Dialect $aDialect=null,$sAction='statement')
	{
		if( $bShare and isset($this->arrShareParsers[$sAction]) )
		{
			return $this->arrShareParsers[$sAction] ;
		}
		
		if(!$aDialect)
		{
			$aDialect = Dialect::singleton() ;
		}
		
		switch($sAction)
		{
			case 'statement' :
				$aParser = self::createParserInstace('AbstractParser',$aDialect)
								->addChildState($this->create($bShare,$aDialect,'select'))
								->addChildState($this->create($bShare,$aDialect,'from'))
								->addChildState($this->create($bShare,$aDialect,'where')) 
								->addChildState($this->create($bShare,$aDialect,'group'))
								->addChildState($this->create($bShare,$aDialect,'order'))
								->addChildState($this->create($bShare,$aDialect,'limit')) ; 
				break ;
				
			case 'select' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'select')
								->addChildState($this->create($bShare,$aDialect,'column')) ;
				break ;
				
			case 'from' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'from') 
							->addChildState($this->create($bShare,$aDialect,'subquery') )
							->addChildState($this->create($bShare,$aDialect,'table'))
							->addChildState($this->create($bShare,$aDialect,'join'))  ;
				break ;
				
			case 'where' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'where')
								->addChildState($this->create($bShare,$aDialect,'table')) ;
				break ;
				
			case 'group' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'group')
								->addChildState($this->create($bShare,$aDialect,'table')) ;
				break ;
				
			case 'order' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'order')
								->addChildState($this->create($bShare,$aDialect,'table')) ;
				break ;
				
			case 'limit' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'limit')
								->addChildState($this->create($bShare,$aDialect,'table')) ;
				break ;
				
			case 'on' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'on') 
								->addChildState($this->create($bShare,$aDialect,'table')) ;
				break ;
				
			case 'using' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'using') 
								->addChildState($this->create($bShare,$aDialect,'table')) ;
				break ;
				
			case 'column' :
				$aParser = self::createParserInstace('ColumnParser',$aDialect) ;
				break ;
				
			case 'table' :
				$aParser = self::createParserInstace('TableParser',$aDialect) ;
				break ;
				
			case 'join' :
				$aParser = self::createParserInstace('TableJoinParser',$aDialect)
								->addChildState($this->create($bShare,$aDialect,'table'))
								->addChildState($this->create($bShare,$aDialect,'on'))
								->addChildState($this->create($bShare,$aDialect,'using')) ;
				break ;
				
			case 'subquery' :
				$aParser = self::createParserInstace('SubQueryParser',$aDialect) ;
				break ;
		}
		
		if( $bShare )
		{
			$this->arrShareParsers[$sAction] = $aParser ;
		}
		
		return $aParser ;
	}
	
	/**
	 * @return AbstractParser
	 */
	public function createParserSelect($bShare=true,Dialect $aDialect=null)
	{
		return $this->create($bShare,$aDialect,'select') ;
	}
	/**
	 * @return AbstractParser
	 */
	public function createParserFrom($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'from') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserWhere($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'where') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserOrder($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'order') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserGroup($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'group') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserLimit($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'limit') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserOn($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'on') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserUsing($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'Using') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserColumn($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'column') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserTable($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'table') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserSubquery($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'suquery') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserJoin($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'join') ; }
	
	
	/**
	 * @return AbstractParser
	 */
	static private function createParserInstace($sClass,Dialect $aDialect,$argvs=null,$sNamespace=__NAMESPACE__)
	{
		$sClass = $sNamespace.'\\'.$sClass ;
		$aParser = $sClass::createInstance($argvs) ;
		
		$aParser->setDialect($aDialect) ;
		
		return $aParser ;
	}
	
	private $arrShareParsers ;
}

?>