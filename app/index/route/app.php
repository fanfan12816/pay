<?php
use think\facade\Route;

Route::any('/', 'Index/index');
Route::any('/index', 'Index/index');