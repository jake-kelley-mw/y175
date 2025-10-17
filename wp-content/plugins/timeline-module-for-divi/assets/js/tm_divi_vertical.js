(function($) {

    function tmdivi_scroll_callback(tm) {

        const timelineEntry = tm.find('.tmdivi-story');
        const outer_line = tm.find('.tmdivi-line');
        const inner_line = tm.find('.tmdivi-inner-line');
        const year_container = tm.find('.tmdivi-year-container');

        if (tm.length < 1) {
            return false;
        }
        // fill line color start
        var rootElement = document.documentElement;
        var half_viewport = (jQuery(window).height()) / 2;
        var lineID = outer_line[0];

        if (lineID == null) {
            return;
        }
        var rect = lineID.getBoundingClientRect();
        var timelineTop;


        if (rect.top < 0) {
            timelineTop = Math.abs(rect.top);
        } else {
            timelineTop = -Math.abs(rect.top);
        }
        var lineInnerHeight = timelineTop + half_viewport;
        var outer_line_height = outer_line.outerHeight();
        var timeline_position = jQuery(tm).offset().top;
        var timeline_top = timeline_position - rootElement.scrollTop;

        tm.addClass("tmdivi-start-out-viewport");

        if (lineInnerHeight <= outer_line_height) {
            tm.addClass("tmdivi-end-out-viewport");
            tm.addClass("tmdivi-start-out-viewport");
            inner_line.height(lineInnerHeight);
            if ((timeline_top) < ((half_viewport))) {
                tm.removeClass("tmdivi-start-out-viewport");
                tm.addClass("tmdivi-start-fill");
            }
        } else {
            tm.removeClass("tmdivi-end-out-viewport");
            tm.addClass("tmdivi-end-fill");
            inner_line.height(outer_line_height);
        }
        // // fill line color end

        var timelineEntry_position, timelineEntry_top,
            year_container_pos, year_container_top;

        for (var i = 0; i < timelineEntry.length; i++) {

            const icon=jQuery(timelineEntry[i]).find('.tmdivi-icon, .tmdivi-icondot');
            const iconPosition=icon.length > 0 ? icon[0].offsetTop : 0;
            timelineEntry_position = jQuery(timelineEntry[i]).offset().top + iconPosition;

            timelineEntry_top = timelineEntry_position - rootElement.scrollTop;

            if ((timelineEntry_top) < ((half_viewport))) {
                timelineEntry[i].classList.remove("tmdivi-out-viewport");
            } else {
                timelineEntry[i].classList.add("tmdivi-out-viewport");
            }

        }

        //fill year_container border
        for (var i = 0; i < year_container.length; i++) {

            year_container_pos = jQuery(year_container[i]).offset().top;

            year_container_top = year_container_pos - rootElement.scrollTop + 35;


            if ((year_container_top) < ((half_viewport))) {
                year_container[i].classList.remove("tmdivi-out-viewport");
            } else {
                year_container[i].classList.add("tmdivi-out-viewport");
            }
        }
    }

    $(document).ready(function() {
        $('.tmdivi-wrapper').each(function() {
            var timeline = $(this); 
            var lineFilling = timeline.data("line-filling");
            if (lineFilling !== undefined && lineFilling) {
                tmdivi_scroll_callback(timeline)
                window.addEventListener("scroll", ()=>{
                    tmdivi_scroll_callback(timeline)
                }); 
            }
        });
    });  

})(jQuery);
