<!--
window.onload=$(function(){
	// Tabs
	$('#fe_tabs').tabs();

    $('#news_ticker').cycle({
        fx:      'scrollHorz',
        timeout:  0,
        prev:    '#prev',
        next:    '#next',
		pager:   '#nav',
        pagerAnchorBuilder: pagerFactory
    });

    function pagerFactory(idx, slide) {
        var s = idx > 2 ? ' style="display:none"' : '';
        return '<li'+s+'><a href="#">'+(idx+1)+'</a></li>';
    };

});
-->