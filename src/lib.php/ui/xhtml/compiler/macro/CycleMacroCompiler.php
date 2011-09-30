<?php
namespace jc\ui\xhtml\compiler\macro;

use jc\lang\Exception;

use jc\ui\xhtml\compiler\NodeCompiler;

use jc\ui\xhtml\compiler\MacroCompiler;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class CycleMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject, TargetCodeOutputStream $aDev, CompilerManager $aCompilerManager)
	{
		$sSource = $aObject->source ();
		
		//如果开头是变量
		if (substr ( $sSource, 0, 1 ) === '$')
		{
			//分辨是定义还是调用
			if($bIsDefine and $nEqual = stripos($sSource, '=') and strlen(substr($sSource, $nEqual)) > 0)
			{
				//这是定义
				$sObjName = substr($sSource, 1, $nEqual);
				$arrStrings = $this->getElementsBySource(substr($sSource, $nEqual));
				$sArrName = '$' . NodeCompiler::assignVariableName ( 'arrChangByLoopIndex' );
				$aDev->write ( "{$sArrName} = " . var_export ( $arrStrings, true ) . ";
								if(!isset({$sObjName}))
								{
									{$sObjName} = new jc\\ui\\xhtml\\compiler\\macro\\Cycle({$sArrName});
								}
								$aVariables->set( substr({$sObjName},1) , {$sObjName} ) ;
								");
			}else{
				//这是调用
				$sObjName = substr($sSource, 1, $nEqual);
				$aDev->write ( "
								if(isset({$sObjName}))
								{
									{$sObjName}->printArr(\$aDevice);
								}
								" );
				
			}
		}
		//如果开头不是变量，是基本用法
		else
		{
			$sArrName = '$' . NodeCompiler::assignVariableName ( 'arrChangByLoopIndex' );
			$sObjName = '$' . NodeCompiler::assignVariableName ( 'aStrChangByLoopIndex' );
			
			$aDev->write ( "{$sArrName} = " . var_export ( $arrStrings, true ) . ";
				if(!isset({$sObjName}))
				{
					{$sObjName} = new jc\\ui\\xhtml\\compiler\\macro\\Cycle({$sArrName});
				}
				{$sObjName}->printArr(\$aDevice);
				$aVariables->set( substr({$sObjName},1) , {$sObjName} ) ;
			" );
		}
	}
	
	/**
	 * Enter description here ...
	 * @param string $sSource
	 * @return array 
	 */
	public function getElementsBySource($sSource)
	{
		$arrStrings = array ();
		$sTemp = '';
		
		//参数 分解成数组
		for($i = 0; $i < strlen ( $sSource ); $i ++)
		{
			if ($sSource [$i] == '\\')
			{
				//转义反斜线
				if ($sSource [$i + 1] == '\\')
				{
					$sTemp .= '\\';
					$i ++;
				}
				//转移逗号
				elseif ($sSource [$i + 1] == ',')
				{
					$sTemp .= ',';
					$sTemp .= '=';
					$i ++;
				
				//转义等号
				}
				elseif ($sSource [$i + 1] == '=')
				{
					$sTemp .= '=';
					$i ++;
				}
				continue;
			}
			
			if ($sSource [$i] == ',')
			{
				$arrStrings [] = $sTemp;
				$sTemp = '';
				continue;
			}
			
			$sTemp .= $sSource [$i];
		}
		
		$arrStrings [] = $sTemp;
		
		return $arrStrings;
	}
}
