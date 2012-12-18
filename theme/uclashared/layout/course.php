<?php

// Process and simplify all the options
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) 
    && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) 
    && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) 
    && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));
$hasintrobanner = (!empty($PAGE->layout_options['introbanner']));

// START UCLA MODIFICATION CCLE-2452
$showcontrolpanel = (!empty($PAGE->layout_options['controlpanel'])); 

$showsidepre = ($hassidepre 
    && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost 
    && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));
    
//$PAGE->requires->yui_module('yui2-animation');

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) 
    && !empty($custommenu));

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}

if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

$envflag = $OUTPUT->get_environment();

// Do all drawing

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <link rel="apple-touch-icon" href="<?php echo $OUTPUT->pix_url('apple-touch-icon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <link rel="stylesheet" type="text/css" media="print" href="<?php echo $CFG->wwwroot .'/theme/'. current_theme() ?>/style/print.css" />
</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="page">
<?php if ($hasheading || $hasnavbar) { ?>
    <div id="page-header" class="env-<?php echo $envflag ?>">
        <?php if ($hasheading) { ?>
        <div class="headermain">
            <div id="uclalogo">
                <?php echo $OUTPUT->logo('ucla_ccle_logo', 'theme') ?>
            </div>
        </div>
	<div class="headermenu"><?php
            if ($haslogininfo) {
                echo $OUTPUT->login_info();
            }

            if ($showcontrolpanel) { ?>
            <div id="control-panel">
            <?php echo $OUTPUT->control_panel_button() ?>
            </div>
            <div id="weeks-display" class="weeks-display-with-control-panel">
            <?php echo $OUTPUT->weeks_display() ?>
            </div>
            <?php

            } else {

            ?>
            <div id="weeks-display" class="weeks-display">
            <?php echo $OUTPUT->weeks_display() ?>
            </div>
            <?php

            }

            ?>

            <?php
            if (!empty($PAGE->layout_options['langmenu'])) {
                echo $OUTPUT->lang_menu();
            }

            echo $PAGE->headingmenu
        ?>
        </div>
<div id="sublogo">
            <?php
                echo $OUTPUT->sublogo();
            ?>
            </div>

        <?php } ?>
        <?php if ($hascustommenu) { ?>
        <div id="custommenu"><?php echo $custommenu; ?></div>
        <?php } ?>
        <?php if($hasintrobanner) { ?>
        <div class="introbanner" ></div>
        <?php } ?>
        <?php if ($hasnavbar) { ?>
            <div class="navbar clearfix">
                <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                <div class="navbutton"> <?php echo $PAGE->button; ?></div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<!-- END OF HEADER -->

    <?php $twocolumnlayout = $hassidepost ? '' : 'two-col-responsive'; ?>

    <div id="page-content">
        <?php
            // Determine if we need to display banner
            // @todo: right now it only works for 'red' alerts
            if(get_config('block_ucla_alert', 'alert_sitewide')) {

                // If config is set, then alert-block exists... 
                // There might be some pages that don't load the block however..
                if(!class_exists('ucla_alert_banner_site')) {
                    $file = $CFG->dirroot . '/blocks/ucla_alert/locallib.php';
                    require_once($file);
                }
                
                // Display banner
                $banner = new ucla_alert_banner(SITEID);
                echo $banner->render();
            }
        ?>
        <div id="region-main-box">
            <div id="region-post-box">
            
                <div id="region-main-wrap" class="<?php echo $twocolumnlayout ?>">
                    <div id="region-main">
                        <div class="region-content">
                            <?php
                                // Alert banner display for courses
                                // @todo: finish implementing
//                                if($banner = ucla_alert_banner::load($COURSE->id)) {
//                                    echo $banner->alert();
//                                }
                            ?>
                            <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($hassidepre) { ?>
                <br class="clear-responsive-left" />
                <div id="region-pre" class="block-region <?php echo $twocolumnlayout ?>">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if ($hassidepost) { ?>
                <br class="clear-responsive-right" />
                <div id="region-post" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

    <!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>
    <div id="page-footer" >
    <!--
        <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?></p>
    -->
        <span id="copyright-info">
        <?php echo $OUTPUT->copyright_info() ?>
        </span>

        <span id="footer-links">
        <?php echo $OUTPUT->footer_links() ?>
        </span>

        <?php echo $OUTPUT->standard_footer_html() ?>
    </div>
    <?php } ?>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
