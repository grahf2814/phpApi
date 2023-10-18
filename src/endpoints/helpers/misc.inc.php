<?php
function response($data):void
{
    header('header:"Content-Type: application/json"');
    echo json_encode($data);

}