// Vertical Class
class CtlVertical {
	constructor() {
		this.prevScrollComplete = true;
		this.swiperInitId = new Array();
		this.ctlWrapper = jQuery('.cool-timeline-wrapper.ctl-vertical-wrapper');
		if (this.ctlWrapper.length > 0) {
			this.storyLoop();
		}

		if (this.ctlWrapper.hasClass('ctl-compact-wrapper')) {
			this.compactLayoutInit();
		}
	}

	// Function to delay execution asynchronously
	delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

	// line filling call back function
	VrLineFillCallback = async () => {
		await this.delay(200);
		jQuery('.ctl-vertical-wrapper').each((index, element) => {
			const parentWrp = jQuery(element);
			this.CtlVrLineFilling(parentWrp);
		});
	};

	// Vertical Line filling
	CtlVrLineFilling = (element) => {
		const parentWrp = element;
		const timelineYear = parentWrp.find('.ctl-year');
		const lineFilling = parentWrp.data('line-filling');
		const Stories = parentWrp.find('.ctl-story');

		let rect = '';
		let ctlTop = '';
		let height = '';
		let centerLine = '';

		if (lineFilling && Stories.length > 0) {
			const outerDiv = parentWrp.hasClass('compact-wrapper')
				? parentWrp.find('.clt-compact-cont')
				: parentWrp.find('.ctl-timeline-container');
			const outerHeight = outerDiv.outerHeight();

			rect = outerDiv[0].getBoundingClientRect();
			
			const parentHost = window.parent;
			let parentWindowAccess=false;
			try{
				parentWindowAccess = parentHost && parentHost.jQuery ? true : false;
			}catch{
				parentWindowAccess = false;
			}

			const shortcodePrview = parentWindowAccess ? window.parent.jQuery('#ctl_preview') : false;

			if (shortcodePrview && shortcodePrview.length > 0) {
				height = shortcodePrview.height() / 2;
			} else {
				height = jQuery(window).height() / 2;
			}
			centerLine = parentWrp.find('.ctl-inner-line');
			ctlTop = rect.top < 0 ? Math.abs(rect.top) : -Math.abs(rect.top);
			const lineInnerHeight = ctlTop + height;
			const centerLineHeight = centerLine.height();
			const topScroll = jQuery(window).scrollTop() + height + 10;

			centerLine.height(
				lineInnerHeight <= outerHeight ? lineInnerHeight : outerHeight
			);

			timelineYear.each((index, year) => {
				const yearPosition = jQuery(year).offset().top;
				const yearHeight = jQuery(year).height() / 1.5;
				jQuery(year).toggleClass(
					'innerViewPort',
					topScroll >= yearPosition + yearHeight
				);
			});

			if (jQuery(Stories[0]) && !jQuery(Stories[0]).hasClass('ctl-story-no-icon')) {
				Stories.each((index, wrp) => {
					const icons = wrp.classList.contains('ctl-story-dot-icon')
						? '.ctl-icondot'
						: '.ctl-icon';
					const iconsSize=jQuery(wrp).find(icons).width();
					const iconPosition = (jQuery(wrp).find(icons).offset().top + iconsSize / 2);
					jQuery(wrp).toggleClass(
						'innerViewPort',
						topScroll >= iconPosition
					);
				});
			}

			if (
				lineInnerHeight >= outerHeight &&
				centerLineHeight < outerHeight
			) {
				centerLine.height(outerHeight);
			}

			const centerLineElement = parentWrp.find('.center-line');
			parentWrp.toggleClass('ctl-start-fill', lineInnerHeight > 0);
			centerLineElement.toggleClass(
				'BeforeViewPort',
				lineInnerHeight > 0
			);
			centerLineElement.toggleClass(
				'AfterViewPort',
				lineInnerHeight >= outerHeight
			);
			parentWrp.toggleClass(
				'ctl-end-fill',
				lineInnerHeight >= outerHeight
			);
		}
	};

