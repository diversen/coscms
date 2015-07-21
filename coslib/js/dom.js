var page = require('webpage').create();
var system = require('system');
var url = 'http://twitter.com/search/javascript';
var timeout = 8000;  

page.settings.userAgent = 'Mozilla/4.8 [en] (X11; U; SunOS; 5.7 sun4u)';
page.viewportSize = { width: 1024, height: 800 };


function displayHelp () {
    console.log('Usage:');
    console.log(system.args[0] + ' \'http://twitter.com/search/javascript\'');
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

// parse args
argParser();

page.onInitialized = function() {
  page.evaluate(function(domContentLoadedMsg) {
    document.addEventListener('DOMContentLoaded', function() {
      window.callPhantom('DOMContentLoaded');
    }, false);
  });
};

/*
page.onCallback = function(data) {
  // your code here
  console.log('DOMContentLoaded');
  phantom.exit(0);
};
*/
page.open(url, function (status) {
    
    
    
    if (status == 'success') {
        // inject jquery
        //page.injectJs("http://code.jquery.com/jquery-latest.min.js", function() {
    // jQuery is loaded, now manipulate the DOM
        //});
        
        //page.viewportSize = { width: 1024, height: 800 };
        
        getFullDom();
        page.render("test.jpg", { format: "jpg" });
        phantom.exit();
    } else {
       console.log('failure open page');
    }
});

//page.open('http://twitter.com/search/javascript');

function getFullDom() {
    //window.setTimeout(function () {
        var results = page.evaluate(function() {
            return document.getElementsByTagName('html')[0].innerHTML;
            // If jquery is loaded you can use this: 
            //return $('html').html();
        });
        //console.log(results);
        
    //}, timeout);
}
