# Check Source Code links

## Introduction

The check source URLs module is to review the source code for a module and try to find any broken lins.
When the module is first installed it obtains a list of top level domains from
*http://data.iana.org/TLD/tlds-alpha-by-domain.txt* 
To review a module a dev task has been created which can be run to check urls in the source code of
php, JavaScript, SilverStripe templates and MarkDown files.
It need the php module curl to be installed and it records all the suspected broken urls it finds in a table.

The list it returns can not be guaranteed but it gives a good indication of broken urls.

## Maintainer Contact

        * Kirk Mayo kirk (at) silverstripe (dot) com

## Requirements

        * SilverStripe 3.0 +

## Features

* Add a model admin for broken links
* Add a task to track source code broken links

## Installation

 1. Download the module form GitHub (Composer support to be added)
 2. Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3. Make sure the folder after being extracted is named 'check-source-urls'
 4. Place this directory in your sites root directory. This is the one with framework and cms in it.
 5. Run in your browser - `/dev/build` to rebuild the database.
 6. You should see a new menu called *Broken URLs*
 7. Run the following task *http://path.to.silverstripe/BrokenScriptsURLS?module=framework* to check 
 the framework module for broken source code links

## Dev task ##

Run the following task *http://path.to.silverstripe/BrokenScriptsURLS?module=framework* to check your site for 
broken source code links.
To ignore certain directories add the param excludeDir=/directory/to/exclude as per the example below

*http://localhost/brokenURLs/dev/tasks/BrokenScriptsURLS?module=framework&excludeDir=framework/docs/en/changelogs*

## Disable the Broken external link menu

To disable the *Broken Ext. Links* menu add the following code to mysite/_config.php

`CMSMenu::remove_menu_item('BrokenURLModelAdmin');`

## TODO ##

Add support for Punycode top level domains.

Add multi language support
