<?php

class EmailCssInlinator {

    public static function inlinate($html, $dirpath=null) {
        if ( $dirpath === null ) {
            $dirpath = Director::baseFolder();
        }
        require_once("libraries/emogrifier.php");	
		require_once('libraries/simple_html_dom.php');
		require_once('libraries/html2text.php');
	    $htmldom = str_get_html($html);
	    $css = '';
	    $countcss = 0;
	    // find all link tags with attribite rel=stylesheet and media=screen
	    foreach($htmldom->find('link[rel=stylesheet]') as $stylesheet) {
		    $countcss++;
		    $css .= file_get_html($dirpath.DIRECTORY_SEPARATOR.$stylesheet->href); // open the css file
	    }

	    // Remove redundant CSS links
	    foreach($htmldom->find('link[rel=stylesheet]') as $csslink) {
		    $csslink->outertext = '';
	    }
	
	    // Convert the css to inline		
	    $convertcss = new Emogrifier($htmldom,$css);
	    $convertedhtml = $convertcss->emogrify();    

        return $convertedhtml;
    }


}

