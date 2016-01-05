<?php

function allParamsDefined($required_params, $given_params) {
    foreach ($required_params as $req) {
        if (array_key_exists($req, $given_params) == false) {
            return false;
        }
    }
    return true;
}
