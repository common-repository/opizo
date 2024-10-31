(function ($) {
    'use strict';
    $(document).ready(function () {
        $("#opizo-shrink-form").submit(function (e) {
            e.preventDefault();
            var button = $(this).find("button");

            if(button.hasClass("disabled"))
                return ;

            var data = $(this).serialize();
            console.log(data);
            button.addClass("disabled");
            jQuery.post(ajaxurl, data, function (response) {
                $("#shrink-result").find("textarea").val(response);
                $("#shrink-result").show();
                $("#links-to-shrink").val("");
                $("#opizo-shrink-form").hide();
                button.removeClass("disabled");
            });
        });
        $("#new-opizo-shrink-button").click(function(){
            $("#opizo-shrink-form").show();
            $("#shrink-result").hide();
        });

        $(".opizo-delete-link").click(function (e) {
            e.preventDefault();
            if(confirm(opizo_translations.delete_link))
            {
                var _this = this;
                var id = $(_this).attr("data-opizo-link-id");
                var data = {
                    action: 'opizo_delete_link',
                    id: id
                };
                jQuery.post(ajaxurl, data, function (response) {
                    $(_this).closest('tr').fadeOut(250, function(){$(this).remove();});
                });
            }
        });

        $(".opizo-delete-old-post").click(function (e) {
            e.preventDefault();
            if(confirm(opizo_translations.delete_old_post))
            {
                var _this = this;
                var id = $(_this).attr("data-opizo-old-post-id");
                var data = {
                    action: 'opizo_delete_old_post',
                    id: id
                };
                jQuery.post(ajaxurl, data, function (response) {
                    $(_this).closest('tr').fadeOut(250, function(){$(this).remove();});
                });
            }
        });

        var show = false;
        $("#toggle_tutorial").click(function(){

            if(show)
                $("#image_tutorial").fadeOut();
            else
                $("#image_tutorial").fadeIn();

            show = !show;
        });
    });
})(jQuery);
