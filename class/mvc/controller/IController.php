<?php
namespace org\jecat\framework\mvc\controller ;


use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\message\IMessageQueueHolder;

interface IController extends IMessageQueueHolder
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
    public function modelIterator() ;
    public function clearModels() ;
    
    
    public function addView(IView $aView,$sName=null) ;
    public function removeView(IView $aView) ;
    /**
	 * @return org\jecat\framework\mvc\view\IView
     */
    public function viewByName($sName) ;
    public function viewIterator() ;
    public function clearViews() ;
}
?>