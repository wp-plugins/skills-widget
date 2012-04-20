<?php /*

**************************************************************************

Plugin Name:  Skills Widget
Plugin URI:   https://github.com/jlopezcur/skills-widget
Description:  HTML5 Dinamyc Skills Diagram (based on tutorial from http://tympanus.net/codrops/2011/04/22/animated-skills-diagram/)
Version:      1.0
Author:       Javier López Úbeda
Author URI:   http://www.noknokstdio.com

**************************************************************************

Copyright (C) 2012 Javier López Úbeda

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

/**
 * Adds Skills_Widget widget.
 */
class Skills_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        $description = __('HTML5 Dinamyc Skills Diagram', 'skills-widget');
        parent::__construct(
            'skills_widget', // Base ID
            'Skills', // Name
            array('description' => $description), // Args
            array('width' => 400)
        );
        $plugin_dir = basename(dirname(__FILE__));
        load_plugin_textdomain('skills-widget', false, $plugin_dir.'/languages/');
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $width = apply_filters('widget_width', $instance['width']);
        $skills = apply_filters('widget_skill', $instance['skill']);
        
        echo $before_widget;
        if (!empty($title)) echo $before_title . $title . $after_title;
        
        ?><div id="diagram">
        <div class="get" style="display:none;">
            <?php foreach ($skills as $skill) : ?>
            <div class="arc">
                <span class="text"><?php echo $skill['name'] ?></span>
                <input type="hidden" class="percent" value="<?php echo $skill['percentage'] ?>" />
                <input type="hidden" class="color" value="<?php echo $skill['color'] ?>" />
            </div>
            <?php endforeach; ?>
        </div>
        </div>
