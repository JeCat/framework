<?php
namespace jc\lang\oop ;

use jc\lang\Exception;
use jc\fs\IFile;
use jc\fs\IFolder;

class Package 
{		
	public function __construct($sNamespace,IFolder $aFolder=null)
	{
		$this->setNamespace($sNamespace) ;
		
		$this->aFolder = $aFolder ;
		
		// OOXX.php
		$this->addClassFilenameWrapper(function ($sClassName){ return "{$sClassName}.php" ; }) ;		
		
		// OOXX.class.php
		// $this->addClassFilenameWrapper(function ($sClassName){ return "{$sClassName}.class.php" ; }) ;
		
		// class.OOXX.php
		// $this->addClassFilenameWrapper(function ($sClassName){ return "class.{$sClassName}.php" ; }) ;
	}

	/**
	 * @return jc\fs\IFolder
	 */
	public function folder()
	{
		return $this->aFolder ;
	}
	
	public function setFolder(IFolder $aFolder)
	{
		$this->aFolder = $aFolder ;
	}

	/**
	 * (namespace 是 php 的关键词不能做为函数名)
	 */
	public function ns()
	{
		return substr($this->sNamespace,0,-1) ;
	}
	
	protected function setNamespace($sNamespace)
	{
		if( substr($sNamespace,0,1)=='\\' )
		{
			if( strlen($sNamespace)>1 )
			{
				$sNamespace = substr($sNamespace,1) ;
			}
			else 
			{
				$sNamespace = '' ;
			}
		}
		
		$this->sNamespace = $sNamespace.'\\' ;
		$this->nNamespaceLen = strlen($this->sNamespace) ;
	}

	public function searchClass($sClassName)
	{
		if(!$this->aFolder)
		{
			return null ;
		}
		
		if( list($sInnerFolderPath,$sClassName) = $this->parsePath($sClassName) )
		{
			return $this->searchClassEx($sInnerFolderPath,$sClassName) ;
		}
		else
		{
			return null ;
		}
	}
	
	/**
	 * @return js\fs\IFile
	 */
	public function searchClassEx($sSubFolder,$sShortClassName)
	{
		if(!$this->aFolder)
		{
			return null ;
		}
		
		foreach($this->arrClassFilenameWraps as $func)
		{
			$sClassFilename = call_user_func_array($func, array($sShortClassName)) ;
			
			$sClassFilePath = $sSubFolder? ($sSubFolder . '/' . $sClassFilename): $sClassFilename ;
			
			if( $aFile=$this->aFolder->findFile($sClassFilePath) and $aFile instanceof IFile )
			{
				return $aFile ;
			}
		}
		
		return null ;
	}
	
	public function parsePath($sClassName)
	{
		if( $this->nNamespaceLen===0 )
		{
			$sPath = $sClassName ;
		}
		
		else if( substr($sClassName,0,$this->nNamespaceLen)===$this->sNamespace )
		{
			$sPath = substr($sClassName,$this->nNamespaceLen) ;
		}
		
		else
		{
			return null ;
		}
		
		$pos = strrpos($sPath,'\\') ;
		if( $pos!==false )
		{
			return array(
				str_replace('\\', '/', substr($sPath,0,$pos))
				, substr($sPath,$pos+1)
			) ;
		}
		else 
		{
			return array('',$sPath) ;
		}
	}

	public function addClassFilenameWrapper($func) 
	{
		$this->arrClassFilenameWraps[] = $func ;
	}
	
	public function createClassFile($sInnerPath,$sShortClassName)
	{
		if($this->aFolder)
		{
			if( $fnClassFilenameWraps = reset($this->arrClassFilenameWraps) )
			{
				$sClassPath = call_user_func_array($fnClassFilenameWraps, array($sShortClassName)) ;
			}
			else 
			{
				$sClassPath = $sShortClassName . '.php' ;
			}
			if($sInnerPath)
			{
				$sClassPath = $sInnerPath.'/'.$sClassPath ;
			}
			
			if( !$aClassFile=$this->aFolder->createFile($sClassPath) )
			{
				throw new Exception(
					"无法在以下路径上创建类%s的编译文件：%s",array($sClassFullName,$sClassCompilePath)
				) ;
			}
			
			return $aClassFile ;
		}
		
		else
		{
			return null ;
		}
	}
	
	private $sNamespace ;
	
	private $nNamespaceLen = 0 ;
	
	private $aFolder ;
	
	private $arrClassFilenameWraps = array() ;
}

?>