<?php

if( !defined('ABSPATH') ){
    exit;
}

class TMDIVI_DefaultDataHelper{

    public function default_items_helpers($exists = array()){
        $timeline_child_default_data = $this->generate_module_shortcodes('tmdivi_timeline_story', [
            [
            'story_title' => 'Amazon is born',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Erat enim res aperta. Ne discipulum abducam, times. Primum quid tu dicis breve? An haec ab eo non dicuntur?',
            // 'media' => TMDIVI_URL . 'assets/image/amazon1.jpg',
            'label_date' => 'July 5',
            'sub_label' => 'Introduced'
            ],
            [
            'story_title' => 'Amazon Prime debuts',
            'content' => 'Aliter homines, aliter philosophos loqui putas oportere? Sin aliud quid voles, postea. Mihi enim satis est, ipsis non satis. Negat enim summo bono afferre incrementum diem. Quod ea non occurrentia fingunt, vincunt Aristonem.',
            // 'media' => TMDIVI_URL . 'assets/image/amazon2.jpg',
            'label_date' => 'February 2',
            'sub_label' => 'Expanded'
            ],
            [
            'story_title' => 'Amazon acquires Audible',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            // 'media' => TMDIVI_URL . 'assets/image/amazon3.png',
            'label_date' => 'January 31',
            'sub_label' => 'Expanded'
            ],
        ]);

        $helpers = [
            'defaults' => [
                'tmdivi_timeline' => [
                    'content'   => et_fb_process_shortcode($timeline_child_default_data),
                ],
            ]
        ];
        
        return array_merge_recursive($exists, $helpers);
    }

    private function generate_module_shortcodes($child_slug, $optionsArray){
        return implode('', array_map(function ($options) use ($child_slug) {
            return $this->child_module_shortcode($child_slug, $options);
        }, $optionsArray));
    }

    private function child_module_shortcode($child_slug, $options){
        $shortcode = sprintf('[%1$s', $child_slug);
        foreach ($options as $key => $value) {
            $shortcode .= sprintf(' %1$s="%2$s"', $key, $value);
        }
        $shortcode .= sprintf('][/%1$s]', $child_slug);
     
        return $shortcode;
    }

    public function asset_helpers($content){

        $helpers = $this->default_items_helpers();
        
        return $content . sprintf(
            ';window.DCLBuilderBackend=%1$s; jQuery.extend(true, window.ETBuilderBackend, %1$s);',
            et_fb_remove_site_url_protocol(wp_json_encode($helpers))
        );
    }
}
