# jquery.cookiebar
Stick it to the EU Government with this compliant cookie bar

It's a paradox. "If you dont want tracking cookies, click here to let us know you dont want tracked, and we'll place a tracking cookie on your computer that tracks you noting the fact that you dont want tracked.". What a lot of nonsense. 

This jquery plugin is a githubbed fork of http://www.primebox.co.uk/projects/cookie-bar/ 

Usage
-----
```javascript
<script type="text/javascript">
	$(document).ready(function(){
		$.cookieBar({
			fixed: true,
			bottom: true
		});
	});
</script>
```
Options
-------
```javascript
message: 'We use cookies to track usage and preferences.', //Message displayed on bar
acceptButton: true, //Set to true to show accept/enable button
acceptText: 'I Understand', //Text on accept/enable button
acceptFunction: false, //Callback function that triggers when user accepts
declineButton: false, //Set to true to show decline/disable button
declineText: 'Disable Cookies', //Text on decline/disable button
declineFunction: false, //Callback function that triggers when user declines
policyButton: false, //Set to true to show Privacy Policy button
policyText: 'Privacy Policy', //Text on Privacy Policy button
policyFunction: false, //Callback function that triggers before redirect when user clicks policy button
policyURL: '/privacy-policy/', //URL of Privacy Policy
autoEnable: true, //Set to true for cookies to be accepted automatically. Banner still shows
acceptOnContinue: false, //Set to true to silently accept cookies when visitor moves to another page
expireDays: 365, //Number of days for cookieBar cookie to be stored for
forceShow: false, //Force cookieBar to show regardless of user cookie preference
effect: 'slide', //Options: slide, fade, hide
element: 'body', //Element to append/prepend cookieBar to. Remember "." for class or "#" for id.
append: false, //Set to true for cookieBar HTML to be placed at base of website. YMMV
fixed: false, //Set to true to add the class "fixed" to the cookie bar. Default CSS should fix the position
bottom: false, //Force CSS when fixed, so bar appears at bottom of website
customClass: '', // Optional cookie bar class. Target #cookiebar.<customClass> to avoid !important overwrites and separate multiple classes by spaces
zindex: '', //Can be set in CSS, although some may prefer to set here
redirect: String(window.location.href), //Current location. Setting to false stops redirect
domain: String(window.location.hostname), //Location of privacy policy
referrer: String(document.referrer) //Where visitor has come from
```
Minifying Files
-------
1. Install [node and npm](http://nodejs.org/) on your machine.
2. Run "npm install" to install required grunt packages.
3. Run "grunt minify" to minify js and css.
4. Remember to add the short license back into the top of the minified files.

Change Log
-------
####v1.2.1####

 * Fixed a bug that occurs when autoEnable and redirect are both false the bar does not hide.

####v1.2.0####

 * Added support for including classes in the cookie bar.
 * Added custom function support for policy button.

####v1.1.0####

 * Added dist folder that contains minified files.
 * Added custom function support for accept and decline buttons.
 * Added support for stopping redirect after accepting.

####v1.0.0####

 * Initial jquery.cookiebar release