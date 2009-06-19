/**
 * Multi-level Drop Down Menu (multi-ddm)
 * March 26, 2009
 * Corey Hart @ http://www.codenothing.com
 *
 * @timer: [Default 500] Time in milliseconds to hold menu's open on mouseouts
 * @parentMO: CSS class to add/remove from parent menu on mouseover/mouseouts
 * @childMO: CSS class to add/remove to ALL child menus
 * @levels: Array of CSS classes in order of appearance on drop downs
 * @parentTag: List type of the parent menu ('ul' or 'ol')
 * @childTag: List type of each menu level ('ul' or 'ol')
 * @tags: List type of each level in order ('ul' or 'ol' for each)
 * @numberOfLevels: [Default 5] Number of levels the menu has. Will default to 
 * 	length of levels array when childMO is null.
 */ 
;(function(A){A.fn.dropDownMenu=function(I){var D=new Array();var G;var J;var F;var H;var C=A.extend({timer:500,parentMO:null,childMO:null,levels:[],parentTag:"ul",childTag:"ul",tags:[],numberOfLevels:5},I||{});if(C.tags.length>0){C.numberOfLevels=C.tags.length}else{if(C.levels.length){C.numberOfLevels=C.levels.length}}if(C.childMO){for(var E=0;E<C.numberOfLevels;E++){C.levels[E]=C.childMO}}if(C.tags.length<1){for(var E=0;E<C.numberOfLevels;E++){C.tags[E]=C.childTag}}D[0]=A(this).children("li");for(var E=1;E<C.numberOfLevels+2;E++){G=(E==1)?C.parentMO:C.levels[E-2];J=(E==1)?C.parentTag:C.tags[E-2];D[E]=D[E-1].children(C.tag).children("li");D[E-1].attr({rel:G+";"+J}).mouseover(function(){if(H){clearTimeout(H)}F=A(this).attr("rel").split(";");A(this).siblings("li").children("a").removeClass(F[0]).siblings(F[1]).hide();A(this).children("a").addClass(F[0]).siblings(F[1]).show()}).mouseout(function(){F=A(this).attr("rel").split(";");if(F[0]==C.parentMO){H=setTimeout(function(){B()},C.timer)}})}A(document).click(function(){B()});var B=function(){for(var K=D.length;K>-1;K--){if(D[K]&&D[K].attr("rel")){F=D[K].attr("rel").split(";");D[K].children(F[1]).hide().siblings("a").removeClass(F[0])}}A("a",D[0]).removeClass(C.parentMO);if(H){clearTimeout(H)}}}})(jQuery);
