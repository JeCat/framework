<?php

namespace jc\compile ;

use jc\lang\Exception;

use jc\lang\Type;
use jc\compile\object\Token;
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
		$this->bCompiling = true ;
		
		$aObjectContainer = new Container('jc\\compile\\object\\AbstractObject') ;
		
		// 扫描 tokens
		$this->scan($aSourceStream, $aObjectContainer) ;
		
		// 解析
		foreach($this->arrInterpreters as $sInterpreterClass)
		{
			$sInterpreterClass::singleton()->analyze($aObjectContainer) ;
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
			$aCompiledStream->write($aObject->targetCode()) ;
		}
		
		$this->bCompiling = false ;
	}
	
	protected function scan(IInputStream $aSourceStream,IContainer $aObjectContainer)
	{
		$aSource = new String() ;
		$aSourceStream->readInString($aSource) ;
		
		$arrTokens = token_get_all($aSource) ;
		foreach($arrTokens as &$oneToken)
		{
			if( is_array($oneToken) )
			{
				$oneToken[3] = token_name($oneToken[0]) ;
				$aObjectContainer->add(
					new Token($oneToken[0], $oneToken[1], $oneToken[2]), null, true
				) ;
			}
			else if( is_string($oneToken) )
			{
				$aObjectContainer->add(
					new Token(T_STRING, $oneToken, 0), null, true
				) ; 
			}
		}
		
		return ;
	}
	
	
	public function registerInterpreter($sInterpreterClass)
	{
		if( !in_array($sInterpreterClass,$this->arrInterpreters) )
		{
			$this->arrInterpreters[] = $sInterpreterClass ;
		}
	}

	public function unregisterInterpreter($sInterpreterClass)
	{
		if( $nIdx=array_search($sInterpreterClass,$this->arrInterpreters) )
		{
			unset($this->arrInterpreters[$nIdx]) ;
		}
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
	
	/**
	 * 根据编译器的编译规则生成一段"策略签名"，当编译规则更改后，用于识别失效的编译文件
	 */
	public function strategySignature()
	{
		if( !$this->sStrategySignature )
		{
			$sStrategySummaries = '' ;
			foreach($this->arrStrategySummaries as &$summay)
			{
				$sStrategySummaries.= ($summay instanceof IStrategySummary)? $summay->strategySummary(): $summay ;
			}
			
			$this->sStrategySignature = md5( $sStrategySummaries ) ;
		}
		
		return $this->sStrategySignature ;
	}
	
	public function invalidStrategySignature()
	{
		$this->sStrategySignature = '' ;
	}
	
	/**
	 * 提供一个策略概要，用于生成策路签名
	 */
	public function addStrategySummary($summay)
	{
		if( !is_string($summay) and !($summay instanceof IStrategySummary) )
		{
			throw new Exception("strategy summary 必须为字符串或".__NAMESPACE__."\\IStrategySummary 对象") ;
		}
		
		$this->arrStrategySummaries[] = $summay ;
	}
	
	
	
	public function setCompiling($bCompiling=true)
	{
		$this->bCompiling = $bCompiling ;
	} 
	
	public function isCompiling()
	{
		return $this->bCompiling ;
	}
	
	private $bCompiling = false ;
	
	private $sStrategySignature ;

	private $arrInterpreters = array() ;
	
	private $mapGeneratorClasses = array() ;
	
	private $arrGenerators = array() ;
	
	private $arrStrategySummaries = array() ;
}
?>