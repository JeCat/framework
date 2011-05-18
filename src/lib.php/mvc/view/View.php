<?php
namespace jc\mvc ;

use jc\pattern\Container;
use jc\util\HashTable;
use jc\io\OutputStreamBuffer;
use jc\pattern\composite\NamableComposite;
use jc\ui\UI;

class View extends NamableComposite implements IView
{
	public function __construct($sSourceFilename=null,UI $aUI=null)
	{
		$this->setSourceFilename($sSourceFilename) ;
		$this->setUi( $aUI? $aUI: UIFactory::singleton()->create() ) ;
		
		parent::__construct("jc\\mvc\\IView") ;
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
	 * @return OutputStreamBuffer
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
	
	public function render()
	{
		$aVars = $this->variables() ;
		$aVars->set('theView',$this) ;
		
		$this->ui()->display($this->sourceFilename(),$aVars,$this->OutputStream()) ;
	}
	
	public function display()
	{
		// 找到可收容当前视图
		if( $aParent=$this->parent() )
		{
			$aParent->outputStream()->write( $this->outputStream() ) ;
		}
		
		else 
		{
			$this->application()->response()->output(
				$this->outputStream()->bufferBytes() 
			) ;
		}
	}
	
	public function show()
	{
		$this->render() ;
		
		$this->display() ;
	}
	
	/**
	 * @return Container
	 */
	/*public function viewContainers()
	{
		if( !$this->aViewContainer )
		{
			$this->aViewContainer = new Container('jc\\mvc\\IViewContainer') ;
		}
		
		return $this->aViewContainer ;
	}*/
	
	private $sSourceFile ;
	private $aUI ;
	private $aOutputStream ;
	private $aVariables ;
}

?>