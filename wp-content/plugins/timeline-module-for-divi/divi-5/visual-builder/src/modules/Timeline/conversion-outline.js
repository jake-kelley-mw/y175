const convertInlineFont = (value) => (isString(value) ? value.split(',') : []);

export const conversionOutline = {
  advanced: {
    admin_label:    'module.meta.adminLabel',
    animation:      'module.decoration.animation',
    background:     'module.decoration.background',
    disabled_on:    'module.decoration.disabledOn',
    module:         'module.advanced.htmlAttributes',
    overflow:        'module.decoration.overflow',
    position_fields: 'module.decoration.position',
    scroll:         'module.decoration.scroll',
    sticky:         'module.decoration.sticky',
    text:           'module.advanced.text',
    transform:      'module.decoration.transform',
    transition:     'module.decoration.transition',
    z_index:        'module.decoration.zIndex',
    margin_padding: 'module.decoration.spacing',
    max_width:      'module.decoration.sizing',
    height:         'module.decoration.sizing',
    link_options:   'module.advanced.link',
    fonts:      {
      header:         'story_title.decoration.font',
      body:           'content.decoration.bodyFont.body',
      body_link:      'content.decoration.bodyFont.link',
      body_ul:        'content.decoration.bodyFont.ul',
      body_ol:        'content.decoration.bodyFont.ol',
      body_quote:     'content.decoration.bodyFont.quote',
    },
    text_shadow:     {
      default: 'module.advanced.text.textShadow',
    },
    box_shadow: {
      default: 'module.decoration.boxShadow',
    },
    borders:        {
      default: 'module.decoration.border',
    },
    filters:   {
      default: 'module.decoration.filters',
    },
  },
  css: {
    after:        'css.*.after',
    before:       'css.*.before',
    main_element: 'css.*.mainElement',
    title:        'css.*.story_title',
    content:      'css.*.content',
  },
  module: {
    timeline_layout: 'timeline_layout.advanced.layout.*.timeline_layout',
    icon_color: 'icon_color.advanced.*',
    icon_background_color: 'icon_background_color.advanced.*',
    timeline_color: 'timeline_color.advanced.*',
    story_background_color: 'story_background_color.advanced.*',
    timeline_fill_setting: 'timeline_fill_setting.advanced.*',
    timeline_fill_color: 'timeline_fill_color.advanced.*',
  },
  valueExpansionFunctionMap: {
    inline_fonts: convertInlineFont
  },
};