<?php
    header("content-type:application/json");
    //--//
    set_time_limit(0);
    //--//
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '99999M');
    //--//
    //error_reporting(E_ERROR | E_PARSE);
    error_reporting(1);
    //--//
    $api_base_url = "https://www.urbanpoint.com/dev2/index.php/webservice/extract/";
    //--//
    $imagesBasePath = "/var/www/html/up_qatar/uploads/";
    //--//
    $baseCategoryUrl = "https://www.urbanpoint.com/dev2/media/catalog/category/";
    $baseProductsUrl = "https://www.urbanpoint.com/dev2/media/catalog/product/";
    $baseMerchantsUrl = "https://www.urbanpoint.com/dev2/media/magebuzz/avatar/";
