<?php
namespace jc\mvc\controller ;


use jc\message\IMessageQueueHolder;

interface IController extends IMessageQueueHolder
{
	public function mainRun($Params=null) ;
	
	public function process() ;
    
    public function executeParams() ;
    
    public function buildParams($Params) ;
}
?>