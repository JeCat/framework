<?php
namespace jc\io ;

use jc\lang\Object;

class OutputStream extends Stream implements IOutputStream, ILockable
{
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function write($Contents,$nLen=null,$bFlush=false)
	{
		if( $aStream = $this->redirectStream() )
		{
			$aStream->write($Contents,$nLen,$bFlush) ;
		}
		
		else
		{		
			$nRet = ($nLen===null)?
				fwrite($this->hHandle,strval($Contents)) :
				fwrite($this->hHandle,strval($Contents),$nLen) ;
			
			if($bFlush)
			{
				$this->flush() ;
			}
		}
	}

	public function bufferBytes()
	{
		return '' ;
	}
	
	public function clean()
	{}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function flush()
	{
		if( !$this->redirectStream() )
		{
			fflush($this->hHandle) ;
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
			flock($this->hHandle,LOCK_EX) ;
		}
	}
	
	
	public function redirect(IOutputStream $aStream)
	{
		parent::redirect($aStream) ;
	}
	
}
?>