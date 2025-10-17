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
    story_title: 'story_title.innerContent.*',
    content:      'content.innerContent.*',
    show_label:   'show_label.innerContent.enable.*',
    label_text:   'label_text.innerContent.*',
    label_date:   'label_date.innerContent.*',
    sub_label:   'sub_label.innerContent.*',
    media:   'media.innerContent.*.src',
    media_alt_tag:   'media_alt_tag.innerContent.*',
    show_story_icon:   'show_story_icon.innerContent.enable.*',
    story_icons: 'icon.innerContent.*',
},
  valueExpansionFunctionMap: {
    inline_fonts: convertInlineFont
  },
};