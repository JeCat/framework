<?php
namespace jc\ui\xhtml\compiler\macro ;

use jc\ui\xhtml\compiler\NodeCompiler;

use jc\ui\xhtml\compiler\MacroCompiler ;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class CycleMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$sSource = $aObject->source() ;
		
		$arrStrings = array();
		$sTemp = '';
		
		//测试字符串{@11, ,bb\\\,b\1\nb,\ 22 ,344}
		for($i = 0; $i < strlen($sSource) ; $i++ )
		{
			if( $sSource[$i] == '\\')
			{
				if($sSource[$i+1] == '\\')
				{
					$sTemp.='\\';
					$i++;
				}
				elseif($sSource[$i+1] == ',')
				{
					$sTemp.=',';
					$i++;
				}
				continue;
			}
			
			if( $sSource[$i] == ',' )
			{
				$arrStrings[] = $sTemp;
				$sTemp = '';
				continue;
			}
			
			$sTemp .= $sSource[$i];
		}
		$arrStrings[] = $sTemp;
		
		$sArrName ='$'. NodeCompiler::assignVariableName('arrChangByLoopIndex');
		$sObjName ='$'. NodeCompiler::assignVariableName('aStrChangByLoopIndex');
		
		$aDev->write("{$sArrName} = " . var_export($arrStrings,true) .";
			if(!isset({$sObjName}))
			{
				{$sObjName} = new jc\\ui\\xhtml\\compiler\\macro\\Cycle({$sArrName});
			}
			{$sObjName}->printArr(\$aDevice);
		") ;
		
		
//		$aDev->write("\$aDevice->write('this is Cycle Macro <br />');") ;
		
//		$aDev->output("macro's content is :{$sSource}") ;
	}
}
