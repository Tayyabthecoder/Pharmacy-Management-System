<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Company;

class SupplierController {
    protected $supplierModel;
    protected $companyModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin']))->handle();
        $this->supplierModel = new Supplier();
        $this->companyModel = new Company();
    }

    public function index() {
        $pageTitle = "Suppliers & Companies";
        
        $msg = '';
        $msgType = '';
        $activeTab = 'suppliers';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['action'])) {
                $type = $_POST['type'] ?? 'supplier';
                $activeTab = $type === 'company' ? 'companies' : 'suppliers';
                
                $name = trim($_POST['name'] ?? '');
                $contact = trim($_POST['contact_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $status = $_POST['status'] ?? 'active';
                
                $model = $type === 'company' ? $this->companyModel : $this->supplierModel;
                $entityName = $type === 'company' ? 'Company' : 'Supplier';

                if ($_POST['action'] == 'add') {
                    if (!empty($name)) {
                        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $msg = "Please enter a valid email address.";
                            $msgType = "danger";
                        } else {
                            $data = [
                                'name' => $name,
                                'contact_name' => $contact,
                                'email' => $email,
                                'phone' => $phone,
                                'address' => $address,
                                'status' => $status
                            ];
                            if ($model->create($data)) {
                                $msg = "{$entityName} added successfully!";
                                $msgType = "success";
                            } else {
                                $msg = "Failed to add {$entityName}.";
                                $msgType = "danger";
                            }
                        }
                    } else {
                        $msg = "{$entityName} name is required.";
                        $msgType = "danger";
                    }
                } elseif ($_POST['action'] == 'edit') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id > 0 && !empty($name)) {
                        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $msg = "Please enter a valid email address.";
                            $msgType = "danger";
                        } else {
                            $data = [
                                'name' => $name,
                                'contact_name' => $contact,
                                'email' => $email,
                                'phone' => $phone,
                                'address' => $address,
                                'status' => $status
                            ];
                            if ($model->update($id, $data)) {
                                $msg = "{$entityName} updated successfully!";
                                $msgType = "success";
                            } else {
                                $msg = "Failed to update {$entityName}.";
                                $msgType = "danger";
                            }
                        }
                    } else {
                        $msg = "Invalid {$entityName} details.";
                        $msgType = "danger";
                    }
                } elseif ($_POST['action'] == 'delete') {
                    $id = (int)($_POST['delete_id'] ?? 0);
                    if ($id > 0) {
                        $deleteMethod = $type === 'company' ? 'deleteCompany' : 'deleteSupplier';
                        if ($model->$deleteMethod($id)) {
                            $msg = "{$entityName} deleted successfully!";
                            $msgType = "success";
                        } else {
                            $msg = "Failed to delete {$entityName}.";
                            $msgType = "danger";
                        }
                    }
                }
            }
        }

        // Fetch using Model with Pagination
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $cpage = (int)($_GET['cpage'] ?? 1);
        if ($cpage < 1) $cpage = 1;
        
        $settingModel = new Setting();
        $perPage = (int)$settingModel->get('records_per_page', 10);

        $pagination = $this->supplierModel->paginate('suppliers', $page, $perPage, '1=1', [], 'name ASC');
        $suppliers = $pagination['data'];

        $compPagination = $this->companyModel->paginate('companies', $cpage, $perPage, '1=1', [], 'name ASC');
        $companies = $compPagination['data'];

        // Compute metrics for top KPI summary cards
        $allSuppliers = $this->supplierModel->getAll();
        $allCompanies = $this->companyModel->getAll();

        $totalSuppliersCount = count($allSuppliers);
        $activeSuppliersCount = count(array_filter($allSuppliers, function($s) {
            return ($s['status'] ?? 'active') === 'active';
        }));

        $totalCompaniesCount = count($allCompanies);
        $activeCompaniesCount = count(array_filter($allCompanies, function($c) {
            return ($c['status'] ?? 'active') === 'active';
        }));

        require_once BASE_PATH . '/resources/views/admin/suppliers.php';
    }
}
