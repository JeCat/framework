<?php
namespace org\jecat\framework\cache ;

use org\jecat\framework\util\String;

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
		
		// 尝试 .php
		if( $aFile = $this->aFolder->findFile($sDataPath.'.php') )
		{
			return $aFile->includeFile(false,false) ;
		}
		// 尝试 .data
		if( $aFile = $this->aFolder->findFile($sDataPath.'.data') )
		{
			return unserialize(file_get_contents($aFile->url()) ) ;
		}

		return null ;
	}
	
	function setItem($sDataPath,$data,$fCreateTimeMicroSec=-1)
	{
		self::trimPath($sDataPath) ;
		
		if( is_object($data) )
		{
			if( !$aFile=$this->aFolder->findFile($sDataPath.'.data',FileSystem::FIND_AUTO_CREATE) )
			{
				return false ;
			}
			$sSerialize = serialize($data) ;
		}
		else 
		{
			if( !$aFile=$this->aFolder->findFile($sDataPath.'.php',FileSystem::FIND_AUTO_CREATE) )
			{
				return false ;
			}
			$sSerialize = "<?php\r\nreturn ".var_export($data,true).' ;' ;
		}
		
		$aWriter = $aFile->openWriter() ;
		$aWriter->write($sSerialize) ;
		$aWriter->close() ;
		
		// create time
		if($fCreateTimeMicroSec<0)
		{
			$fCreateTimeMicroSec = microtime(true) ;
		}
		if( !$aFile=$this->aFolder->findFile($sDataPath.'.time',FileSystem::FIND_AUTO_CREATE) )
		{
			return false ;
		}
		$aWriter = $aFile->openWriter() ;
		$aWriter->write("<?php return {$fCreateTimeMicroSec} ;") ;
		$aWriter->close() ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 */
	function delete($sDataPath)
	{
		self::trimPath($sDataPath) ;

		// 所有
		if( empty($sDataPath) )
		{
			$this->aFolder->delete(true,true) ;
		}
		// 删除目录
		else if( $aFolder=$this->aFolder->findFolder($sDataPath) )
		{
			$aFolder->delete(true,true) ;
		}
		// 删除指定内容
		else
		{
			$this->aFolder->deleteChild($sDataPath.'.data') ;
			$this->aFolder->deleteChild($sDataPath.'.php') ;
			$this->aFolder->deleteChild($sDataPath.'.time') ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function isExpire($sDataPath,$fValidSec)
	{
		return $this->createTime($sDataPath) + $fValidSec < microtime(true) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return float
	 */
	function createTime($sDataPath)
	{
		self::trimPath($sDataPath) ;
		
		if( !$aFile = $this->aFolder->findFile($sDataPath.'.time') )
		{
			return 0 ;
		}

		return (float)$aFile->includeFile(false,false) ;
	}
	
	static public function trimPath(&$sPath)
	{
		$sPath = preg_replace('`^\\s*/+`','',$sPath) ;
	}
	
	/**
	 * @var org\jecat\framework\fs\FsFolder
	 */
	private $aFolder ;
}
?>