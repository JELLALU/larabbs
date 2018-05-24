<?php 
/**
 * 助手函数
 */

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}
