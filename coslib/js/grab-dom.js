var page = new WebPage();
var system = require('system');
var url = 'http://twitter.com/#!/search/javascript';
var timeout = 8000;  

function displayHelp () {
    console.log('Usage:');
    console.log(system.args[0] + ' \'http://twitter.com/#!/search/javascript\'');
    phantom.exit();
}


function argParser () {
    if (system.args.length === 1) {        
        displayHelp();
        phantom.exit();          
    } else {
        //console.log(system.args.length);
        url = system.args[1];
        if (system.args[2] != 'undefined') {
            timeout = system.args[2];
        }
        //phantom.exit();        
    }
}

argParser();

page.open(url, function (status) {
    
    
    
    if (status == 'success') {
        // inject jquery
        //page.injectJs("http://code.jquery.com/jquery-latest.min.js", function() {
    // jQuery is loaded, now manipulate the DOM
        //});
        getFullDom();
    } else {
       console.log('failure open page');
    }
});




function getFullDom() {
    window.setTimeout(function () {
        var results = page.evaluate(function() {
            return document.getElementsByTagName('html')[0].innerHTML;
            // If jquery is loaded you can use this: 
            //return $('html').html();
        });
        console.log(results);
        phantom.exit();
    }, timeout);
}
