<?php
/**
 * home_page.php
 */

//
// DeepDive Home page containing messages, FAQs, and other useful hints.
//

set_include_path("/Users/gregparkin/www/DeepDive/classes");

require_once __DIR__ . '/autoload.php';

if (session_id() == '')
    session_start();

ini_set('xdebug.collect_vars',    '5');
ini_set('xdebug.collect_vars',    'on');
ini_set('xdebug.collect_params',  '4');
ini_set('xdebug.dump_globals',    'on');
ini_set('xdebug.dump.SERVER',     'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');

$lib = new library();
date_default_timezone_set('America/Denver');
$lib->globalCounter();

?>
<!DOCTYPE html>
<html>
<head>
    <title>DeepDive Home Page</title>
</head>
<body>
<div class="loader"></div>
<center>
<table border="0" cellspacing="0" cellpadding="0" style="width: 100%; height: 100%; background-color: lightgoldenrodyellow">
    <tr>
        <td align="center" valign="top">
            <img src="images/cct70_lightgoldenrodyellow.png">
        </td>
    </tr>
    <tr>
        <td align="left" valign="top">
            <p style="color: blue; font-size: 16px;">
                Welcome to the Full Search and Summarizer known as DeepDive. This is a web-based tool designed to
                manage, search, and summarize documents securely within an organization. It allows users to upload
                documents or scanning share-point libraries, which are then organized into private and public libraries
                and classified according to security clearance levels. The tool enforces strict access controls,
                ensuring that users can only view or classify documents according to their clearance, and it stores all
                documents securely in a relational database as encrypted large objects (LOBs).
            </p>
            <p style="color: blue; font-size: 16px;">
                DeepDive features advanced full-text search capabilites using PostgreSQL databases, allowing users
                to search for documents using keywords and phrases. The search results are displayed in a grid
                format, and users can select documents for viewing and summarizing. The tool uses AI-drive natural
                language processing (NLP) to summarize documents, providing users with high-level overviews without
                relying on external large language models (LLMs). This makes information retrievel more secure.
            </p>
            <p style="color: blue; font-size: 16px;">
                Overall, DeepDive enhances the company's document management by ensuring secure access, facilitating
                efficient searches, and enabling quick summarization of documents. It supports collaboration through
                private and team libraries, while maintaining document history to preserve original documents. By
                improving the security and efficiency of document management, DeepDive is an invaluable tool for any
                organization that handles sensitive information.
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <br>
        </td>
    </tr>
</table>
</center>
</body>
</html>
