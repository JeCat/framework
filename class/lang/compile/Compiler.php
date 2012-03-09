<?php

namespace org\jecat\framework\lang\compile ;

use org\jecat\framework\lang\oop\Package;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\system\Application;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\util\String;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\io\IInputStream;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\lang\compile\object\IObject;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\lang\Object as JcObject ;

class Compiler extends JcObject 
{
	/**
	 * @return org\jecat\framework\lang\oop\Package
	 */
	public function compiledPackage()
	{
		if(!$this->aCompiledPackage)
		{
			$sFolderPath = $this->sCompiledFolderPath.'/'.$this->strategySignature() ;
			$aFolder = Folder::singleton()->findFolder($sFolderPath,Folder::FIND_AUTO_CREATE) ;
			$this->aCompiledPackage = new Package('',$aFolder) ;
		}
		
		return $this->aCompiledPackage ;
	}
	
	public function setCompiledFolderPath($sFolderPath)
	{
		if($this->sCompiledFolderPath!=$sFolderPath)
		{
			$this->aCompiledPackage = null ;
		}
		
		$this->sCompiledFolderPath = $sFolderPath ;
	}
	
	public function compile(IInputStream $aSourceStream,IOutputStream $aCompiledStream)
	{
		// 扫描 tokens
		$aTokenPool = $this->scan($aSourceStream) ;
		
		// 解释
		$this->interpret($aTokenPool) ;
		
		// 生成
		$this->generate($aTokenPool) ;
		
		// 编译结果写入文件
		foreach($aTokenPool->iterator() as $aObject)
		{
			$aCompiledStream->write($aObject->targetCode()) ;
		}
	}

	/**
	 * @return org\jecat\framework\lang\compile\object\TokenPool
	 */
	public function scan(IInputStream $aSourceStream)
	{
		$aTokenPool = $this->createTokenPool() ;
	
		$aSource = new String() ;
		$aSourceStream->readInString($aSource) ;
		$nLine = 1 ;
		$nPosition = 1 ;
		
		$arrTokens = token_get_all($aSource) ;
		foreach($arrTokens as &$oneToken)
		{
			if( is_array($oneToken) )
			{
				if( $nLine != $oneToken[2] )
				{
					$nLine = $oneToken[2] ;
					$nPosition = 1 ;
				}
				
				$oneToken[3] = token_name($oneToken[0]) ;
				$aTokenPool->add(
					new Token($oneToken[0], $oneToken[1], $nPosition++, $nLine)
				) ;
			}
			else if( is_string($oneToken) )
			{
				$aTokenPool->add(
					new Token(T_STRING, $oneToken, $nPosition++, $nLine)
				) ; 
			}
		}
		
		return $aTokenPool ;
	}	
	
	
	public function interpret(TokenPool $aTokenPool)
	{		
		// 解析
		foreach($this->arrInterpreters as $interpreter)
		{
			if( is_string($interpreter) )
			{
				$interpreter = $this->interpreter($interpreter) ;
			}
			
			$interpreter->analyze($aTokenPool) ;
		}
		
		return ;
	}
	
	public function generate(TokenPool $aTokenPool)
	{
		// 编译
		foreach($aTokenPool->iterator() as $aObject)
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

					$aGenerator->generateTargetCode($aTokenPool,$aObject) ;
				}
			}
		}
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
		if( !isset($this->mapGeneratorClasses[$sObjectClass]) )
		{
			$this->mapGeneratorClasses[$sObjectClass] = array() ;
		}
		
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

	/**
	 * @return IGenerator
	 */
	public function generator($sGeneratorClass)
	{
		if( empty($this->arrGenerators[$sGeneratorClass]) )
		{
			$this->arrGenerators[$sGeneratorClass] = new $sGeneratorClass() ;
		}
		
		return $this->arrGenerators[$sGeneratorClass] ;
	}
	
	/**
	 * @return IInterpreter
	 */
	public function interpreter($sInterpreterClass)
	{
		if( empty($this->arrInterpreters[$sInterpreterClass]) )
		{
			$this->arrInterpreters[$sInterpreterClass] = new $sInterpreterClass() ;
		}
		
		return $this->arrInterpreters[$sInterpreterClass] ;
	}
	
	/**
	 * 根据编译器的编译规则生成一段"策略签名"，当编译规则更改后，用于识别失效的编译文件
	 */
	public function strategySignature($bRegenerate=false)
	{
		if( !$this->sStrategySignature or $bRegenerate )
		{
			$sStrategySummaries = '' ;
			foreach($this->arrStrategySummaries as &$summay)
			{
				$sStrategySummaries.= ($summay instanceof IStrategySummary)? $summay->strategySummary(): $summay ;
			}
			
			$this->setStrategySignature( md5($sStrategySummaries) ) ;
		}
		
		return $this->sStrategySignature ;
	}
	
	public function setStrategySignature($sStrategySignature)
	{
		// 重新提供 compiled folder
		if($sStrategySignature!=$this->sStrategySignature)
		{
			$this->aCompiledPackage = null ;
		}
		
		$this->sStrategySignature = $sStrategySignature ;
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
	
	public function createTokenPool()
	{
		return new TokenPool('org\\jecat\\framework\\lang\\compile\\object\\AbstractObject') ;
	}
	
	
	private $sStrategySignature ;
	
	private $mapGeneratorClasses = array() ;

	private $arrInterpreters = array() ;
	
	private $arrGenerators = array() ;
	
	private $arrStrategySummaries = array() ;
	
	private $aCompiledPackage ;
	
	private $sCompiledFolderPath = '/data/compiled/class' ;
}
?>