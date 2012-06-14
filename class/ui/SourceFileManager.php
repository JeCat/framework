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
//  正在使用的这个版本是：0.8
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

namespace org\jecat\framework\ui ;

use org\jecat\framework\locale\Locale;

use org\jecat\framework\fs\File;
use org\jecat\framework\resrc\ResourceManager;

class SourceFileManager extends ResourceManager implements \Serializable
{
	public function setCompiledFolderPath($sPath)
	{
		$this->sCompiledFolderPath = $sPath ;
	}
	public function compiledFolderPath()
	{
		return $this->sCompiledFolderPath ;
	}
	
	public function isCompiledValid(File $aSourceFile,File $aCompiledFile)
	{
		if($this->bForceCompile)
		{
			return false ;
		} 
		
		return $aCompiledFile->exists() and $aCompiledFile->length() and $aSourceFile->modifyTime()<=$aCompiledFile->modifyTime() ;
	}

	/**
	 * @return org\jecat\framework\fs\File
	 */
	public function findCompiled($sSourceFile,$sNamespace,$bAutoCreate=false)
	{
		$sPath = $this->compiledFolderPath() . '/' . $this->compileStrategySignture() . '/' . Locale::singleton()->localeName() . '/' . $sNamespace . '/' . $sSourceFile . '.php' ;
		$aFile = new File($sPath) ;
		
		if( $bAutoCreate and !$aFile->exists() )
		{
			$aFile->create() ;
		}
		return $aFile ;
	}
	
	public function setCompileStrategySignture($sCompileStrategySignture)
	{
		$this->sCompileStrategySignture = $sCompileStrategySignture ;
	} 
	public function compileStrategySignture()
	{
		if(!$this->sCompileStrategySignture)
		{
			$this->sCompileStrategySignture = md5(__CLASS__) ;
		}
		return $this->sCompileStrategySignture ;
	}
	
	public function isForceCompile()
	{
		return $this->bForceCompile ;
	}
	
	public function setForceCompile($bForceCompile)
	{
		$this->bForceCompile = $bForceCompile ;
	}

	public function serialize()
	{
		return serialize(array(
				'parent' => parent::serialize() ,	
				'sCompileStrategySignture' => $this->sCompileStrategySignture ,	
				'sCompiledFolderPath' => $this->sCompiledFolderPath ,	
		)) ;
	}
	
	public function unserialize($serialized)
	{
		$arrData = unserialize($serialized) ;
		
		parent::unserialize($arrData['parent']) ;
		$this->sCompileStrategySignture = $arrData['sCompileStrategySignture'] ;
		$this->sCompiledFolderPath = $arrData['sCompiledFolderPath'] ;
	}

	static public function makeTemplateSignature($sTemplate,$sNamespace=null)
	{
		return $sNamespace? md5($sNamespace.':'.$sTemplate): md5($sTemplate) ;
	}
	
	private $sCompileStrategySignture ;
	
	private $sCompiledFolderPath ;
	
	private $bForceCompile = false ;
	
}

