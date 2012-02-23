jc = {
	mvc : {
		view : {
			widget : {
				menu : {
					base : {
						timer : -1,
					},
					fun : {
						onload : {
							addOnLoad : function(fun){
								this.loadList.push(fun);
							},
							loadOnLoad : function(){
								var i;
								for(i=0;i<this.loadList.length;++i){
									var fun = this.loadList[i] ;
									fun() ;
								}
							},
							loadList : [] ,
						}
					},
					objectList : {
						sItemClassName : 'jc-widget-menu-item' ,
						sMenuClassName : 'jc-widget-menu' ,
						itemList : [] ,
						menuList : [] ,
					},
				}
			}
		}
	}
};
window.onload = function(){
	jc.mvc.view.widget.menu.fun.onload.loadOnLoad() ;
}

// add all item
jc.mvc.view.widget.menu.fun.onload.addOnLoad(function(){
	
	function containsClassName( o , sClassName ){
		var i ;
		for( i=0 ; i<o.classList.length ; ++i ){
			if( sClassName == o.classList[i] ){
				return true;
			}
		}
		return false;
	}
	// add all li
	var lilist = document.getElementsByTagName('li');
	var i;
	
	for(i=0;i<lilist.length;++i){
		var li = lilist[i] ;
		if( containsClassName( li , jc.mvc.view.widget.menu.objectList.sItemClassName ) ){
			li.isItem = true ;
			
			// li is parent of o
			li.isParentOf = function(o){
				var p = o ;
				
				while(p && typeof(p)!='undefined'){
					if(p == this) return true;
					p = p.parentNode;
				}
				return false;
			}
			
			
			li.contractExceptParent = function(){
				var itemList = jc.mvc.view.widget.menu.objectList.itemList ;
				var k;
				for( k=0 ; k<itemList.length ; ++k ){
					var item = itemList[k] ;
					if( ! item.isParentOf(this) ){
						item.contract() ;
					}
				}
			};
			
			
			li.stopBubble = function(event){
				var e = e ? e : window.event;
				if (window.event) { // IE
					e.cancelBubble = true;
				} else { // FF
					e.stopPropagation();
				}
			}
			
			
			li.expand = function(){
				var childlist = this.childNodes;
				for(var i=0;i<childlist.length;++i){
					o = childlist[i] ;
					if( typeof(o.show) != 'undefined' ){
						o.show();
					}
				}
			};
			
			
			li.contract=function(){
				var childlist = this.childNodes;
				for(var i=0;i<childlist.length;++i){
					var o = childlist[i];
					if( typeof(o.hide) != 'undefined' ){
						o.hide();
					}
				}
			};
			
			
			li.onmouseover = function(){
				clearTimeout(jc.mvc.view.widget.menu.base.timer);
				jc.mvc.view.widget.menu.base.timer=-1;
				this.contractExceptParent() ;
				this.expand();
				this.stopBubble(event);
			} ;
			
			
			li.onmouseout = function(){
				var ob_li = this ;
				if(jc.mvc.view.widget.menu.base.timer == -1){
					jc.mvc.view.widget.menu.base.timer=setTimeout(
						function(){
							if(jc.mvc.view.widget.menu.base.timer != -1){
								jc.mvc.view.widget.menu.base.timer = -1 ;
								ob_li.contract() ;
								
								// contract all
								for( k=0 ; k<jc.mvc.view.widget.menu.objectList.itemList.length ; ++k ){
									var item = jc.mvc.view.widget.menu.objectList.itemList[k] ;
									item.contract() ;
								}
							}
						}
						,500);
				}
			} ;
			
			jc.mvc.view.widget.menu.objectList.itemList.push( li ) ;
		}
	}
	
	// add all ul
	var ulList = document.getElementsByTagName('ul');
	for( i=0 ; i<ulList.length ; ++i ){
		var ul = ulList[i] ;
		if( containsClassName( ul , jc.mvc.view.widget.menu.objectList.sMenuClassName ) ){
			ul.getAttr = function(sName){
				var k ;
				for( k=0 ; k<this.attributes.length ; ++k ){
					if( sName == this.attributes[k].name ){
						return this.attributes[k].value ;
					}
				}
			}
			
			if( ul.getAttr('tearoff') == 'on' ){
				ul.style.display = 'none' ;
			}
			ul.show = function(){
				this.parentNode.style.position="relative";
				this.style.position="absolute";
				
				var dire = this.getAttr('direction');
				if( 'v' == dire ){
					o.style.left = o.parentNode.offsetWidth+'px';
					o.style.top = 0;
				}else if( 'h' == dire ){
					o.style.left = 0;
					o.style.top = o.parentNode.offsetHeight+'px';
				}else{
				}
				
				this.style.left = o.parentNode.offsetWidth+'px';
				this.style.top = 0;
				
				this.style.display="block";
			};
			ul.hide = function(){
				this.style.display='none';
			}
			if( ! ul.parentNode.isItem ){
				jc.mvc.view.widget.menu.objectList.menuList.push( ul ) ;
			}
		}
	}
	
	console.log(jc.mvc.view.widget.menu.objectList);
});
