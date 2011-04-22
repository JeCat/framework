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
		$aUI->setSourceFileManager( $this->sourceFileManager() ) ;
		
		// for Compiler
		$aUI->setCompiler( $this->compiler() ) ;
		
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

	/**
	 * return SourceFolderManager
	 */
	public function sourceFileManager()
	{
		if(!$this->aSourceFileManager)
		{
			if( $this->aSourceFileManager=$this->createSourceFileManager() )
			{
				$this->aSourceFileManager->setApplication($this->application(true)) ;
			}
		}
		
		return $this->aSourceFileManager ;
	}
	
	public function setSourceFileManager(SourceFileManager $aSrcMgr)
	{
		$this->aSourceFileManager = $aSrcMgr ;
	}
	
	/**
	 * return ICompiler
	 */
	public function compiler()
	{
		if( !$this->aCompiler )
		{
			if( $this->aCompiler=$this->createCompiler() )
			{
				$this->aCompiler->setApplication($this->application(true)) ;
			}
		}
		return $this->aCompiler ;
	}
	
	public function setCompiler(ICompiler $aCompiler)
	{
		$this->aCompiler = $aCompiler ;
	}
	
	static protected $aGlobalInstance ;
	
	private $aSourceFileManager ;
	private $aCompiler ;
}

?>