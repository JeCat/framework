<?php
namespace jc\mvc ;

use jc\util\HashTable;
use jc\io\OutputStreamBuffer;
use jc\pattern\composite\ContainedableObject;
use jc\ui\UI;

class View extends ContainedableObject implements IView
{
	public function __construct($sSourceFilename,UI $aUI=null)
	{
		$this->setSourceFilename($sSourceFilename) ;
		$this->setUi( $aUI? $aUI: UIFactory::singleton()->create() ) ;
		
		parent::__construct() ;
	}

	/**
	 * @return jc\ui\UI
	 */
	public function ui()
	{
		return $this->aUI ;
	}
	public function setUi(UI $aUI)
	{
		$this->aUI = $aUI ;
	}
	
	public function sourceFilename()
	{
		return $this->sSourceFile ;
	}
	public function setSourceFilename($sSourceFilename)
	{
		$this->sSourceFile = $sSourceFilename ;
	}

	/**
	 * @return IHashTable
	 */
	public function variables()
	{
		if(!$this->aVariables)
		{
			$this->aVariables = new HashTable() ;
		}
		return $this->aVariables ;
	}
	
	public function setVariables(IHashTable $aVariables)
	{
		$this->aVariables = $aVariables ;
	}
	
	/**
	 * @return IViewOutputStream
	 */
	public function outputStream()
	{
		if(!$this->aOutputStream)
		{
			$this->aOutputStream = new OutputStreamBuffer() ;
		}
		
		return $this->aOutputStream ;
	}
	public function setOutputStream(IOutputStream $aDev)
	{
		$this->aOutputStream = $aDev ;
	}
	
	public function show()
	{
		$this->ui()->display($this->sourceFilename(),$this->variables(),$this->OutputStream()) ;
	}
	
	private $sSourceFile ;
	private $aUI ;
	private $aOutputStream ;
	private $aVariables ;
}

?>