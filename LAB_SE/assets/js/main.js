
(function() {

    /* ====================
    Preloader
    ======================= */
	window.onload = function () {
		window.setTimeout(fadeout, 300);
	};

	// Hide preloader earlier (don't wait for all images)
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			window.setTimeout(fadeout, 200);
		});
	} else {
		window.setTimeout(fadeout, 200);
	}

	// Watchdog: ensure preloader hides even if onload never fires
	window.setTimeout(fadeout, 3000);

	function fadeout() {
		var preloader = document.querySelector('.preloader');
		if (preloader) {
			preloader.style.opacity = '0';
			preloader.style.display = 'none';
		}
	}


    window.onscroll = function () {
        // show or hide the back-top-top button
        var backToTo = document.querySelector(".scroll-top");
		if (backToTo) {
			if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
				backToTo.style.display = "flex";
			} else {
				backToTo.style.display = "none";
			}
		}
    };

    // section menu active
	function onScroll(event) {
		var sections = document.querySelectorAll('.page-scroll');
		var scrollPos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

		for (var i = 0; i < sections.length; i++) {
			var currLink = sections[i];
			var val = currLink.getAttribute('href');
			var refElement = val ? document.querySelector(val) : null;
			if (!refElement) continue;
			var scrollTopMinus = scrollPos + 73;
			if (refElement.offsetTop <= scrollTopMinus && (refElement.offsetTop + refElement.offsetHeight > scrollTopMinus)) {
				var activeLink = document.querySelector('.page-scroll.active');
				if (activeLink) activeLink.classList.remove('active');
				currLink.classList.add('active');
			} else {
				currLink.classList.remove('active');
			}
		}
	};

    window.document.addEventListener('scroll', onScroll);


      //====== counter up 
	  if (typeof counterUp !== 'undefined') {
		  var cu = new counterUp({
			start: 0,
			duration: 2000,
			intvalues: true,
			interval: 100,
			append: " ",
		  });
		  cu.start();
	  }

	// WOW active
	if (typeof WOW !== 'undefined') {
		new WOW().init();
	}

})();