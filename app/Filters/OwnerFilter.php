<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * OwnerFilter — allows access to Rental Owners and SND Admins.
 * Customers are blocked.
 */
class OwnerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/portal')->with('error', 'Please login first.');
        }

        if (! in_array(session()->get('role'), ['snd_admin', 'owner'])) {
            return redirect()->to('/portal')->with('error', 'Access denied. Rental Owner access required.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing needed after
    }
}
