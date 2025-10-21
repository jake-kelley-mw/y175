class CtpCommonFunc {
    constructor() {
        this.Init();
    }
    // Lightbox Custom Html
    CtlLightBoxHtml = (type) => {
        // Lightbox Dynamic container class
        const layoutClass = type === 'minimal' ? ' minimal-layout' : '';

        return `<div id="glightbox-body" class="glightbox-container">
            <div class="gloader visible"></div>
            <div class="goverlay"></div>
            <div class="gcontainer ctl_glightbox_container${layoutClass}">
            <div id="glightbox-slider" class="gslider"></div>
            <button class="gnext gbtn" tabindex="0" aria-label="Next" data-customattribute="example">{nextSVG}</button>
            <button class="gprev gbtn" tabindex="1" aria-label="Previous">{prevSVG}</button>
            <button class="gclose gbtn" tabindex="2" aria-label="Close">{closeSVG}</button>
            </div>
        </div>`;
    };

    // Lightbox Minimal Layout Media Slider
    lightboxSlider = (e) => {
        const slidesWrp = e.find('.ctp_popup_slide');
        if (slidesWrp.length > 0) {
            const sliderimg = Object.values(slidesWrp)
                .map((element) => {
                    const src = jQuery(element).data('popup-image');
                    return src
                        ? `<div class="swiper-slide"><img src="${src}"/></div>`
                        : '';
                })
                .join('');

            return `<div class="ctl_popup_slideshow">
              <div class="swiper-wrapper">
              ${sliderimg}
              </div>
              <div class="ctl_popup_slidePrev">
              ${this.CtlStaticSvgIcons('chevronLeft')}
			  </div>
              <div class="ctl_popup_slideNext">
              ${this.CtlStaticSvgIcons('chevronRight')}
			  </div>
          </div>`;
        }
    };

    // Lightbox Gallery Slider
    CtlLightBoxSlider = (direction) => {
        let navigation = {
            nextEl: '.ctl_popup_slideNext',
            prevEl: '.ctl_popup_slidePrev',
        };
        if ('rtl' === direction) {
            navigation = {
                nextEl: '.ctl_popup_slidePrev',
                prevEl: '.ctl_popup_slideNext',
            };
        }
        new Swiper('.ctl_popup_slideshow', {
            loop: true,
            spaceBetween: 30,
            autoHeight: true,
            navigation,
        });
    };

    // Lightbox Custom Svg Icons and HTML
    CtlLightBoxSettings = (type) => {
        const Lightboxsvg = {
            close: '<i class="ctl_glightbox_close_btn"></i>',
            next: '<i class="ctl_glightbox_hidden"></i>',
            prev: '<i class="ctl_glightbox_hidden"></i>',
        };

        const attributes = {
            lightboxHTML: this.CtlLightBoxHtml(type),
            svg: Lightboxsvg,
            touchNavigation: type === 'minimal' ? false : true,
        };

        return GLightbox(attributes);
    };

    // Default Lightbox for single image
    CtlLighBox = (ele) => {
        ele.preventDefault();
        const element = ele.currentTarget;
        const lightbox = this.CtlLightBoxSettings('default');

        const targetHref = element.getAttribute('href');
        const title = element.getAttribute('data-glightbox');
        lightbox.setElements([{ href: targetHref, title }]);
        lightbox.open();
    };

    // Video Lightbox for youtube and vimeo video
    CtlVideoLightbox = (ele) => {
        const element = jQuery(ele.currentTarget);
        const videoId = element.data('video-id');

        const videoUrl = element.hasClass('ctl_youtube_thumbnail') ? `https://www.youtube.com/watch?v=${videoId}` : `https://vimeo.com/${videoId}`;

        // Glightbox Library init
        const lightbox = GLightbox({
            touchNavigation: true,
            loop: true,
            autoplayVideos: true,
        });

        //elements

        // Video elements for glightbox
        const elements = {
            href: videoUrl,
            type: 'video', // Type is only required if GLightbox fails to know what kind of content should display
            width: '900px',
        };

        lightbox.setElements([elements]);
        lightbox.open();
    };

    // Lightbox For minimal Layout
    CtlLighBoxMinimal = (ele) => {
        ele.preventDefault();
        const parentWrp = ele.currentTarget.closest('.cool-timeline-wrapper');
        const direction =
            'rtl' === jQuery(parentWrp).data('dir') ? 'rtl' : 'ltr';
        const popUpContainerID = jQuery(ele.currentTarget).data('popup-id');
        const popUpConatiner = jQuery(popUpContainerID);

        const lightbox = this.CtlLightBoxSettings('minimal');

        const slideShow = popUpConatiner.find(
            '.ctl_popup_media .ctp_popup_slides'
        );
        const youTubeVideo = popUpConatiner
            .find('.ctl_popup_media .ctl_popup_video')
            .data('popup-video');
        const img = popUpConatiner
            .find('.ctl_popup_media .ctl_popup_img')
            .data('popup-image');
        let date = popUpConatiner.find('.ctl_popup_date').html();
        const title = popUpConatiner.find('.ctl_popup_title').html();
        const desc = popUpConatiner.find('.ctl_popup_desc').html();

        const elements = [];
        if(slideShow){
            if (slideShow.find('.ctp_popup_slide').length > 0) {
                const content = `${this.lightboxSlider(
                    slideShow
                )}<div class='ctl_glightbox_content ctl_slideshow_content'><h2 class='ctl_glightbox_title'>${title}</h2><div class='ctl_glightbox_date'>${date}</div><div class="ctl_glightbox_desc">${desc}</div></div>`;
    
                elements.push({
                    content,
                    height: 'auto',
                    width: '500px',
                });
            } else if (img !== undefined) {
                elements.push({
                    href: img,
                    type: 'image',
                    description: `<div class='ctl_glightbox_content'><h2 class='ctl_glightbox_title'>${title}</h2><div class='ctl_glightbox_date'>${date}</div><div class="ctl_glightbox_desc">${desc}</div></div>`,
                    height: '200px',
                });
            } else if (youTubeVideo !== undefined) {
                elements.push({
                    href: youTubeVideo,
                    type: 'video',
                    description: `<div class='ctl_glightbox_content'><h2 class='ctl_glightbox_title'>${title}</h2><div class='ctl_glightbox_date'>${date}</div><div class="ctl_glightbox_desc">${desc}</div></div>`,
                    height: '200px',
                });
            } else {
                elements.push({
                    content: `<h2 class='ctl_glightbox_title'>${title}</h2><div class='ctl_glightbox_content'><div class='ctl_glightbox_date'>${date}</div><div class="ctl_glightbox_desc">${desc}</div></div>`,
                    'max-width': '50vw',
                    height: 'auto',
                });
            }
        }

        lightbox.setElements(elements);
        lightbox.open();
        if (slideShow.length > 0) {
            this.CtlLightBoxSlider(direction);
        }
    };

    // Lightbox Gallery for slider Images
    CtlLighBoxGallery = () => {
        if('undefined' !== typeof GLightbox){
            const lightbox = GLightbox({
                selector: '.ctl_glightbox_gallery',
                lightboxHTML: this.CtlLightBoxHtml('default'),
                svg: {
                    close: '<i class="ctl_glightbox_close_btn"></i>',
                },
            });
            return lightbox;
        }
    };

    // Timeline Media Slider Call Back Function
	CtlStorySliderCall = (parent, data) => {
		const storiesSlider = jQuery(parent).find(
			'.ctl-story .ctp-story-slider:not(.swiper-initialized)'
		);

		storiesSlider.each((indx, ele) => {
			this.CtlStorySlider(jQuery(ele));
		});
	};

	// Init Timeline Media Slider
	CtlStorySlider = (element, extraData) => {
		const swiperWrp = element[0];
		const parentWrp=element.closest('.cool-timeline-wrapper');
		const autoplaySpeed = parseInt(element.data('swiper-speed'));
		const slideshow = parseInt(element.data('swiper-autoplay'));
		const autoHeight = !element
			.parents('.cool-timeline')
			.hasClass('compact');
		const prevButton = element.find('.story-swiper-button-prev')[0];
		const nextButton = element.find('.story-swiper-button-next')[0];
		const direction = parentWrp.data('dir');
		let navigation = {
			nextEl: nextButton,
			prevEl: prevButton,
		};
		if ('rtl' === direction) {
			navigation = {
				nextEl: prevButton,
				prevEl: nextButton,
			};
		}

		const swiperOptions = {
			slidesPerView: 1,
			spaceBetween: 30,
			autoHeight,
			autoplay:
				slideshow === 1
					? { delay: autoplaySpeed, disableOnInteraction: true }
					: false,
			navigation
		};

		const swiper = new Swiper(swiperWrp, swiperOptions);

		// Initial check when the page loads
		this.checkSwiperVisibility(swiper, swiperWrp);

		// Attach event listeners
		if (slideshow === 1) {
			const handleMouseEnter = () => swiper.autoplay.stop();
			const handleMouseLeave = () => swiper.autoplay.start();

			swiperWrp.addEventListener('mouseenter', handleMouseEnter);
			swiperWrp.addEventListener('mouseleave', handleMouseLeave);
		}

		const handleVisibilityCheck = () => {
			this.checkSwiperVisibility(swiper, swiperWrp);
		};

		const debouncedHandleVisibilityCheck = this.debounce(
			handleVisibilityCheck,
			200
		);

        if(parentWrp.hasClass('ctl-vertical-wrapper')){
            window.addEventListener('scroll', debouncedHandleVisibilityCheck);
            window.addEventListener('resize', debouncedHandleVisibilityCheck);
        }
	};

	// Check media slider visiblity
	checkSwiperVisibility = (swiper, wrapper) => {
		const isVisible = this.isElementInViewport(wrapper);
		const autoPlay = jQuery(swiper.el).data('swiper-autoplay');
		if (isVisible && autoPlay) {
			swiper.autoplay.start();
		} else {
			swiper.autoplay.stop();
		}
	};

	// Check media slider viewport
	isElementInViewport = (element) => {
		const rect = element.getBoundingClientRect();
		return (
			rect.top >= 0 &&
			rect.left >= 0 &&
			rect.bottom <=
				(window.innerHeight || document.documentElement.clientHeight) &&
			rect.right <=
				(window.innerWidth || document.documentElement.clientWidth)
		);
	};

	debounce(callback, delay) {
		let timerId;
		return (...args) => {
			clearTimeout(timerId);
			timerId = setTimeout(() => {
				callback(...args);
			}, delay);
		};
	}

    ctlDropdownFilter = (dropdownClass) => {
        const parentElement = jQuery('.ctl-wrapper');
        parentElement.on(
            'click',
            `.ctl-${dropdownClass}-dropdown-button`,
            (event) => {
                event.stopPropagation();
                const dropdownButton = jQuery(event.currentTarget);
                const parentWrp = dropdownButton.closest('.ctl-wrapper');
                const parentDropdownWrp = parentWrp.find(
                    `.ctl-${dropdownClass}-dropdown`
                );
                const dropdownList = parentDropdownWrp.find(
                    `.ctl-${dropdownClass}`
                );

                jQuery(
                    '.ctl-nav-dropdown .ctl-nav, .ctl-nav-dropdown .ctl-nav-dropdown-button, ' +
                    '.ctl-category-dropdown .ctl-category, .ctl-category-dropdown .ctl-category-dropdown-button'
                )
                    .not(dropdownButton)
                    .not(dropdownList)
                    .removeClass('active');

                dropdownButton.toggleClass('active');
                dropdownList.toggleClass('active');

                jQuery(window).one('click', () => {
                    dropdownButton.removeClass('active');
                    dropdownList.removeClass('active');
                });
            }
        );

        parentElement.on('click', `.ctl-${dropdownClass}-item`, (event) => {
            event.stopPropagation();
            const listItem = jQuery(event.currentTarget);
            const parentWrp = listItem.closest(
                `.ctl-${dropdownClass}-dropdown`
            );
            const listItemText = listItem.text();
            const listItemValue = listItem.data('value');
            const dropdownButton = parentWrp.find(
                `.ctl-${dropdownClass}-dropdown-button`
            );

            listItem
                .closest(`.ctl-${dropdownClass}`)
                .find('li')
                .removeClass('active');
            listItem.addClass('active');

            dropdownButton.find('span').text(listItemText);
            dropdownButton.attr('data-value', listItemValue);
            dropdownButton.toggleClass('active');
            parentWrp.find(`.ctl-${dropdownClass}`).toggleClass('active');
        });
    };

    ctlResponsiveDevice = () => {
        const screenWidth = jQuery(window).width();
        const ctlDropdown = new Array('category', 'nav');

        const classUpdate = (isMobile) => {
            if(!isMobile){
                const dateLabel=jQuery('.ctl-wrapper .ctl-vertical-wrapper').find('.ctl-navigation-bar');
                if(dateLabel.length > 0 && dateLabel.hasClass('ctl-nav-style-1') && dateLabel.hasClass('ctl-nav-active')){
                    dateLabel.removeClass('ctl-nav-active');
                }
            }

            ctlDropdown.forEach((value) => {
                jQuery(`.ctl-${value}-container`).each((index, element) => {
                    const wrapper = jQuery(element);
                    const totalItems = wrapper.find('ul li').length;
                    if (totalItems < 5) {
                        if (
                            isMobile &&
                            totalItems > 2 &&
                            'category' === value
                        ) {
                            wrapper.addClass(`ctl-${value}-dropdown`);
                            wrapper.find('button').show();
                        } else if (
                            isMobile &&
                            totalItems > 3 &&
                            'nav' === value
                        ) {
                            wrapper.addClass(`ctl-${value}-dropdown`);
                            wrapper.find('button').show();
                        } else {
                            wrapper.removeClass(`ctl-${value}-dropdown`);
                            wrapper.find('button').hide();
                        }
                    } else {
                        wrapper.addClass(`ctl-${value}-dropdown`);
                    }
                });
            });

        };

        const yearLabelOverflowCls=(isMobile)=>{
            const vrDateLabels=jQuery('.ctl-wrapper .ctl-vertical-wrapper').not('.ctl-compact-wrapper').find('.ctl-labels');
            if(vrDateLabels.length>0){
                if(isMobile){
                    vrDateLabels.each((_,ele)=>{
                        const contentWidth=jQuery(ele).closest('.ctl-story').find('.ctl-content')[0].scrollWidth;
                        const labelWidth=jQuery(ele)[0].scrollWidth;
                        if(labelWidth > contentWidth){
                            ele.classList.add('ctl-label-overflow');
                        }
                    })
                }else{
                    vrDateLabels.removeClass('ctl-label-overflow');
                }
            }
        }

        if (screenWidth < 767) {
            classUpdate(true);
        } else {
            classUpdate(false);
        }
        if (screenWidth < 380) {
            yearLabelOverflowCls(true);
        } else {
            yearLabelOverflowCls(false);
        }  
    };

    btnHoverEffect = () =>{
        const eleClasses='.ctl-button-next:not(".swiper-button-disabled"), .ctl-button-prev:not(".swiper-button-disabled"), .ctl-pagination a.page-numbers';

        jQuery(document).on('mousedown',eleClasses,(e)=>{
            const ele=jQuery(e.currentTarget);

            ele.addClass('ctl-btn-click-effect')
			setTimeout(
		        function() {
		            ele.removeClass('ctl-btn-click-effect');
		    },
		    500);
        });
    }

    CtlStaticSvgIcons = (icon) => {
        const iconsArr = {
            chevronLeft: `<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
			<path d="M34.52 239.03L228.87 44.69c9.37-9.37 24.57-9.37 33.94 0l22.67 22.67c9.36 9.36 9.37 24.52.04 33.9L131.49 256l154.02 154.75c9.34 9.38 9.32 24.54-.04 33.9l-22.67 22.67c-9.37 9.37-24.57 9.37-33.94 0L34.52 272.97c-9.37-9.37-9.37-24.57 0-33.94z"/>
			</svg>`,
            chevronRight: `<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
			<path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/>
			</svg>`,
            chevronUp: `<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512">
			<path d="M240.971 130.524l194.343 194.343c9.373 9.373 9.373 24.569 0 33.941l-22.667 22.667c-9.357 9.357-24.522 9.375-33.901.04L224 227.495 69.255 381.516c-9.379 9.335-24.544 9.317-33.901-.04l-22.667-22.667c-9.373-9.373-9.373-24.569 0-33.941L207.03 130.525c9.372-9.373 24.568-9.373 33.941-.001z"/>
			</svg>`,
            spinner: `<svg class="ctl-loader-spinner" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
			<path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/>
			</svg>`,
        };
        const data = undefined !== iconsArr[icon] ? iconsArr[icon] : '';
        return data;
    };

    Init() {

        const timelines=jQuery('.cool-timeline-wrapper');
        timelines.each((index,element)=>{
            this.CtlStorySliderCall(element);
        })

        this.ctlDropdownFilter('category');
        this.ctlDropdownFilter('nav');
        this.ctlResponsiveDevice();
        this.btnHoverEffect();

        jQuery(window).on('resize', () => {
            this.ctlResponsiveDevice();
        });

        // Lightbox gallery for slider images
        this.CtlLighBoxGallery();

        // Default Lightbox for single image
        jQuery(document).on(
            'click',
            '.ctl-wrapper .ctl_glightbox',
            (ele) => {
                this.CtlLighBox(ele);
            }
        );

        // Lightbox for minimal Layout
        jQuery(document).on(
            'click',
            '.ctl-wrapper .minimal_glightbox',
            (ele) => {
                this.CtlLighBoxMinimal(ele);
            }
        );

        jQuery(document).on(
            'click',
            '.ctl-wrapper .glightbox-video',
            (ele) => {
                this.CtlVideoLightbox(ele);
            }
        );
    }
}

(function(){
    new CtpCommonFunc();   
})(jQuery);
