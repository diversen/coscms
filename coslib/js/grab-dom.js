var page = new WebPage();
var system = require('system');

var url = 'http://twitter.com/#!/search/javascript';
var timeout = 4000;  
var test = page.injectJs('./args.js');
console.log(test);
//argsParser();
function displayHelp () {
    console.log('Usage:');
    console.log(system.args[0] + ' \'http://twitter.com/#!/search/javascript\'');
    phantom.exit();
}

var options = {
    'commands' : {
        'url' : {
            'required': true,
            'help': 'page of url to grab'
        },
        'timeout' : {
            'required': true,
            'help': 'set timeout for loading Ajax of page to grab'
        },
        'help': {
            'help': displayHelp()
        }
    }
   
}


function argParser (options) {
    if (system.args.length === 1) {        
        options.help.help();        
    } else {
        console.log(system.args.length);
        phantom.exit();
        url = system.args[1];
        console.log(system.args[2]);
        if (system.args[2] != 'undefined') {
            timeout = system.args[2];
        }
        phantom.exit();        
    }
}

argParser(options);

page.open(url, function (status) {
    if (status === 'success') {
        getFullDom();
    } else {
       console.log('failure open page');
    }
});

function getFullDom() {
    window.setTimeout(function () {
        var results = page.evaluate(function() {
            return document.getElementsByTagName('html')[0].innerHTML
            // If jquery is loaded you can use this: 
            //return $('html').html();
        });
        console.log(results);
        phantom.exit();
    }, timeout);
}
