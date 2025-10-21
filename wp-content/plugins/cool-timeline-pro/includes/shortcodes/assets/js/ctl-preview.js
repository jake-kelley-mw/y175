/* eslint-disable prettier/prettier */
class CtlPreview {
    constructor() {
        this.iframeWrp = jQuery('#ctl_preview')[0];
        this.iframe =
            this.iframeWrp.contentDocument ||
            this.iframeWrp.contentWindow.document;
        this.CtlPreviewShortcode();
        this.CtlPreviewInit();
    }

    // Preivew Iframe Initialize and added script
    CtlPreviewInit() {
        // eslint-disable-next-line no-undef
        const fieldWrp = this.iframeWrp.closest('.ctl-field-content');
        const ModelWrp =
            this.iframeWrp.closest('.ctl-modal-content');
        fieldWrp.style = `height: calc(${ModelWrp.clientHeight}px - 108px); overflow: hidden`;

        this.CtlPreviewIframeStyle();

        const jqueryScript = this.iframe.createElement('script');
        jqueryScript.src = 'https://code.jquery.com/jquery-3.6.4.min.js';
        this.iframe.head.appendChild(jqueryScript);
        const preloaderUrl=jQuery(this.iframeWrp).data('preloader');
        jQuery(this.iframe.body).append(`<img id="ctl-preview-preloader" src="${preloaderUrl}" style="position: absolute; top: 50%; left: 50%; transform: scale(3) translate(-50%, -50%)">`);
        this.ctlPreviewAjax();
    }

    CtlPreviewIframeStyle() { 
        const style = this.iframe.createElement('style');
        style.id= 'ctl-preview-style';
        style.textContent = `           
        #ctl_preview{
			width: 200%;
			height: 200%;
			transform: translate(-25%, -25%) scale(0.5);
		}`;

        if(!document.getElementById('ctl-preview-style')){
            document.head.appendChild(style);
        };
    } 

    // Get Timeline Shortcode Attribute
    CtlPreviewShortcode() {
        const selectedVal =
            parseInt(jQuery('.ctl-modal-header select').val()) - 1;
        const selectedShortCode = jQuery('.ctl-modal-header select option')[
            selectedVal
        ];
        const shortcodeType = jQuery(selectedShortCode).attr('data-shortcode');
        const shortcodeTypeRender =
            shortcodeType === 'cool-timeline' ?
            'cool-timeline' :
            'cool-content-timeline';

        // Create an object to store the shortcode values
        const shortcodeObject = {
            shortcodeType: shortcodeTypeRender,
        };

        // Select the relevant input and select elements
        const layoutInput = jQuery(
            '.ctl-fieldset select , .ctl-fieldset input:checked ,.ctl-fieldset input:text , .ctl-fieldset .ctl-siblings .ctl--active input:checked'
        );

        // Loop through the selected inputs and selects
        layoutInput.each(function() {
            const inputKey = jQuery(this).attr('data-depend-id');
            const inputValue = jQuery(this).val();

            // Include input and select values in the object
            if (inputKey && inputValue) {
                shortcodeObject[inputKey] = inputValue;
            }
        });

        this.shortcodeObject = shortcodeObject;
        return shortcodeObject;
    }

    // Timeline Preview Ajax Request
    ctlPreviewAjax() {
        jQuery.ajax({
            type: 'POST',
            // eslint-disable-next-line no-undef
            url: ctlPreviewData.ajaxurl, // Set this using wp_localize_script
            data: {
                action: 'get_shortcode_preview',
                shortcode: this.shortcodeObject,
                // eslint-disable-next-line no-undef
                nonce: ctlPreviewData.nonce,
            },
            success: (response) => {
                setTimeout(()=>{
                    jQuery(this.iframe.body).html('');
                    this.CtlPreviewAjaxSuccess(response);
                }, 1000)
            },
            error: (xhr, status, error) => {
                console.log(xhr.responseText);
                console.log(error);
                console.log(status);
            },
        });
    }

