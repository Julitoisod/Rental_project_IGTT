<?php

namespace App\Controllers;

use App\Models\PaymentGatewayModel;

class PaymentGatewayController extends BaseController
{
    protected PaymentGatewayModel $model;

    public function __construct()
    {
        $this->model = new PaymentGatewayModel();
    }

    public function index()
    {
        $ownerId  = (int) session()->get('user_id');
        $gateways = $this->model->where('owner_id', $ownerId)->orderBy('id', 'DESC')->findAll();

        return view('payment_gateways/index', [
            'pageTitle' => 'Payment Gateways',
            'gateways'  => $gateways,
        ]);
    }

    public function store()
    {
        $ownerId = (int) session()->get('user_id');

        $rules = [
            'name'           => 'required|min_length[2]|max_length[100]',
            'type'           => 'required|max_length[50]',
            'account_number' => 'required|max_length[100]',
            'qr_code'        => 'permit_empty|is_image[qr_code]|max_size[qr_code,2048]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $qrFilename = null;
        $qrFile = $this->request->getFile('qr_code');
        if ($qrFile && $qrFile->isValid() && ! $qrFile->hasMoved()) {
            $qrFilename = $qrFile->getRandomName();
            $qrFile->move(FCPATH . 'uploads/qrcodes', $qrFilename);
        }

        $this->model->save([
            'owner_id'       => $ownerId,
            'name'           => $this->request->getPost('name'),
            'type'           => $this->request->getPost('type'),
            'account_number' => $this->request->getPost('account_number'),
            'qr_code'        => $qrFilename,
        ]);

        return redirect()->to('/payment-gateways')->with('success', 'Payment gateway added successfully.');
    }

    public function edit(int $id)
    {
        $ownerId = (int) session()->get('user_id');
        $gateway = $this->model->where('id', $id)->where('owner_id', $ownerId)->first();
        if (! $gateway) {
            return redirect()->to('/payment-gateways')->with('error', 'Gateway not found.');
        }

        return $this->response->setJSON($gateway);
    }

    public function update(int $id)
    {
        $ownerId = (int) session()->get('user_id');
        $gateway = $this->model->where('id', $id)->where('owner_id', $ownerId)->first();
        if (! $gateway) {
            return redirect()->to('/payment-gateways')->with('error', 'Gateway not found.');
        }

        $rules = [
            'name'           => 'required|min_length[2]|max_length[100]',
            'type'           => 'required|max_length[50]',
            'account_number' => 'required|max_length[100]',
            'qr_code'        => 'permit_empty|is_image[qr_code]|max_size[qr_code,2048]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $qrFilename = $gateway['qr_code'];
        $qrFile = $this->request->getFile('qr_code');
        if ($qrFile && $qrFile->isValid() && ! $qrFile->hasMoved()) {
            // Delete old QR if exists
            if ($qrFilename && file_exists(FCPATH . 'uploads/qrcodes/' . $qrFilename)) {
                unlink(FCPATH . 'uploads/qrcodes/' . $qrFilename);
            }
            $qrFilename = $qrFile->getRandomName();
            $qrFile->move(FCPATH . 'uploads/qrcodes', $qrFilename);
        }

        $this->model->update($id, [
            'name'           => $this->request->getPost('name'),
            'type'           => $this->request->getPost('type'),
            'account_number' => $this->request->getPost('account_number'),
            'qr_code'        => $qrFilename,
        ]);

        return redirect()->to('/payment-gateways')->with('success', 'Payment gateway updated.');
    }

    public function delete(int $id)
    {
        $ownerId = (int) session()->get('user_id');
        $gateway = $this->model->where('id', $id)->where('owner_id', $ownerId)->first();
        if (! $gateway) {
            return redirect()->to('/payment-gateways')->with('error', 'Gateway not found.');
        }

        if ($gateway['qr_code'] && file_exists(FCPATH . 'uploads/qrcodes/' . $gateway['qr_code'])) {
            unlink(FCPATH . 'uploads/qrcodes/' . $gateway['qr_code']);
        }

        $this->model->delete($id);
        return redirect()->to('/payment-gateways')->with('success', 'Payment gateway deleted.');
    }

    /**
     * AJAX: Return the payment gateways for the owner of a given item.
     * GET /payment-gateways/for-item/{item_id}
     */
    public function forItem(int $itemId)
    {
        $item = (new \App\Models\ItemModel())->find($itemId);
        if (! $item) {
            return $this->response->setJSON(['gateways' => []]);
        }
        $gateways = $this->model->where('owner_id', $item['owner_id'])->findAll();
        // Build public QR URLs
        foreach ($gateways as &$gw) {
            $gw['qr_url'] = $gw['qr_code']
                ? base_url('uploads/qrcodes/' . $gw['qr_code'])
                : null;
        }
        return $this->response->setJSON(['gateways' => $gateways]);
    }
}
