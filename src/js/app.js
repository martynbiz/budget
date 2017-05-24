(function() {

    $(document).foundation();

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

    // $(".slicknav").slicknav({
    //     "appendTo": "#slicknav",
    //     "label": "",
    //     "brand": "Budget",
    //     "duplicate": false
    // });

    // //Clone both menus to keep them intact
    // var combinedMenu = $('.slicknav:nth-child(1)').clone();
    // var secondMenu = $('.slicknav:nth-child(2)').clone();
    //
    // secondMenu.children('li').appendTo(combinedMenu);
    //
    // combinedMenu.slicknav({
    //     duplicate:false
    // });

    // var nav = responsiveNav(".slicknav", { // Selector
    // //   animate: true, // Boolean: Use CSS3 transitions, true or false
    // //   transition: 284, // Integer: Speed of the transition, in milliseconds
    // //   label: "Menu", // String: Label for the navigation toggle
    // //   insert: "before", // String: Insert the toggle before or after the navigation
    // //   customToggle: "", // Selector: Specify the ID of a custom toggle
    // //   closeOnNavClick: false, // Boolean: Close the navigation when one of the links are clicked
    // //   openPos: "relative", // String: Position of the opened nav, relative or static
    // //   navClass: "nav-collapse", // String: Default CSS class. If changed, you need to edit the CSS too!
    // //   navActiveClass: "js-nav-active", // String: Class that is added to  element when nav is active
    // //   jsClass: "js", // String: 'JS enabled' class which is added to  element
    // //   init: function(){}, // Function: Init callback
    // //   open: function(){}, // Function: Open callback
    // //   close: function(){} // Function: Close callback
    // });

}).call(this);