	// Year Navigation
	ctlYearNavigation = (element) => {
		const selector = jQuery(element);
		const navBarEnable = selector.data('nav');
		const navStyle=selector.data('nav-style');
		const wrpId = selector[0].id;
		const postId = Number(wrpId.replace(/\D/g, ''));
		const yearEntry = selector.find('.scrollable-section');

		if (yearEntry.length > 0 && navBarEnable === 'show') {
			if (!selector.find('.ctl-navigation-bar').length) {
				const navPosition = selector.data('nav-pos');
				const navBar = `<nav id="ctl-navigation-bar-${postId}" class="ctl-navigation-bar ctl-nav-position-${navPosition} ctl-nav-${navStyle}"></nav>`;
				jQuery(navBar).prependTo(`#${wrpId}`);

				const listAll = jQuery(
					'<ul class="ctl-navigation-items"></ul>'
				);
				
				if('style-2' !== navStyle){
					const navIcon='<div class="ctl-nav-icon"><span></span><span></span><span></span></div>';
					selector.find('.ctl-navigation-bar').append(navIcon);

					jQuery(document).on('click',`#${wrpId} .ctl-navigation-bar .ctl-nav-icon`,function(e){
						jQuery(e.currentTarget).closest('.ctl-navigation-bar').toggleClass('ctl-nav-active');
					});
				}

				selector.find('.ctl-navigation-bar').append(listAll);
			}

			const navigationBar = selector.find('.ctl-navigation-bar');
			navigationBar.addClass('ctl-out-viewport');

			const navigator = selector.find('.ctl-year-container');
			navigator.each((i, labelWrp) => {
				this.CtlNavLiElement(i, wrpId, labelWrp);
			});

			this.yearNavHandleScroll({ data: selector });
			jQuery(window).on('scroll', selector, this.yearNavHandleScroll);
			this.ctlYearScrollAnimation();
		}
	};

	// Year Navigation Scroll Handler
	yearNavHandleScroll = async (event) => {
		const selector = event.data;
		const TimelineStories = selector.find('.ctl-story');
		const compact = selector.hasClass('ctl-compact-wrapper');
		const laststory = TimelineStories[TimelineStories.length - 1];
		const rootElement = document.documentElement;
		const viewport = jQuery(window).height() / 3;
		const timelineWrapperPosition = selector.offset().top;
		const laststoryDiv = jQuery(laststory);
		const navigationBar = selector.find('.ctl-navigation-bar');
		const mainNavLinks = navigationBar.find('ul li a');
		let previousEl = null;

		if (laststoryDiv.length > 0) {
			const timelineBottom = laststoryDiv.offset().top +
				laststoryDiv.height() -
				rootElement.scrollTop;
			const timelineTop = timelineWrapperPosition - rootElement.scrollTop;

			navigationBar.toggleClass(
				'ctl-in-viewport',
				timelineTop < viewport
			);
			navigationBar.toggleClass(
				'ctl-out-viewport',
				timelineTop >= viewport || timelineBottom < viewport
			);

			const extraspace = compact ? 70 : 400;
			const fromTop = window.scrollY + extraspace;

			mainNavLinks.each(function () {
				const hash = this.hash;
				if (!jQuery(hash).length) {
					return false;
				}

				const section = jQuery(hash).offset().top;

				if (section <= fromTop) {
					if (previousEl !== null) {
						previousEl
							.removeClass('current')
							.parent()
							.removeClass('current');
					}
					jQuery(this)
						.addClass('current')
						.parent()
						.addClass('current');
				}

				if (section >= fromTop) {
					jQuery(this)
						.removeClass('current')
						.parent()
						.removeClass('current');
				}

				previousEl = jQuery(this);
			});
		}
	};

	// Creat Nav Elements
	CtlNavLiElement = (index, wrpId, labelWrp) => {
		const postId = Number(wrpId.replace(/\D/g, ''));
		const YeraLabel = jQuery(labelWrp).data('section-title');
		const uniqueID = `${postId}-${YeraLabel}`;
		const scrollbarID = `ctl-scrollar-${uniqueID}`;
		jQuery(labelWrp).attr('id', scrollbarID);
		const listEl = jQuery(
			`<li><a href="#${scrollbarID}">${YeraLabel}</a></li>`
		);
		if (index === 0) {
			listEl.addClass('current');
		}

		const ulList = jQuery(`#ctl-navigation-bar-${postId}`).find('ul');
		ulList.append(listEl);
	};

