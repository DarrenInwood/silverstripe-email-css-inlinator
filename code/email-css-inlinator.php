<?php
		/* CSS Inlinator - takes a standard html email and converts 
			 external CSS into inline CSS that's suitable for sending 
			 via Outlook, or some other mail mechanisms that are NOT 
			 Campaign Monitor, Mail Chimp etc. 
			 
			 Basically this script does the same thing that those sites do.
			 
			 -------------------------------------------------
			 
			 Requirements: PHP5 with DOM support.
			 Credit goes to: http://www.pelagodesign.com/sidecar/emogrifier/
			 
		*/
		error_reporting(E_ALL); // show errors relating to incompatible CSS selectors etc 
		//error_reporting(0); // suppress all errors
		
		// Load the Emogrifier class
    include_once("libraries/emogrifier.php");
		// Load SimpleDom		
		include_once('libraries/simple_html_dom.php');
		// Load HTML2text
		include_once('libraries/html2text.php');

		$scriptresults = '';
		$scriptresultsheader = '';
		$count = 0;
		
		// Process form
		if (isset($_POST['submit'])) {
				
				// Read the directory
				if (strlen($_POST['dirpath']) > 0) {
					$dirpath = trim($_POST['dirpath']);
					$dircharcount=strlen($dirpath);
					if (strpos($dirpath, '/', ($dircharcount-1)) === false) {
							$dirpath .= '/'; // add a trailing slash
					}
					
					// Open a known directory, and proceed to read its contents
					if (is_dir($dirpath)) {
							if ($dh = opendir($dirpath)) {
									$scriptresults .= '<div><table cellpadding="1" cellspacing="2" border="1">
									<tr>
										<th>No.</th>
										<th>Generated HTML with inline CSS</th>
										<th>CSS files read</th>
										<th>Plaintext</th>
									</tr>
									';
									
									while (($file = readdir($dh)) !== false) {
											if ((is_file($dirpath . $file)) && ((strstr($file,'.html')) || (strstr($file,'.htm')) || (strstr($file,'.HTML')) || (strstr($file,'.HTM')))) { // filter out non html files
											// should use regex for this, as it will also process files with '.htm' at the beginning or middle of the filename, but shouldn't.
												if (!strstr($file,'-cssinline.')) { // don't reprocess previously processed files.
													$count++;
													processCSS($dirpath,$file); // process the file
												}
											}
									}
									/*
									foreach (glob($dirpath."*.htm") as $filename) {
											$scriptresults .= "$filename size " . filesize($filename) . "<br />";
									}
									*/

									$scriptresults .= '</table></div>';
									$scriptresultsheader = '<p><strong>Processed '.$count.' files</strong></p>';
									closedir($dh);
							} else {
									$scriptresults = '<p class="error"><strong>Error: </strong>Could not open "'.$dirpath.'".</p>';
							}
					} else {
							$scriptresults = '<p class="error"><strong>Error: </strong>"'.$dirpath.'" doesn\'t seem to be a directory.</p>';
					}
				} else {
					$scriptresults = '<p class="error"><strong>Error: </strong>There was a problem reading the directory, did you enter a path?</p>';
				}
				
				
		} 

		function processCSS($dirpath,$file) {
				global $scriptresults;
				global $count;
				$cssresults = '<ul>';
				// Read the html file
				$htmldom = file_get_html($dirpath.$file);
				$css = '';
				$countcss = 0;
				// find all link tags with attribite rel=stylesheet and media=screen
				foreach($htmldom->find('link[rel=stylesheet]') as $stylesheet) {
					$countcss++;
					//if (strtolower($stylesheet->media) == 'screen') {
							$css .= file_get_html($dirpath.$stylesheet->href); // open the css file
							$cssresults .= '<li>'.$countcss.'. '.$stylesheet->href . '</li>';
					//}
				}
				$cssresults .= '</ul>';

				// Remove redundant CSS links
				foreach($htmldom->find('link[rel=stylesheet]') as $csslink) {
    			//if (strtolower($csslink->media) == 'screen') { 
						$csslink->outertext = '';
					//}
				}
				
				// Convert the css to inline		
				$convertcss = new Emogrifier($htmldom,$css);
				$convertedhtml = $convertcss->emogrify();
				
				// Prepare filenames
				if ((strstr($file,'.html')) || (strstr($file,'.HTML'))) { // should use a regular expression for this!
					$convertedfile = str_replace('.html','-cssinline.html',$file);
					$plaintextfilename = str_replace('.html','-plaintext.txt',$file);
				} else {
					$convertedfile = str_replace('.htm','-cssinline.htm',$file);
					$plaintextfilename = str_replace('.htm','-plaintext.txt',$file);
				}
				
				// Save plaintext version 
				$h2t =& new html2text($dirpath.$file, true); 
				$plaintext = $h2t->get_text();
				
				// clean up
				$plaintext = str_replace("  ","", $plaintext); // strip whitespaces
				$plaintext = preg_replace("/\n+/", "\n\n", $plaintext); // strip excess newlines
				$plaintext = preg_replace("/^\n{2}/", "", $plaintext); // strip excess newlines
				$plaintextfh = @fopen($dirpath.$plaintextfilename, 'w');
				$plaintextkbytes = fwrite($plaintextfh, $plaintext);
				fclose($plaintextfh);
				
				// Save converted HTML email
				$fh = @fopen($dirpath.$convertedfile, 'w');
				$kbytes = fwrite($fh, $convertedhtml);
				$kbytes = number_format(($kbytes/1024),1);
				$scriptresults .= '<tr><td>'.$count.'.</td><td><a href="'.$dirpath.$convertedfile.'" title="opens in a new window" target="_blank">'.$convertedfile.'</a> ('.$kbytes.' Kb)</td><td>'.$cssresults.'</td><td><a href="'.$dirpath.$plaintextfilename.'" title="opens in a new window" target="_blank">Plaintext version</a></td></tr>';
				fclose($fh);
			
		}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Chrometoaster email CSS-inlinator (using Emogrifier)</title>
