<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\mvc\controller\IController;
use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\message\IMessageQueueHolder;
use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\mvc\view\widget\IViewWidget;
use org\jecat\framework\mvc\view\widget\IWidgetContainer ;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\pattern\composite\Container;

interface IView extends IContainer, IMessageQueueHolder, IWidgetContainer
{
	
	/**
	 * @return org\jecat\framework\util\IHashTable
	 */
	public function variables() ;
	
	public function setVariables(IHashTable $aVariables) ;
	
	/**
	 * @return OutputStreamBuffer
	 */
	public function outputStream() ;
	
	public function setOutputStream(OutputStreamBuffer $aDev) ;
	
	public function isVagrant() ;

	public function render($bRerender=false) ;
	
	public function assembly() ;
	
	public function display() ;
	
	public function show() ;
		
	public function enable($bEnalbe=true) ;
	
	public function isEnable() ;
	
	public function exchangeData($nWay=DataExchanger::MODEL_TO_WIDGET) ;
	
	/**
	 * @return org\jecat\framework\mvc\model\IModel
	 */
	public function model() ;
	
	public function setModel(IModel $aModel) ;
	
	/**
	 * @return org\jecat\framework\mvc\controller\IContainer
	 */
	public function controller() ;
	
	public function setController(IController $aController=null) ;
    
    public function id() ;
    
}

?>