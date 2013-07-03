silverstripe-email-css-inlinator
================================

HTML email formatter: converts HTML + CSS into HTML with inline styles

## Overview

This module converts HTML with externally included stylesheets into HTML with inline styles, suitable for reading with Outlook and other email clients with limited rendering capacities.

## Requirements

SilverStripe 2.4, untested with 3.x though it should work (let me know!)

## Installation

Extract the module into your site root, then run /dev/build?flush=1 to tell SilverStripe about your new module.

## Usage

Use in your email sending code like so:

<code>
// Send an email
$email = new Email(Email::getAdminEmail(), 'test@example.com');
$body = new DataObject();
Requirements::clear();
$body = $body->customise(array(
	'Name' => 'John Smith' // This allows us to use $Name in EmailTemplate.ss
))->renderWith('EmailTemplate.ss');
if ( class_exists('EmailCssInlinator') ) {
	$body = EmailCssInlinator::inlinate($body, Director::absoluteBaseURL());
}
$email->setSubject( SiteConfig::current_site_config()->Title . ' - emailed page' );
$email->setBody($body);
$email->send();
</code>
