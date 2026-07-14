<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Models\RentalModel;
use App\Models\ReviewModel;
use App\Models\WalletModel;

class UserController extends BaseController
{
    protected UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function profile()
    {
        $userId = session()->get('user_id');
        $user   = $this->model->find($userId);
        if (! $user) return $this->redirectToDashboard()->with('error', 'User not found.');

        return view('users/profile', ['pageTitle' => 'My Profile', 'user' => $user]);
    }

    /** Server-side OCR via OCR.space API (Engine 5 for better ID reading) */
    public function ocrExtract()
    {
        $imgData = $this->request->getPost('image'); // base64 data URI
        if (empty($imgData)) {
            return $this->response->setJSON(['text' => '']);
        }

        // Try Engine 5 first (best for documents/IDs), fallback to Engine 2
        $engines = ['5', '2'];
        $text = '';

        foreach ($engines as $engine) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => 'https://api.ocr.space/parse/image',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => [
                    'apikey'       => 'helloworld',
                    'base64Image'  => $imgData,
                    'language'     => 'eng',
                    'isTable'      => 'true',
                    'OCREngine'    => $engine,
                    'scale'        => 'true',
                    'detectOrientation' => 'true',
                ],
                CURLOPT_TIMEOUT => 30,
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($result, true);

            if (!empty($data['ParsedResults'])) {
                foreach ($data['ParsedResults'] as $r) {
                    $text .= ($r['ParsedText'] ?? '') . "\n";
                }
            }

            // If we got meaningful text, stop trying other engines
            if (strlen(trim($text)) > 10) {
                break;
            }
            $text = ''; // Reset for next engine attempt
        }

