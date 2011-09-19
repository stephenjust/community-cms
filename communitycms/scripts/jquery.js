/*!
 * Multi-level Drop Down Menu 2.1
 * October 1, 2009
 * Corey Hart @ http://www.codenothing.com
 *
 * Credit to Shaun Johnson for pointing out the Prototype confliction, and IE6 bgiframe fix
 */
(function($, undefined){
	// Needed for IE Compatibility (Closing menus must be done backwards in IE)
	// Ensure that no complications arise from other libraries modifiying the
	// array functionality (and hope that they store the old reverse function into _reverse)
	$.fn.reverse = []._reverse||[].reverse;

	// bgiframe is needed to fix z-index problem for IE6 users.
	$.fn.bgiframe = $.fn.bgiframe ? $.fn.bgiframe : $.fn.bgIframe ? $.fn.bgIframe : function(){
		// For applications that don't have bgiframe plugin installed, create a useless
		// function that doesn't break the chain
		return this;
	};

	// Drop Down Plugin
	$.fn.dropDownMenu = function(options){
		return this.each(function(){
			// Defaults with metadata support
			var $mainObj = $(this), menus = [], classname, timeout, $obj, $obj2,
				settings = $.extend({
					timer: 500,
					parentMO: undefined,
					childMO: undefined,
					levels: [],
					numberOfLevels: 5
				}, options||{}, $.metadata ? $mainObj.metadata() : {});

			// Set number of levels
			if (settings.levels.length){
				settings.numberOfLevels = settings.levels.length;
			}else{
				settings.levels[0] = settings.parentMO ? settings.parentMO : settings.childMO;
				for (var i=1; i<settings.numberOfLevels+1; i++)
					settings.levels[i] = settings.childMO;
			}

			// Run through each level
			menus[0] = $mainObj.children('li');
			for (var i=1; i<settings.numberOfLevels+1; i++){
				// Set Vars
				classname = settings.levels[i-1];
				menus[i] = menus[i-1].children('ul').children('li');

				// Action
				menus[i-1].bind('mouseover.multi-ddm', function(){
					// Defaults
					$obj = $(this); $obj2 = $obj.children('a');

					// Clear closing timer if open
					if (timeout)
						clearTimeout(timeout);

					// Remove all mouseover classes
					$('a', $obj.siblings('li')).each(function(){
						var $a = $(this), classname = $a.data('classname');
						if ($a.hasClass(classname))
							$a.removeClass(classname);
					});

					// Hide open menus and open current menu
					$obj.siblings('li').find('ul:visible').reverse().hide();
					$obj2.addClass( $obj2.data('classname') ).siblings('ul').bgiframe().show();
				}).bind('mouseout.multi-ddm', function(){
					if ($(this).children('a').data('classname') == settings.levels[0])
						timeout = setTimeout(closemenu, settings.timer);
				}).children('a').data('classname', classname);
			}

			// Allows user option to close menus by clicking outside the menu on the body
			$(document).click(closemenu);

			// Closes all open menus
			function closemenu(){
				// Clear mouseovers
				$('a', $mainObj).each(function(){
					var $a = $(this), classname = $a.data('classname');
					if ($a.hasClass(classname))
						$a.removeClass(classname);
				});

				// Close Menus backwards for IE Compatibility
				$('ul:visible', $mainObj).reverse().hide();

				// Clear timer var
				if (timeout)
					clearTimeout(timeout);
			}
		});
	};
})(jQuery);

/*
 *
 * Copyright (c) 2006/2007 Sam Collett (http://www.texotela.co.uk)
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Version 2.0
 * Demo: http://www.texotela.co.uk/code/jquery/newsticker/
 *
 * $LastChangedDate: 2007-05-29 11:31:36 +0100 (Tue, 29 May 2007) $
 * $Rev: 2005 $
 *
 */

(function($) {
/*
 * A basic news ticker.
 *
 * @name     newsticker (or newsTicker)
 * @param    delay      Delay (in milliseconds) between iterations. Default 4 seconds (4000ms)
 * @author   Sam Collett (http://www.texotela.co.uk)
 * @example  $("#news").newsticker(); // or $("#news").newsTicker(5000);
 *
 */
$.fn.newsTicker = $.fn.newsticker = function(delay)
{
	delay = delay || 4000;
	initTicker = function(el)
	{
		stopTicker(el);
		el.items = $("li", el);
		// hide all items (except first one)
		el.items.not(":eq(0)").hide().end();
		// current item
		el.currentitem = 0;
		startTicker(el);
	};
	startTicker = function(el)
	{
		el.tickfn = setInterval(function() { doTick(el) }, delay)
	};
	stopTicker = function(el)
	{
		clearInterval(el.tickfn);
	};
	pauseTicker = function(el)
	{
		el.pause = true;
	};
	resumeTicker = function(el)
	{
		el.pause = false;
	};
	doTick = function(el)
	{
		// don't run if paused
		if(el.pause) return;
		// pause until animation has finished
		el.pause = true;
		// hide current item
		$(el.items[el.currentitem]).fadeOut("slow",
			function()
			{
				$(this).hide();
				// move to next item and show
				el.currentitem = ++el.currentitem % (el.items.size());
				$(el.items[el.currentitem]).fadeIn("slow",
					function()
					{
						el.pause = false;
					}
				);
			}
		);
	};
	this.each(
		function()
		{
			if(this.nodeName.toLowerCase()!= "ul") return;
			initTicker(this);
		}
	)
	.addClass("newsticker")
	.hover(
		function()
		{
			// pause if hovered over
			pauseTicker(this);
		},
		function()
		{
			// resume when not hovered over
			resumeTicker(this);
		}
	);
	return this;
};

})(jQuery);