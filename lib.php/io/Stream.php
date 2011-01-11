<?php
namespace jc\io ; 

use jc\io\IClosable ;
use jc\lang\Object ;


class Stream extends Object implements IClosable
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
	public function initialize($hHandle=null)
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
		fclose($this->hHandle) ;
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
	abstract public function lock() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function unlock()
	{
		flock($this->hHandle,LOCK_UN) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var handle
	 */
	protected $hHandle ;
}
?>