// Horizontal slider class
class CtlHorizontal {
	// construnctor function
	// eslint-disable-next-line no-useless-constructor
	constructor() {
		this.ctlWrapper = jQuery('.cool-timeline-wrapper.ctl-horizontal-wrapper');

		if (this.ctlWrapper.length > 0) {
			this.storyLoop();
		}
	}

	// Initialize main slider
	initializeMainSlider = () => {
		const swiperAttr = this.ctlSlideAttribute();
		const swiperObj = new Swiper(this.mainSwiper, swiperAttr);

		// Set up navigation between main and nav sliders if applicable
		this.initializeSliderNav(swiperObj);

		// Listen for slide change event
		this.swiperOnChange(swiperObj);

		// Line filling update
		if (swiperObj.pagination.el) {
			this.ctlScrollFilling(swiperObj);
		}

		// swiper auto height function
		if (swiperAttr.autoHeight) {
			const adjustHeight = () => {
				this.autoAdjustSwiperHeight(swiperObj);
			};
			adjustHeight();
			swiperObj.on('slideChange', adjustHeight);
		}
	};

	initializeSliderNav = (swiperObj) => {
		const navWrpCls = this.parentWrp.attr('class').split(' ');
		const navSlider = ['ctl-default', 'ctl-design-6', 'ctl-design-8'];
		const navLayout = navSlider.find((element) =>
			navWrpCls.includes(element)
		);
		if (navLayout) {
			// Use the const keyword for navContainer as it won't be reassigned
			const navContainer = this.parentWrp.find(
				'.ctl-nav-slider-container'
			)[0];

			// Ensure navContainer exists before proceeding
			if (navContainer) {
				const navSlideAttr = this.ctlYearSlideAttribute();

				// Initialize the navSwiper with the 'new' keyword
				const navSwiper = new Swiper(navContainer, navSlideAttr);

				// Use strict equality (===) for comparison
				swiperObj.controller.control = navSwiper;
				navSwiper.controller.control = swiperObj;
			}
		}
	};

	// Main Swiper slider On change function
	swiperOnChange = (swiperObj) => {
		let slideChangeComplete = false;
		swiperObj.on('slideChange', (event) => {
			const parentContainer = jQuery(
				swiperObj.el.closest('.ctl-horizontal-wrapper')
			);
			const parentWrp = parentContainer.closest('.ctl-wrapper');
			const navWrp = parentContainer.data('nav');
			const activeIndex = swiperObj.activeIndex;

			if (navWrp === 'show') {
				let activeSlide = '';
				const navSliderWrp = parentContainer.find(
					'.ctl-nav-slider-container'
				);
				if (navSliderWrp.length > 0) {
					activeSlide = navSliderWrp.find('.ctl-year-swiper-slide')[
						activeIndex
					];
				} else {
					activeSlide = swiperObj.slides[activeIndex];
				}
				const activeYear = this.activeYearLabel(activeSlide);
				const navButton = parentWrp.find(
					'.ctl-nav-dropdown-button span'
				);
				if (navButton.text() !== activeYear) {
					const listItem = parentWrp.find('.ctl-nav li');
					navButton.text(activeYear);
					listItem.removeClass('active');
					listItem.each((key, element) => {
						if (element.innerText === activeYear) {
							element.classList.add('active');
						}
					});
				}
			}

			if (swiperObj.pagination.el) {
				let scrollFilling;
				slideChangeComplete = false;
				this.ctlScrollFilling(swiperObj);
				scrollFilling = setInterval(() => {
					this.ctlScrollFilling(swiperObj);
					if (slideChangeComplete) {
						clearInterval(scrollFilling);
					}
				}, 100);
			}
		});

		swiperObj.on('slideChangeTransitionEnd', () => {
			if (swiperObj.pagination.el) {
				slideChangeComplete = true;
			}
		});
	};
	