<script type="text/javascript">
var o = {
    init: function(){
        this.diagram();
    },
    random: function(l, u){
        return Math.floor((Math.random()*(u-l+1))+l);
    },
    diagram: function(){
        
        var width = <?php echo $width ?>, height = <?php echo $width ?>;
        var marginArcs = 5;
        var mainCircleR = width/6;
        var arcStrokeArea = (width/2) - (width/6) - marginArcs;
        var numberOfArcs = jQuery('.get').find('.arc').length;
        var arcStrokeWidth = (arcStrokeArea / numberOfArcs) - marginArcs;
        
        var r = Raphael('diagram', width, height),
            rad = mainCircleR, //73
            defaultText = '<?php echo $title ?>',
            speed = 250;
        
        r.circle(width/2, height/2, mainCircleR).attr({ stroke: 'none', fill: '#193340' });
        
        var title = r.text(width/2, height/2, defaultText).attr({
            font: '20px Arial',
            fill: '#fff'
        }).toFront();
        
        r.customAttributes.arc = function(value, color, rad){
            var v = 3.6*value,
            alpha = v == 360 ? 359.99 : v,
            random = o.random(91, 240),
            a = (random-alpha) * Math.PI/180,
            b = random * Math.PI/180,
            sx = width/2 + rad * Math.cos(b),
            sy = height/2 - rad * Math.sin(b),
            x = width/2 + rad * Math.cos(a),
            y = height/2 - rad * Math.sin(a),
            path = [['M', sx, sy], ['A', rad, rad, 0, +(alpha > 180), 1, x, y]];
            return { path: path, stroke: color }
        }
        
        jQuery('.get').find('.arc').each(function(i){
            var t = jQuery(this), 
                color = t.find('.color').val(),
                value = t.find('.percent').val(),
                text = t.find('.text').text();
            
            rad += (arcStrokeWidth/2) + marginArcs;
            var z = r.path().attr({ arc: [value, color, rad], 'stroke-width': arcStrokeWidth });
            rad += (arcStrokeWidth/2);
            
            z.mouseover(function(){
                this.animate({ 'stroke-width': arcStrokeWidth + marginArcs, opacity: .75 }, 1000, 'elastic');
                if(Raphael.type != 'VML') //solves IE problem
                this.toFront();
                title.stop().animate({ opacity: 0 }, speed, '>', function(){
                    this.attr({ text: text + '\n' + value + '%' }).animate({ opacity: 1 }, speed, '<');
                });
            }).mouseout(function(){
                this.stop().animate({ 'stroke-width': arcStrokeWidth, opacity: 1 }, speed*4, 'elastic');
                title.stop().animate({ opacity: 0 }, speed, '>', function(){
                    title.attr({ text: defaultText }).animate({ opacity: 1 }, speed, '<');
                }); 
            });
        });
        
    }
}
jQuery(function(){ o.init(); });
</script>    
        <?php
        
        echo $after_widget;
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['width'] = $new_instance['width'];
        $instance['skill'] = $new_instance['skill'];
        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
        
        
        if (isset($instance['title'])) $title = $instance['title'];
        else $title = __('Skills', 'skills-widget');
        
        if (isset($instance['width'])) $width = $instance['width'];
        else $width = 300;
        
        
        if (isset($instance['skill'])) $skills = $instance['skill'];
        else {
            $skills = array(
                array('name' => 'PHP', 'percentage' => '85', 'color' => '#ff0000')
            );
        }
        
        ?>
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'skills-widget'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
        <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:', 'skills-widget'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width ?>" />
        </p>
        <table style="border: none; border-bottom: 1px dashed #ccc;" id="<?php echo $this->id_base.$this->number.'-table'; ?>">
            <tr>
                <th style="border: none; border-bottom: 1px dashed #ccc;"><label><?php _e('Skill Name', 'skills-widget'); ?></label></th>
                <th style="border: none; border-bottom: 1px dashed #ccc;"><label><?php _e('Percentage (%)', 'skills-widget'); ?></label></th>
                <th style="border: none; border-bottom: 1px dashed #ccc;"><label><?php _e('Color', 'skills-widget'); ?></label></th>
                <th style="border: none; border-bottom: 1px dashed #ccc;" align="right">
                    <a href="javascript: void(0);" onclick="addSkill<?php echo $this->number; ?>()" class="button-primary"><?php echo __('Add', 'skills-widget'); ?></a>
                </th>
            </tr>
            <?php $i = 0; foreach ($skills as $skill) : ?>
            <tr>
                <td> 
                    <input class="widefat" name="<?php echo 'widget-'.$this->id_base.'['.$this->number.'][skill]['.$i.'][name]'; ?>" type="text" value="<?php echo $skill['name'] ?>" />
                </td>
                <td>
                    <input class="widefat" name="<?php echo 'widget-'.$this->id_base.'['.$this->number.'][skill]['.$i.'][percentage]'; ?>" type="text" value="<?php echo $skill['percentage'] ?>" />
                </td>
                <td>
                    <input class="widefat color" name="<?php echo 'widget-'.$this->id_base.'['.$this->number.'][skill]['.$i.'][color]'; ?>" type="text" value="<?php echo $skill['color'] ?>" />
                </td>
                <td valign="middle" align="right">
                    <a href="javascript: void(0);" class="button-primary"
                        onclick="jQuery(this).parent().parent().remove();">
                        <?php echo __('Remove', 'skills-widget'); ?>
                    </a>
                </td>
            </tr>
            <?php $i++; endforeach; ?>
        </table>
        <script type="text/javascript">
        jQuery.noConflict();
        jQuery(document).ready(function($) {
            $('#<?php echo $this->id_base.$this->number.'-table'; ?> input.color').ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val('#'+hex);
                    $(el).ColorPickerHide();
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                }
            })
            .bind('keyup', function(){
                $(this).ColorPickerSetColor(this.value);
            });
            skill_iter_<?php echo $this->number ?> = <?php echo $i ?>;
        });
        function addSkill<?php echo $this->number; ?>() {
            var nameBase = '<?php echo 'widget-'.$this->id_base.'['.$this->number.'][skill]'; ?>';
            var out = '';
            out += '<tr><td>';
            out += '<input class="widefat" name="'+nameBase+'['+skill_iter_<?php echo $this->number ?>+'][name]" type="text" value="" />';
            out += '</td><td>';
            out += '<input class="widefat" name="'+nameBase+'['+skill_iter_<?php echo $this->number ?>+'][percentage]" type="text" value="0" />';
            out += '</td><td>';
            out += '<input class="widefat color" id="<?php echo $this->id_base.$this->number; ?>'+skill_iter_<?php echo $this->number ?>+'" name="'+nameBase+'['+skill_iter_<?php echo $this->number ?>+'][color]" type="text" value="#'+Math.floor(Math.random()*16777215).toString(16)+'" />';
            out += '</td><td valign="middle" align="right">';
            out += '<a href="javascript: void(0);" class="button-primary"';
            out += 'onclick="jQuery(this).parent().parent().remove();">';
            out += '<?php echo __('Remove', 'skills-widget'); ?>';
            out += '</a>';
            out += '</td></tr>';
            jQuery('#<?php echo $this->id_base.$this->number.'-table'; ?>').append(out);
            
            jQuery('#<?php echo $this->id_base.$this->number; ?>'+skill_iter_<?php echo $this->number ?>).ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    jQuery(el).val('#'+hex);
                    jQuery(el).ColorPickerHide();
                },
                onBeforeShow: function () {
                    jQuery(this).ColorPickerSetColor(this.value);
                }
            })
            .bind('keyup', function(){
                jQuery(this).ColorPickerSetColor(this.value);
            });
            
            skill_iter_<?php echo $this->number ?>++;
        }
        </script>
        <?php 
    }

} // class Foo_Widget

// register Foo_Widget widget
add_action('widgets_init', create_function('', 'register_widget("skills_widget");'));

function skills_widget_init() {
    wp_enqueue_script('raphael', plugin_dir_url( __FILE__ ) .'raphael.js',array('jquery'));
    wp_enqueue_style('color-picker', plugin_dir_url( __FILE__ ) . 'colorpicker/css/colorpicker.css');
    wp_enqueue_script('color-picker-skill', plugin_dir_url( __FILE__ ) .'colorpicker/js/colorpicker.js',array('jquery'));
}
add_action('init', 'skills_widget_init');
