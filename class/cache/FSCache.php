<?php
namespace org\jecat\framework\cache ;

use org\jecat\framework\fs\FileSystem;

class FSCache implements ICache
{
	public function __construct(FileSystem $aCacheFilesystem)
	{
		$this->aCacheFilesystem = $aCacheFilesystem ;
	}
	
	function item($sDataPath)
	{
		if( !$aFile = $this->aCacheFilesystem->findFile($sDataPath) )
		{
			return ;
		}

		list( ,$data ) = $aFile->includeFile(false,false) ;
		return $data ;
	}
	
	function setItem($sDataPath,$data,$fCreateTimeMicroSec=-1)
	{
		if( !$aFile=$this->aCacheFilesystem->findFile($sDataPath) and !$aFile=$this->aCacheFilesystem->createFile($sDataPath) )
		{
			return false ;
		}
		
		if( is_object($data) )
		{
			$sSerialize = 'unserialize("'.addslahes(serialize($data)).'")' ;
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
		$aWriter->write("<?php return array( 'create'=>{$fCreateTimeMicroSec}, 'data'=>" . $sSerialize) . ") ;" ;
		$aWriter->close() ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function delete($sDataPath)
	{
		$this->aCacheFilesystem->delete($sDataPath) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function isExpire($sDataPath,$fValidSec)
	{
		if( !$aFile = $this->aCacheFilesystem->findFile($sDataPath) )
		{
			return ;
		}

		list( $fTime ) = $aFile->includeFile(false,false) ;
		return $fTime + $fValidSec < microtime(true) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return float
	 */
	function createTime($sDataPath)
	{
		if( !$aFile = $this->aCacheFilesystem->findFile($sDataPath) )
		{
			return ;
		}

		list( $fTime ) = $aFile->includeFile(false,false) ;
		return $fTime ;
	}
	
	/**
	 * @var org\jecat\framework\fs\FileSystem
	 */
	private $aCacheFilesystem ;
}
?>