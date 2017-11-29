<?php

// header text
$header_text = get_field('header_text');

// carousel data
$carouselImages = [];
foreach (get_field('carousel') as $slide) {
    array_push($carouselImages, $slide['image']['url']);
}
$carouselImagesAttr = implode(',', $carouselImages);

// menu data
$mainMenuJSON = json_encode(wp_get_nav_menu_items('Main menu'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <title>Prison & Probation Jobs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDDplfBkLzNA3voskfGyExYnQ46MJ0VtpA"></script>
    <script>
        var mainMenuJSON = <?= $mainMenuJSON ?>;
    </script>


    <?php wp_head(); ?>
</head>

<body  <?php body_class(); ?>>
<div id="site-container">
    <?php


    ?>
<page-container>
    <page-header :menu-data='<?= $mainMenuJSON ?>'
                 carousel-images="<?= $carouselImagesAttr ?>">
        <?php echo $header_text; ?>
    </page-header>

