(function() {

    $(document).foundation();

    $.fn.confirmSubmit = function(message) {
    if (confirm(message)) {
      return $(this).submit();
    }
    };

    $(".alert").not(".alert-important").delay(3000).slideUp(300);

}).call(this);
