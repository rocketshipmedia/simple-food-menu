jQuery(document).ready(function($){

	$('.menu-section header').click(function(){
		$(this).parent('div').find('.menu-item').slideToggle(250);
		$(this).toggleClass('items-visible');
	})

});