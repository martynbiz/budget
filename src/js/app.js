(function() {

    $(document).foundation();

    $.fn.confirmSubmit = function(message) {
        if (confirm(message)) {
          return $(this).submit();
        }
    };

    $(".callout.alert").not(".callout-important").delay(3000).slideUp(300);

    // filter selects
    $("form#filters_form select").on("change", function() {
        $('form#filters_form').submit();
    });

}).call(this);
