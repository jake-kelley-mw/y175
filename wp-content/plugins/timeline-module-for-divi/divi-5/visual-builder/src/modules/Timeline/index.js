import TimelineEdit from './edit';
import { TimelineSettingsContent } from './settings-content';
import { TimelineSettingsDesign }  from './settings-design';
import { TimelineSettingsAdvanced } from './settings-advanced';
import metadata from './module.json';
import {conversionOutline} from './conversion-outline'

export const timelineMetadata = metadata  

export const timeline = {
  settings: {
    content: TimelineSettingsContent,
    design: TimelineSettingsDesign,
    advanced: TimelineSettingsAdvanced,
  },
  renderers: {
    edit: TimelineEdit,
  },
  childrenName: ['tmdivi/timeline-story'],
  template:     [
    ['tmdivi/timeline-story', {}],
    ['tmdivi/timeline-story', {}],
    ['tmdivi/timeline-story', {}],
  ],
  conversionOutline
};
