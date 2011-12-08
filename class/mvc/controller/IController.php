<?php
namespace org\jecat\framework\mvc\controller ;


use org\jecat\framework\pattern\composite\IContainedable;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\message\IMessageQueueHolder;

interface IController extends IMessageQueueHolder, IContainer, IContainedable
{
	public function mainRun() ;
	
	public function process() ;
    
    /**
     * @return org\jecat\framework\util\IDataSrc
     */
    public function params() ;
    
    
    
    public function addModel(IModel $aModel,$sName=null) ;
    public function removeModel(IModel $aModel) ;
    /**
	 * @return org\jecat\framework\mvc\model\IModel
     */
    public function modelByName($sName) ;
    /**
     * @return \Iterator
     */
    public function modelIterator() ;
    /**
     * @return \Iterator
     */
    public function modelNameIterator() ;
    public function clearModels() ;
    
    
    public function addView(IView $aView,$sName=null) ;
    public function removeView(IView $aView) ;
    /**
	 * @return org\jecat\framework\mvc\view\IView
     */
    public function viewByName($sName) ;
    public function viewIterator() ;
    public function clearViews() ;
    
    public function id() ;
}
?>