	// Year Navigation Scroll Animation
	ctlYearScrollAnimation = () => {
		jQuery(document).on(
			'click',
			'.ctl-vertical-wrapper .ctl-navigation-bar ul li',
			(e) => {
				if (this.prevScrollComplete) {
					this.prevScrollComplete = false;
					e.preventDefault();
					const targetElement = jQuery(e.currentTarget)
						.find('a')
						.attr('href');

					if (targetElement.length && jQuery(targetElement).length > 0) {
						const targetTopPosition =
							jQuery(targetElement).offset().top - 40;
						jQuery('html, body').animate(
							{
								scrollTop: targetTopPosition,
							},
							500,
							() => {
								this.prevScrollComplete = true;
							}
						);
					}
				}
			}
		);
	};

	// Aos animation
	CtlStoryAos = (wrapperId) => {
		// enabled animation on page scroll
		const parentWrp = jQuery(`#${wrapperId}`);
		const timelineContainer = parentWrp.find('.ctl-timeline-container');
		const animation = timelineContainer.attr('data-animation');
		let prevHeight = jQuery(`#${wrapperId}`).height();
		const parentWrpAosCls =
			!parentWrp.hasClass('ctl-compact-wrapper') &&
			parentWrp.hasClass('ctl-design-6');

		// addded empty data aos attribute on element for before init the AOS
		if (animation !== 'none') {
			// Animation on year label
			if(!parentWrp.hasClass('ctl-compact-wrapper')){
				timelineContainer.find('.timeline-year.scrollable-section').each((_index,element)=>{
					element.dataset.aos='';
				});
			}
			// Animation on story element
			timelineContainer.find('.ctl-story').each((index, story) => {
				if (parentWrpAosCls) {
					story.dataset.aos = '';
				} else {
					jQuery(story)
						.find(
							'.ctl-icon, .ctl-content, .ctl-labels, .ctl-icondot, .ctl-arrow'
						)
						.each((_index, element) => {
							element.dataset.aos = '';
						});
				}
			});

			// You can also pass an optional settings object
			// below listed default settings
			// init aos animation
			AOS.init({
				// Global settings:
				disable: 'mobile',
				startEvent: 'DOMContentLoaded',
				offset: 75,
				delay: 0,
				duration: 750,
				easing: 'ease-in-out-sine',
				mirror: true,
			});

			// Added animation value in aos attribute on year label
			if(!parentWrp.hasClass('ctl-compact-wrapper')){
				timelineContainer.find('.timeline-year.scrollable-section').each((_index,element)=>{
					element.dataset.aos = animation;
				});
			}
			// Added animation value in aos attribute on element
			timelineContainer.find('.ctl-story').each((index, story) => {
				if (parentWrpAosCls) {
					story.dataset.aos = animation;
				} else {
					jQuery(story)
						.find(
							'.ctl-icon, .ctl-content, .ctl-labels, .ctl-icondot, .ctl-arrow'
						)
						.each((_index, element) => {
							element.dataset.aos = animation;
						});
				}
			});

			setTimeout(() => {
				AOS.refresh();
			}, 500);
		}
		jQuery(window).scroll(() => {
			const currentHeight = jQuery(`#${wrapperId}`).height();
			if (prevHeight !== currentHeight) {
				setTimeout(function () {
					AOS.refresh();
				}, 500);
				prevHeight = currentHeight;
			}
		});
	};

