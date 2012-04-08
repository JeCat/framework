<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/

namespace org\jecat\framework\lang\compile ;

use org\jecat\framework\fs\File;
use org\jecat\framework\lang\oop\Package;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\util\String;
use org\jecat\framework\lang\Object as JcObject;

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
	
	public function compile($sSourceFile,$sCompiledFile)
	{		
		// 扫描 tokens
		$aTokenPool = $this->scan($sSourceFile) ;
		
		// 解释
		$this->interpret($aTokenPool) ;
		
		// 生成
		$this->generate($aTokenPool) ;
		
		// 编译结果写入文件
		$aCompiledFile = new File($sCompiledFile) ;
		$aCompiledStream = $aCompiledFile->openWriter() ;
		foreach($aTokenPool->iterator() as $aObject)
		{
			$aCompiledStream->write($aObject->targetCode()) ;
		}
	}

	/**
	 * @return org\jecat\framework\lang\compile\object\TokenPool
	 */
	public function scan($sSourceFile)
	{
		$aSource = new String() ;
		$aSourceFile = new File($sSourceFile) ;
		$aSourceStream = $aSourceFile->openReader()->readInString($aSource) ;
		
		$aTokenPool = $this->createTokenPool($sSourceFile) ;
	
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
		foreach($this->arrInterpreters as $name=>$v)
		{
			$interpreter = $this->interpreter($name) ;
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
		if( is_string( $sInterpreterClass ) ){
			if( !isset($this->arrInterpreters[$sInterpreterClass] ) )
			{
				$this->arrInterpreters[$sInterpreterClass] = null ;
			}
		}else if(is_object( $sInterpreterClass ) ) {
			$aObject = $sInterpreterClass ;
			$sInterpreterClass = get_class($aObject);
			
			if( !isset($this->arrInterpreters[$sInterpreterClass] ) )
			{
				$this->arrInterpreters[$sInterpreterClass] = $aObject ;
			}
		}
	}

	public function unregisterInterpreter($sInterpreterClass)
	{
		if( isset($this->arrInterpreters[$sInterpreterClass] ) )
		{
			unset($this->arrInterpreters[$sInterpreterClass]);
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
	
	public function createTokenPool($sSourceFilepath)
	{
		return new TokenPool($sSourceFilepath) ;
	}
	
	
	private $sStrategySignature ;
	
	private $mapGeneratorClasses = array() ;

	private $arrInterpreters = array() ;
	
	private $arrGenerators = array() ;
	
	private $arrStrategySummaries = array() ;
	
	private $aCompiledPackage ;
	
	private $sCompiledFolderPath = '/data/compiled/class' ;
}

