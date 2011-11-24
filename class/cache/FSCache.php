<?php
namespace org\jecat\framework\cache ;

use org\jecat\framework\fs\FileSystem;
use org\jecat\framework\fs\IFolder;

class FSCache implements ICache
{
	public function __construct(IFolder $aFolder)
	{
		$this->aFolder = $aFolder ;
	}
	
	function item($sDataPath)
	{
		self::trimPath($sDataPath) ;
		
		if( !$aFile = $this->aFolder->findFile($sDataPath) )
		{
			return ;
		}

		$arrItem = $aFile->includeFile(false,false) ;
		return isset($arrItem['data'])? $arrItem['data']: null ;
	}
	
	function setItem($sDataPath,$data,$fCreateTimeMicroSec=-1)
	{
		self::trimPath($sDataPath) ;
		
		if( !$aFile=$this->aFolder->findFile($sDataPath,FileSystem::FIND_AUTO_CREATE) )
		{
			return false ;
		}
		
		if( is_object($data) )
		{
			$sSerialize = 'unserialize("'.addslashes(serialize($data)).'")' ;
		}
		else 
		{
			$sSerialize = var_export($data,true) ;
		}
		
		if($fCreateTimeMicroSec<0)
		{
			$fCreateTimeMicroSec = microtime(true) ;
		}
		
		$aWriter = $aFile->openWriter() ;
		$aWriter->write("<?php
return array(
	'create'=>{$fCreateTimeMicroSec},
	'data'=>{$sSerialize}
) ;") ;
		$aWriter->close() ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function delete($sDataPath)
	{
		self::trimPath($sDataPath) ;
		
		$this->aFolder->delete($sDataPath) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function isExpire($sDataPath,$fValidSec)
	{
		self::trimPath($sDataPath) ;
		
		if( !$aFile = $this->aFolder->findFile($sDataPath) )
		{
			return ;
		}

		$arrItem = $aFile->includeFile(false,false) ;
		return $arrItem['create'] + $fValidSec < microtime(true) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return float
	 */
	function createTime($sDataPath)
	{
		self::trimPath($sDataPath) ;
		
		if( !$aFile = $this->aFolder->findFile($sDataPath) )
		{
			return ;
		}

		$arrItem = $aFile->includeFile(false,false) ;
		return $arrItem['create'] ;
	}
	
	static public function trimPath(&$sPath)
	{
		if( strlen($sPath)>0 and substr($sPath,0,1)=='/' )
		{
			$sPath = substr($sPath,1) ;
		}
	}
	
	/**
	 * @var org\jecat\framework\fs\FileSystem
	 */
	private $aFolder ;
}
?>