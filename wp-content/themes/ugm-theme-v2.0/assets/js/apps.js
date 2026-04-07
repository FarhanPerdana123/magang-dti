// =SCROLL HEADER CONDITION
jQuery(window).scroll(function($) {
	var postContent = jQuery("#content").position().top;
	var contentHeight = (jQuery(".single-post").height()) / 1.5;
	jQuery(".share-box-wrapper").height(contentHeight);
	if ( jQuery(this).scrollTop() > postContent) {
		jQuery(".share-box-wrapper .share-box").addClass("sticky");
	} else {
		jQuery(".share-box-wrapper .share-box").removeClass("sticky");
	}

	if (jQuery(this).scrollTop() > contentHeight ) {
		jQuery(".share-box-wrapper .share-box").addClass("holdy");
	} else {
		jQuery(".share-box-wrapper .share-box").removeClass("holdy");
	}

});

;(function($){
  $.fn.customerPopup = function (e, intWidth, intHeight, blnResize) {
    
    // Prevent default anchor event
    e.preventDefault();
    
    // Set values for window
    intWidth = intWidth || '500';
    intHeight = intHeight || '400';
    strResize = (blnResize ? 'yes' : 'no');

    // Set title and open popup with focus on it
    var strTitle = ((typeof this.attr('title') !== 'undefined') ? this.attr('title') : 'Social Share'),
        strParam = 'width=' + intWidth + ',height=' + intHeight + ',resizable=' + strResize,            
        objWindow = window.open(this.attr('href'), strTitle, strParam).focus();
  }
    
}(jQuery));

jQuery(document).ready(function($) {
	$(".post-slider").slick({
		dots: true,
		lazyLoad: '',
		autoplay: true,
		autoplaySpeed: 7000,
		//pauseOnHover: true
	});
	
	// Gallery Slider Preview
	$(".slider-preview").slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: false,
		fade: true,
		asNavFor: '.slider-nav',
		responsive: [
			{
				breakpoint: 600,
				settings: {
					arrows: true,
					asNavFor: '',
					fade: false
				}
			}
		]
	});

	// Gallery Slider Nav
	$(".slider-nav").slick({
		slidesToShow: 3,
		slidesToScroll: 1,
		asNavFor: '.slider-preview',
		dots: false,
		focusOnSelect: true
	});

	// Navbar on mobile
	$(".dropdown > a").click(function(event) {
		event.stopPropagation();
		$(this).parent().find(".dropdown-menu").toggle();
	});

	// Click dropdown menu on mobile
	$(".dropdown").on("click", function(){
		$(this).toggleClass("active");
	});

	// Navbar dropdown on mobile
	function checkWidth() {
		// alert("berubah");
		var screenWidth = $(window).width();
		if (screenWidth > 992) {
			$(".dropdown > a").on("click", function(e) {
				$(this).parent().toggleClass("active");
				$(this).parent().find(".dropdown-menu").toggleClass("open");

				//e.preventDefault();
			});
		}
	}
	checkWidth();

	$(window).resize(checkWidth);
	$(".topbar").scroll(function(){
		$(this).addClass('scrolled');
	});

	$('.share-box a').on("click", function(e) {
		$(this).customerPopup(e);
	});

  	$(".helpCenter").click(function(){
		$(".helpCenter__content").toggleClass('open');
		$(".helpCenter__action-content").toggleClass('bukaPanel');
	});
	$(".accessibility").click(function(){
		$(".pojo-a11y-toolbar-left").toggleClass('pojo-a11y-toolbar-open');
		$(".accessibility__action-content").toggleClass('bukaPanel');
	});

	//nav-more mobile
	if ($('.nav-more')) {
		$('.nav-more').on('click', function(){
			$(this).parent().toggleClass('expand');
		})
	}
	

	//Navbar Toggle addClass body
	$('.navbar-toggle').on('click', function () {
		$('body').toggleClass('navbar-collapse-in');
	})

	$('.navbar-nav .dropdown-menu > .dropdown > a').each(function(){
		$(this).prepend('<span class="caret"></span>');
	});

	$('#navbar .menu-item-has-children.dropdown').each(function(){
		$(this).prepend('<span class="triggerClick"></span>');
	});

	// $('.dropdown > a').find('.caret').on('click', function(){
	// 	if($(this).parent().siblings('.dropdown-menu').hasClass('active')) {
	// 		$(this).parent().siblings('.dropdown-menu').removeClass("active");
	// 		$(this).parent().siblings('.dropdown-menu').addClass("disable");
	// 	}else{
	// 		$(this).parent().siblings('.dropdown-menu').addClass("active");
	// 		$(this).parent().siblings('.dropdown-menu').removeClass("disable");
	// 	}
	// });
	
	$('.dropdown > .triggerClick').on('click', function(){
		if($(this).siblings('.dropdown-menu').hasClass('active')) {
			$(this).siblings('.dropdown-menu').removeClass("active");
			$(this).siblings('.dropdown-menu').addClass("disable");
		}else{
			$(this).siblings('.dropdown-menu').addClass("active");
			$(this).siblings('.dropdown-menu').removeClass("disable");
		}
	});

	//mega menu
	const megaMenu = $('header#header.header-burger');

	if (megaMenu) {
		$('header#header.header-burger .menu-item a').each(function(){
			const buttonExpand = $(this).find('span.caret');
			if (buttonExpand) {
				buttonExpand.on('click', function(e){
					e.preventDefault();
					if (!$(this).parent().parent().hasClass('expand')) {
						$(this).parent().parent().addClass('expand');
					}else{
						$(this).parent().parent().removeClass('expand');
					}
				})
			}
		});

		$('header#header.header-burger .current-menu-ancestor').each(function(){
			$(this).addClass('expand');
		});

		$('header#header.header-burger #menu-primary-menu').mouseleave(function() {
			$(this).find('.current-menu-ancestor').each(function(){
				$(this).addClass('expand');
			});
		}).mouseenter(function(){
			$(this).find('.current-menu-ancestor').each(function(){
				$(this).removeClass('expand');
			});
		});

		$('header#header.header-burger nav#navbar .current-menu-item').addClass('expand');

		if (window.matchMedia('(min-width: 991px)').matches)
		{
			$('header#header.header-burger nav#navbar ul > .menu-item-has-children').each(function(){
				$(this).hover(
					function(){
						$(this).addClass('expand');
					},
					function(){
						$(this).removeClass('expand');
					}
				);
			});
		}
	}

});
