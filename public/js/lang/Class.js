jc.lang.Class = {
	
	define : function(sName,clsParent,mapMembers)
	{
		if( typeof(sName)!="string" )
		{
			throw new TypeError("sName must be string type") ;
		}

		if( typeof(clsParent)=="undefined" )
		{
			clsParent = null ;
		}
		if( clsParent && typeof(clsParent)!="function" )
		{
			throw new TypeError("clsParent must be function, null type, or undefined ") ;
		}

		if( typeof(mapMembers)=="undefined" )
		{
			mapMembers = {} ;
		}
		if( typeof(mapMembers)!="object" )
		{
			throw new TypeError("mapMembers must be object or undefined ") ;
		}
		
		this.__sName = sName ;
		this.__clsParent = clsParent ;
		this.__mapMembers = mapMembers ;
		
		if( this.__clsParent )
		{
			this.prototype = __clsParent ;
		}
		for(var name in mapMembers)
		{
			this[name] = mapMembers[name] ;
		}
	}
}

