<?php

namespace jc\compile ;

use jc\util\String;
use jc\io\IOutputStream;
use jc\io\IInputStream;
use jc\compile\object\IObject;
use jc\pattern\composite\IContainer;
use jc\pattern\composite\Container;
use jc\lang\Object as JcObject ;

class Compiler extends JcObject 
{
	public function compile(IInputStream $aSourceStream,IOutputStream $aCompiledStream)
	{
		$aObjectContainer = new Container('jc\\compile\\object\\IObject') ;
		
		// 扫描 tokens
		$this->scan($aSourceStream, $aObjectContainer) ;
		
		// 解析
		foreach($this->interpreters()->iterator() as $aInterpreter)
		{
			$aInterpreter->analyze($aObjectContainer) ;
		}
		
		// 编译
		foreach($aObjectContainer->iterator() as $aObject)
		{
			for( $sClassName=get_class($aObject); $sClassName; $sClassName=get_parent_class($sClassName) )
			{
				if( empty($this->mapGeneratorClasses[$sClassName]) )
				{
					continue ;
				}
				
				foreach($this->mapGeneratorClasses[$sClassName] as $sGeneratorClass)
				{
					$aGenerator = $this->generator($sGeneratorClass) ;
					
					$aGenerator->generateTargetCode($aObject) ;
				}
			}
		}
		
		// 保存到编译文件中
		foreach($aObjectContainer->iterator() as $aObject)
		{
			$aCompiledStream->write($aObject->targetCode) ;
		}
	}
	
	protected function scan(IInputStream $aSourceStream,IContainer $aObjectContainer)
	{
		$aSource = new String() ;
		$aSourceStream->readInString($aSource,$nBytes) ;
		
		$arrTokens = token_get_all($aSource) ;
		foreach($arrTokens as $arrOneToken)
		{
			print_r($arrOneToken) ;
		}
	}
	
	/**
	 * @return jc\util\IContainer
	 */
	public function interpreters()
	{
		if( !$this->aInterpreters )
		{
			$this->aInterpreters = new Container(__NAMESPACE__.'\\IInterpreter') ; 
		}
		
		return $this->aInterpreters ;
	}
	
	public function registerGenerator($sObjectClass,$sGeneratorClass,array $arrCreateArgs=array())
	{
		if( !in_array($sGeneratorClass, $this->mapGeneratorClasses[$sObjectClass]) )
		{
			$this->mapGeneratorClasses[$sObjectClass][] = $sGeneratorClass ;
		}
	}
	
	public function unregisterGenerator($sObjectClass,$sGeneratorClass)
	{
		if( ($nPos=array_search($this->mapGeneratorClasses[$sObjectClass],$sGeneratorClass))!==false )
		{
			unset($this->mapGeneratorClasses[$sObjectClass][$nPos]) ;
		}
	}
	
	public function generator($sGeneratorClass)
	{
		if( empty($this->arrGenerators[$sGeneratorClass]) )
		{
			$this->arrGenerators[$sGeneratorClass] = $sGeneratorClass::singleton() ;
		}
		
		return $this->arrGenerators[$sGeneratorClass] ;
	}

	private $aInterpreters ;
	
	private $mapGeneratorClasses = array() ;
	
	private $arrGenerators = array() ;
}
?>