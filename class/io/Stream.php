<?php
namespace jc\io ; 

use jc\io\IClosable ;
use jc\lang\Object ;


abstract class Stream extends Object implements IStream, IClosable
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct($hHandle=null)
	{
		$this->hHandle = $hHandle ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __destruct()
	{
		if( $this->isActiving() )
		{
			$this->close() ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @throws
	 * @return bool
	 */
	public function close()
	{
		if($this->isActiving())
		{
			fclose($this->hHandle) ;
			$this->hHandle = null ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isActiving()
	{
		return $this->hHandle? true: false ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function supportsLock()
	{
		return stream_supports_lock($this->hHandle) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function unlock()
	{
		flock($this->hHandle,LOCK_UN) ;
	}
		
	protected $hHandle ;
	
}
?>