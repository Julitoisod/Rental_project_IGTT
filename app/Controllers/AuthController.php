<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ItemModel;
use App\Models\CategoryModel;

class AuthController extends BaseController
{
    // ─── Portal Selection ───
    public function portal()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }

        $itemModel     = new ItemModel();
        $categoryModel = new CategoryModel();

        $categoryId   = $this->request->getGet('category');
        $searchQ      = trim((string)$this->request->getGet('q'));
        $activeSort   = $this->request->getGet('sort')         ?? 'newest';
        $activeAvail  = $this->request->getGet('availability') ?? 'available';

        $itemQuery = $itemModel
            ->select('items.*, categories.name AS category_name')
            ->join('categories', 'categories.id = items.category_id', 'left');

        if ($activeAvail !== 'all') {
            $itemQuery->where('items.quantity >', 0);
        }

        switch ($activeSort) {
            case 'name_asc':  $itemQuery->orderBy('items.name', 'ASC');          break;
            case 'name_desc': $itemQuery->orderBy('items.name', 'DESC');         break;
            default:          $itemQuery->orderBy('items.created_at', 'DESC');   break;
        }

        if ($categoryId) {
            $itemQuery->where('items.category_id', $categoryId);
        }

        if ($searchQ !== '') {
            $itemQuery->groupStart()
                ->like('items.name', $searchQ)
                ->orLike('items.description', $searchQ)
                ->orLike('items.rental_details', $searchQ)
                ->orLike('categories.name', $searchQ)
            ->groupEnd();
        }

        $items = $itemQuery->limit(60)->findAll();

        // PHP-side price sort (prices are stored in JSON, not a DB column)
        if ($activeSort === 'price_asc' || $activeSort === 'price_desc') {
            usort($items, function ($a, $b) use ($activeSort) {
                $pa = (float)($a['price'] ?? 0) > 0
                    ? (float)$a['price']
                    : (float)(json_decode($a['rate_option_1'] ?? '{}', true)['price'] ?? 0);
                $pb = (float)($b['price'] ?? 0) > 0
                    ? (float)$b['price']
                    : (float)(json_decode($b['rate_option_1'] ?? '{}', true)['price'] ?? 0);
                return $activeSort === 'price_asc' ? $pa <=> $pb : $pb <=> $pa;
            });
        }

        $recommendedItems = $this->getRecommendedItems();

        // Social proof for shop cards: avg rating + times rented, keyed by item id
        $allItemIds = array_map('intval', array_unique(array_merge(
            array_column($items, 'id'),
            array_column($recommendedItems, 'id')
        )));
        $ratings    = (new \App\Models\ReviewModel())->getAvgRatingsForItems($allItemIds);
        $rentCounts = [];
        if ($allItemIds !== []) {
            $rows = \Config\Database::connect()->table('rentals')
                ->select('item_id, COUNT(id) AS cnt')
                ->whereIn('item_id', $allItemIds)
                ->whereIn('status', ['ongoing', 'overdue', 'returned'])
                ->groupBy('item_id')
                ->get()->getResultArray();
            foreach ($rows as $row) {
                $rentCounts[(int) $row['item_id']] = (int) $row['cnt'];
            }
        }

        $data = [
            'items'       => $items,
            'categories'  => $categoryModel->orderBy('name', 'ASC')->findAll(),
            'activeCat'   => $categoryId,
            'searchQ'     => $searchQ,
            'activeSort'  => $activeSort,
            'activeAvail' => $activeAvail,
            'reviews'     => $this->getRecentReviews(),
            'recommendedItems' => $recommendedItems,
            'ratings'     => $ratings,
            'rentCounts'  => $rentCounts,
        ];

        return view('auth/portal', $data);
    }

    private function getRecentReviews()
    {
        $db = \Config\Database::connect();
        return $db->table('reviews')
            ->select('reviews.*, users.name as user_name, items.name as item_name, items.image as item_image')
            ->join('users', 'users.id = reviews.user_id', 'left')
            ->join('items', 'items.id = reviews.item_id', 'left')
            ->orderBy('reviews.created_at', 'DESC')
            ->limit(12)
            ->get()->getResultArray();
    }

    private function getRecommendedItems()
    {
        $itemModel = new ItemModel();
        return $itemModel
            ->select('items.*, categories.name as category_name')
            ->join('categories', 'categories.id = items.category_id', 'left')
            ->where('items.quantity >', 0)
            ->orderBy('items.created_at', 'DESC')
            ->limit(8)
            ->findAll();
    }

    // ══════════════════════════════════════════════
    //  CUSTOMER  (blue)
    // ══════════════════════════════════════════════
    public function login()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }
        return view('auth/login');
    }

    public function loginProcess()
    {
        return $this->handleLogin('customer', '/login');
    }

    public function register()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }
        return view('auth/register');
    }

    public function registerProcess()
    {
        return $this->handleRegister('customer', '/login');
    }

    // ══════════════════════════════════════════════
    //  OWNER  (green)
    // ══════════════════════════════════════════════
    public function ownerLogin()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }
        return view('auth/owner-login');
    }

    public function ownerLoginProcess()
    {
        return $this->handleLogin('owner', '/owner/login');
    }

    public function ownerRegister()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }
        return view('auth/owner-register');
    }

    public function ownerRegisterProcess()
    {
        return $this->handleRegister('owner', '/owner/login');
    }

    // ══════════════════════════════════════════════
    //  SND ADMIN  (crimson)
    // ══════════════════════════════════════════════
    public function adminLogin()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }
        return view('auth/admin-login');
    }

    public function adminLoginProcess()
    {
        return $this->handleLogin('snd_admin', '/admin/login');
    }

    public function adminRegister()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }
        return view('auth/admin-register');
    }

    public function adminRegisterProcess()
    {
        $secret   = $this->request->getPost('secret_code');
        $expected = env('admin.secretCode');
        if (empty($expected) || $secret !== $expected) {
            return redirect()->back()->withInput()->with('error', 'Invalid Admin Secret Code.');
        }

        return $this->handleRegister('snd_admin', '/admin/login');
    }

    // ══════════════════════════════════════════════
    //  EMAIL VERIFICATION
    // ══════════════════════════════════════════════
    public function showVerifyEmail()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }

        $email = session()->get('pending_verify_email');
        if (! $email) {
            return redirect()->to('/portal');
        }

        return view('auth/verify-email', [
            'email' => $email,
        ]);
    }

    public function processVerifyEmail()
    {
        $email = session()->get('pending_verify_email');
        if (! $email) {
            return redirect()->to('/portal');
        }

        // Throttle: max 6 code attempts per IP per 10 minutes (codes are 6 digits).
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5('verify_' . $this->request->getIPAddress()), 6, 10 * MINUTE) === false) {
            return redirect()->to('/verify-email')->with('error', 'Too many attempts. Please wait a few minutes and try again.');
        }

        $code  = trim($this->request->getPost('code') ?? '');
        $model = new UserModel();
        $user  = $model->where('email', $email)->first();

        if (! $user) {
            return redirect()->to('/portal')->with('error', 'Account not found.');
        }

        // Already verified
        if (! empty($user['email_verified_at'])) {
            session()->remove(['pending_verify_email', 'pending_verify_portal']);
            $portal = session()->get('pending_verify_portal') ?? '/portal';
            return redirect()->to($portal)->with('success', 'Email already verified. You can now sign in.');
        }

        // Expired
        if (empty($user['email_verify_expires']) || strtotime($user['email_verify_expires']) < time()) {
            return redirect()->to('/verify-email')
                ->with('error', 'Your verification code has expired. Click <strong>Resend Code</strong> to get a new one.');
        }

        // Wrong code
        if ($code !== $user['email_verify_code']) {
            return redirect()->to('/verify-email')
                ->with('error', 'Incorrect verification code. Please try again.');
        }

        // ✓ Correct — mark verified
        $model->skipValidation(true)->update($user['id'], [
            'email_verified_at'    => date('Y-m-d H:i:s'),
            'email_verify_code'    => null,
            'email_verify_expires' => null,
        ]);

        $portal = session()->get('pending_verify_portal') ?? '/portal';
        session()->remove(['pending_verify_email', 'pending_verify_portal']);

        return redirect()->to($portal)->with('success', 'Email verified! You can now sign in.');
    }

    public function resendVerifyEmail()
    {
        $email = session()->get('pending_verify_email');
        if (! $email) {
            return redirect()->to('/portal');
        }

        $model = new UserModel();
        $user  = $model->where('email', $email)->first();
        if (! $user) {
            return redirect()->to('/portal');
        }

        $code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 900); // 15 minutes

        $model->skipValidation(true)->update($user['id'], [
            'email_verify_code'    => $code,
            'email_verify_expires' => $expires,
        ]);

        $this->sendVerificationEmail($email, $user['name'], $code);

        return redirect()->to('/verify-email')
            ->with('success', "A new code was sent to <strong>{$email}</strong>.");
    }

    // ─── Logout ───
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/portal')->with('success', 'Logged out successfully.');
    }

    // ─── Forgot Password ───
    public function forgotPassword()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }
        return view('auth/forgot-password');
    }

    // ─── Send Reset Code ───
    public function sendResetCode()
    {
        // Throttle: max 3 reset requests per IP per 10 minutes.
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5('reset_' . $this->request->getIPAddress()), 3, 10 * MINUTE) === false) {
            return redirect()->back()->with('error', 'Too many reset requests. Please wait a few minutes and try again.');
        }

        $email = trim($this->request->getPost('email'));

        if (empty($email)) {
            return redirect()->back()->with('error', 'Please enter your email address.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'No account found with that email address.');
        }

        // Generate 6-digit reset code
        $code = random_string('numeric', 6);
        $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        // Store in session temporarily (or you could save to database)
        session()->set('password_reset', [
            'email' => $email,
            'code' => $code,
            'expires' => $expires
        ]);

        // Send email — the code is delivered ONLY by email, never in the URL/response.
        $this->sendPasswordResetEmail($email, $user['name'], $code);

        return redirect()->to('/reset-password')
            ->with('success', 'A 6-digit reset code was sent to your email. Enter it below.');
    }

    // ─── Reset Password Form ───
    public function resetPasswordForm()
    {
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }

        $resetData = session()->get('password_reset');

        if (!$resetData) {
            return redirect()->to('/forgot-password')->with('error', 'Please request a password reset code first.');
        }

        if (strtotime($resetData['expires']) < time()) {
            session()->remove('password_reset');
            return redirect()->to('/forgot-password')->with('error', 'Reset code has expired. Please request a new one.');
        }

        // Do NOT pass the code to the view — the user must type it from their email.
        return view('auth/reset-password');
    }

    // ─── Reset Password ───
    public function resetPassword()
    {
        $code = $this->request->getPost('code');
        $newPassword = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('password_confirm');

        $resetData = session()->get('password_reset');

        if (!$resetData || $resetData['code'] !== $code) {
            return redirect()->to('/forgot-password')->with('error', 'Invalid or expired reset code.');
        }

        if (strlen($newPassword) < 6) {
            return redirect()->back()->with('error', 'Password must be at least 6 characters.');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'Passwords do not match.');
        }

        if (strtotime($resetData['expires']) < time()) {
            session()->remove('password_reset');
            return redirect()->to('/forgot-password')->with('error', 'Reset code has expired. Please request a new one.');
        }

        // Update password
        $userModel = new UserModel();
        $userModel->where('email', $resetData['email'])
            ->set(['password' => password_hash($newPassword, PASSWORD_DEFAULT)])
            ->update();

        // Clear reset session
        session()->remove('password_reset');

        return redirect()->to('/login')->with('success', 'Password reset successfully! You can now log in with your new password.');
    }

    // ─── Send Password Reset Email ───
    private function sendPasswordResetEmail($email, $name, $code)
    {
        $emailConfig = config('Email');

        $message = "Hi {$name},\n\n";
        $message .= "Your password reset code is: {$code}\n\n";
        $message .= "This code will expire in 30 minutes.\n\n";
        $message .= "If you didn't request this, please ignore this email.\n\n";
        $message .= "Regards,\nIslands Getaway Travel and Tours";

        $emailService = \CodeIgniter\Email\Email::initialize($emailConfig);
        $emailService->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
        $emailService->setTo($email);
        $emailService->setSubject('Password Reset Code - IGTT');
        $emailService->setMessage($message);
        $emailService->send();
    }

    // ══════════════════════════════════════════════
    //  SHARED HELPERS
    // ══════════════════════════════════════════════

    /**
     * Get dashboard URL based on user role
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
     * Redirect to role-based dashboard
     */
    protected function redirectToDashboard()
    {
        return redirect()->to($this->getDashboardUrl());
    }

    private function handleLogin(string $expectedRole, string $backUrl)
    {
        // Throttle: max 5 login attempts per IP per minute to blunt brute force.
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5('login_' . $this->request->getIPAddress()), 5, MINUTE) === false) {
            return redirect()->to($backUrl)->withInput()->with(
                'error',
                'Too many login attempts. Please wait ' . $throttler->getTokenTime() . ' seconds and try again.'
            );
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to($backUrl)->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new UserModel();
        $user  = $model->where('email', $this->request->getPost('email'))->first();

        if (! $user || ! password_verify($this->request->getPost('password'), $user['password'])) {
            return redirect()->to($backUrl)->withInput()->with('error', 'Invalid email or password.');
        }

        // Enforce role match — prevent cross-portal login
        if ($user['role'] !== $expectedRole) {
            return redirect()->to($backUrl)->withInput()
                ->with('error', 'Invalid email or password.');
        }

        // Check email verification (skip for snd_admin)
        if ($user['role'] !== 'snd_admin' && empty($user['email_verified_at'])) {
            session()->set('pending_verify_email', $user['email']);
            session()->set('pending_verify_portal', $backUrl);
            return redirect()->to('/verify-email')
                ->with('warning', 'Please verify your email address before signing in.');
        }

        // Prevent session fixation: issue a fresh session ID on privilege change.
        session()->regenerate(true);

        session()->set([
            'logged_in'   => true,
            'user_id'     => $user['id'],
            'user_name'   => $user['name'],
            'role'        => $user['role'],
            'id_verified' => ! empty($user['is_id_verified']),
        ]);

        return $this->redirectToDashboard();
    }

    private function handleRegister(string $role, string $successUrl)
    {
        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'password_confirm' => ['matches' => 'Passwords do not match.'],
            'email'            => ['is_unique' => 'This email is already registered.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $name  = $this->request->getPost('name');

        // snd_admin skips email verification
        $needsVerify = ($role !== 'snd_admin');
        $code        = $needsVerify ? str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT) : null;
        $expires     = $needsVerify ? date('Y-m-d H:i:s', time() + 900) : null;
        $verifiedAt  = $needsVerify ? null : date('Y-m-d H:i:s');

        $model = new UserModel();
        $model->save([
            'name'                 => $name,
            'email'                => $email,
            'password'             => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'                 => $role,
            'email_verify_code'    => $code,
            'email_verify_expires' => $expires,
            'email_verified_at'    => $verifiedAt,
        ]);

        if ($needsVerify) {
            $this->sendVerificationEmail($email, $name, $code);
            session()->set('pending_verify_email', $email);
            session()->set('pending_verify_portal', $successUrl);
            return redirect()->to('/verify-email')
                ->with('info', "A 6-digit verification code was sent to <strong>{$email}</strong>. Check your inbox.");
        }

        $labels = ['snd_admin' => 'Admin', 'owner' => 'Rental Owner', 'customer' => 'Customer'];
        return redirect()->to($successUrl)->with('success', ($labels[$role] ?? '') . ' account created! You can now sign in.');
    }

    private function sendVerificationEmail(string $toEmail, string $toName, string $code): void
    {
        try {
            $config = config('Email');

            $email = \CodeIgniter\Email\Email::initialize($config);
            $email->setFrom($config->fromEmail, $config->fromName);
            $email->setTo($toEmail);
            $email->setSubject('Your SND Rental Verification Code — ' . $code);
            $email->setMailType('html');
            $email->setMessage($this->buildVerifyEmailHtml($toName, $code));

            if (! $email->send(false)) {
                log_message('error', '[Email] Verification send failed: ' . $email->printDebugger(['headers']));
                return;
            }

            log_message('info', "[Email] Verification sent to {$toEmail}");
        } catch (\Throwable $e) {
            log_message('error', '[Email] Exception: ' . $e->getMessage());
        }
    }

    private function buildVerifyEmailHtml(string $name, string $code): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#e8eaf6;font-family:'Segoe UI',Arial,sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
            <tr><td align="center">
              <table width="480" cellpadding="0" cellspacing="0"
                     style="background:#fff;border-radius:16px;overflow:hidden;
                            box-shadow:0 4px 24px rgba(13,44,138,.13);">
                <tr>
                  <td style="background:linear-gradient(135deg,#0d2c8a,#2979ff);
                              padding:28px 36px;text-align:center;">
                    <div style="font-size:1.4rem;font-weight:700;color:#fff;letter-spacing:.5px;">
                      🏠 SND Rental
                    </div>
                    <div style="color:rgba(255,255,255,.75);font-size:.85rem;margin-top:4px;">
                      Email Verification
                    </div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:36px;">
                    <p style="margin:0 0 12px;font-size:1rem;color:#1a1a2e;">
                      Hi <strong>{$name}</strong>,
                    </p>
                    <p style="margin:0 0 24px;color:#555;line-height:1.6;">
                      Use the code below to verify your email address.
                      It expires in <strong>15 minutes</strong>.
                    </p>
                    <div style="text-align:center;margin:28px 0;">
                      <div style="display:inline-block;font-size:2.6rem;font-weight:800;
                                  letter-spacing:14px;color:#0d2c8a;background:#eef0fb;
                                  padding:18px 36px;border-radius:12px;
                                  border:2px dashed #c5cdf0;">
                        {$code}
                      </div>
                    </div>
                    <p style="margin:0;color:#999;font-size:.8rem;line-height:1.5;">
                      If you did not create an account on SND Rental, you can safely ignore this email.
                    </p>
                  </td>
                </tr>
                <tr>
                  <td style="background:#f8f9ff;padding:16px 36px;text-align:center;
                              font-size:.75rem;color:#aaa;border-top:1px solid #eef0f6;">
                    &copy; <?= date('Y') ?> SND Rental System
                  </td>
                </tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }
}

