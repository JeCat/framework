<?php
namespace org\jecat\framework\mvc\controller ;

class ExceptionRelocation extends \Exception
{
	public function __construct($sUrl,$nFlashSec=3)
	{
		$this->sUrl = $sUrl ;
		$this->nFlashSec = $nFlashSec ;
	}

	public function url()
	{
		return $this->sUrl ;
	}
	public function flashSec()
	{
		return $this->nFlashSec ;
	}
	
	private $sUrl ;
	
	private $nFlashSec ;
}

