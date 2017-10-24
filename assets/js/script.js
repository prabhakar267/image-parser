$(document).ready(function() {
    var spinner = toggleSpinner(),
        submit_button = $("#submit");
    submit_button.click(function(e) {
        e.preventDefault();
        spinner();

        $.ajax({
            url: 'api/image-parser.php?url=' + $('input[name="url"]').val(),
            dataType: 'json',
            beforeSend: function() {
                submit_button.text("Extracting...");
                submit_button.prop('disabled', true);
            },
            success: function(result) {
                spinner();
                if (result.success) {
                    renderStats(result);
                    renderImages(result);
                } else {
                    $('#result').html('Invalid URL!');
                }
            },
            error: function(xhr, resp, text) {
                spinner();
                $('#result').html('Could not connect to server, please try again later');
            },
            complete: function() {
                submit_button.text("Extract");
                submit_button.prop('disabled', false);
            }
        })
    });
});


function renderStats(result) {
    var stats =
        '<strong>URL Searched</strong> : <a href="' + result.url_searched + '" target="_blank">' + $('input[name="url"]').val() + '</a><br>' +
        '<strong>Parent Domain</strong> : <a href="' + result.parent_url + '" target="_blank">' + result.parent_url + '</a><br>';
    $('#stats .other-text').empty().append(stats);
}


function renderImages(result) {
    var images;
    if (0 == result.images.length) {
        images = '<b>No Image Found at your Given Location</b>';
    } else {
        images = result.images.map(function(image) {
            return '<a href="' + image + '"><img src="' + image + '" width="250" style="margin:20px"></a>';
        });
    }
    $('#result').empty().append(images);
}


function toggleSpinner() {
    var isHidden = true;
    return function() {
        if (isHidden) {
            $('#stats').hide();
            $('#result').empty();
            $('.spinner').show();
        } else {
            $('#stats').show();
            $('.spinner').hide();
        }
        isHidden = !isHidden;
    }
}