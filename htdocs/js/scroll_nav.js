// 
// From:
// http://code.stephenmorley.org/javascript/detachable-navigation/ 
// http://code.stephenmorley.org/about-this-site/copyright/
// another solution: 
// http://www.pixelbind.com/make-a-div-stick-when-you-scroll/

/* Handles the page being scrolled by ensuring the navigation is always in
 * view.
 */
function handleScroll(){

  // check that this is a relatively modern browser
  if (window.XMLHttpRequest){

    // determine the distance scrolled down the page
    var offset = window.pageYOffset
               ? window.pageYOffset
               : document.documentElement.scrollTop;

    // set the appropriate class on the navigation
    document.getElementById('sidebar_left').className =
        (offset > 170 ? 'fixed' : '');

  }

}

// add the scroll event listener
if (window.addEventListener){
  window.addEventListener('scroll', handleScroll, false);
}else{
  window.attachEvent('onscroll', handleScroll);
}
