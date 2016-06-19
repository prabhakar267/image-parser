function checkEnter(event) {
	if(event.keyCode === 13) {
		parse();
	}
}
function parse() {
	var url = $("#url").val();
	if (url == "") {
		$("#effect").slideDown(500, function() {
			$("#result").html("<h2>Please enter a URL</h2>");
		});
	}
	else {
		$(".loader").fadeIn(500);
		var api = "../extract.php?url=" + url;
		$.getJSON(api, function(data) {
			let url_searched = "URL Searched = " + data.url_searched;
			if(data.valid_url == true) {
				let parent_url = "Parent URL = " + data.parent_url;
				if(data.success == true) {
					$(".loader").fadeOut(500, function() {
						$("#effect").slideUp(500, function() {
							$("#result").html(url_searched + "<br>" + parent_url);
							$("#result").append("<br><br>");
							$.each(data.images, function (index, value) {
								$("#result").append("<img src='" + value + "' class='images'>");
							})
						});
					});
				}
				else {
					$(".loader").fadeOut(500, function() {
						$("#effect").slideDown(500, function() {
							$("#result").html(url_searched + "<br>" + parent_url);
							$("#result").html(url_searched + "<br><h2>No Image Found<h2>");
						});
					});
				}
			}
			else {
				$(".loader").fadeOut(500, function() {
					$("#effect").slideDown(500, function() {
						$("#result").html(url_searched + "<br><h2>Invalid URL<h2>");
					});
				});
			}
		});
	}
}