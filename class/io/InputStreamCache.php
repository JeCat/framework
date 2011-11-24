<?php
namespace org\jecat\framework\io ;

use org\jecat\framework\util\String;
use org\jecat\framework\lang\Object;

class InputStreamCache extends Object implements IInputStream
{
	public function __construct($sData='')
	{
		$this->setData($sData) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function read($nBytes=-1)
	{
		$nDataLen = $this->available() ;
		
		if( $nBytes<0 )
		{
			$nBytes = $nDataLen ;
		}
		else 
		{
			if($nBytes>$nDataLen)
			{
				$nBytes = $nDataLen ;
			}
		}
		
		if( $nBytes<=0 )
		{
			return '' ;
		}
		
		$nFrom = $this->nPostion ;
		$this->nPostion+= $nBytes ;
		
		return substr($this->sData,$nFrom,$nBytes) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
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
		$this->nPostion = 0 ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function available()
	{
		return strlen($this->sData)-$this->nPostion ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function seek($nPosition)
	{
		if($nPosition<0)
		{
			$nPosition = -1 ;
		}
		
		$this->nPosition = $nPosition ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isEnd()
	{
		$this->nPostion == strlen($this->sData) ;
	}


	public function setData($sData)
	{
		$this->sData = $sData ;
		
		$this->reset() ;
	}
	public function data()
	{
		return $this->sData ;
	}
	
	private $sData ;
	
	private $nPostion = 0 ;
}

?>