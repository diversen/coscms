(function( $ ){

  $.fn.showhide = function( options ) {  

    // Create some defaults, extending them with any options that were provided
    var settings = $.extend( {
      'initial'         : 'hide',
      'hide_class' : 'form_hide',
      'show_class' : 'form_show',
      'element' : '.sliding_div'

    }, options);


    $(settings.element).hide();
    this.show();
 
    $(this).click(function(){
        $(settings.element).slideToggle();
    });

  };
})( jQuery );