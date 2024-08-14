<?php

if (!function_exists('handleQueryParameter')) {
    function handleQueryParameter($request) {
        return [
            'page' => $request->query('page') ? $request->query('page') : 1,
            'limit' => $request->query('limit') ? $request->query('limit') : 5,
            'search' => $request->query('search') ? trim($request->query('search')) : '',
        ];
    }
} 