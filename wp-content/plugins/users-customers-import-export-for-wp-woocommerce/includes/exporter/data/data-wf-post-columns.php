<?php

if (!defined('ABSPATH')) {
    exit;
}

$columns = array(
    'ID' => 'ID',
    'user_login' => 'user_login',
    'user_pass' => 'user_pass',
    'wt_hash' => 'wt_hash',
    'user_nicename' => 'user_nicename',
    'user_email' => 'user_email',
    'user_url' => 'user_url',
    'user_registered' => 'user_registered',
    'display_name' => 'display_name',
    'first_name' => 'first_name',
    'last_name' => 'last_name',
    'user_status' => 'user_status',
    'description' => 'description',
    'roles' => 'roles'
);

return apply_filters('hf_csv_customer_post_columns', $columns);
