$(document).ready( function() {

	$('.carousel').carousel();
		$('#recommended-item-carousel').on('slide.bs.carousel', function(e) {
			var from = $('.category-tab .nav li.active').index();
			var next = $(e.relatedTarget);
			var to =  next.index();
		$('.category-tab .nav li').removeClass('active').eq(to).addClass('active');
	});


});