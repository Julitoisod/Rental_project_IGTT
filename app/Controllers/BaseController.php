<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url', 'text'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Get the role-based dashboard URL
     */
    protected function getDashboardUrl(): string
    {
        $role = session()->get('role');
        return match ($role) {
            'snd_admin' => '/admin/dashboard',
            'owner'     => '/owner/dashboard',
            'customer'  => '/customer/dashboard',
            default     => '/portal',
        };
    }

    /**
     * Redirect to the role-based dashboard
     */
    protected function redirectToDashboard()
    {
        return redirect()->to($this->getDashboardUrl());
    }

    /**
     * Validate an uploaded image and return a SAFE, non-executable filename.
     *
     * Enforces real image MIME (not just client-claimed), a whitelisted
     * extension, and a size cap. Returns null when no file was provided,
     * or the string 'INVALID' when a file was provided but rejected — the
     * caller decides how to surface the error.
     *
     * @return string|null  Safe random filename, null (no upload), or 'INVALID'.
     */
    protected function validateImageUpload(?\CodeIgniter\HTTP\Files\UploadedFile $file, int $maxMb = 5): ?string
    {
        if (! $file || ! $file->isValid() || $file->hasMoved() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if ($file->getSizeByUnit('mb') > $maxMb) {
            return 'INVALID';
        }
        // getMimeType() reads the actual file signature, not the client header.
        if (! in_array($file->getMimeType(), $allowedMime, true)) {
            return 'INVALID';
        }
        $ext = strtolower($file->getExtension() ?: $file->getClientExtension());
        if (! in_array($ext, $allowedExt, true)) {
            return 'INVALID';
        }

        // Force a safe extension so the stored name can never be *.php etc.
        return bin2hex(random_bytes(16)) . '.' . $ext;
    }
}
