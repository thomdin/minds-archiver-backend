<?php

header("Access-Control-Allow-Origin: *");

function main()
{
    if (!isset($_GET['post_id'])) {
        sendError(400, 'Parameter post_id is missing.');
    }

    $postId = trim($_GET['post_id']);
    if ($postId === '') {
        sendError(400, 'Parameter post_id is missing.');
    }

    if (!isset($_GET['action'])) {
        sendError(400, 'Parameter action is missing.');
    }

    $action = trim($_GET['action']);
    if ($action === '') {
        sendError(400, 'Parameter action is missing.');
    }

    switch (strtolower($action)) {
        case 'validate':
            validateAction($postId);
            return;
        case 'download':
            downloadAction($postId);
            return;
        default:
            sendError(400, 'Parameter action is invalid');
    }
}

function validateAction($postId)
{
    try {
        get_headers(urlToVideo($postId));
        $responseCode = extractResponseCode($http_response_header[0]);
    } catch (Exception $e) {
        sendError(500);
    }
    if (!$responseCode !== 200) {
        sendError($responseCode);
    }

    header('X-PHP-Response-Code: 200', true, 200);
    exit();
}

function downloadAction($postId)
{
    try {
        $fp = @fopen(urlToVideo($_GET['post_id']), false, 'r');
        $responseCode = extractResponseCode($http_response_header[0]);
    } catch (Exception $e) {
        sendError(500);
    }

    if ($responseCode !== 200) {
        sendError($responseCode);
    }

    header('Content-disposition:attachment; filename=minds.mp4');
    header('Content-Type: application/octet-stream');
    fpassthru($fp);
    fclose($fp);
    exit;
}

function urlToVideo($postId)
{
    return 'https://cdn-cinemr.minds.com/cinemr_com/' . $postId . '/720.mp4';
}

function sendError($statusCode, $message = null)
{
    header('X-PHP-Response-Code:' . $statusCode, true, $statusCode);
    if ($message) {
        echo $message;
    }
    exit();
}

function extractResponseCode($responseHeader)
{
    $parts = explode(' ', $responseHeader);
    if (sizeof($parts) < 2) {
        throw new ErrorException('Failed to parse response http response code');
    }

    if (!is_numeric($parts[1])) {
        throw new ErrorException('Failed to parse response http response code');
    }

    return (int) $parts[1];
}

main();
