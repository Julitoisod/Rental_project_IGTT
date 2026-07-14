<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('role_url_prefix')) {
    /**
     * URL prefix for the logged-in role's route group (e.g. '/owner').
     * Views use this to build links that stay inside the role's section.
     */
    function role_url_prefix(): string
    {
        return match (session()->get('role')) {
            'snd_admin' => '/admin',
            'owner'     => '/owner',
            'customer'  => '/customer',
            default     => '',
        };
    }
}
