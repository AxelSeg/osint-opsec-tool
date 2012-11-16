/*global $, document */

$.ajaxSetup({
    cache: false
});

$(document).ready(function () {

    "use strict";

    function resizeBox() {
        var popMargTop, popMargLeft;
        popMargTop = ($("#keyword-box").height() + 24) / 2;
        popMargLeft = ($("#keyword-box").width() + 24) / 2;
        $("#keyword-box").css({
            'margin-top' : -popMargTop,
            'margin-left' : -popMargLeft
        });
    }

    function getKeywords(format, div) {
        $.get("api/wordpress/get.keywords.php", { format: format }, function (data) {
            $(div).replaceWith('<div id="keywords">' + data + '</div>');
        });
        resizeBox();
    }

    getKeywords('list', '#keywords');

    $(".results-button").click(function () {

        var form, keyword;
        form = $('#sel-keyword');
        keyword = $("#keywords :selected").val();
        $.get("api/wordpress/get.results.php", { keyword: keyword, page: '1' }, function (data) {
            $("#content").replaceWith('<div id=content>' + data + '</div>');
        });

        $('#mask , .popup-hidden').fadeOut(300, function () {
            $('#mask').remove();
        });

        return false;

    });

    $(".keyword-button").click(function () {

        var form, keyword, postString;
        form = $('#add-user-keyword');
        keyword = $("input#keyword").val();
        postString = 'keyword=' + keyword;

        $.ajax({
            type: "POST",
            url: "api/wordpress/add.keyword.php",
            data: postString,
            success: function () {
                getKeywords('list', '#keywords');
                $("input#keyword").replaceWith('<input type="text" name="keyword" id="keyword"></input>');
                resizeBox();
            }
        });
        return false;
    });

    $(".del-keyword-button").click(function () {

        var keyword, postString;
        keyword = $("#keyword-selection option:selected").val();
        postString = 'keyword=' + keyword;

        $.ajax({
            type: "POST",
            url: "api/wordpress/del.keyword.php",
            data: postString,
            success: function () {
                getKeywords('list', '#keywords');
                resizeBox();
            }
        });
        return false;
    });


});
