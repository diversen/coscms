(function( $ ){

  $.fn.showhide = function( options ) {  

    // Create some defaults, extending them with any options that were provided
    var settings = $.extend( {
      'initial'         : 'hide',
      'element' : '.sliding_div',
      'hide_elements' : ''

    }, options);

    //alert(settings.hide_elements);
    

    $(settings.element).hide();
    this.show();
 
    $(this).click(function(){
        $(settings.hide_elements).hide();
        $(settings.element).slideToggle();
    });

  };
})( jQuery );