	// Icon and year label line filling
	ctlScrollFilling = (obj) => {
		const swiperWrp = obj.el;

		if (swiperWrp) {
			const parentWrp = swiperWrp.closest('.cool-timeline-wrapper');
			if (parentWrp) {
				const iconFilling = ['ctl-design-1', 'ctl-design-2', 'ctl-design-6'].some(design => parentWrp.classList.contains(design));
				const yearFilling = ['ctl-design-6', 'ctl-design-2'].some(design => parentWrp.classList.contains(design));

				if (iconFilling || yearFilling) {
					const sliderLeftPostion = swiperWrp.getBoundingClientRect().left - swiperWrp.offsetLeft;
					const pagination = obj.pagination.el.querySelector('.swiper-pagination-progressbar-fill');
					const paginationTransformStyle = pagination.style.transform;
					const progressValue = paginationTransformStyle.match(/scaleX\(([^)]+)\)/)[1];
					const paginationWidth = Math.round(pagination.offsetWidth * progressValue);

					const stories = parentWrp.classList.contains("ctl-design-6") ? parentWrp.querySelectorAll('.ctl-nav-slider-container .ctl-year-swiper-slide') : parentWrp.querySelectorAll('.ctl-slider-container .ctl-story')

					for (let i = 0; i < stories.length; i++) {
						if (yearFilling) {
							const yearLabel = stories[i].querySelector('.timeline-year .ctl-year-text');

							if (yearLabel) {
								const yearLeftPosition = yearLabel.getBoundingClientRect().left;
								const yearPosition = yearLeftPosition - sliderLeftPostion;

								if (paginationWidth >= yearPosition) {
									yearLabel.classList.add('ctl-in-view-port');
								} else {
									yearLabel.classList.remove('ctl-in-view-port');
								}
							}

						}

						if (iconFilling) {
							const icon = stories[i].querySelector('.ctl-icondot,.ctl-icon')

							if (icon) {
								const iconLeftPosition = icon.getBoundingClientRect().left;
								const storyPosition = iconLeftPosition - sliderLeftPostion;

								if (paginationWidth >= storyPosition) {
									stories[i].classList.add('ctl-in-view-port');
								} else {
									stories[i].classList.remove('ctl-in-view-port');
								}
							}


						}
					}
				}
			}
		}
	}

	// Function to handle auto height adjustment of swiper slides
	autoAdjustSwiperHeight = (event) => {
		// Object to store previous heights
		const previousHeights = { swiperHeight: '', mediaSwiperHeight: '' };

		// Function to get the current active slides
		const currentSlide = (swiper) => {
			const {
				activeIndex,
				params: { slidesPerView },
				slides,
			} = swiper;
			const slide = Array.prototype.slice.call(slides);
			return slide.slice(activeIndex, activeIndex + slidesPerView);
		};

		// Callback function for the MutationObserver
		const observerCallback = (mutations) => {
			// Determine whether it's a media swiper or main swiper
			const currentElement = jQuery(mutations[0].target).hasClass(
				'ctp-media-slider'
			)
				? 'mediaSwiperHeight'
				: 'swiperHeight';

			// Get the current height of the wrapper
			const currentWrapperHeight = jQuery(mutations[0].target).height();

			// If the height has changed
			if (previousHeights[currentElement] !== currentWrapperHeight) {
				previousHeights[currentElement] = currentWrapperHeight;

				// If the change is due to a style attribute mutation
				if (
					mutations.some(
						(mutation) =>
							mutation.type === 'attributes' &&
							mutation.attributeName === 'style'
					)
				) {
					heightUpdate(event);
				}
			}
		};

		// Create a new MutationObserver with the callback function
		const observer = new MutationObserver(observerCallback);

		// Function to update swiper height
		const heightUpdate = (swiper) => {
			const activeSlides = currentSlide(swiper);

			// Calculate the maximum height
			const wrpHeight = Math.max(
				...activeSlides
					.map(jQuery)
					.map((element) => element.outerHeight(true))
			);

			// Set the height of the swiper element
			jQuery(swiper.el).height(wrpHeight);
		};

		const OBSERVER_CONFIG = { attributes: true };

		// Function to start observing a target
		const startObserving = (target) =>
			observer.observe(target, OBSERVER_CONFIG);

		// Start observing the main swiper
		if (event.slidesEl) {
			startObserving(event.slidesEl);
		}

		// Find and observe media sliders
		setTimeout(() => {
			const mediaSlides = jQuery(event.slidesEl).find(
				'.ctp-media-slider .ctp-story-slider.swiper-initialized'
			);
			mediaSlides.each((index, element) => {
				startObserving(element.swiper.slidesEl);
			}
			);
		}, 500)

		// Call heightUpdate initially
		heightUpdate(event);
	};

	// Year Filter Silder Control Update
	yearFilterSliderControl = (id) => {
		const parentWrp = jQuery(`.ctl-wrapper #${id}`).closest('.ctl-wrapper');

		const slideToIndex = (event) => {
			const yearLabel = event.currentTarget.innerText;
			const yearWrp = parentWrp.find(`.timeline-year#year-${yearLabel}`);
			const index = parseInt(yearWrp.parent().data('story-index'));
			const swiper = parentWrp.find('.ctl-slider-container')[0].swiper;
			swiper.slideTo(index - 1);
		};

		const navItems = parentWrp.find('.ctl-navigation-bar li');
		if (navItems) {
			navItems.click(slideToIndex);
		}
	};

	// Create Horizontal Year Navigation Filter
	ctlHrYearNavigation = () => {
		const wrpId = this.parentWrp[0].id;
		const postId = Number(wrpId.replace(/\D/g, ''));
		const allYears = this.parentWrp.find('.scrollable-section');
		const yearWrp = this.parentWrp
			.closest('.ctl-wrapper')
			.find('.ctl-navigation-bar');
		const YearWrpId = `ctl-navigation-bar-${postId}`;
		const navPostion = `ctl-nav-${this.parentWrp.data('nav-pos')}`;

		const currentSlider = this.parentWrp.find(
			'.ctl-slider-container .ctl-story.swiper-slide-active, .ctl-year-swiper-slide.swiper-slide-active'
		);

		const activeYear = this.activeYearLabel(currentSlider[0]);

		yearWrp.attr('id', YearWrpId);

		// Create dropdown elements
		const dropdownButton = jQuery('<button>', {
			role: 'button',
			'data-value': '',
			class: 'ctl-nav-dropdown-button',
			style: 'display: none',
		}).html(
			`<span>${activeYear}</span> ${this.CtlStaticSvgIcons('chevronUp')}`
		);

		const dropdownList = jQuery('<ul>', {
			class: `ctl-nav ${navPostion}`,
			role: 'tablist'
		});

		const dropdown = jQuery('<div>', {
			class: 'ctl-nav-default ctl-nav-container',
		}).append(dropdownButton, dropdownList);

		yearWrp.append(dropdown);

		// Create dropdown list items
		allYears.each((index, element) => {
			this.ctlYearLiElement(YearWrpId, element, activeYear);
		});

		this.yearFilterStyle(this.parentWrp[0].id);
	};

	yearFilterStyle = (id) => {
		const navWrapper = jQuery(`#${id}`)
			.closest('.ctl-wrapper')
			.find('.ctl-nav-container');
		const liElement = navWrapper.find('li');
		if (liElement.length < 5) {
			navWrapper
				.find('button.ctl-nav-dropdown-button')
				.css('display', 'none');
			navWrapper
				.addClass('ctl-nav-default')
				.removeClass('ctl-nav-dropdown');
		} else {
			navWrapper
				.find('button.ctl-nav-dropdown-button')
				.css('display', 'block');
			navWrapper
				.addClass('ctl-nav-dropdown')
				.removeClass('ctl-nav-default');
		}
		this.yearFilterSliderControl(id);
	};

	// Create Horiontal Yera Navigation Filter List
	ctlYearLiElement = (navId, element, activeYear) => {
		const listItem = jQuery('<li>', {
			class: `ctl-nav-item${element.innerText === activeYear ? ' active' : ''
				}`,
			role: 'tab'
		});

		const anchorTag = jQuery('<a>', {
			text: element.innerText,
		});

		listItem.append(anchorTag);

		const dropdownList = jQuery(`#${navId}`).find('ul');
		dropdownList.append(listItem);
	};

	// Active Year For Year Filter
	activeYearLabel = (element) => {
		const currentYear = jQuery(element).find('.timeline-year');
		if (0 >= currentYear.length) {
			const preveYearWrp = jQuery(element).prevAll(
				'.ctl-story,.ctl-year-swiper-slide'
			);
			const allYearWrp = preveYearWrp.filter((e, data) => {
				return jQuery(data).find('.timeline-year').length > 0;
			});
			const activeYear = jQuery(allYearWrp[0]).find('.timeline-year');
			return activeYear.text();
		}
		return currentYear.text();
	};

	// Render Horizontal Default Slider attribute
	ctlSlideAttribute = () => {
		this.slideChangeComplete = false;

		// parent Element
		const element = jQuery(this.parentWrp);

		// Elements
		const direction = element.data('dir');
		const nextButton = element.find('.ctl-button-next')[0];
		const prevButton = element.find('.ctl-button-prev')[0];
		const initialSlide = parseInt(element.data('start-on')) || 0;
		const autoplay = !!element.data('autoplay');
		const autoplaySpeed = parseInt(element.data('autoplay-speed')) || 0;
		const pagination = element.data('line-filling')
			? element.find('.ctl-line-fill')[0]
			: false;
		// Slider settings
		const showSlides =
			// eslint-disable-next-line no-nested-ternary
			element.data('items') === ''
				? element.hasClass('ctl-design-7')
					? 6
					: 3
				: parseInt(element.data('items'));

		// swiper navigation
		let navigation = {
			nextEl: nextButton,
			prevEl: prevButton,
		};

		// Swiper Navigation for right to left language
		if ('rtl' === direction) {
			navigation = {
				nextEl: prevButton,
				prevEl: nextButton,
			};
		}

		// Check if navigation layout is present
		const navWrpCls = element.attr('class').split(' ');
		const navSlider = ['ctl-default', 'ctl-design-6', 'ctl-design-8'];
		const navLayout = navSlider.find((navElement) =>
			navWrpCls.includes(navElement)
		);

		// Slider attribute configuration
		const attribute = {
			slidesPerGroup: 1,
			initialSlide,
			autoplay: autoplay
				? { delay: autoplaySpeed, disableOnInteraction: true }
				: false,
			autoHeight: true,
			pagination: {
				el: pagination,
				type: 'progressbar',
			},
			slidesPerView: navLayout ? 1 : showSlides,
			navigation,
			breakpoints: {
				0: {
					slidesPerView: 1,
				},
				640: {
					slidesPerView: 1,
				},
				768: {
					slidesPerView: navLayout ? 1 : 2,
				},
				1024: {
					slidesPerView: navLayout ? 1 : showSlides,
				},
			},
		};

		return attribute;
	};

	// Render Horizontal Nav Slider attribute
	ctlYearSlideAttribute = () => {
		const element = this.parentWrp;
		const initialSlide =
			element.data('start-on') === ''
				? 0
				: parseInt(element.data('start-on')) - 1;
		const items =
			jQuery(element).data('items') === ''
				? 3
				: parseInt(jQuery(element).data('items'));
		const showSlides = items % 2 === 0 ? items + 1 : items;
		const attribute = {
			slidesPerView: 1,
			slidesPerGroup: 1,
			initialSlide,
			centeredSlides: true,
			slideToClickedSlide: true,
			breakpoints: {
				640: {
					slidesPerView: 1,
				},
				768: {
					slidesPerView: 1,
				},
				1024: {
					slidesPerView: showSlides,
				},
			}
		};

		return attribute;
	};

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
			</svg>`
		};
		const data = undefined !== iconsArr[icon] ? iconsArr[icon] : '';
		return data;
	};

	// Each timeline story
	storyLoop = () => {
		this.ctlWrapper.each((index, element) => {
			const container = jQuery(element).find(
				'.ctl-timeline-container'
			);
			const totalStory = container.find('.ctl-story').length;
			// eslint-disable-next-line no-unused-expressions
			if (totalStory === 0) {
				jQuery(element).addClass('ctl-content-empty');
				return;
			}
			
			this.index = index;
			this.parentWrp = jQuery(element);
			this.mainSwiper = this.parentWrp.find('.ctl-slider-container')[0];
			
			// Initialize main slider
			this.initializeMainSlider();
			
			// Initialize nav slider
			if (this.parentWrp.data('nav') === 'show') {
				this.ctlHrYearNavigation();
			}
		});
	};
}

(function () {
	new CtlHorizontal();
})(jQuery);
