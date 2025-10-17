import React from "react";
import { StoryYearLabel, StoryContent, StoryIcon, StoryLabels } from "../TimelineModules";
import "./style.css";

class TMDIVI_TimelineChild extends React.Component {

    static slug = "tmdivi_timeline_story";

    render() {

        let sided_css = '';
        const timelineStory = document.querySelectorAll('.tmdivi-vertical');
        let story_css = this.props.show_story_icon === 'on' ? 'tmdivi-story-icon' : 'tmdivi-story-icon'; // Check and fix this css class in case of empty icon
        for (let i = 0; i < timelineStory.length; i++) {
            let timelineLayout = timelineStory[i].getAttribute('data-layout'); // Scoped variable within the loop
            if (timelineLayout === "both-side") {
                sided_css = this.props.moduleInfo.order % 2 ? "tmdivi-story-left" : "tmdivi-story-right";
            } 

            story_css += ' ' + sided_css;

        }

        
        const storyTitle = this.props.story_title;          
        const labelDate = this.props.label_date;         
        const subLabel = this.props.sub_label;         
        const yearLabelText = this.props.label_text;               
        const storyMedia = this.props.media;  
        const storyDescription = this.props.content();  
        const yearLabel = this.props.show_label; 
        const storyIcon = this.props.show_story_icon;  
        const storyIconData = this.props.story_icons;  

        return (

            <>
                <StoryYearLabel
                    isEnabled={yearLabel}
                    label={yearLabelText}
                />
                <div className={`tmdivi-story ${story_css}`}>
                    <StoryLabels
                        label_date={labelDate}
                        sub_label={subLabel}
                    />
                    <StoryIcon
                        isIcon={storyIcon}
                        icon={storyIconData}
                    />
                    <div className="tmdivi-arrow"></div>
                    <StoryContent
                        story_title={storyTitle}
                        media={storyMedia}
                        alt_tag={this.props.media_alt_tag}
                        content={storyDescription}
                    />
                </div>
            </>
        );
    }

    static css(props) {
        const ChildTimelineCss = [];

        const renderIconClass=(selector)=>{
            const showIcon = props.show_story_icon
            const iconSetting= (showIcon === "on" && props.story_icons !== undefined) ? props.story_icons : '&#x7d;||divi||400';
                if (iconSetting) {
                    let fontFamily = {
                            divi: "ETmodules !important",
                            fa: "FontAwesome!important"
                        },
                        icon = iconSetting ? iconSetting.split("|") : [],
                        additionalCss = [];
                    additionalCss.push([
                        {
                            selector,
                            declaration: `
                            font-family: ${fontFamily[icon[2]]};
                            font-weight: ${icon[4]}!important;`
                        }
                    ]);
                    return additionalCss;
                }
                return [];
        };

        const child_story_background_color = props.child_story_background_color 
        const child_story_heading_color = props.child_story_heading_color 
        const child_story_description_color = props.child_story_description_color 
        const child_story_label_color = props.child_story_label_color 
        const child_story_sub_label_color = props.child_story_sub_label_color 

        if (child_story_background_color !== undefined) {
            ChildTimelineCss.push(
                [
                {
                    selector: "%%order_class%% .tmdivi-story .tmdivi-content",
                    declaration: `background-color: ${child_story_background_color};`,
                },
                {
                    selector: "%%order_class%% .tmdivi-story > .tmdivi-arrow",
                    declaration: `background: ${child_story_background_color} !important;`,
                },
                ]
            )
        }

        if (child_story_heading_color !== undefined) { 
            ChildTimelineCss.push(
                [{
                    selector: "%%order_class%% .tmdivi-story .tmdivi-content .tmdivi-title",
                    declaration: `--tw-cbx-title-color: ${child_story_heading_color};`,
                }]
            )
        }

        if (child_story_description_color !== undefined) { 
            ChildTimelineCss.push(
                [{
                    selector: "%%order_class%% .tmdivi-story .tmdivi-content .tmdivi-description",
                    declaration: `--tw-cbx-des-color: ${child_story_description_color};`,
                }]
            )
        }

        if (child_story_label_color !== undefined) { 
            ChildTimelineCss.push(
                [{
                    selector: "%%order_class%% .tmdivi-story div.tmdivi-label-big",
                    declaration: `--tw-lbl-big-color: ${child_story_label_color};`,
                }]
            )
        }

        if (child_story_sub_label_color !== undefined) { 
            ChildTimelineCss.push(
                [{
                    selector: "%%order_class%% .tmdivi-story div.tmdivi-label-small",
                    declaration: `--tw-lbl-small-color: ${child_story_sub_label_color};`,
                }]
            )
        }

        const iconStyle = renderIconClass("%%order_class%% .tmdivi-story .tmdivi-icon .et-tmdivi-icon");

        ChildTimelineCss.push(iconStyle[0]);

        return ChildTimelineCss;
    }

}

export default TMDIVI_TimelineChild;