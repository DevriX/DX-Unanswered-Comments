jQuery(document).ready(function ($) {
    $(".mark_as_replied").on('click', function (e) {
        e.preventDefault();
        var comment_id = $(this).data("value");
        $.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                action: "mark_comment_as_replied",
                "selected_comment_id" : comment_id,
            },
            success: function(response) {
                location.reload();
            }
       });
    });
    $(".mark_as_non_replied").on('click', function (e) {
        e.preventDefault();
        var comment_id = $(this).data("value");
        $.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                action: "mark_comment_as_non_replied",
                "selected_comment_id" : comment_id,
            },
            success: function(response) {
                location.reload();
            }
       });
    });
 });