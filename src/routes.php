<?php

// Mapping of routes to functions
$app->post('/poi', 'addPoi');
$app->get('/poi/getByLoc', 'getPoisByLoc');

// Function definitions
function addPoi($request, $response, $args) {
	$response->getBody()->write("addPoi");
    return $response;
};

function getPoisByLoc($request, $response, $args) {
	$response->getBody()->write("getPoisByLoc");
    return $response;
};
