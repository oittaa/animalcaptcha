/*---------------------------------------
 * AnimalCaptcha frontend
 *---------------------------------------
 * A novel way to tell
 * if you are a human ;)
 *---------------------------------------
 * Author: Maciej "mav" Trębacz
 * Contact: maciej.trebacz@gmail.com
 * Last modified: 17-04-2010
 *---------------------------------------
 * // Initialize with:
 * ac = new AnimalCaptcha(container, form)
 *
 *     container - element where images 
 *                 will show
 *     form - form element to inject 
 *            the answer
 *
 * // Generate new captcha:
 * ac.requestCaptcha();
 *
 * // Check captcha result:
 * ac.checkCaptcha(callback);
 *
 *     callback - function which will
 *                handle check results
 *---------------------------------------*/

var AnimalCaptcha = new Class({
	Implements: [Events, Options],

	// Create new instance of captcha class
	initialize: function(el, form){
		this.container = $(el);

		// Inject a hidden input with captcha answer into the form
		this.answer = new Element("input", {
			"type": "text",
			"name": "captcha",
			"styles": {
				"display": "none"
			}
		}).inject($(form));
	
		// Request new captcha
		this.requestCaptcha();
	},

	// Request new captcha
	requestCaptcha: function()
	{
		new Request({url: "ac.php?generate_captcha", method: "get", onSuccess: function(response) {
			this.container.set('html', '');
			var imageList = response.split(",");
			imageList.forEach(function(imageID)
			{
				var captchaImageDiv = new Element("div", {
					"class": "ac_image_div",
					"events": {
						"click": function(e) {
							if (e.target.hasClass("ac_image_div"))
								$(e.target).toggleClass('selected');
							else
								$(e.target).getParent().toggleClass('selected');

							// Custom event for handling user action
							this.fireEvent("imagePicked", this.container.getElements(".selected").length);
						}.bind(this)
					}
				});
				captchaImageDiv.set('imageID', imageID);
				var captchaImage = new Element("img", {
					"src": "ac.php?get_image=" + imageID,
					"class": "ac_image"
				});
				captchaImage.inject(captchaImageDiv);
				captchaImageDiv.inject(this.container);
			}.bind(this));
		}.bind(this)}).send();
	},

	checkCaptcha: function(callback)
	{
		var image_array = [];
		this.container.getElements(".selected").forEach(function(img){
			image_array.push(img.get("imageID"));
		});
		this.answer.set("value", image_array.join(","));
		new Request({url: "ac.php?check=" + image_array.join(","), method: "get", onSuccess: callback}).send();
	}
});
