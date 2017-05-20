(function() {

    // $(document).foundation();

    $.fn.confirmSubmit = function(message) {
    if (confirm(message)) {
      return $(this).submit();
    }
    };

    $(".callout.alert").not(".callout-important").delay(3000).slideUp(300);

    // init the fund switcher
    $("select[name='filter__fund_id']").on("change", function() {
        $('form#switch_fund_form').submit();
    });

    // init the month switcher
    $("select[name='filter__month']").on("change", function() {
        $('form#switch_month_form').submit();
    });

}).call(this);
