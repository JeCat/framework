<?php
namespace jc\io ;

use jc\util\String;
use jc\lang\Object;
use jc\io\IClosable;
use jc\io\IInputStream;

class InputStream extends Stream implements IInputStream, ILockable
{
	const MAX_READ_BYTES = 8192 ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	function read($nBytes=self::MAX_READ_BYTES,$bBlock=true)
	{
		if($nBytes<0)
		{
			$sReaded = '' ;
			while(!feof($this->hHandle))
			{
				$sReaded.= fread($this->hHandle,10240) ;
			}
			return $sReaded ;
		}
		else 
		{
			return fread($this->hHandle,$nBytes) ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function readInString(String $aString,$nBytes=-1)
	{
		$sBytes = $this->read($nBytes) ;
		$aString->append( $sBytes ) ;
		
		return strlen($sBytes) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function reset()
	{
		fseek($this->hHandle,0,SEEK_SET) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function available()
	{
		$arrInfo = stream_get_meta_data($this->hHandle) ;
		return isset($arrInfo['unread_bytes'])? $arrInfo['unread_bytes']: -1 ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function seek($nPosition)
	{
		fseek($this->hHandle,$nPosition,SEEK_SET) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function skip($nBytes)
	{
		fseek($this->hHandle,$nBytes,SEEK_CUR) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isEnd()
	{
		return feof($this->hHandle) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function lock()
	{
		flock($this->hHandle,LOCK_SH) ;
	}
}

?>