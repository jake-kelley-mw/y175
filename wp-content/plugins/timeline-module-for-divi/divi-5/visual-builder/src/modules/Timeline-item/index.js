import TimelineItemEdit from './edit';
import metadata from './module.json';
import TimelineItemSettingsContent from './settings-content';
import TimelineItemSettingsDesign from './settings-design';
import TimelineItemSettingsAdvaced from './settings-advanced';
import {conversionOutline} from './conversion-outline'


export const timelineChildMetadata = metadata
export const timelineChild = {
  settings: {
    content: TimelineItemSettingsContent,
    design:TimelineItemSettingsDesign,
    advanced:TimelineItemSettingsAdvaced,
  },
  renderers: {
    edit: TimelineItemEdit,
  },
  parentsName: ['tmdivi/timeline'],
  placeholderContent: {
    label_date: {
      innerContent: {
        desktop: {
          value: 'Date'
        }
      }
    },
    sub_label: {
      innerContent: {
        desktop: {
          value: 'Sub Label'
        }
      }
    },
    story_title: {
      innerContent: {
        desktop: {
          value: 'What Is Lorem Ipsum'
        }
      }
    },
    content: {
      innerContent: {
        desktop: {
          value: "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book."
        }
      }
    }
  },
  conversionOutline
};
