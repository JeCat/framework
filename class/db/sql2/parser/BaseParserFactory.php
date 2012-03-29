<?php
namespace org\jecat\framework\db\sql2\parser ;


use org\jecat\framework\lang\Object;

/**
 * @return Parser
 */
class BaseParserFactory extends Object
{	
	public function create($bShare=true,Dialect $aDialect=null)
	{
		if( $bShare and $this->aShareParser )
		{
			return $this->aShareParser ;
		}
		
		if(!$aDialect)
		{
			$aDialect = Dialect::singleton() ;
		}
		
		$aParser = new AbstractParser($aDialect) ;
		
		$aParser->setDialect($aDialect)
		
				// SELECT 子句
				->addChildState(
					self::createParser('ClauseParser',$aDialect,'select') 
						->addChildState( self::createParser('ColumnParser',$aDialect) )
				)
				
				// FROM 子句
				->addChildState(
					self::createParser('ClauseParser',$aDialect,'from') 
						->addChildState( self::createParser('SubQueryParser',$aDialect,$aParser) )
						->addChildState(self::createParser('TableParser',$aDialect))
						
						// JOIN 子句
						->addChildState(
								self::createParser('TableJoinParser',$aDialect)
									->addChildState(self::createParser('TableParser',$aDialect))
									// ON 子句
									->addChildState(
										self::createParser('ClauseParser',$aDialect,'on') 
											->addChildState(self::createParser('ColumnParser',$aDialect))
									)
									// USING 子句
									->addChildState(
										self::createParser('ClauseParser',$aDialect,'using') 
											->addChildState(self::createParser('ColumnParser',$aDialect))
									)
						)
				)
				
				// WHERE 子句
				->addChildState(
						self::createParser('ClauseParser',$aDialect,'where')
							->addChildState(self::createParser('ColumnParser',$aDialect))
				)
				// GROUP 子句
				->addChildState(
					self::createParser('ClauseParser',$aDialect,'group') 
							->addChildState(self::createParser('ColumnParser',$aDialect))
				)
				// ORDER 子句
				->addChildState(
					self::createParser('ClauseParser',$aDialect,'order') 
							->addChildState(self::createParser('ColumnParser',$aDialect))
				)
				// LIMIT 子句
				->addChildState(
					self::createParser('ClauseParser',$aDialect,'limit') 
							->addChildState(self::createParser('ColumnParser',$aDialect))
				) ;
	
		
		if( $bShare )
		{
			$this->aShareParser = $aParser ;
		}
		
		
		return $aParser ;
	}
	
	/**
	 * @return AbstractParser
	 */
	static private function createParser($sClass,Dialect $aDialect,$argvs=null,$sNamespace=__NAMESPACE__)
	{
		$sClass = $sNamespace.'\\'.$sClass ;
		$aParser = $sClass::createInstance($argvs) ;
		
		$aParser->setDialect($aDialect) ;
		
		return $aParser ;
	}
	
	private $aShareParser ;
}

?>