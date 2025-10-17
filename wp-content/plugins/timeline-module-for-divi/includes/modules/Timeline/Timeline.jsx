import React from "react";
import {LineFillEffect } from "../TimelineModules";
import "./style.css";

class TMDIVI_Timeline extends React.Component {

    static slug = "tmdivi_timeline";
    render() {

        return (
            <Timeline timeline_fill_setting={this.props.timeline_fill_setting} timeline_layout={this.props.timeline_layout} timeline_props={this.props}>
                {this.props.content}
            </Timeline>
        );

    }

    /**
    * 
    * Generate static css for Divi Module
    */
    static css(props) {

        const TimelineCSS = [];

        const story_padding = props.story_padding;
        const story_background_color = props.story_background_color;
        const line_color = props.timeline_color;
        const timeline_fill_setting = props.timeline_fill_setting;
        const line_fill_color = props.timeline_fill_color;
        const icon_background_color = props.icon_background_color;
        const icon_color = props.icon_color;
        const label_font = props.label_font;
        const label_fcolor = props.label_font_color;
        const label_fsize = props.label_font_size;
        const sub_label_font = props.sub_label_font;
        const sub_label_fcolor = props.sub_label_font_color;
        const sub_label_fsize = props.sub_label_font_size;
        const year_label_font = props.year_label_font;
        const year_label_font_size = props.year_label_font_size;
        const year_label_fcolor = props.year_label_font_color;
        const year_label_boxsize = props.year_label_box_size;
        const year_label_bgcolor = props.year_label_bg_color;
        const heading_text_size = props.heading_text_size;
        const heading_line_height = props.heading_line_height;
        const heading_font_color = props.heading_font_color;
        const heading_custom_padding = props.heading_custom_padding;
        const heading_background_color = props.heading_background_color;
        const heading_text_align = props.heading_text_align;
        const description_text_size = props.description_text_size;
        const description_line_height = props.description_line_height;
        const description_font_color = props.description_font_color;
        const description_background_color = props.description_background_color;
        const description_text_align = props.description_text_align;
        const description_custom_padding = props.description_custom_padding;
        const labels_position = props.labels_position;
        const labels_spacing_bottom = props.labels_spacing_bottom;
        const story_spacing_top = props.story_spacing_top;
        const story_spacing_bottom = props.story_spacing_bottom;
        const background_main = props.background_main;
        const timeline_line_width = props.timeline_line_width;
        const heading_settings_font = props.heading_settings_font

        function extractFontProperties(fontString) {
            const fontParts = fontString.split('|');
            const fontFamily = fontParts[0]; 
            const fontWeight = fontParts[1]; 
            const fontStyle = fontParts[2] === "on" ? "italic" : "normal"; 
            let textTransform = "none";
            let textDecoration = "none";
        
            // Determine text transform
            if (fontParts[3] === "on") {
                textTransform = "uppercase";
            } else if (fontParts[5] === "on") {
                textTransform = "capitalize";
            } else {
                textTransform = "none"
            }
        
            // Determine text decoration
            if (fontParts[4] === "on" && fontParts[6] === "on") {
                textDecoration = "line-through";
            } else if (fontParts[4] === "on") {
                textDecoration = "underline";
            } else if (fontParts[6] === "on") {
                textDecoration = "line-through";
            } else {
                textDecoration = "none"
            }
                
            const textDecorationLineColor = (fontParts[7] !== "") ? fontParts[7] : "";
            const textDecorationStyle = (fontParts[8] !== "") ? fontParts[8] : "";

            return {
                fontFamily,
                fontWeight,
                fontStyle,
                textTransform,
                textDecoration,
                textDecorationLineColor,
                textDecorationStyle
            };
        }
        
        if (label_font !== undefined) {

            const FontProperties = extractFontProperties(label_font); 

            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-font: ${FontProperties['fontFamily']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-weight: ${FontProperties['fontWeight']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-text-transform: ${FontProperties['textTransform']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-text-decoration: ${FontProperties['textDecoration']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-style: ${FontProperties['fontStyle']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-text-decoration-color: ${FontProperties['textDecorationLineColor']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-text-decoration-style: ${FontProperties['textDecorationStyle']};`
                },
                ]
            )
        }
        if (label_fcolor !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-color: ${label_fcolor};`
                }]
            )
        }
        if (label_fsize !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-big-size: ${label_fsize};`
                }]
            )
        }
        if (sub_label_font !== undefined) {

            const FontProperties = extractFontProperties(sub_label_font); 
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-font: ${FontProperties['fontFamily']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-weight: ${FontProperties['fontWeight']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-text-transform: ${FontProperties['textTransform']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-text-decoration: ${FontProperties['textDecoration']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-style: ${FontProperties['fontStyle']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-text-decoration-color: ${FontProperties['textDecorationLineColor']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-text-decoration-style: ${FontProperties['textDecorationStyle']};`
                },
                ]
            )
        }
        if (sub_label_fcolor !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-color: ${sub_label_fcolor};`
                }]
            )
        }
        if (sub_label_fsize !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-lbl-small-size: ${sub_label_fsize};`
                }]
            )
        }
        if (year_label_font !== undefined) {

            const FontProperties = extractFontProperties(year_label_font); 
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-font: ${FontProperties['fontFamily']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-text-weight: ${FontProperties['fontWeight']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-text-text-transform: ${FontProperties['textTransform']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-text-text-decoration: ${FontProperties['textDecoration']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-text-style: ${FontProperties['fontStyle']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-text-text-decoration-color: ${FontProperties['textDecorationLineColor']};`
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-text-text-decoration-style: ${FontProperties['textDecorationStyle']};`
                },
                ]
            )
        }
        if (year_label_fcolor !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-text-color: ${year_label_fcolor};`
                }]
            )
        }
        if (year_label_boxsize !== undefined) {
            TimelineCSS.push(
                [{
                    // selector: "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-size: ${year_label_boxsize};`
                }]
            )
        }
        if (year_label_font_size !== undefined) {
            TimelineCSS.push(
                [{
                    // selector: "%%order_class%% .tmdivi-wrapper .tmdivi-year-text",
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-text-size: ${year_label_font_size};`
                }]
            )
        }
        if (year_label_bgcolor !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ybx-bg: ${year_label_bgcolor};`
                }]
            )
        }
        /* make sure not to create any css array without checking props value */
        if (line_color !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-line-bg: ${line_color};`
                }]
            )
        }

        if (line_fill_color !== undefined && timeline_fill_setting === "on") {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-line-filling-color: ${line_fill_color};`
                }]
            )
        }
        if (icon_background_color !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ibx-bg: ${icon_background_color};`
                }]
            )
        }
        if (icon_color !== undefined) {
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-ibx-color: ${icon_color};`,
                }]
            )
        }
        if (heading_text_size !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-title-font-size: ${heading_text_size};`,
                }]
            )
        }
        if (heading_line_height !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-title",
                    declaration: `line-height: ${heading_line_height};`,
                }]
            )
        }
        if (heading_font_color !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-title-color: ${heading_font_color};`,
                }]
            )
        }
        if (heading_background_color !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-title-background-color: ${heading_background_color};`,
                }]
            )
        }
        if (heading_text_align !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-text-align: ${heading_text_align};`,
                }]
            )
        }
        if (heading_custom_padding !== undefined) { 
            const padding_part = heading_custom_padding.split('|');

            const padding_top = (padding_part[0] !== "") ? padding_part[0] : "5px";
            const padding_right = (padding_part[1] !== "") ? padding_part[1] : "5px";
            const padding_bottom = (padding_part[2] !== "") ? padding_part[2] : "5px";
            const padding_left = (padding_part[3] !== "") ? padding_part[3] : "5px";

            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-title-padding: ${padding_top} ${padding_right} ${padding_bottom} ${padding_left};`,
                }]
            )
        }
        if (heading_settings_font !== undefined) { 
            const FontProperties = extractFontProperties(heading_settings_font); 
            const fontWeight = FontProperties['fontWeight'] === "" ? 'normal' : FontProperties['fontWeight'];

            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-title-font-weight:${fontWeight};`,
                }]
            )
        }
        if (description_text_size !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-des-text-size: ${description_text_size};`,
                }]
            )
        }
        if (description_line_height !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-description",
                    declaration: `line-height: ${description_line_height};`,
                }]
            )
        }
        if (description_font_color !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-des-color: ${description_font_color};`,
                }]
            )
        }
        if (description_background_color !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-des-background: ${description_background_color};`,
                }]
            )
        }
        if (description_text_align !== undefined) { 
            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-des-text-align: ${description_text_align};`,
                }
                ]
            )
        }
        if (description_custom_padding !== undefined) { 
            const padding_part = description_custom_padding.split('|');

            const padding_top = (padding_part[0] !== "") ? padding_part[0] : "5px";
            const padding_right = (padding_part[1] !== "") ? padding_part[1] : "0px";
            const padding_bottom = (padding_part[2] !== "") ? padding_part[2] : "0px";
            const padding_left = (padding_part[3] !== "") ? padding_part[3] : "0px";

            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-des-padding: ${padding_top} ${padding_right} ${padding_bottom} ${padding_left};`,
                }
                ]
            )
        }
        if (story_padding !== undefined) {
            const padding_part = story_padding.split('|');

            const padding_top = (padding_part[0] !== "") ? padding_part[0] : "0.75em";
            const padding_right = (padding_part[1] !== "") ? padding_part[1] : "0.75em";
            const padding_bottom = (padding_part[2] !== "") ? padding_part[2] : "2px";
            const padding_left = (padding_part[3] !== "") ? padding_part[3] : "0.75em";

            TimelineCSS.push(
                [{
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-padding: ${padding_top} ${padding_right} ${padding_bottom} ${padding_left};`,
                }
                ]
            )
        }
        if (story_background_color !== undefined) {
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-cbx-bgc: ${story_background_color};`,
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-arrow",
                    declaration: `background: ${story_background_color};`,
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                    declaration: `background: ${story_background_color};`,
                },
                ]
            )
        }
        if (labels_position !== undefined) {
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-vertical",
                    declaration: `--tw-ibx-position: ${parseInt(labels_position)};`,
                }
                ]
            )
        }
        if (labels_spacing_bottom !== undefined) {
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-labels",
                    declaration: `gap: ${labels_spacing_bottom};`,
                }
                ]
            )
        }
        if (story_spacing_top !== undefined) {
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story",
                    declaration: `margin-top: ${story_spacing_top};`,
                }
                ]
            )
        }
        if (story_spacing_bottom !== undefined) {
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story",
                    declaration: `margin-bottom: ${story_spacing_bottom};`,
                }
                ]
            )
        }
        if (background_main !== undefined) {
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-vertical",
                    declaration: `--tw-tw-main-bc: ${background_main};`,
                }
                ]
            )
        }
        if(props.hasOwnProperty('border_style_all_story_settings')){
            const border_style_all = props.border_style_all_story_settings
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                    declaration: `border-style: hidden hidden ${border_style_all} ${border_style_all};`,
                },
                {
                    selector: "%%order_class%% .tmdivi-vertical-left.tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                    declaration: `border-style:${border_style_all} ${border_style_all} hidden hidden;`,
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                    declaration: `border-style: ${border_style_all} ${border_style_all} hidden hidden;`,
                }
                ]
            )
        }else{
            if(props.hasOwnProperty('border_width_all_story_settings')){
                TimelineCSS.push(
                    [
                    {
                        selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                        declaration: `border-style: hidden hidden solid solid;`,
                    },
                    {
                        selector: "%%order_class%% .tmdivi-vertical-left.tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                        declaration: `border-style:solid solid hidden hidden;`,
                    },
                    {
                        selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                        declaration: `border-style: solid solid hidden hidden;`,
                    },
                    {
                        selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                        declaration: `border-width: ${props.border_width_all_story_settings};`,
                    },
                    {
                        selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                        declaration: `border-width: ${props.border_width_all_story_settings};`,
                    }
                    ]
                )
            }            
        }
        if (props.border_color_all_story_settings !== undefined) {
            const border_color_all = props.border_color_all_story_settings
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                    declaration: `border-color: transparent transparent ${border_color_all} ${border_color_all};`,
                },
                {
                    selector: "%%order_class%% .tmdivi-vertical-left.tmdivi-wrapper .tmdivi-story .tmdivi-arrow",
                    declaration: `border-color:${border_color_all} ${border_color_all} transparent transparent;`,
                },
                {
                    selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story.tmdivi-story-left .tmdivi-arrow",
                    declaration: `border-color: ${border_color_all} ${border_color_all} transparent transparent;`,
                }
                ]
            )
        }

        if (props.timeline_line_width !== undefined) {
            TimelineCSS.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-wrapper",
                    declaration: `--tw-line-width:${timeline_line_width};`,
                },
                ]
            )
        }

        TimelineCSS.push(
            [{
                selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story-right .tmdivi-labels",
                declaration: `text-align: right;`
            }]
        )
        TimelineCSS.push(
            [{
                selector: "%%order_class%% .tmdivi-wrapper .tmdivi-story-left .tmdivi-labels",
                declaration: `text-align: left;`
            }]
        )

        //--tw-ibx-bg
        return TimelineCSS;
    }


}
/**
 * @returns ReactJS Custom component for timeline
 */
function Timeline({children ,timeline_fill_setting, timeline_layout,timeline_props}) {

    const className = timeline_props.moduleInfo.orderClassName;
    const instanceNum = className.split("_").pop(); 
    const wrapperId = `tmdivi-wrapper-${instanceNum}`;    

    let timelineLayout;
    switch(timeline_layout){
        case "one-side-left":
            timelineLayout = "tmdivi-vertical-right";
            break;
        case "one-side-right":
            timelineLayout = "tmdivi-vertical-left";
            break;
        default:
            timelineLayout = "both-side";
    }
    const verticalLayout = document.querySelector('.tmdivi-vertical');
    if(verticalLayout !== null){
        verticalLayout.setAttribute(`data-layout`,timelineLayout)
    }
    return (
        <div id={wrapperId} className={`tmdivi-vertical tmdivi-wrapper ${timelineLayout} style-1 tmdivi-bg-simple`}>
            <div className="tmdivi-start"></div>
            <div className="tmdivi-line tmdivi-timeline">
                <TimelineStories> {children} </TimelineStories>
                <LineFillEffect timeline_fill_setting={timeline_fill_setting}/>
            </div>
            <div className="tmdivi-end"></div>
        </div>
    );
}

/**
 * 
 * @param string children - Timeline stories
 * @returns TimelineChild component
 */
function TimelineStories({ children }) {
    return children;
}

export default TMDIVI_Timeline;