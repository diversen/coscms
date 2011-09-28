/**
 * @file jquery.tr.js
 * @brief Support for internationalization.
 * @author Jonathan Giroux (Bloutiouf)
 * @site https://github.com/Bloutiouf/jquery.tr
 * @version 1.1
 * @license MIT license <http://www.opensource.org/licenses/MIT>
 * 
 * jquery.tr is a jQuery plugin which enables you to translate text on the
 * client side.
 * 
 * Features:
 * - uses a predefined dictionary.
 * - translates into languages with several plurals.
 * - replaces parameters in translations.
 * - uses cookie information if jQuery.cookie is available.
 * - designed to be used by CouchApps.
 */

(function($) {

	// configuration, feel free to edit the following lines

	/**
	 * Language at the start of the application.
	 * If you use the jQuery's Cookie plugin, then the language will be stored
	 * in a cookie.
	 */
	var language = 'en';

	/**
	 * Name of cookie storing language. Change it if it conflicts.
	 * If you don't use the jQuery's Cookie plugin, it doesn't matter.
	 */
	var cookieName = 'language';

	// end of configuration

	/**
	 * Intern dictionary.
	 */
	var dictionary;

	/**
	 * Standard replace function.
	 */
	var replace = function(str, opt) {
		var args = (typeof opt === 'object' && opt != null) ? opt : arguments;
		return str.replace(/&(\w+)/g, function(match, n) {
			var value = args[n];
			if (value === undefined) {
				return match;
			}
			return value;
		});
	};

	/**
	 * Default translator in case of error or unavailability...
	 */
	var lambda = function(key, opt) {
		var args = (typeof opt === 'object' && opt != null) ? opt : arguments;
		return replace(key, args);
	};

	// load language from cookie
	if ($.cookie) {
		language = $.cookie(cookieName) || language;
	}

	$.tr = {

		/**
		 * @name $.tr.dictionary
		 * @brief Get the current dictionary.
		 * @returns object dictionary.
		 * 
		 * Example: Gets the current dictionary.
		 * @code
		 * var dict = $.tr.dictionary();
		 * @endcode
		 */
		/**
		 * @name $.tr.dictionary
		 * @brief Set the current dictionary.
		 * @param object newDictionary new dictionary.
		 * 
		 * Example: Sets the current dictionary.
		 * @code
		 * $.tr.dictionary(dict);
		 * @endcode
		 */
		dictionary : function(newDictionary) {
			if (newDictionary !== undefined) {
				dictionary = newDictionary;
			}
			return dictionary;
		},

		/**
		 * @name $.tr.language
		 * @brief Get the current language.
		 * @returns string language.
		 * 
		 * Example: Gets the current language.
		 * @code
		 * var lg = $.tr.language();
		 * @endcode
		 */
		/**
		 * @name $.tr.language
		 * @brief Set the current language.
		 * @param string newLanguage new language.
		 * @param bool useCookie optional if true and cookie plugin is
		 * available, do nothing (allows to use a default language)
		 * @returns string language.
		 * 
		 * Example: Sets the current language.
		 * @code
		 * $.tr.language('fr');
		 * @endcode
		 */
		language : function(newLanguage, useCookie) {
			if (newLanguage !== undefined) {
				if (useCookie && $.cookie) {
					var cookieLanguage = $.cookie(cookieName);
					if (cookieLanguage) {
						return cookieLanguage;	
					}
				}
				language = newLanguage;
				if ($.cookie) {
					$.cookie(cookieName, language);
				}
			}
			return language;
		},

		/**
		 * @name $.tr.translator
		 * @brief Get a translator function.
		 * @param object customDictionary optional associative array replacing the
		 * library dictionary.
		 * @param mixed ... list of keys to traverse the dictionary.
		 * @returns function
		 */
		translator : function(customDictionary) {
			
			// varargs
			var args = $.makeArray(arguments);
			
			// which dictionary to use
			var dict = dictionary;
			if (typeof customDictionary == 'object') {
				args.shift();
				dict = customDictionary;
			}

			// if the chosen dictionary is not available...
			if (!dict) {
				return lambda;
			}
			
			// parse through the hierarchy
			var langSet = dict;
			for (var i in args) {
				langSet = langSet[args[i]];
				if (!langSet) {
					return lambda;
				}
			}

			// dictionary for the chosen language
			var lang = langSet[language];

			// if lang is an associative map encoded as a string, parse the map
			if (typeof lang == 'function') {
				lang = lang();
			}

			// if the chosen language is not available...
			if (!lang) {
				return lambda;
			}

			// time to get the real translator
			return function(key, opt) {
				var value = lang[key];
				var args = (typeof opt === 'object' && opt != null) ? opt : arguments;
				if (typeof value === 'string') {
					return replace(value, args);
				} else if (typeof value === 'function') {
					return value(args, replace);
				} else if (typeof value === 'number') {
					return value;
				} else {
					return replace(key, args);
				}
			};
		}

	};

})(jQuery);