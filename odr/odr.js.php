<?php
header("Content-type: text/javascript");
?>

demo_reset_domain = '<?php echo $uri = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($_SERVER['HTTP_HTTPS']) && $_SERVER['HTTP_HTTPS'] != 'off') || $_SERVER['REQUEST_SCHEME'] == 'https' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'].str_replace('odr/'.basename(__FILE__), null, $_SERVER['REQUEST_URI']); ?>';
var jQueryResetFound = false;
function initJQuery() {

//if the jQuery object isn't available
if (typeof(jQuery) == 'undefined') {


if (! jQueryResetFound) {
//only output the script once..
jQueryResetFound = true;

//output the script (load it from google api)
document.write("<scr" + "ipt type=\"text/javascript\" src=\"odr/jquery-1.11.3.min.js\"></scr" + "ipt>");
}
setTimeout("initJQuery()", 50);
} else {

$(function() {


check_demo_reset();

// make draggable
var $body = $('body');
    var $target = null;
    var isDraggEnabled = false;

    $body.on("mousedown", "div", function(e) {
    	//$this = $(this);
    	$this = $("#reset_demo_widget");
       	isDraggEnabled = $this.data("draggable");

       	if (isDraggEnabled) {
       		if(e.offsetX==undefined){
				x = e.pageX-$(this).offset().left;
				y = e.pageY-$(this).offset().top;
			}else{
				x = e.offsetX;
				y = e.offsetY;
			};

			$this.addClass('draggable');
        	$body.addClass('noselect');
        	//$target = $(e.target);
        	$target = $this;
       	};

    });
    
     $body.on("mouseup", function(e) {
        $target = null;
        $body.find(".draggable").removeClass('draggable');
        $body.removeClass('noselect');
    });
    
     $body.on("mousemove", function(e) {
        if ($target) {
            $target.offset({
                top: e.pageY  - y,
                left: e.pageX - x
            });
        };     
     });
// end draggable


});


}

}
initJQuery();



function check_demo_reset(){
if($('#reset_demo_widget').length==0){
$('body').append('<div id="reset_demo_widget" data-draggable="true"><div class="reset_timer_text">Time until demo reset:</div><div class="reset_timer_time">-- : -- : --</div></div>');
}

$.get( demo_reset_domain+"odr/functions.php", function( data ) {
var left_time = parseInt(data);
if(left_time>0){

setTimeout(function(){
display_timer(left_time-1);
}, 1000);

setTimeout("reload_page()", 1000*(left_time+1)); // a little late timeout to make sure old page expires.
} else {
reload_page();
}

});
}

function display_timer(left_time){
$('#reset_demo_widget').html('<div class="reset_timer_text">Demo will reset in :</div><div class="reset_timer_time">'+reset_secondsToHms(left_time)+'</div>');
setTimeout(function(){
display_timer(left_time-1);
}, 1000);
}


function reload_page(){
var xhtml = '<div class="reset_demo_popup">\
<div class="reset_demo_popup_bg"></div>\
<div class="reset_demo_popup_data"><h2>Please wait while the demo is cleaning.</h2>\
<img class="reset-loading" src="'+demo_reset_domain+'/odr/loading.gif" />\
<p>All data on this site is being removed.</p>\
</div>\
</div>';
$('body').append(xhtml);
$('#reset_demo_widget').hide();
$.get(demo_reset_domain+"odr/functions.php", function( data ) {
setTimeout("reset_reload_final_func()", 3000);
});
}


function reset_reload_final_func(){
document.location.href=document.location.href;
}



function reset_secondsToHms(d) {
d = Number(d);
var h = Math.floor(d / 3600);
var m = Math.floor(d % 3600 / 60);
var s = Math.floor(d % 3600 % 60);
return ((h > 0 ? h + ":" + (m < 10 ? "0" : "") : "") + m + ":" + (s < 10 ? "0" : "") + s);
}


