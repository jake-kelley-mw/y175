(function ($) {
	/**
	 * Class ctp Load more.
	 */
	class CtpLoadMore {
		/**
		 * Contructor.
		 *
		 * @param wrapperId
		 */
		constructor(wrapperId) {
			const ctl_ajax_object_data='undefinded' !== typeof ctl_ajax_object ? ctl_ajax_object : {};  
			const { ajax_url: ajaxUrl, ajax_nonce: ajaxNonce } =
			ctl_ajax_object_data;
			this.ajaxUrl = 'undefinded' !== typeof ajaxUrl ? ajaxUrl : '';
			this.ajaxNonce = 'undefinded' !== typeof ajaxNonce ? ajaxNonce : '';
			this.wrapperId = wrapperId;
			this.timelineAttr = [];
			this.newAttribute = [];
			this.newTimeline = false;
			this.hrStyle = false;
			this.loadMoreBtn = jQuery(
				`#${wrapperId} .ctl_load_more_pagination button.ctl_load_more`
			);

			this.timelineContainer = jQuery(
				`#${wrapperId} .ctl-timeline-container`
			);
			this.StylesContainer = jQuery(`#${wrapperId}-styles`);
			this.buttonContainer = jQuery(
				`#${wrapperId} .ctl_load_more_pagination`
			);

			this.catFilterContainer = jQuery(`#${wrapperId}`)
				.parents('.ctl-wrapper')
				.find('.ctl-category-container');

			this.isRequestProcessing = false;
			this.init();
		}

		init() {
			if (
				this.loadMoreBtn.length === 0 &&
				this.catFilterContainer.length === 0
			) {
				return;
			}

			// Timeline shortcode attribute
			this.shortcodeAttributes();

			if (this.timelineAttr.length <= 0) {
				return;
			}

			const parentWrp = jQuery(`#${this.wrapperId}`);

			if (parentWrp.hasClass('ctl-horizontal-wrapper')) {
				this.hrStyle = true;
				if (
					parentWrp.data('load') === 'yes' &&
					this.loadMoreBtn.length > 0
				) {
					const swiper = parentWrp.find('.ctl-slider-container')[0]
						.swiper;
					swiper.on('slideChange', (data) => {
						if (!this.newTimeline) {
							this.hrLoadMore(data);
						}
					});
				}
			} else {
				this.loadMoreBtn.on('click', () => {
					this.vrLoadMore();
				});
			}

			// category filter for horizontal and vertical layout
			this.catFilterContainer
				.find('ul.ctl-category li.ctl-category-item')
				.on('click', (event) => {
					event.preventDefault();
					const activeEleSelector = $(event.currentTarget).find('a');
					const activeCategory =
						activeEleSelector.attr('data-term-slug');
					if (
						((this.timelineAttr.ctl_type !== 'story_timeline' && activeCategory !== this.newAttribute.category) || (this.timelineAttr.ctl_type === 'story_timeline' && activeCategory !== this.newAttribute.filter_category)) &&
						!this.isRequestProcessing
					) {
						const categoryBtn = parentWrp
							.closest('.ctl-wrapper')
							.find('.ctl-category-dropdown-button');
						const spinner = this.CtlStaticSvgIcons('spinner');
						if(categoryBtn){
							jQuery(spinner).insertBefore(categoryBtn.find('span'));
						}

						const catUl = activeEleSelector.closest(
							'.ctl-category-container'
						);
						if (!catUl.hasClass('ctl-category-dropdown')) {
							catUl.find('ul').prepend(spinner);
						}
						if (this.loadMoreBtn.length > 0) {
							this.loadMoreBtn.parent().remove();
							this.loadMoreBtn = '';
						}

						if(this.timelineAttr && this.timelineAttr.ctl_type === 'story_timeline'){
							this.newAttribute.filter_category = activeCategory;
						}else{
							this.newAttribute.category = activeCategory;
						}

						this.newTimeline = true;
						const newLocal = this;
						if (newLocal.hrStyle) {
							this.hrLoadMore(
								parentWrp.find('.ctl-slider-container')[0]
									.swiper
							);
						} else {
							this.vrLoadMore();
						}
					}
				});
		}

		/**
		 * Vertical Load More Request.
		 *
		 */
		vrLoadMore = async () => {
			if (this.isRequestProcessing) {
				return; // Exit early if a request is already in progress
			}

			try {
				const response = await this.handleLoadMorePosts();

				if (this.newTimeline) {
					const noContent = jQuery(`#${this.wrapperId} .no-content`);
					if(noContent){
						noContent.remove();
					}
					this.timelineContainer
						.find('.ctl-story, .timeline-year')
						.remove();
				}

				const contentUpdate = this.ctlBeforeContentLoad(response);
				if (contentUpdate && response.data) {
					this.timelineContainer.append(response.data.HTML);
				}

				if (this.newTimeline) {
					const defaultPagination = jQuery(
						`#${this.wrapperId} nav.ctl-pagination`
					);
					if(defaultPagination){
						defaultPagination.remove();
					}
				}

				this.ctlAfterContentLoad(response);
			} catch (error) {
				console.error('Error occurred while loading more:', error);
				// Handle the error or display an error message
			} finally {
				this.isRequestProcessing = false;
				this.newTimeline = false;
			}
		};

		/**
		 * Horizontal Load More Request.
		 *
		 * @param {Object} data Swiper Object.
		 */
		hrLoadMore = async (data) => {
			if (this.isRequestProcessing) {
				return; // Exit early if a request is already in progress
			}

			// parent Wrapper
			const parentWrp = this.timelineContainer.closest(
				'.cool-timeline-wrapper'
			);

			// Nav Slider Design Check
			const navWrpCls = parentWrp.attr('class').split(' ');
			const navSlider = ['ctl-default', 'ctl-design-6', 'ctl-design-8'];
			const navLayout = navSlider.find((element) =>
				navWrpCls.includes(element)
			);
			const navSliderWrp = parentWrp.find('.ctl-nav-slider-container');

			const swiperPosition = navLayout ? navSliderWrp[0].swiper : data;

			// Swiper last slider position
			const lastSlidePosition =
				swiperPosition.snapGrid[swiperPosition.snapGrid.length - 2];

			// Swiper current slide position
			const currentSlidePosition = Math.abs(swiperPosition.translate);

			// Ajax Request Send After last slide check
			const shouldLoadMore =
				(currentSlidePosition >= lastSlidePosition &&
					this.loadMoreBtn.length > 0) ||
				this.newTimeline;

			if (!shouldLoadMore) {
				return; // Exit early if no more content should be loaded
			}

			const nextBtn = data.navigation.nextEl;
			const nextBtnIcon = nextBtn.innerHTML;

			if (!this.newTimeline) {
				nextBtn.innerHTML = this.CtlStaticSvgIcons('spinner');
			}
			try {
				const response = await this.handleLoadMorePosts();

				if (this.newTimeline) {
					const noContent = jQuery(`#${this.wrapperId} .no-content`);
					if(noContent){
						noContent.remove();
					}
					data.removeAllSlides();
					if (navLayout && navSliderWrp[0]) {
						navSliderWrp[0].swiper.removeAllSlides();
					}
				}

				const contentUpdate = this.ctlBeforeContentLoad(response);
				
				if (contentUpdate && response.data && response.data.HTML) {
					if (
						this.timelineContainer
							.closest('.cool-timeline-wrapper')
							.hasClass('ctl-content-empty')
					) {
						this.timelineContainer.append(response.data.HTML);
					} else {
						const tempElement = jQuery('<div>').html(
							response.data.HTML
						);
						const totalStories = tempElement.find('.ctl-story');
						totalStories.each((index, slider) => {
							data.appendSlide(slider);
						});
						const commentNode =
							document.createComment('Timeline Content');
						this.timelineContainer
							.find('.ctl-story')
							.before(commentNode);

						if (navLayout) {
							const tempNavElement = jQuery('<div>').html(
								response.data.HR_NAV_SLIDER
							);
							const navSlides = tempNavElement.find(
								'.ctl-year-swiper-slide'
							);
							navSlides.each((index, slides) => {
								if(navSliderWrp[0]){
									navSliderWrp[0].swiper.appendSlide(slides);
								}
							});
						}
					}
				}

				this.ctlAfterContentLoad(response);
			} catch (error) {
				console.error('Error occurred while loading more:', error);
				// Handle the error or display an error message
			} finally {
				if (!this.newTimeline) {
					nextBtn.innerHTML = nextBtnIcon;
				}
				this.isRequestProcessing = false;
				this.newTimeline = false;
			}
		};

		// Load more common function call before content load
		ctlBeforeContentLoad = (response) => {
			// Remove all comment nodes in content if newTimeline is true.
			if (this.newTimeline) {
				const childNodes = this.timelineContainer[0] ? this.timelineContainer[0].childNodes : undefined;
				Object.values(childNodes).forEach((node) => {
					if (node.nodeType === Node.COMMENT_NODE) {
						node.remove();
					}
				});
			}

			const yearNavigation = this.timelineAttr['year-navigation'];
			const yearNavigationSection =
				yearNavigation === 'show'
					? jQuery(`#${this.wrapperId}`)
						.closest('.ctl-wrapper')
						.find('.ctl-navigation-bar')
					: undefined;

			if(this.timelineContainer.closest('.ctl-wrapper').find('.ctl-category-container .ctl-loader-spinner')){
				this.timelineContainer.closest('.ctl-wrapper').find('.ctl-category-container .ctl-loader-spinner').remove();
			}

			if (!this.newTimeline) {
				this.loadMoreBtn.attr('data-page', response.nextpage);
			}

			if (response.data && response.data.HTML) {
				const noContent = jQuery('<div>')
					.html(response.data.HTML)
					.find('.no-content');
				if (noContent.length === 0) {
					this.timelineContainer
						.closest('.cool-timeline-wrapper')
						.removeClass('ctl-content-empty');
						if(yearNavigationSection){
							yearNavigationSection.show();
						}
				} else {
					this.timelineContainer
						.closest('.cool-timeline-wrapper')
						.addClass('ctl-content-empty');
						if(yearNavigationSection){
							yearNavigationSection.hide();
						}
				}

				if (noContent.length && !this.newTimeline) {
					this.loadMoreBtn.remove();
					this.loadMoreBtn = '';
					return false;
				}
			}
			return true;
		};

		// Load more common function call after content load
		ctlAfterContentLoad = (response) => {
			const yearNavigation = this.timelineAttr['year-navigation'];
			const parentWrp = this.timelineContainer.closest(
				'.cool-timeline-wrapper'
			);
			const isVrStyle = !this.hrStyle;

			// vertical AOS animation reInit
			if (isVrStyle) {
				this.CtlStoryAos(this.wrapperId);
			}

			// compact layout reInit
			if (parentWrp.hasClass('ctl-compact-wrapper')) {
				this.ctlCompactMasonry(
					jQuery('.ctl-compact-wrapper .ctl-timeline-container'),
					this.timelineAttr.animation,
					true
				);
				setTimeout(() => {
					this.ctlCompactMasonry(
						jQuery('.ctl-compact-wrapper .ctl-timeline-container'),
						this.timelineAttr.animation
					);
				}, 100);
			}

			// update year navigation in both layout
			if (yearNavigation === 'show') {
				if (response.data && response.data.HR_NAV_SLIDER) {
					this.updateYearNavigation(response.data.HR_NAV_SLIDER);
				} else if(response.data && response.data.HTML){
					this.updateYearNavigation(response.data.HTML);
				}
			}

			// Add Loadmore Btn if Load More Btn Not Exist
			if (this.loadMoreBtn.length === 0) {
				const newPagination = jQuery(response.data) && jQuery(response.data.newPagination);
				if(newPagination){
					if ('post_timeline' === this.timelineAttr.ctl_type) {
						this.timelineContainer
							.closest('.cool-timeline-wrapper')
							.append(newPagination);
					} else {
						if(this.StylesContainer.length){
							newPagination.insertBefore(this.StylesContainer);
						}else{
							this.timelineContainer
							.closest('.cool-timeline-wrapper')
							.append(newPagination);
						}
					}
				}

				this.loadMoreBtn = jQuery(
					`#${this.wrapperId} .ctl_load_more_pagination button.ctl_load_more`
				);

				// New Vertical Load More button on click function
				if (isVrStyle) {
					this.loadMoreBtn.on('click', () => {
						this.vrLoadMore();
					});
				}
			}

			// Append New css in style container
			if (response.data && response.data.CSS) {
				// eslint-disable-next-line no-unused-expressions
				if (this.newTimeline) {
					const previousStyle = this.StylesContainer.html();
					const updatedStyle = previousStyle.replace(
						/\/\* Specific Story Style Start \*\/[\s\S]*?\/\* Specific Story Style End \*\//,
						`/* Specific Story Style Start */\n${response.data.CSS}\n/* Specific Story Style End */`
					);
					this.StylesContainer.html(updatedStyle);
				} else {
					this.StylesContainer.append(response.data.CSS);
				}
			}

			// Remove Load Btn if next post does not exist
			if (this.loadMoreBtn.length > 0) {
				const totalPagesCount = parseInt(
					this.loadMoreBtn.attr('data-max-num-pages')
				);
				const removeLoadMore = this.removeLoadMoreIfOnLastPage(
					response.nextpage,
					totalPagesCount
				);
				if (!removeLoadMore) {
					this.loadMoreBtn.find('span.clt_loading_state').hide();
					this.loadMoreBtn.find('span.default_state').show();
				}
			}

			// Initialize Popup Slider on load more item
			this.CtlLighBoxGallery();

			// Timeline Media Slider Call Back after content load
			this.CtlStorySliderCall(jQuery(`#${this.wrapperId}`));
		};

		/**
		 * timeline shortcode attribute assign to TimelineShortcodeAttr
		 */
		shortcodeAttributes = () => {
			const attrObject = `config_${this.wrapperId}`;
			if (window[attrObject] === undefined) {
				return null;
			}
			const attributes = window[attrObject];
			const attributeObject = JSON.parse(attributes.attributes);
			this.timelineAttr = attributeObject;
		};

		/**
		 * Overwrite shortcode attributes with new load more attributes
		 */
		loadMoreAttributes = () => {
			const activeCategoryWrp =
				this.catFilterContainer.length !== 0
					? this.catFilterContainer.find('ul li.active a')
					: false;
			const activeCategory =
				activeCategoryWrp.length > 0
					? activeCategoryWrp.data('term-slug')
					: false;
			let attributes = '';
			if (this.newTimeline) {
				attributes = JSON.stringify({
					...this.timelineAttr,
					...this.newAttribute,
				});

			} else if (activeCategory) {
				attributes = JSON.stringify({
					...this.timelineAttr,
					category: activeCategory,
				});
			} else {
				attributes = JSON.stringify(this.timelineAttr);
			}

			return attributes;
		};

		/**
		 * create data object for ajax request
		 */
		getDataObject = () => {
			const page = this.newTimeline
				? 1
				: this.loadMoreBtn.attr('data-page');
			// Increment page count by one.
			const nextPage = this.newTimeline ? 1 : parseInt(page) + 1;

			let lastStoryIndex = this.timelineContainer
				.find('.ctl-story:last-of-type')
				.attr('data-story-index');
			lastStoryIndex = parseInt(lastStoryIndex) + 1;

			const lastYear = this.timelineContainer
				.closest('.cool-timeline-wrapper')
				.find('.ctl-year-container.scrollable-section')
				.last()
				.attr('data-section-title');
			const allAttributes = this.loadMoreAttributes();
			let Action;
			if ('post_timeline' === this.timelineAttr.ctl_type) {
				Action = 'ctl_post_ajax_load_more';
			} else {
				Action = 'ctl_ajax_load_more';
			}

			const dataObject = {
				page_number: nextPage,
				action: Action,
				ajax_nonce: this.ajaxNonce,
				last_story_index: this.newTimeline ? 1 : lastStoryIndex,
				active_year: this.newTimeline ? '' : lastYear,
				attributes: allAttributes,
				new_timeline: this.newTimeline,
			};
			return dataObject;
		};

		/**
		 * Load more posts.
		 *
		 * 1.Make an ajax request, by incrementing the page no. by one on each request.
		 * 2.Append new/more stories to the existing content.
		 * 3.If the response is 0 ( which means no more posts available ), remove the load-more button from DOM.
		 */
		handleLoadMorePosts = () => {
			// Get page no from data attribute of load-more button.
			const page = this.newTimeline
				? 0
				: parseInt(this.loadMoreBtn.attr('data-page'));
			const maxNumPages = this.newTimeline
				? 1
				: parseInt(this.loadMoreBtn.attr('data-max-num-pages'));
			if (this.isRequestProcessing || page === maxNumPages) {
				return null;
			}

			// Increment page count by one.
			const nextPage = parseInt(page) + 1;

			// Multiple Reuqest Stop Const True
			this.isRequestProcessing = true;
			// const layout = this.loadMoreBtn.data("timeline-type");

			return new Promise((completed, failed) => {
				$.ajax({
					url: this.ajaxUrl,
					type: 'post',
					data: this.getDataObject(),
					beforeSend: () => {
						//   this.buttonContainer.addClass('loading');
						if (!this.newTimeline) {
							this.loadMoreBtn.addClass('loading');
							this.loadMoreBtn
								.children('span.clt_loading_state')
								.show();
							this.loadMoreBtn
								.children('span.default_state')
								.hide();
						}
					},
					success: (response) => {
						const data = { ...response, nextpage: nextPage };
						completed(data);
					},
					error: (response) => {
						failed(response);
					},
				});
			});
		};

		/**
		 * update year navigation after ajax request
		 *
		 * @param {string} HTML .
		 */
		updateYearNavigation = (HTML) => {
			const yearNavigationSection = jQuery(`#${this.wrapperId}`)
				.closest('.ctl-wrapper')
				.find('.ctl-navigation-bar');

			const parentWrp=jQuery(`#${this.wrapperId}`);

			const yearNavigation = yearNavigationSection.find('ul');

			const elementsCount = this.newTimeline
				? 1
				: yearNavigation.children('li').length;
			if (this.newTimeline) {
				yearNavigation.empty();
			}

			const tempElement = jQuery('<div>').html(HTML);

			const postId = Number(this.wrapperId.replace(/\D/g, ''));

			tempElement.find('.ctl-year-container').each((index, element) => {
				// timeline year label
				const { sectionTitle } = jQuery(element).data();

				const currentIndex = this.newTimeline ? 0 : elementsCount;
				const navigationId = `ctl-scrollar-${postId}-${sectionTitle}`;

				// Vertical year label id update
				if (!this.hrStyle && !parentWrp.hasClass('ctl-compact-wrapper')) {
					const yearLabel =
						this.timelineContainer.find('.timeline-year')[
						currentIndex + index
						];
					yearLabel.id = navigationId;
				}

				if(parentWrp.hasClass('ctl-compact-wrapper')){
					const yearLabel =
						this.timelineContainer.find('.ctl-year-container')[
						currentIndex + index
						];
					yearLabel.id = navigationId;
				}

				// Append new li element in year navigation
				const hrefAttr = this.hrStyle ? '' : `href="#${navigationId}"`;
				const liClass = this.hrStyle ? 'ctl-nav-item' : '';
				const yearHTML = `<li class="${liClass}"><a ${hrefAttr}>${sectionTitle}</a></li>`;
				yearNavigation.append(yearHTML);

				// Horizontal year navigation title update
				if (index === 0 && this.hrStyle && this.newTimeline) {
					const yearNavBtn = yearNavigationSection.find('button');
					yearNavBtn.attr('data-value', sectionTitle);
					yearNavBtn.find('span').text(sectionTitle);
					yearNavigationSection
						.find('.ctl-nav-item')
						.addClass('active');
				}
			});

			// Change Year Filter Style in horizontal layout & Vertical on scroll Handler Event call
			if (this.hrStyle) {
				this.yearFilterStyle(this.wrapperId);
			} else {
				this.yearNavHandleScroll({
					data: this.timelineContainer,
				});
			}
		};

		// Extra feature for vertical layout start
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
					// story.dataset.aos = animation;
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
						if('undefined' !== typeof AOS){
							AOS.refresh();
						}
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
				// await this.delay(this.delayTimer);
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

				// this.delayTimer = 0;
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

		// Horiozntal Year Filter Style
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
			if(navItems){
				navItems.click(slideToIndex);
			}
		};
		// Extra feature for vertical layout end

		// Common Functions
		// Common function for vertical and horizontal layout start
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
		CtlStorySliderCall = (parent) => {
			const storiesSlider = parent.find(
				'.ctl-story .ctp-story-slider:not(.swiper-initialized)'
			);

			storiesSlider.each((indx, ele) => {
				this.CtlStorySlider(jQuery(ele));
			});
		};

		// Init Timeline Media Slider
		CtlStorySlider = (element) => {
			const swiperWrp = element[0];
			const parentWrp = element.closest('.cool-timeline-wrapper');
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
				navigation,
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

			window.addEventListener('scroll', debouncedHandleVisibilityCheck);
			window.addEventListener('resize', debouncedHandleVisibilityCheck);
		};
		// Common function for vertical and horizontal layout end

		/**
		 * Remove Load more Button If on last page.
		 *
		 * @param nextPage        New Page.
		 * @param totalPagesCount Total Page.
		 */
		removeLoadMoreIfOnLastPage(nextPage, totalPagesCount) {
			if (nextPage + 1 > totalPagesCount) {
				this.loadMoreBtn.closest('.ctl_load_more_pagination').remove();
				this.loadMoreBtn = '';
			}
			return nextPage + 1 > totalPagesCount;
		}

		CtlStaticSvgIcons = (icon) => {
			const iconsArr = {
				spinner: `<svg class="ctl-loader-spinner" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
				<path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/>
				</svg>`,
			};
			const data = undefined !== iconsArr[icon] ? iconsArr[icon] : '';
			return data;
		};
	}

	jQuery(document).ready(function () {
		jQuery('div.cool-timeline-wrapper').each((index, timelineElement) => {
			const wrapperId = timelineElement.id;
			new CtpLoadMore(wrapperId);
		});
	});
})(jQuery);
