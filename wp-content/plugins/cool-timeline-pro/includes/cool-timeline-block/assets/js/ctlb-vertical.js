class CtlBlockVerticalCommon {
    constructor() {
        this.prevScrollComplete = true;
        const parentCls = 'cool-vertical-timeline-body';
        this.init(parentCls);
    }

    CtlStoryDateEmpty=(element)=>{
        const parentWrp = jQuery(`.${element}`).find('.cool-vertical-timeline-body.ctlb-wrapper');
        const dateLabel=parentWrp.find('.timeline-content .timeline-block-time .story-time');

        if(dateLabel.length <= 0){
            parentWrp.addClass('ctlb-date-not');
        }
    }

    // Vertical Line filling
    CtlVrLineFilling = (element) => {
        const parentWrp = jQuery(`.${element}`).find('.cool-vertical-timeline-body.ctlb-wrapper');
        const timelineYear = parentWrp.find('.ctlb-year-section');
        const lineFilling = parentWrp.data('line-filling');
        const Stories = parentWrp.find('.wp-block-cp-timeline-content-timeline-child');
        let rect = '';
        let ctlTop = '';
        let height = '';
        let centerLine = '';

        if (lineFilling && Stories.length > 0) {
            const outerDiv = parentWrp;
            const outerHeight = outerDiv.outerHeight();

            rect = outerDiv[0].getBoundingClientRect();
            const shortcodePrview = window.parent.jQuery('#ctl_preview');
            if (shortcodePrview.length > 0) {
                height = shortcodePrview.height() / 2;
            } else {
                height = jQuery(window).height() / 2;
            }
            centerLine = parentWrp.find('.ctlb-center-line-fill');
            ctlTop = rect.top < 0 ? Math.abs(rect.top) : -Math.abs(rect.top);
            const lineInnerHeight = ctlTop + height;
            const centerLineHeight = centerLine.height();
            const topScroll = jQuery(window).scrollTop() + height + 10;
            centerLine.height(
                lineInnerHeight <= outerHeight ? lineInnerHeight : outerHeight
            );

            timelineYear.each((index, year) => {
                const yearPosition = jQuery(year).offset().top - 20;
                const yearHeight = jQuery(year).height();
                jQuery(year).toggleClass(
                    'innerViewPort',
                    topScroll >= yearPosition + yearHeight
                );
            });

            Stories.each((index, wrp) => {
                const icons = '.timeline-block-icon';
                const iconsSize = jQuery(wrp).find(icons).width();
                const iconPosition = (jQuery(wrp).find(icons).offset().top + iconsSize / 2);
                jQuery(wrp).toggleClass(
                    'innerViewPort',
                    topScroll >= iconPosition
                );
            });

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

    // Aos animation
    CtlStoryAos = (wrapperCls) => {
        // enabled animation on page scroll
        const parentWrp = jQuery(`.${wrapperCls}`);
        const timelineContainer = parentWrp.find('.cool-vertical-timeline-body.ctlb-wrapper');
        const animation = timelineContainer.attr('data-animation');
        let prevHeight = jQuery(`.${wrapperCls}`).height();

        // addded empty data aos attribute on element for before init the AOS
        if (animation !== 'none' && animation !== undefined) {
            // Animation on story element
            timelineContainer.find('.wp-block-cp-timeline-content-timeline-child').each((index, story) => {
                jQuery(story)
                    .find(
                        '.timeline-block-icon, .timeline-block-detail, .timeline-block-time, .ctlb-year-label'
                    )
                    .each((_index, element) => {
                        element.dataset.aos = '';
                    });
            });

            // You can also pass an optional settings object
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

            // Added animation value in aos attribute on element
            timelineContainer.find('.wp-block-cp-timeline-content-timeline-child').each((index, story) => {
                jQuery(story)
                    .find(
                        '.timeline-block-icon, .timeline-block-detail, .timeline-block-time, .ctlb-year-label'
                    )
                    .each((_index, element) => {
                        element.dataset.aos = animation;
                    });
            });

            setTimeout(() => {
                AOS.refresh();
            }, 500);
        }
        jQuery(window).scroll(() => {
            const currentHeight = jQuery(`#${wrapperCls}`).height();
            if (prevHeight !== currentHeight) {
                setTimeout(function () {
                    AOS.refresh();
                }, 500);
                prevHeight = currentHeight;
            }
        });
    };

    CtlYearNav=(wrapperCls)=>{
        const parentWrp = jQuery(`.${wrapperCls}`);
        const timelineContainer = parentWrp.find('.cool-vertical-timeline-body.ctlb-wrapper');
        const yearNav=timelineContainer.data('year-nav');
        const yearNavWrp=parentWrp.find('.ctlb-year-nav-wrapper');
        if(undefined !== yearNav && yearNav){
            const yearLabels=timelineContainer.find('.ctlb-year-section .ctlb-year-label .ctlb-year-text');

            let yearNavHtml='<ul class="ctlb-year-nav-container">';
            yearLabels.each((_,ele)=>{
                const yearLabel=jQuery(ele);
                const yearLabelText=yearLabel.text();
                const yearSectionId=yearLabel.closest('.ctlb-year-section')[0].id;
                if(undefined !== yearLabelText && '' !== yearLabelText){
                    yearNavHtml += `<li class="ctlb-year-nav-text"><a href="#${yearSectionId}">${yearLabelText}</a></li>`;
                }
            });
            yearNavHtml += '</ul>';

            yearNavWrp.append(yearNavHtml);

            this.yearNavHandleScroll({ data: parentWrp });
			jQuery(window).on('scroll', parentWrp, this.yearNavHandleScroll);
            this.ctlYearScrollAnimation();
        }
    }

    // Year Navigation Scroll Handler
	yearNavHandleScroll = async (event) => {
		const selector = event.data;
		const TimelineStories = selector.find('.wp-block-cp-timeline-content-timeline-child');
		const laststory = TimelineStories[TimelineStories.length - 1];
		const rootElement = document.documentElement;
		const viewport = jQuery(window).height() / 3;
		const timelineWrapperPosition = selector.offset().top;
		const laststoryDiv = jQuery(laststory);
		const navigationBar = selector.find('.ctlb-year-nav-wrapper');
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

			const extraspace = 400;
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

    // Year Navigation Scroll Animation
	ctlYearScrollAnimation = () => {
		jQuery(document).on(
			'click',
			'.ctlb-wrapper.cool-vertical-timeline-body .ctlb-year-nav-wrapper ul li',
			(e) => {
				if (this.prevScrollComplete) {
					this.prevScrollComplete = false;
					e.preventDefault();
					const targetElement = jQuery(e.currentTarget)
						.find('a')
						.attr('href');
					if (targetElement.length) {
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

    init = (parentCls) => {
        const vrTimelines = jQuery(`.${parentCls}`);
        vrTimelines.each((_, ele) => {
            const timelineWrp = jQuery(ele);
            if (timelineWrp[0]) {
                const parentWrp = timelineWrp[0].parentElement;
                const classArr = Object.values(parentWrp.classList);

                const filterCls = classArr.filter(cls => {
                    return cls.indexOf('cool-timeline-block-') >= 0;
                });
                if (filterCls[0]) {
                    const yearNav=timelineWrp.data('year-nav');
                    const storyAos=timelineWrp.data('animation');
                    const lineFilling=timelineWrp.data('line-filling');

                    this.CtlStoryDateEmpty(filterCls[0]);

                    if('none' !== storyAos && undefined !== storyAos){
                        this.CtlStoryAos(filterCls[0]);
                    }
                    if(undefined !== lineFilling && lineFilling){
                        this.CtlVrLineFilling(filterCls[0]);
                    }

                    if(undefined !== yearNav && yearNav){
                        this.CtlYearNav(filterCls[0]);
                    }

                    jQuery(window).on('scroll', parentWrp, (el) => {
                        this.CtlVrLineFilling(filterCls[0]);
                    });
                }
            }

        });
    }
}


jQuery(document).ready(() => {
    new CtlBlockVerticalCommon;
})