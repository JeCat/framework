<?php
namespace org\jecat\framework\db\sql2\parser ;


use org\jecat\framework\lang\Object;

/**
 * @return Parser
 */
class BaseParserFactory extends Object
{
	public function create(Dialect $aDialect=null)
	{
		if(!$aDialect)
		{
			$aDialect = Dialect::singleton() ;
		}
		
		
		
		$aParser = new Parser($aDialect) ;
		
		return $aParser->setDialect($aDialect)
			
				->addChildState(
					self::createParser('SubSQLParser',$aDialect,'select') 
						//->addChildState( self::createParser('KeywordsParser',$aDialect) )
						->addChildState( self::createParser('AliasParser',$aDialect) )
						->addChildState( self::createParser('NameSeparatorParser',$aDialect) )
						->addChildState( self::createParser('ColumnParser',$aDialect) )
				)
				->addChildState(
					self::createParser('SubSQLParser',$aDialect,'from') 
						->addChildState( self::createParser('SubQueryParser',$aDialect,$aParser) )
						->addChildState( self::createParser('AliasParser',$aDialect) )
						->addChildState( self::createParser('NameSeparatorParser',$aDialect) )
						->addChildState(self::createParser('TableParser',$aDialect))
						
						->addChildState(
								self::createParser('TableJoinParser',$aDialect)
									->addChildState( self::createParser('AliasParser',$aDialect) )
									->addChildState( self::createParser('NameSeparatorParser',$aDialect) )
									->addChildState(self::createParser('TableParser',$aDialect))
						)
				)	
				->addChildState(
					self::createParser('SubSQLParser',$aDialect,'where') 
						->addChildState(self::createParser('ColumnParser',$aDialect))
				) ;
	} 
	
	/**
	 * @return AbstractParserState
	 */
	static private function createParser($sClass,Dialect $aDialect,$argvs=null,$sNamespace=__NAMESPACE__)
	{
		$sClass = $sNamespace.'\\'.$sClass ;
		$aParser = $sClass::createInstance($argvs) ;
		
		$aParser->setDialect($aDialect) ;
		
		return $aParser ;
	}
}

?>