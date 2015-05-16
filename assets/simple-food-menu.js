jQuery(document).ready(function($){

	$('.menu-section .toggle-item-list').click(function(){
		$(this).closest('div').find('.menu-item').slideToggle(250);
		$(this).closest('div').addClass('item-list-visible');
	})

});