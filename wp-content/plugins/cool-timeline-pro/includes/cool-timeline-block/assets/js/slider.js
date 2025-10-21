

jQuery(document).ready(function(){

  CtlStaticSvgIcons = (icon) => {
    const iconsArr = {
      chevronLeft: `<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
      <line x1="30" y1="51" x2="80" y2="0" stroke-width="8"></line>
      <line x1="30" y1="49" x2="80" y2="100" stroke-width="8"></line>
    </svg>`,
      chevronRight: `<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
      <line x1="20" y1="0" x2="70" y2="51" stroke-width="8"></line>
      <line x1="20" y1="100" x2="70" y2="49" stroke-width="8"></line>
    </svg>`
    };
    const data = undefined !== iconsArr[icon] ? iconsArr[icon] : '';
    return data;
  };

  const removeEmptyElement=()=>{
    const imageBlocks=jQuery('.ctlb-wrapper .wp-block-cp-timeline-content-timeline-child .story-content .wp-block-image img[src=""]')
    const headingBlocks=jQuery('.ctlb-wrapper .wp-block-cp-timeline-content-timeline-child .story-content .wp-block-heading:empty');
    const paragraphBlocks=jQuery('.ctlb-wrapper .wp-block-cp-timeline-content-timeline-child .story-content>p:empty');

    if(imageBlocks && imageBlocks.length > 0){
      imageBlocks.closest('figure').remove();
    }
    if(headingBlocks && headingBlocks.length > 0){
      headingBlocks.remove();
    }
    if(paragraphBlocks && paragraphBlocks.length > 0){
      paragraphBlocks.remove();
    }

    const eachStory=jQuery('.ctlb-wrapper .wp-block-cp-timeline-content-timeline-child');

    eachStory.each((index,element)=>{
      const parentElement=jQuery(element);
      const imageSlide=parentElement.find('.story-content .wp-block-image img');
      const mediaBlock=parentElement.find('.story-content .ctlb-block-video, .story-content .ctlb-block-slideshow .swiper-slide');
      const storyDate=parentElement.find('.timeline-block-time .story-time p');
      const headingBlocks=parentElement.find('.story-content .wp-block-heading');
      const paragraphBlocks=parentElement.find('.story-content>p');
      const navDate=parentElement.closest('.ctlb-wrapper.cool-horizontal-timeline-body').find('.ctlb-nav-swiper-outer .swiper-slide')[index];
      const navDateEmpty=undefined === navDate ? false : jQuery(navDate).find('.ctlb_nav_date:empty').length;
      if(imageSlide.length === 0 && mediaBlock.length <= 0 && storyDate.length === 0 && headingBlocks.length === 0 && paragraphBlocks.length === 0 && !navDateEmpty){
        const parentWrp=parentElement.closest('.ctlb-wrapper.cool-horizontal-timeline-body').find('.ctlb-nav-swiper-outer .swiper-slide')[index-1];
        if(parentWrp && undefined !== parentWrp){
          parentWrp.remove();
        }
        parentElement.remove();
      }
    });

  };

  removeEmptyElement();

  var swiper_el=jQuery(".swiper-outer")
  var child_swiper_el=jQuery(".child-swiper-outer")
  jQuery.each(swiper_el,function(){
      var swiper_id=jQuery(this).attr("id")
      var slides=jQuery(this).attr("data-slide")
     const parentWrp=document.querySelector(`.cool-timeline-block-${swiper_id}`).querySelector('.cool-horizontal-timeline-body');
     const designCls=parentWrp.classList;
     const mainSlidePerView=designCls.contains('design-1') ? 1 : slides;

     const prevIconHtml = CtlStaticSvgIcons('chevronLeft');
     const nextIconHtml = CtlStaticSvgIcons('chevronRight');
 
     jQuery(parentWrp).find('.swiper-button-prev').append(prevIconHtml);
     jQuery(parentWrp).find('.swiper-button-next').append(nextIconHtml);

    let navigation={};
    if(jQuery('body').hasClass('rtl') || jQuery('html').attr('dir') === 'rtl' ){
          navigation.prevEl= '.cool-timeline-block-'+swiper_id+' .swiper-button-next';
          navigation.nextEl= '.cool-timeline-block-'+swiper_id+' .swiper-button-prev';
        }else{
          navigation.nextEl= '.cool-timeline-block-'+swiper_id+' .swiper-button-next';
          navigation.prevEl= '.cool-timeline-block-'+swiper_id+' .swiper-button-prev';
    }

    if(designCls.contains('design-1')){
      var navSwiper= new Swiper('.cool-timeline-block-'+swiper_id+' .ctlb-nav-swiper-outer .swiper',{
        slidesPerView: slides,
        centeredSlides: true,
        allowTouchMove: false,
        breakpoints: {
          // when window width is >= 320px
          280: {
            slidesPerView: 1,
        
          },
          // when window width is >= 480px
          480: {
            slidesPerView: slides < 2 ? slides : 2,
        
          },
          // when window width is >= 640px
          640: {
            slidesPerView: slides,
      
          }
        }
      });
    }else{
      const yearLabel=jQuery(parentWrp).find('.ctlb-year-section');

      if(yearLabel.length <= 0){
        jQuery(parentWrp).addClass('ctlb-year-lable-not');
      }
    }

    const swiper =new Swiper('.cool-timeline-block-'+swiper_id+' .swiper-outer .swiper', {
      // Default parameters
      slidesPerView: mainSlidePerView,
      thumbs: designCls.contains('design-1') ? {
        swiper: navSwiper
      } : false,
      // Navigation arrows
      navigation,
      // pagination: {
      //   el: swiperPagination,
      //   type: 'progressbar',
      // },
      breakpoints: {
        // when window width is >= 320px
        280: {
          slidesPerView: 1,
       
        },
        // when window width is >= 480px
        480: {
          slidesPerView: mainSlidePerView < 2 ? mainSlidePerView : 2,
       
        },
        // when window width is >= 640px
        640: {
          slidesPerView: mainSlidePerView,
     
        }
      }
    })

    if(designCls.contains('design-1')){
      swiper.controller.control = navSwiper;
    }
  })
  jQuery.each(child_swiper_el,function(){
    var swiper_id=jQuery(this).attr("id")
    new Swiper('.timeline-child-swiper-outer-'+swiper_id+' .child-swiper', {
        // Default parameters
        slidesPerView: 1  ,
        // Navigation arrows
        navigation: {
            nextEl: '.timeline-child-swiper-outer-'+swiper_id+' .swiper-child-button-next',
            prevEl: '.timeline-child-swiper-outer-'+swiper_id+' .swiper-child-button-prev',
        },
  })
  })
})