    // Ajax Success Function
    CtlPreviewAjaxSuccess(response) {
        // Added Timeline Style Files in iframe
        let styleFiles = '';
        if ('icon' === this.shortcodeObject.icons) {
            styleFiles += `<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?ver=4.5.6" media="all">`;
            styleFiles += `<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css" media="all">`;
        };
        response.data.assets.style.map((file) => {
            styleFiles += `<link rel="stylesheet" href="${file}" media="all">`;
        });

        // Added Timeline Script Files in iframe
        let scriptFiles = '';
        response.data.assets.script.map((file) => {
            scriptFiles += `<script type="text/javascript" src="${file}"></script>`;
        });
        if ('compact' === this.shortcodeObject.layout) {
            scriptFiles += this.CtlCompactReInit();
        }

        // Create a custom style element for iframe
        const styleElement = this.iframe.createElement('style');
        styleElement.textContent = response.data.assets.custom_style;

        // Append the style files to the head of the iframe's content document
        jQuery(this.iframe).find('head').append(styleFiles);
        // Append the Customstyle element to the head of the iframe's content document
        this.iframe.head.appendChild(styleElement);
        // Append the timeline html to the body of the iframe's content document
        jQuery(this.iframe).find('body').prepend(response.data.shortcode);
        // Append the script files to the head of the iframe's content document
        jQuery(this.iframe).find('body').append(scriptFiles);

        jQuery(this.iframe)
            .find('a')
            .on('click', (e) => {
                e.preventDefault();
            });
    }

    CtlCompactReInit() {
        const script = `<script type="text/javascript">const ctlCompactReInit=(grids,reloadItems)=>{let grid="",leftReminder=0,rightReminder=0;grid=reloadItems?grids.masonry("reloadItems"):grids.masonry({itemSelector:".ctl-story",initLayout:!1}),grid.imagesLoaded().progress(()=>{grid.masonry("layout")}),grid.one("layoutComplete",()=>{let leftPos=0,topPosDiff;grid.find(".ctl-story").each((index,element)=>{if(leftPos=jQuery(element).position().left,leftPos<=0){const extraCls=leftReminder%2==0?"ctl-left-odd":"ctl-left-even",prevCls="ctl-left-odd"===extraCls?"ctl-left-even":"ctl-left-odd";jQuery(element).removeClass("ctl-story-right").removeClass("ctl-right-even").removeClass("ctl-right-odd").removeClass(prevCls).addClass("ctl-story-left").addClass(extraCls),leftReminder++}else{const extraCls=rightReminder%2==0?"ctl-right-odd":"ctl-right-even",prevCls="ctl-right-odd"===extraCls?"ctl-right-even":"ctl-right-odd";jQuery(element).removeClass("ctl-story-left").removeClass("ctl-left-odd").removeClass("ctl-left-even").removeClass(prevCls).addClass("ctl-story-right").addClass(extraCls),rightReminder++}topPosDiff=jQuery(element).position().top-jQuery(element).prev().position().top,topPosDiff<40&&(jQuery(element).removeClass("ctl-compact-up").addClass("ctl-compact-down"),jQuery(element).prev().removeClass("ctl-compact-down").addClass("ctl-compact-up"))}),jQuery(".ctl-icon").addClass("showit"),jQuery(".ctl-title").addClass("showit-after")})};setTimeout(()=>{ctlCompactReInit(jQuery(document).find(".ctl-compact-wrapper .ctl-timeline-container"),!1)},200),setTimeout(()=>{ctlCompactReInit(jQuery(document).find(".ctl-compact-wrapper .ctl-timeline-container"),!0)},1e3);</script>`;

        return script;
    }
}

(function() {
    // Set up a click event for the third tab
    let shortcodePreview = false;

    const iframeReload = () => {
        shortcodePreview = false;
        const iframeWrp = jQuery('#ctl_preview')[0];
        if(iframeWrp){
            iframeWrp.contentWindow.location.reload(true);
        }
    };

    jQuery(document).on('click', '.ctl-tabbed-nav a:nth-child(3)', (e) => {
        // Update the shortcode before rendering and submit
        if (!shortcodePreview) {
            new CtlPreview();
            shortcodePreview = true;
        }
    });

    jQuery(document).on('click', '.ctl-modal-inner', (e) => {
        e.stopPropagation();
    });

    // Set up a click event for the third tab
    jQuery(document).on(
        'click',
        '.ctl-tabbed-nav a:not(:nth-child(3)), .ctl-modal-close , .ctl-modal-overlay',
        () => {
            iframeReload();
        }
    );

    jQuery(document).on('change', '.ctl-modal-header select', () => {
        iframeReload();
    })
})(jQuery);