<link rel="stylesheet" type="text/css" href="http://stage2.chrometoaster.com/_documentation/styles/screen.css" media="screen" />
<link rel="stylesheet" type="text/css" href="http://stage2.chrometoaster.com/_documentation/styles/print.css" media="print" />
<meta name="robots" content="noindex, nofollow" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript">
//<![CDATA[

//]]>
</script>

<style type="text/css">
.error { color:red; }
#results { background:#f2f2f2; padding:8px; }
</style>
</head>
<body>
<div id="shell" class="section-0">
  <div><a id="top"></a></div>
	  <h1><a href="http://www.chrometoaster.com/"><img src="http://stage2.chrometoaster.com/_documentation/images/chromewatermark.gif" alt="Chrometoaster. " /></a></h1>		
	<div id="header">
	  <h2>Chrometoaster email CSS-inlinator  <span>using Emogrifier</span></h2>
		<p class="disclaimer">This documentation is best viewed at 1152*864 or higher, in Firefox 2 or Safari 3, with Javascript enabled.		</p>
	</div>
  <hr />
  
  <div id="main">
    <h3>Chrometoaster email CSS-inlinator</h3>
    <p class="last-updated"><em>Last Updated:
      <!-- #BeginDate format:fcBr1a -->Monday, 11.04.11 11:52 AM<!-- #EndDate -->
      </em></p>
						
		<hr />

    <p>Batch convert HTML &amp; CSS into email friendly templates. This PHP script will take a path that you supply below and batch process all of the html files therein and apply the CSS that it finds linked from within each document as inline CSS. Subfolders are not processed. Converted files are saved with the text '-cssinline' appended to the filename, in the same directory as the files being processed. A plaintext version is also generated, with the '-plaintext.txt' appended to the filename. Credit goes to: http://www.pelagodesign.com/sidecar/emogrifier/</p>
    <h4>Download</h4>
    <p><a href="email-css-inlinator.zip">email-css-inlinator.zip</a></p>
    <h4>Requirements</h4>
    <ul>
      <li>PHP5 with DOM support. </li>
      <li>Check also that you've got write permissions to the directory where you install this.</li>
      <li>Ensure that your external CSS stylesheets are located in either the same directory or a subdirectory relative to the local directory given below (call it &quot;resources&quot; to be consistent!)</li>
    </ul>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
      <fieldset>
      	<legend>Process form</legend>
      <label for="dirpath"><strong>Insert local relative or absolute path to this directory (<?php echo $_SERVER['PHP_SELF']; ?>)</strong><br />
      eg "../../resources/email-templates/"<br />
      eg "C:/resources/email-templates/"<br />
      eg. "sample-email" <em>(try this for a demo)</em></label><br /><br />
        <input name="dirpath" type="text" id="dirpath" size="100"  value="<?php if (isset($dirpath)) { if ($dirpath != '') { echo $dirpath; } } else if(isset($_POST['dirpath'])) { echo $_POST['dirpath']; } ?>"/>
     	<input type="submit" name="submit" id="submit" value="Go" />
      </fieldset>
    </form>
    <hr />
    <h4>Results</h4>
    <div id="results"><?php echo $scriptresultsheader . $scriptresults; ?></div>
		
    
    <hr />
    <h4>Usage</h4>
    <p>Note: this PHP class is limited to CSS level 1 selectors. Pseudo selectors are therefore not supported (it's not part of CSS level 1), and that means you can't use a:link, a:visited, a:active, a:hover { ... }. Just target the 'a' element instead. </p>
    <p>&nbsp;</p>
  </div>
	<div id="footer">
    <p class="top"><a href="#top">Back to top</a></p>
		<hr />
	</div>
</div>
</body>
</html>