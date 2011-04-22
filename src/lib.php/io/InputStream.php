<?php
namespace jc\io ;

use jc\util\String;
use jc\lang\Object;
use jc\io\IClosable;
use jc\io\IInputStream;

class InputStream extends Stream implements IInputStream, ILockable
{
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	function read($nBytes)
	{
		if( $aStream = $this->redirectStream() )
		{
			return $aStream->fread($nBytes) ;
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
	function readInString($nBytes,String $aString)
	{
		$sBytes = $this->read($nBytes) ;
		$aString->append( &$sBytes ) ;
		
		return strlen($sBytes) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function reset()
	{
		if( $aStream = $this->redirectStream() )
		{
			$aStream->reset() ;
		}
		
		else
		{
			fseek($this->hHandle,0,SEEK_SET) ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function available()
	{
		if( $aStream=$this->redirectStream() )
		{
			return $aStream->available() ;
		}
		else
		{
			$arrInfo = stream_get_meta_data($this->hHandle) ;
			return isset($arrInfo['unread_bytes'])? $arrInfo['unread_bytes']: -1 ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function seek($nPosition)
	{
		if( $aStream = $this->redirectStream() )
		{
			return $aStream->seek($nPosition) ;
		}
		
		else
		{
			fseek($this->hHandle,$nPosition,SEEK_SET) ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function skip($nBytes)
	{
		if( $aStream = $this->redirectStream() )
		{
			return $aStream->skip($nBytes) ;
		}
		
		else
		{
			fseek($this->hHandle,$nBytes,SEEK_CUR) ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isEnd()
	{
		if( $aStream = $this->redirectStream() )
		{
			return $aStream->isEnd() ;
		}
		
		else
		{
			return feof($this->hHandle) ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function lock()
	{
		if( !$this->redirectStream() )
		{
			flock($this->hHandle,LOCK_SH) ;
		}
	}

	public function redirect(IInputStream $aStream)
	{
		parent::redirect($aStream) ;
	}
}

?>