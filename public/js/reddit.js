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

    function getUsers(format) {
        $.get("api/reddit/get.users.php", {format: format},  function (data) {
            $("#current-users").replaceWith('<div id="current-users">' + data + '</div>');
            resizeBox();
        });
    }

    getUsers('list');

    function getUsersKeywords(user_wanted, format, div) {
        $.get("api/reddit/get.users.keywords.php", { user: user_wanted, format: format }, function (data) {
            $(div).replaceWith('<div id=user-keywords>' + data + '</div>');
        });
    }

    getUsersKeywords('all', 'list', '#user-keywords');

    $(".results-button").click(function () {

        var form, user, keyword;
        form = $('#sel-user-keyword');
        user = form.find('option:selected').attr('id');
        keyword = $("#user-keywords :selected").val();

        $.get("api/reddit/get.results.php", { user: user, keyword: keyword, page: '1' }, function (data) {
            $("#content").replaceWith('<div id=content>' + data + '</div>');
        });

        $('#mask , .popup-hidden').fadeOut(300, function () {
            $('#mask').remove();
        });

        return false;

    });

    $(".add-user-button").click(function () {

        var user, postString;
        user = $("input#user").val();
        postString = 'user=' + user;

        $.ajax({
            type: "POST",
            url: "api/reddit/add.user.php",
            data: postString,
            success: function () {
                $("input#user").replaceWith('<input type="text" name="user" id="user"></input>'); // Blank out the old user
                getUsers('list');
            }
        });
        return false;
    });

    $(".del-user-button").click(function () {

        var user, postString;
        user = $("#user-selection option:selected").val();
        postString = 'user=' + user;

        $.ajax({
            type: "POST",
            url: "api/reddit/del.user.php",
            data: postString,
            success: function () {
                getUsers('list');
            }
        });
        return false;
    });

    $(".add-keyword-button").click(function () {
        var user, keyword, postString;
        user = $("#user-selection option:selected").val();
        keyword = $("input#keyword").val();
        postString = 'user=' + user + '&keyword=' + keyword;

        $.ajax({
            type: "POST",
            url: "api/reddit/add.user.keyword.php",
            data: postString,
            success: function () {
                $("input#keyword").replaceWith('<input type="text" name="keyword" id="keyword"</input>');
                getUsersKeywords(user, 'list', '#user-keywords');
            }
        });
        return false;
    });

    $(".del-keyword-button").click(function () {
        var user, keyword, postString;
        user = $("#user-selection option:selected").val();
        keyword = $("#keyword-selection option:selected").val();
        postString = 'user=' + user + '&keyword=' + keyword;

        $.ajax({
            type: "POST",
            url: "api/reddit/del.user.keyword.php",
            data: postString,
            success: function () {
                $("input#keyword").replaceWith('<input type="text" name="keyword" id="keyword"</input>');
                getUsersKeywords(user, 'list', '#user-keywords');
            }
        });
        return false;
    });

    $('body').on('change', '#user-selection', function () {
        var user_wanted = $(this).val();
        getUsersKeywords(user_wanted, 'list', '#user-keywords');
    });


});

