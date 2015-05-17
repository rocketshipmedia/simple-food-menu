jQuery(document).ready(function($){

	//menu section toggling
	$('.menu-section .toggle-item-list').click(function(){
		$(this).closest('div').find('.menu-item').slideToggle(250);
		$(this).closest('div').addClass('item-list-visible');
	})

	//menu link smooth scrolling
	$('.menu-links a').click(function(){
	    var target = this.hash;
	    $('html, body').stop().animate({
	        'scrollTop': $(target).offset().top
	    }, 900, 'swing');
	    return false;
	})

});