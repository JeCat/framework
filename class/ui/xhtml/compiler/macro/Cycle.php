<?php
namespace org\jecat\framework\ui\xhtml\compiler\macro;

use org\jecat\framework\io\IOutputStream;

class Cycle
{
	public function __construct($arrToPrint)
	{
		$arrToPrint = ( array ) $arrToPrint;
		//构造一个循环迭代器
		$aArrIter = new \ArrayIterator ( $arrToPrint );
		$aArrIter->rewind();
		$this->aArrIter = new \InfiniteIterator ( $aArrIter );
		$this->aArrIter->rewind();
	}
	
	public function printArr(IOutputStream $aDev)
	{
		$aDev->write ( $this->aArrIter->current () );
		$this->aArrIter->next ();
	}
	
	private $aArrIter;
}