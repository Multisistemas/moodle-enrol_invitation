<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * Used to render the myucla links section in the control panel.
 */
class ucla_cp_myucla_row_renderer extends ucla_cp_renderer {
 
    /**
     *  Renders an array of myucla_row modules.
     *
     *  @param array $contents - The contents to diplay using the renderer.
     **/
    static function control_panel_contents($contents, $format=false, 
            $orient='', $handler='') {
        $table = new html_table();
        $table->id = 'my_ucla_functions';
        
        //For each row module
        $nonRowContent = "";
        foreach ($contents as $content_rows) {
            if(isset($content_rows->elements))
            {
                $content_rows_elements = $content_rows->elements;
                $table_row = new html_table_row();
                //For each element in the row module
                foreach ($content_rows_elements as $content_item) {
                   $table_row->cells[] = 
                           ucla_cp_renderer::general_descriptive_link($content_item, 
                                   array("target"=>"_blank"));
                }
                $table->data[] = $table_row;
            }
            else
            {
                $nonRowContent.= ucla_cp_renderer::control_panel_contents(Array($content_rows), true);
            }
        }
        // make sure content that is not part of the row is rendered before the
        // row content to avoid layout problem
        return $nonRowContent.html_writer::table($table);
    }
}
