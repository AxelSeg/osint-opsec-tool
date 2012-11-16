/*global $, document */

$.ajaxSetup({
    cache: false
});

$(document).on('change', '#results-page', function () {

    var user, keyword, page;
    user    = $('#results-user').html();
    keyword = $('#results-keyword').html();
    page    = $('#results-page').val();
    source  = $('#results-source').html();

    console.log(user);
    console.log(keyword);
    console.log(page);
    console.log(source);
    $.get("api/" + source + "/get.results.php", { user: user, keyword: keyword, page: page }, function (data) {
        $("#content").replaceWith('<div id=content>' + data + '</div>');
    });
});

function resizeBox() {
    var popMargTop, popMargLeft;
    popMargTop = ($("#keyword-box").height() + 24) / 2;
    popMargLeft = ($("#keyword-box").width() + 24) / 2;
    $("#keyword-box").css({
        'margin-top' : -popMargTop,
        'margin-left' : -popMargLeft
    });
}

function addLoadingGif() {
    $('#keyword-box').html("<img src='logos/ajax-loader.gif' alt='loading...' />", function () {
        resizeBox();
    });
}

function loadBoxContent(page) {
    addLoadingGif();
    $('#keyword-box').load(page, function () {
        resizeBox();
    });
}

function hideBoxContent() {
    $('#mask , .popup-hidden').fadeOut(300, function () {
        $('#mask').remove();
    });
}

$(document).on('click', '.goBack', function () {
    loadBoxContent("sources.php");
});

$(document).on('click', 'a.main-menu', function () {

    $("#keyword-box").fadeIn(300);
    resizeBox();

    $('body').append('<div id="mask"></div>');
    $('#mask').fadeIn(300);

    return false;
});

$(document).on('click', 'a.close, #mask', function () {
    hideBoxContent();
    return false;
});

$(document).on('click', '.twitter-select', function () {
    loadBoxContent("api/twitter/select.input.php");
});

$(document).on('click', '.twitter-options', function () {
    loadBoxContent("api/twitter/options.input.php");
});

$(document).on('click', '.reddit-select', function () {
    loadBoxContent("api/reddit/select.input.php");
});

$(document).on('click', '.reddit-options', function () {
    loadBoxContent("api/reddit/options.input.php");
});

$(document).on('click', '.stackexchange-select', function () {
    loadBoxContent("api/stackexchange/select.input.php");
});

$(document).on('click', '.stackexchange-options', function () {
    loadBoxContent("api/stackexchange/options.input.php");
});

$(document).on('click', '.facebook-select', function () {
    loadBoxContent("api/facebook/select.input.php");
});

$(document).on('click', '.facebook-options', function () {
    loadBoxContent("api/facebook/options.input.php");
});

$(document).on('click', '.pastebin-select', function () {
    loadBoxContent("api/pastebin/select.input.php");
});

$(document).on('click', '.pastebin-options', function () {
    loadBoxContent("api/pastebin/options.input.php");
});

$(document).on('click', '.wordpress-select', function () {
    loadBoxContent("api/wordpress/select.input.php");
});

$(document).on('click', '.wordpress-options', function () {
    loadBoxContent("api/wordpress/options.input.php");
});
