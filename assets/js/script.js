var timer;

$(document).ready(function() {

	$(".title a").on("click", function() {
		
		var id = $(this).attr("data-linkId");
		var url = $(this).attr("href");

		if(!id) {
			alert("data-linkId attribute not found");
		}

		increaseLinkClicks(id, url);

		return false;
	});

	// Image search tab
	var grid = $(".image-result");

	grid.on("layoutComplete", function() {
		$(".grid-item img").css("visibility", "visible");
	});

	grid.masonry({
		itemSelector: '.grid-item',
		columnWidth: 200,
		gutter: 5,
		transitionDuration: 0,
		isInitLayout: false
	});


	$("[data-fancybox]").fancybox({
		caption: function(instance, item) {
			var caption = $(this).data('caption') || '';
			var siteUrl = $(this).data('siteurl') || '';

			if (item.type === 'image') {
				caption = (caption.length ? caption + '<br/>' : '') 
				+ '<a href="' + item.src + '">View image</a><br>'
				+ '<a href="' + siteUrl + '">View Page</a>';
			}

			return caption;
		}
	});

});

function loadImage(src, className) {
	var image = $("<img>");
	image.on("load", function() {
		$("." + className + " a").append(image);
		
		clearTimeout(timer);
		
		timer = setTimeout(function() {
			$(".image-result").masonry();
		}, 500);
		
	});

	image.on("error", function() {
		$("." + className).remove();
		$.post("ajax/setBroken.php", {src:src});
	});

	image.attr("src", src);
} 


function increaseLinkClicks(linkId, url) {

	$.post("ajax/updateLinkCount.php", {linkId: linkId})
	.done(function(result) {
		if(result != "") {
			alert(result);
			return;
		}

		window.location.href = url;
	});

}

