<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
Creating Meta boxes for timeline stories section
*/
// Control core classes for avoid errors
if( class_exists( 'CTL' ) ) {
        
    // Set a unique slug-like ID
    $prefix = 'ctl_post_meta';
  
    //
    // Create a metabox
    CTL::createMetabox( $prefix, array(
      'title'     => 'Timeline Story Settings',
      'post_type' => 'cool_timeline',
      'data_type' => 'unserialize',
      'context'   => 'normal', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
    ) );
    
    //
    // Create a section
    CTL::createSection( $prefix, array(
        'data_type' => 'unserialize',
        'fields' => array( 
            
            array(
                'id'     => 'story_type',
                'type'   => 'fieldset',
                'title'  => 'Story Type',
                'fields' => array(
                    array(
                        'id'         => 'story_based_on',
                        'type'       => 'button_set',
                        'title'      => 'Story Based On',
                        'options'    => array(
                            'default' => 'Date Based',
                            'custom' => 'Custom Order Based'
                        ),
                        'default'    => 'default'
                    ),
        
                    array(
                        'id'    => 'ctl_story_date',
                        'type'  => 'datetime',
                        'title' => 'Story Date / Year <span class="ctl_required">*</span>',
                        'desc' =>'<p class="ctl_required">Please select story Story Date / Year / Time using datepicker only. <strong>Date Format( mm/dd/yy hh:mm )</strong></p>',
                        'default' => date('m/d/Y h:m a'),   
                        'dependency' => array( 'story_based_on', '==', 'default' ),
                        'settings' => array(
                            'enableTime'      => true,
                            'dateFormat'      => 'm/d/Y h:i K',
                            'minuteIncrement' => '1',
                        ),
                    ),  
        
                    array(
                        'id'         => 'ctl_story_lbl',
                        'type'       => 'text',
                        'title'      => 'Add custom label',               
                        'default'    => '',
                        'dependency' => array( 'story_based_on', '==', 'custom' )
                    ),
        
                    array(
                        'id'         => 'ctl_story_lbl_2',
                        'type'       => 'text',
                        'title'      => 'Add second custom label',               
                        'default'    => '',
                        'dependency' => array( 'story_based_on', '==', 'custom' )
                    ),
        
                    array(
                        'id'         => 'ctl_story_order',
                        'type'       => 'text',
                        'title'      => 'Order<span class="ctl_required">*</span>',    
                        'desc' => '<p class="ctl_required">Please enter story Order.</p>',
                        'dependency' => array( 'story_based_on', '==', 'custom' )
                    ),

                    // A Notice
                    array(
                        'type'    => 'submessage',
                        'style'   => 'info',
                        'content' => 'In order to display custom order based stories select a "Custom Order" in "Timeline Based on?" option inside the timeline shortcode builder.',
                        'dependency' => array( 'story_based_on', '==', 'custom' )
                    ),

                ),
            ),

            array(
                'id'     => 'story_media',
                'type'   => 'fieldset',
                'title'  => 'Story Media',
                'fields' => array(
                    array(
                        'id'         => 'story_format',
                        'type'       => 'button_set',
                        'title'      => 'Media Type',
                        'options'    => array(
                            'default' => 'Default(Image)',
                            'video' => 'Video',
                            'slideshow' => 'Slideshow'
                        ),
                        'default'    => 'default'
                    ),     
                    array(
                        'id'         => 'img_cont_size',
                        'type'       => 'button_set',
                        'title'      => 'Story image size',
                        'options'    => array(
                          'full'  => 'Full',
                          'small' => 'Small',
                        ),
                        'default'    => 'full',
                        'dependency' => array( 'story_format', '==', 'default' )
                    ),    
                    array(
                        'id'          => 'ctl_slide',
                        'type'        => 'gallery',
                        'title'       => 'Slideshow Images',
                        'add_title'   => 'Add Slideshow Images',
                        'edit_title'  => 'Edit Images',
                        'clear_title' => 'Remove Images',
                        'dependency' => array( 'story_format', '==', 'slideshow' )
                    ),        
                    array(
                        'id'       => 'ctl_video',
                        'type'     => 'text',
                        'title'    =>  'Add Youtube/Vimeo video url',
                        'desc'     =>   'e.g <strong>https://www.youtube.com/watch?v=PLHo6uyICVk</strong>  <br/>Or <strong>https://vimeo.com/308828986</strong>',
                        'dependency' => array( 'story_format', '==', 'video' )
                    ),
                ),
            ),

            array(
                'id'     => 'story_icon',
                'type'   => 'fieldset',
                'title'  => 'Story Icon',
                'fields' => array(
                    array(
                        'id'         => 'story_icon_type',
                        'type'       => 'button_set',
                        'title'      => 'Icon Type',
                        'options'    => array(
                            'fontawesome' => 'Font Awesome',
                            'custom_image' => 'Custom Image Icon'
                        ),
                        'default'    => 'fontawesome'
                    ), 
                    array(
                        'id'    => 'fa_field_icon',
                        'type'  => 'icon',
                        'title' => 'Font Awesome Icon',
                        'dependency' => array( 'story_icon_type', '==', 'fontawesome' )
                    ),
                    array(
                        'id'    => 'story_img_icon',          
                        'title' => 'Custom Image Icon', 
                        'type'  => 'media',          
                        'library' => 'image',
                        'url' => false,
                        'preview' => true,
                        'dependency' => array( 'story_icon_type', '==', 'custom_image' )
                    ),
                ),
            ),

            array(
                'id'     => 'extra_settings',
                'type'   => 'fieldset',
                'title'  => 'Extra Settings',
                'fields' => array(
                    array(
                        'id'=>'story_custom_link',
                        'type'=>'link',
                        'title'=> 'Story custom link',                
                    ),     
                    array(
                        'id'=>'ctl_story_color',
                        'type'=>'color',
                        'title'=> 'Story Color',                
                    ),

                ),
            ),
      
              
        )

    )); 
   
}
  