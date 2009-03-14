<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Plugin Toolkit 0.0.1                                              |
// +---------------------------------------------------------------------------+
// | plgen.php                                                                 |
// |                                                                           |
// | Creates a plugin template                                                 |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                              |
// |                                                                           |
// | Authors: Dirk Haun - dirk AT haun-online DOT de                           |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

// this script will run locally, so reporting all errors shouldn't be a
// a security issue ...
error_reporting(E_ALL);

// default data
$pluginData = array(
    'pi_name' => 'foo',
    'pi_display_name' => 'Foo',
    'pi_version'      => '1.0',
    'pi_homepage'     => 'http://www.example.com/',
    'author'          => 'John Doe',
    'email'           => 'john@example.com'
);

/**
* Patch file content with plugin data
*
* @param    string  $content    file content to patch
* @param    array   $plgdata    plugin data
* @return   string              patched file content
*
*/
function patch($content, $plgdata)
{
    $headers = createHeaderTemplates();
    foreach ($headers as $name => $comment) {
        $newComment = '';

        switch ($name) {
        case 'authors':
            $newComment = formattedComment('Authors: ' . $plgdata['author']
                            . ' - ' . obfuscateEmail($plgdata['email']));
            break;

        case 'copyright':
            $newComment = formattedComment('Copyright (C) ' . strftime('%Y')
                            . ' by the following authors:');
            break;

        case 'pi_name':
            $newComment = formattedComment($plgdata['pi_display_name']
                            . ' Plugin ' . $plgdata['pi_version']);
            break;

        default:
            break;
        }

        // echo "$name:\n$comment$newComment";

        if (! empty($newComment)) {
            $content = str_replace($comment, $newComment, $content);
        }
    }

    $content = str_replace(
        array('foo', 'Foo'),
        array($plgdata['pi_name'], $plgdata['pi_display_name']),
        $content
    );

    return $content;
}

/**
* Create list of strings in template headers that we need to replace
*
* @return   array   list of header comments
*
*/
function createHeaderTemplates()
{
    $headers = array();

    $headers['pi_name']   = formattedComment('Foo Plugin 0.0');
    $headers['copyright'] = formattedComment('Copyright (C) yyyy by the following authors:');
    $headers['authors']   = formattedComment('Authors: author name goes here');
/*
    foreach ($headers as $header) {
        echo $header;
    }
*/
    return $headers;
}

/**
* Format comment for a file's copyright header
*
* @param    string  $text   text to be formatted
* @return   string          formatted text, 80 characters long
*
*/
function formattedComment($text)
{
    if (strlen($text) > 73) {
        $text = substr($text, 0, 73);
    }

    return sprintf("// | %-73s |\n", $text);
}

/**
* Make the email address slightly less obvious
*
* @param    string  $email  email address
* @return   string          obfuscated email address
*
*/
function obfuscateEmail($email)
{
    return str_replace(array('@', '.'), array(' AT ', ' DOT '), $email);
}

/**
* Read a line from a file handle (usually stdin)
*
* NOTE: aborts entire script on error!
*
* @param    filehandle  $fp     file handle, e.g. of 'php://stdin'
* @return   string              string read, possibly empty
*
*/
function readln($fp)
{
    $value = fgets($fp);

    if ($value === false) {
        // "this shouldn't happen"
        die("\nError reading from stdin - aborting\n");
    }

    $value = trim($value);

    return $value;
}


// MAIN

$stdin = fopen('php://stdin', 'r');

echo "Internal name of your plugin?\n";
echo "(used in URLs) [{$pluginData['pi_name']}] ";

$name = readln($stdin);
if (! empty($name)) {
    $pluginData['pi_name'] = $name;
}

echo "\nNext question goes here ...\n";

fclose($stdin);

// create plugin directory
if (mkdir($pluginData['pi_name']) === false) {
    die("\nFailed to create directory '{$pluginData['pi_name']}' - aborting\n");
}

$content = file_get_contents('plugin-template/autoinstall.php');
if (($content === false) || empty($content)) {
    die("\nFailed to read template autoinstall.php - aborting\n");
}

$content = patch($content, $pluginData);

$outfileName = $pluginData['pi_name'] . '/' . 'autoinstall.php';
$written = file_put_contents($outfileName, $content);
if (($written === false) || ($written < strlen($content))) {
    die("\nError writing autoinstall.php - aborting\n");
}


echo "\nAll done! You'll find your plugin in the '{$pluginData['pi_name']}' subdirectory.\n\n";

?>
