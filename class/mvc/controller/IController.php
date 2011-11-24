<?php
namespace jc\mvc\controller ;


use jc\mvc\view\IView;
use jc\mvc\model\IModel;
use jc\message\IMessageQueueHolder;

interface IController extends IMessageQueueHolder
{
	public function mainRun() ;
	
	public function process() ;
    
    /**
     * @return jc\util\IDataSrc
     */
    public function params() ;
    
    
    
    public function addModel(IModel $aModel,$sName=null) ;
    public function removeModel(IModel $aModel) ;
    /**
	 * @return jc\mvc\model\IModel
     */
    public function modelByName($sName) ;
    public function modelIterator() ;
    public function clearModels() ;
    
    
    public function addView(IView $aView,$sName=null) ;
    public function removeView(IView $aView) ;
    /**
	 * @return jc\mvc\view\IView
     */
    public function viewByName($sName) ;
    public function viewIterator() ;
    public function clearViews() ;
}
?>