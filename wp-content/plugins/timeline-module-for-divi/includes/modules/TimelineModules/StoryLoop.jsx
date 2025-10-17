import React from "react";
import { _renderProp } from "../ModulesCore/ModulesCore";


/**
 * 
 * @param story_title title of the timeline story
 * @param media URl of the featured image in story
 * @param alt_tag Alt tag for the image in story (optional)
 * @param content JSX function object of the content editor.
 * 
 * @returns JSX custom story component for individual story.
 */
function StoryContent({ story_title, media, content, alt_tag = '' }) {
    return (
        <div className="tmdivi-content">
            <div className="tmdivi-title">{story_title}</div>
            <div className="tmdivi-media full">
                {media && <img decoding="async" src={media} alt={alt_tag} />}
            </div>
            <div className="tmdivi-description">
                {content}
            </div>
        </div>
    );
}

/**
 * 
 * @param isIcon show | hide icon settings.
 * @param icon return object of the icon from settings module.
 * 
 * @returns JSX custom object for Icon of the story.
 */
function StoryIcon({ isIcon, icon }) {

    /**
     * Do not bail out with empty string.
     * Return empty HTML DIV container if icon is not available
     */

    const story_icon = isIcon === 'on' ? _renderProp((icon === undefined) ? "&#x7d;||divi||400" : icon, 'select_icons') : "";
    const icon_class = story_icon === '' ? 'tmdivi-icondot' : 'tmdivi-icon';

    return (
        <div className={`${icon_class}`}>
            {story_icon}
        </div>
    )
}

/**
 * 
 * @param isEnabled Module setting value if label is enabled
 * @param label String value for Story Year label 
 * @returns 
 */
function StoryYearLabel({ isEnabled, label }) {

    if (isEnabled !== 'on' || label === '') return <></>;

    return (<div className="tmdivi-year tmdivi-year-container">
        <div className="tmdivi-year-label tmdivi-year-text">{label} </div></div>);
}


/**
 * 
 * @param label_date Label / Date for the timeline story
 * @param sub_label Sub-Label for the timeline story
 * 
 * @returns JSX custom object for story label(s)
 */
function StoryLabels({ label_date, sub_label }) {

    return (
        <div className="tmdivi-labels">
            <div className="tmdivi-label-big">{label_date}</div>
            <div className="tmdivi-label-small">{sub_label}</div>
        </div>
    )
}

export{
    StoryContent,
    StoryIcon,
    StoryYearLabel,
    StoryLabels
};