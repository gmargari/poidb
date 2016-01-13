<?php

//==============================================================================
// allParamsDefined ()
//==============================================================================
function allParamsDefined($required_params, $given_params) {
    return !array_diff($required_params, array_keys($given_params));
}

//==============================================================================
// responseWithCodeMessage ()
//==============================================================================
function responseWithCodeMessage($response, $code, $message) {
    $response->getBody()->write($message);
    $response = $response->withStatus($code);
    return $response;
}
