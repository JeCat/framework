<?php
namespace jc\ui ;

use jc\util\HashTable;

use jc\util\DataSrc;
use jc\lang\Object;

abstract class FactoryBase extends Object implements IFactory 
{
	/**
	 * (static 延迟绑定，在子类生效)
	 * @return IFactory
	 */
	static public function singleton()
	{
		if( !self::$aGlobalInstance )
		{
			$sClassName = get_called_class() ;
			self::$aGlobalInstance = new $sClassName() ;
		}
		
		return self::$aGlobalInstance ;
	}
	
	/**
	 * return IUI
	 */
	public function create()
	{
		$aApp = $this->application(true) ;
		
		$aUI = $this->createUI() ;
		$aUI->setApplication($aApp) ;
		
		// for SourceFileManager
		if( $aSrcMgr=$this->createSourceFileManager() )
		{
			$aSrcMgr->setApplication($aApp) ;
		}
		$aUI->setSourceFileManager( $aSrcMgr ) ;
		
		// for Compiler
		if( $aCompiler=$this->createCompiler() )
		{
			$aCompiler->setApplication($aApp) ;
		}
		$aUI->setCompiler( $aCompiler ) ;
		
		// for Variables
		if( $aVars=new HashTable() )
		{
			$aVars->setApplication($aApp) ;
		}
		$aUI->setVariables( $aVars ) ;
		
		// for display device
		if( $aDev=$this->createDisplayDevice() )
		{
			$aDev->setApplication($aApp) ;
		}
		$aUI->setDisplayDevice( $aDev ) ;

		return $aUI ;
	}
	
	/**
	 * return IUI
	 */
	public function createUI()
	{
		return new UI() ;
	}

	/**
	 * return ICompiler
	 */
	public function createSourceFileManager()
	{
		return new SourceFileManager() ;
	}
	
	static protected $aGlobalInstance ;
}

?>