	// Compact layout initialize function
	ctlCompactMasonry = (grids, animation, reloadItems) => {
		let grid = '';
		let leftReminder = 0;
		let rightReminder = 0;
		if (reloadItems) {
			grid = grids.masonry('reloadItems');
		} else {
			grid = grids.masonry({
				itemSelector: '.ctl-story',
				initLayout: false,
			});
		}
		// layout images after they are loaded
		grid.imagesLoaded().progress(() => {
			grid.masonry('layout');
		});

		grid.one('layoutComplete', () => {
			let leftPos = 0;
			let topPosDiff;
			grid.find('.ctl-story').each((index, element) => {
				leftPos = jQuery(element).position().left;
				if (leftPos <= 0) {
					const extraCls = (leftReminder % 2) === 0 ? 'ctl-left-odd' : 'ctl-left-even';
					const prevCls = extraCls === 'ctl-left-odd' ? 'ctl-left-even' : 'ctl-left-odd';
					jQuery(element)
						.removeClass('ctl-story-right')
						.removeClass('ctl-right-even')
						.removeClass('ctl-right-odd')
						.removeClass(prevCls)
						.addClass('ctl-story-left')
						.addClass(extraCls);
					leftReminder++;
				} else {
					const extraCls = (rightReminder % 2) === 0 ? 'ctl-right-odd' : 'ctl-right-even';
					const prevCls = extraCls === 'ctl-right-odd' ? 'ctl-right-even' : 'ctl-right-odd';
					jQuery(element)
						.removeClass('ctl-story-left')
						.removeClass('ctl-left-odd')
						.removeClass('ctl-left-even')
						.removeClass(prevCls)
						.addClass('ctl-story-right')
						.addClass(extraCls);
					rightReminder++;
				}

				topPosDiff =
					jQuery(element).position().top -
					jQuery(element).prev().position().top;
				if (topPosDiff < 40) {
					jQuery(element)
						.removeClass('ctl-compact-up')
						.addClass('ctl-compact-down');
					jQuery(element)
						.prev()
						.removeClass('ctl-compact-down')
						.addClass('ctl-compact-up');
				}
			});
			jQuery('.ctl-icon').addClass('showit');
			jQuery('.ctl-title').addClass('showit-after');
			if (animation !== 'none') {
				AOS.refreshHard();
			}
		});
	};

	// Static Svg Icons
	CtlStaticSvgIcons = (icon) => {
		const iconsArr = {
			chevronLeft: `<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
			<path d="M34.52 239.03L228.87 44.69c9.37-9.37 24.57-9.37 33.94 0l22.67 22.67c9.36 9.36 9.37 24.52.04 33.9L131.49 256l154.02 154.75c9.34 9.38 9.32 24.54-.04 33.9l-22.67 22.67c-9.37 9.37-24.57 9.37-33.94 0L34.52 272.97c-9.37-9.37-9.37-24.57 0-33.94z"/>
			</svg>`,
			chevronRight: `<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
			<path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/>
			</svg>`
		};
		const data = undefined !== iconsArr[icon] ? iconsArr[icon] : '';
		return data;
	};

	// Each timeline loop
	storyLoop = () => {
		this.ctlWrapper.each((index, element) => {
			const container = jQuery(element).find(
				'.ctl-timeline-container'
			);
			const totalStory = container.find('.ctl-story').length;

			const parentWrp = jQuery(element);
			const timelineContainer = parentWrp.find('.ctl-timeline-container')[0];
			const animation = jQuery(timelineContainer).attr('data-animation');

			// eslint-disable-next-line no-unused-expressions
			totalStory === 0 &&
				jQuery(element).addClass('ctl-content-empty');


			if (animation !== 'none' && animation !== undefined) {
				this.CtlStoryAos(parentWrp[0].id, animation);
			}

			// Line filling run after page load
			this.CtlVrLineFilling(parentWrp);

			// Line filling update after page scroll
			jQuery(window).on('scroll', parentWrp, (el) => {
				this.CtlVrLineFilling(el.data);

				const mediaSlides = jQuery(parentWrp).find('.ctl-story .ctp-story-slider.swiper-initialized');
				mediaSlides.each((index, element) => {
					const swiper = element.swiper;
					const id = swiper.slidesEl.id;
					if (!this.swiperInitId.includes(id)) {
						this.swiperInitId.push(id);
						swiper.on('slideChange', () => {
							this.VrLineFillCallback();
						});
					}
				});;
			});
			this.ctlYearNavigation(element);
		});
	};

	// Compact Layout call
	compactLayoutInit() {
		const initializeCompactMasonry = () => {
			const wrapper = jQuery(
				'.ctl-compact-wrapper .ctl-timeline-container'
			);
			const animation = wrapper.data('animation');
			this.ctlCompactMasonry(wrapper, animation);
		}

		jQuery(document).ready(initializeCompactMasonry);
		jQuery(window).on('load', function () {
			setTimeout(initializeCompactMasonry, 200);
		});

		jQuery(window).on('resize', initializeCompactMasonry);
	}
}

(function () {
	new CtlVertical();
})(jQuery);