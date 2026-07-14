<?php

namespace App\Controllers;

use App\Models\CategoryModel;

class CategoryController extends BaseController
{
    protected CategoryModel $model;

    public function __construct()
    {
        $this->model = new CategoryModel();
    }

    public function index()
    {
        $data = [
            'pageTitle'  => 'Manage Categories',
            'categories' => $this->model->paginate(10),
            'pager'      => $this->model->pager,
        ];
        return view('categories/index', $data);
    }

    public function create()
    {
        return view('categories/create', ['pageTitle' => 'Add Category']);
    }

    public function store()
    {
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[100]|is_unique[categories.name]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->save(['name' => $this->request->getPost('name')]);
        return redirect()->to($this->rolePrefix() . '/categories')->with('success', 'Category added.');
    }

    public function edit(int $id)
    {
        $cat = $this->model->find($id);
        if (! $cat) return redirect()->to($this->rolePrefix() . '/categories')->with('error', 'Category not found.');

        return view('categories/edit', ['pageTitle' => 'Edit Category', 'category' => $cat]);
    }

    public function update(int $id)
    {
        if (! $this->validate(['name' => "required|min_length[2]|is_unique[categories.name,id,$id]"])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, ['name' => $this->request->getPost('name')]);
        return redirect()->to($this->rolePrefix() . '/categories')->with('success', 'Category updated.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->to($this->rolePrefix() . '/categories')->with('success', 'Category deleted.');
    }
}
