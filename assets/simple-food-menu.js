jQuery(document).ready(function($){

	//menu section toggling
	$('.sfm-toggle-item-list').click(function(){
		$(this).closest('div').find('.sfm-menu-item').slideToggle(250);
		$(this).closest('div').addClass('sfm-item-list-visible');
	})


	//menu link smooth scrolling
	$('.sfm-menu-links a').click(function(){
	    var target = this.hash;
	    $('html, body').stop().animate({
	        'scrollTop': $(target).offset().top
	    }, 900, 'swing');
	    return false;
	})

});