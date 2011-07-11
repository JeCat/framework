<?php
namespace jc\mvc\controller ;


use jc\message\IMessageQueueHolder;

interface IController extends IMessageQueueHolder
{
	public function mainRun() ;
	
	public function process() ;
    
    /**
     * @return jc\util\IDataSrc
     */
    public function params() ;
}
?>