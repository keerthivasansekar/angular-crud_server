<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use CodeIgniter\API\ResponseTrait;

class ProductController extends BaseController
{
    private $validation;

    use ResponseTrait;

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $this->validation = \Config\Services::validation();
    }

    public function create_product()
    {
        $rules = [
            'name' => 'required|min_length[5]|max_length[100]',
            'description' => 'required|min_length[15]|max_length[500]',
            'price' => 'required|decimal',
        ];

        if ($this->validate($rules)) {
            $productsModel = new ProductModel;
            $data = [
                'p_name'        => $this->request->getVar('name'),
                'p_description' => $this->request->getVar('description'),
                'p_price'       => $this->request->getVar('price'),
                'p_slug'        => url_title($this->request->getVar('name'), '-', true),
            ];
            if ($productsModel->save($data)) {
                return $this->respond(['result' => 'success'], 200);
            } else {
                return $this->respond(['result' => 'failed'], 200);
            }
        } else {
            $errors = $this->validation->getErrors();
            return $this->respond([
                'result' => 'failed',
                'errors' => $errors
            ], 200);
        }
    }

    public function read_products($slug = false)
    {

        $productsModel = new ProductModel;
        if ($slug !== false) {
            if (is_numeric($slug)) {
                $products = $productsModel->where(['p_id' => $slug])->first();
            } else {
                $products = $productsModel->where(['p_slug' => $slug])->first();
            }
            

        } else {
            $products = $productsModel->findAll();
        }

        if (count($products) > 0) {
            return $this->respond($products, 200);
        } else {
            return $this->respond(null, 200);
        }
    }

    public function update_product()
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'required|min_length[5]|max_length[100]',
            'description' => 'required|min_length[15]|max_length[500]',
            'price' => 'required|decimal',
        ];

        if ($this->validate($rules)) {
            $productsModel = new ProductModel;
            if (count($productsModel->where(['p_id' => $this->request->getVar('id')])->first()) > 0) {
                $data = [
                    'p_id' => $this->request->getVar('id'),
                    'p_name'     => $this->request->getVar('name'),
                    'p_description'     => $this->request->getVar('description'),
                    'p_price'     => $this->request->getVar('price'),
                    'p_slug'     => url_title($this->request->getVar('name'), '-', true),
                ];
                if ($productsModel->save($data)) {
                    return $this->respond(['result' => 'success'], 200);
                } else {
                    return $this->respond(['result' => 'failed'], 200);
                }
            } else {
                return $this->respond(['result' => 'failed'], 200);
            }
        } else {
            $errors = $this->validation->getErrors();
            return $this->respond([
                'result' => 'failed',
                'errors' => $errors
            ], 200);
        }
    }

    public function delete_product($id = null)
    {
        $productsModel = new ProductModel;
        $product = $productsModel->where(['p_id' => $id])->first();
        if ($product !== null) {
            $productsModel->delete(['p_id' => $id]);
            return $this->respond([
                'result' => 'success'
            ], 200);
        } else {
            return $this->respond([
                'result' => 'failed'
            ], 200);
        }
    }
}
