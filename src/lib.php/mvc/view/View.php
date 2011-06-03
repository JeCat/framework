<?php
namespace jc\mvc\view ;

use jc\mvc\model\IModel;
use jc\mvc\view\widget\IViewWidget;
use jc\pattern\composite\Container;
use jc\util\HashTable;
use jc\io\OutputStreamBuffer;
use jc\pattern\composite\NamableComposite;
use jc\ui\UI;

class View extends NamableComposite implements IView
{
	public function __construct($sSourceFilename=null,UI $aUI=null)
	{
		parent::__construct("jc\\mvc\\view\\IView") ;
		
		$this->setSourceFilename($sSourceFilename) ;
		$this->setUi( $aUI? $aUI: UIFactory::singleton()->create() ) ;
	}

	/**
	 * @return IModel
	 */
	public function model()
	{
		return $this->aModel ;
	}
	
	public function setModel(IModel $aModel)
	{
		$this->aModel = $aModel ;
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
	 * @return HashTable
	 */
	protected function widgits()
	{
		if( !$this->aWidgets )
		{
			$this->aWidgets = new HashTable() ;
		}
		
		return $this->aWidgets ;
	}
	
	public function addWidget(IViewWidget $aWidget)
	{
		$this->widgits()->set($aWidget->id(),$aWidget) ;
		$aWidget->setView($this) ;
	}
	
	public function removeWidget(IViewWidget $aWidget)
	{
		$this->widgits()->remove($aWidget->id()) ;
		$aWidget->setView(null) ;
	}
	
	public function clearWidgets()
	{
		foreach($this->widgitIterator() as $aWidget)
		{
			$this->removeWidget($aWidget) ;
		}
	}
	
	/**
	 * @return IViewWidget
	 */
	public function widget($sId)
	{
		return $this->widgits()->get($sId) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function widgitIterator()
	{
		return $this->widgits()->iterator() ;
	}
	
	private $aModel ;
	private $aWidgets ;
	private $sSourceFile ;
	private $aUI ;
	private $aOutputStream ;
	private $aVariables ;
}

?>