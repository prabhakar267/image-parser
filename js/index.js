function checkEnter(event) {
	if(event.keyCode === 13) {
		parse();
	}
}
$(window).load(function() {
	// Animate loader off screen
	$(".se-pre-con").fadeOut("slow");;
});
function parse() {
	var url = $("#url").val();
	var api = "../Image-Extractor/php/extract.php?url=" + url;
	$.getJSON(api, function(data) {
		let url_searched = "URL Searched = " + data.url_searched;
		if(data.valid_url == true) {
			let parent_url = "Parent URL = " + data.parent_url;
			$("#result").html(url_searched + "<br>" + parent_url);
			if(data.success == true) {
				$("#effect").slideUp("slow");
				$("#result").append("<br><br>");
				$.each(data.images, function (index, value) {
					$("#result").append("<img src='" + value + "' class='images img-thumbnail'>");
				})
			}
			else {
				$("#effect").slideDown("slow");
				$("#result").html(url_searched + "<br><h2>No Image Found<h2>");
			}
		}
		else {
			if (url == "") {
				$("#effect").slideDown("slow");
				$("#result").html("<h2>Please enter a URL</h2>");
			}
			else {
				$("#effect").slideDown("slow");
				$("#result").html(url_searched + "<br><h2>Invalid URL<h2>");
			}
		}
	});
}