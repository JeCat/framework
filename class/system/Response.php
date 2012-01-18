<?php
namespace org\jecat\framework\system ;

use org\jecat\framework\mvc\controller\IController;
use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\io\PrintStream;
use org\jecat\framework\lang\Object ;

class Response extends Object
{	
	public function __construct(PrintStream $aPrinter)
	{
		$this->aPrinter = $aPrinter ;
	}
	
	public function process(IController $aController)
	{
		switch ($aController->params()->get('rspn'))
		{
		// msgqueue ------------
		case 'msgqueue.json' :
			break ;
			
		case 'msgqueue.xml' :
			break ;
			
		case 'msgqueue' :
		case 'msgqueue.html' :
			
			$aController->messageQueue()->display() ;
			
			break ;
			
		// var ------------
		case 'var' :
		case 'var.json' :
			$this->printer()->write(json_encode($this->arrReturnVariables)) ;
			break ;
			
		case 'var.xml' :
			break ;
			
		case 'var.php' :
			$this->printer()->write(var_export($this->arrReturnVariables,true)) ;
			break ;
			
		// view ------------
		case 'noframe' :
		case 'view.noframe' :
			break ;
			
		case 'view' :
		case 'view.inframe' :
		default :
			
			if( $aFrame = $aController->frame() )
			{
				$aController->renderMainView($aFrame->mainView()) ;
				
				$aController->displayMainView($aFrame->mainView(),$this->printer()) ;
			}
			else
			{
				$aController->renderMainView($aController->mainView()) ;
				
				$aController->displayMainView($aController->mainView(),$this->printer()) ;
			}
			
			break ;
		}
	}
	
	// ------------------------
	public function putReturnVariable($aVar,$key=null)
	{
		if($key===null)
		{
			$this->arrReturnVariables[] =& $aVar ;
		}
		else
		{
			$this->arrReturnVariables[$key] =& $aVar ;
		}
	}
	public function returnVariable($key=null)
	{
		
	}
	public function removeReturnVariable($key)
	{
		
	}
	public function clearReturnVariables()
	{
		$this->arrReturnVariables = null ;
	}
	public function returnVariableKeyIterator()
	{
		
	}
	
	// ------------------------
	/**
	 * Enter description here ...
	 * 
	 * @return org\jecat\framework\io\PrintSteam
	 */
	public function printer()
	{
		return $this->aPrinter ;
	}
	
	public function setPrinter(PrintStream $aPrinter)
	{
		$this->aPrinter = $aPrinter ;
	}

	public function output($sBytes)
	{
		if( $aFilters = $this->filters() )
		{
			list($sBytes) = $aFilters->handle($sBytes) ;
		}
		
		$this->aPrinter->println($sBytes) ;
	}
	
	/**
	 * @return IFilterMangeger
	 */
	public function filters()
	{
		return $this->aFilters ;
	}
	
	public function setFilters(IFilterMangeger $aFilters)
	{
		$this->aFilters = $aFilters ;
	}
	
	/**
	 * @return org\jecat\framework\io\IOutputStream
	 */
	public function device()
	{
		return $this->aPrinter ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var org\jecat\framework\io\PrintSteam
	 */
	private $aPrinter ;
	
	private $aFilters ;
	
	private $arrReturnVariables ;
}

?>