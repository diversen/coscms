
$(function() {
    var a = new RegExp('/' + window.location.host + '/');
    $('a').each(function() {
        if (!a.test(this.href)) {
            $(this).on('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                window.open(this.href, '_blank');
            });
        }
    })
});