        return $this->response->setJSON(['text' => trim($text)]);
    }

    public function updateProfile()
    {
        $userId = session()->get('user_id');
        $user   = $this->model->find($userId);
        if (! $user) return $this->redirectToDashboard()->with('error', 'User not found.');

        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]",
            'phone' => 'permit_empty|max_length[30]',
        ];

        if ($this->request->getPost('password')) {
            $rules['password']         = 'min_length[6]|max_length[255]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        // Handle avatar upload
        $avatar  = $this->request->getFile('avatar');
        $newName = $this->validateImageUpload($avatar);
        if ($newName === 'INVALID') {
            return redirect()->back()->withInput()->with('error', 'Avatar must be a JPG, PNG, GIF, or WEBP image under 5 MB.');
        }
        if ($newName !== null) {
            $avatar->move(FCPATH . 'uploads/avatars/', $newName);
            if (! empty($user['avatar'])) {
                $old = FCPATH . 'uploads/avatars/' . $user['avatar'];
                if (file_exists($old)) unlink($old);
            }
            $data['avatar'] = $newName;
        }

        // Handle valid ID upload
        $validId = $this->request->getFile('valid_id');
        $newName = $this->validateImageUpload($validId);
        if ($newName === 'INVALID') {
            return redirect()->back()->withInput()->with('error', 'Valid ID must be a JPG, PNG, GIF, or WEBP image under 5 MB.');
        }
        if ($newName !== null) {
            $validId->move(FCPATH . 'uploads/valid_ids/', $newName);
            if (! empty($user['valid_id'])) {
                $old = FCPATH . 'uploads/valid_ids/' . $user['valid_id'];
                if (file_exists($old)) unlink($old);
            }
            $data['valid_id']      = $newName;
            $data['valid_id_type'] = $this->request->getPost('valid_id_type');
            // SECURITY: verification is NEVER granted from a client-supplied flag.
            // The browser-side OCR is only an assist; the real check is a human
            // owner/admin approving via the ID verification screen (verifyId()).
            // ponytail: to re-enable auto-verify, re-run OCR server-side here on
            // the saved file and compare to $user['name'] — do NOT trust POST.
            $data['is_id_verified'] = 0;
            $data['id_verified_at'] = null;
        } elseif ($this->request->getPost('valid_id_type') && ! empty($user['valid_id'])) {
            $data['valid_id_type'] = $this->request->getPost('valid_id_type');
        }

        $this->model->skipValidation(true)->update($userId, $data);

        // Update session name and verified status
        session()->set('user_name', $data['name']);
        session()->set('id_verified', ! empty($data['is_id_verified']) || ! empty($this->model->find($userId)['is_id_verified']));

        $msg = 'Profile updated successfully.';
        if (isset($data['valid_id'])) {
            $msg = ! empty($data['is_id_verified'])
                ? 'Profile updated. Your ID has been verified automatically.'
                : 'Profile updated. Your ID has been submitted and will be reviewed by an owner.';

            // If pending manual review, notify all admins
            if (empty($data['is_id_verified'])) {
                $admins = $this->model->whereIn('role', ['snd_admin'])->findAll();
                $adminIds = array_column($admins, 'id');
                if ($adminIds) {
                    (new NotificationModel())->notify(
                        $adminIds,
                        'New ID Uploaded for Review 🪪',
                        session()->get('user_name') . ' uploaded a valid ID and is awaiting verification.',
                        'warning',
                        '/verifications'
                    );
                }
            }
        }

        $role = session()->get('role');
        $prefix = match ($role) {
            'snd_admin' => '/admin',
            'owner'     => '/owner',
            'customer'  => '/customer',
            default     => '/customer',
        };
        return redirect()->to($prefix . '/profile')->with('success', $msg);
    }

    /** Mark a customer's ID as verified (name on ID matches account name) */
    public function verifyId(int $id)
    {
        $this->model->skipValidation(true)->update($id, [
            'is_id_verified' => 1,
            'id_verified_at' => date('Y-m-d H:i:s'),
        ]);

        (new NotificationModel())->notify(
            $id,
            'ID Verified ✅',
            'Your valid ID has been verified by admin. You can now rent items.',
            'success',
            '/profile'
        );

        return redirect()->back()->with('success', 'Customer ID verified successfully.');
    }

    /** Remove verification from a customer's ID */
    public function unverifyId(int $id)
    {
        $this->model->skipValidation(true)->update($id, [
            'is_id_verified' => 0,
            'id_verified_at' => null,
        ]);

        (new NotificationModel())->notify(
            $id,
            'ID Verification Removed ❌',
            'Your ID verification was removed by admin. Please re-upload a valid ID on your profile.',
            'danger',
            '/profile'
        );

        return redirect()->back()->with('success', 'Verification removed.');
    }

    /** Owner/Admin views a customer's profile before confirming a rental */
    public function viewCustomerProfile(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['snd_admin', 'owner'])) {
            return $this->redirectToDashboard()->with('error', 'Unauthorized.');
        }

        $user = $this->model->find($id);
        if (! $user) return redirect()->to('/rentals')->with('error', 'User not found.');

        $rentalModel = new \App\Models\RentalModel();
        $rentals = $rentalModel->getWithRelations()
            ->where('rentals.user_id', $id)
            ->orderBy('rentals.id', 'DESC')
            ->findAll();

        $fromRental = $this->request->getGet('from_rental');

        return view('users/customer_profile', [
            'pageTitle'   => 'Customer Profile — ' . esc($user['name']),
            'user'        => $user,
            'rentals'     => $rentals,
            'fromRental'  => $fromRental,
        ]);
    }

    /** JSON endpoint — returns customer profile data for the modal popup */
    public function profileJson(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['snd_admin', 'owner'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        $user = $this->model->find($id);
        if (! $user) {
            return $this->response->setJSON(['error' => 'User not found'])->setStatusCode(404);
        }

        $rentalModel = new \App\Models\RentalModel();
        $totalRentals  = $rentalModel->where('user_id', $id)->countAllResults();
        $activeRentals = $rentalModel->where('user_id', $id)->where('status', 'ongoing')->countAllResults();

        return $this->response->setJSON([
            'id'            => $user['id'],
            'name'          => $user['name'],
            'email'         => $user['email'],
            'phone'         => $user['phone'] ?? null,
            'role'          => $user['role'],
            'avatar'        => $user['avatar'] ?? null,
            'valid_id'      => $user['valid_id'] ?? null,
            'valid_id_type' => $user['valid_id_type'] ?? null,
            'is_id_verified'=> (bool)($user['is_id_verified'] ?? false),
            'id_verified_at'=> $user['id_verified_at'] ?? null,
            'is_email_verified' => (bool)($user['is_email_verified'] ?? false),
            'created_at'    => $user['created_at'],
            'total_rentals' => $totalRentals,
            'active_rentals'=> $activeRentals,
            'avatar_url'    => $user['avatar'] ? base_url('uploads/avatars/' . $user['avatar']) : null,
            'valid_id_url'  => $user['valid_id'] ? base_url('uploads/valid_ids/' . $user['valid_id']) : null,
        ]);
    }

    public function verificationList()
    {
        $role = session()->get('role');
        if ($role !== 'snd_admin') {
            return $this->redirectToDashboard()->with('error', 'Unauthorized.');
        }

        $customers = $this->model
            ->where('role', 'customer')
            ->where('valid_id IS NOT NULL')
            ->where('valid_id !=', '')
            ->orderBy('is_id_verified', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('users/verifications', [
            'pageTitle' => 'ID Verifications',
            'customers' => $customers,
        ]);
    }

    public function index()
    {
        $data = [
            'pageTitle' => 'Manage Users',
            'users'     => $this->model->paginate(10),
            'pager'     => $this->model->pager,
        ];
        return view('users/index', $data);
    }

    public function create()
    {
        return view('users/create', ['pageTitle' => 'Add User']);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[snd_admin,owner,customer]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->save([
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'),
        ]);

        return redirect()->to($this->rolePrefix() . '/users')->with('success', 'User created successfully.');
    }

    public function edit(int $id)
    {
        $user = $this->model->find($id);
        if (! $user) return redirect()->to($this->rolePrefix() . '/users')->with('error', 'User not found.');

        return view('users/edit', ['pageTitle' => 'Edit User', 'user' => $user]);
    }

    public function update(int $id)
    {
        $user = $this->model->find($id);
        if (! $user) return redirect()->to($this->rolePrefix() . '/users')->with('error', 'User not found.');

        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,$id]",
            'role'  => 'required|in_list[snd_admin,owner,customer]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role'  => $this->request->getPost('role'),
        ];

        $newPass = $this->request->getPost('password');
        if (! empty($newPass)) {
            if (strlen($newPass) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password must be at least 6 characters.');
            }
            $updateData['password'] = password_hash($newPass, PASSWORD_DEFAULT);
        }

        $this->model->update($id, $updateData);
        return redirect()->to($this->rolePrefix() . '/users')->with('success', 'User updated successfully.');
    }

    public function delete(int $id)
    {
        // Prevent deleting own account
        if ($id === (int) session()->get('user_id')) {
            return redirect()->to($this->rolePrefix() . '/users')->with('error', 'You cannot delete your own account.');
        }

        $user = $this->model->find($id);
        if (!$user) {
            return redirect()->to($this->rolePrefix() . '/users')->with('error', 'User not found.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Delete related records to avoid FK constraint failures
        $rentalIds = $db->table('rentals')->where('user_id', $id)->get()->getResultArray();
        $rentalIdList = array_column($rentalIds, 'id');

        if (!empty($rentalIdList)) {
            $db->table('reviews')->whereIn('rental_id', $rentalIdList)->delete();
        }
        $db->table('reviews')->where('user_id', $id)->delete();
        $db->table('rentals')->where('user_id', $id)->delete();
        $db->table('wallet_transactions')->where('user_id', $id)->delete();
        $db->table('wallet_topup_requests')->where('user_id', $id)->delete();
        $db->table('notifications')->where('user_id', $id)->delete();
        $db->table('users')->where('id', $id)->delete();

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to($this->rolePrefix() . '/users')->with('error', 'Failed to delete user. Please try again.');
        }

        return redirect()->to($this->rolePrefix() . '/users')->with('success', 'User and all related data deleted.');
    }
}
