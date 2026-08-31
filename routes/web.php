<?php

// Define Routes
global $router;

// Auth
$router->get('/login', 'AuthController@showLoginForm');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

// Admin
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/products', 'ProductController@index');
$router->post('/admin/products/store', 'ProductController@store');
$router->post('/admin/products/update', 'ProductController@update');
$router->post('/admin/products/delete', 'ProductController@destroy');
$router->get('/admin/categories', 'CategoryController@index');
$router->post('/admin/categories/ajax-add', 'CategoryController@storeAjax');
$router->post('/admin/categories/store', 'CategoryController@storeAjax');
$router->post('/admin/categories/update', 'CategoryController@updateAjax');
$router->post('/admin/categories/delete', 'CategoryController@deleteAjax');

$router->post('/admin/generics/ajax-add', 'GenericController@storeAjax');
$router->post('/admin/generics/store', 'GenericController@storeAjax');
$router->post('/admin/generics/update', 'GenericController@updateAjax');
$router->post('/admin/generics/delete', 'GenericController@deleteAjax');

// Admin - Sales (Invoices)
$router->get('/admin/invoices', 'SaleController@index');
$router->get('/admin/create_invoice', 'AdminController@createInvoice');
$router->post('/admin/create_invoice', 'AdminController@createInvoice');
$router->get('/admin/create_return_invoice', 'AdminController@createReturnInvoice');
$router->post('/admin/create_return_invoice', 'AdminController@createReturnInvoice');

$router->get('/admin/users', 'UserController@index');
$router->post('/admin/users', 'UserController@index');
$router->get('/admin/settings', 'SettingController@index');
$router->post('/admin/settings', 'SettingController@index');
$router->get('/admin/profile', 'ProfileController@profile');
$router->post('/admin/profile', 'ProfileController@profile');
$router->get('/admin/reports', 'ReportController@index');
$router->post('/admin/reports/generate', 'ReportController@generate');
$router->get('/admin/expiry_report', 'ReportController@expiryReport');
$router->get('/admin/suppliers', 'SupplierController@index');
$router->post('/admin/suppliers', 'SupplierController@index');

// Admin - Receive Invoices (Suppliers/Stock)
$router->get('/admin/receive_invoices', 'ReceiveInvoiceController@index');
$router->get('/admin/create_receive_invoice', 'ReceiveInvoiceController@create');
$router->post('/admin/create_receive_invoice', 'ReceiveInvoiceController@create');
$router->get('/admin/create_return_receive_invoice', 'ReceiveInvoiceController@createReturn');
$router->post('/admin/create_return_receive_invoice', 'ReceiveInvoiceController@createReturn');

$router->get('/admin/view_receive_invoice', 'ReceiveInvoiceController@view');

$router->get('/invoice/print', 'InvoiceController@print');
$router->get('/api/products/autocomplete', 'ProductController@autocomplete');
$router->get('/api/products/search_generic', 'ProductController@searchGeneric');
$router->get('/api/products/check_duplicate', 'ProductController@checkDuplicate');
$router->get('/api/invoice/details', 'SaleController@detailsApi');
$router->get('/api/receive_invoice/details', 'ReceiveInvoiceController@detailsApi');

// Danger Zone
$router->post('/admin/system/wipe', 'AdminController@wipeData');
$router->post('/admin/system/wipe_invoices', 'AdminController@wipeInvoices');

// Manual Database Backup & Restore System
$router->post('/admin/backup/create', 'BackupController@create');
$router->get('/admin/backup/download', 'BackupController@download');
$router->post('/admin/backup/delete', 'BackupController@delete');
$router->post('/admin/backup/restore', 'BackupController@restore');

// Notifications API & Views
$router->get('/api/notifications', 'NotificationController@fetch');
$router->post('/api/notifications/read', 'NotificationController@read');
$router->post('/api/notifications/read-all', 'NotificationController@readAll');
$router->get('/admin/notifications', 'NotificationController@indexAdmin');
$router->get('/salesman/notifications', 'NotificationController@indexSalesman');
// Salesman
$router->get('/salesman/dashboard', 'SalesmanController@dashboard');
$router->get('/salesman/invoices', 'SalesmanController@invoices');

$router->post('/salesman/delete_invoice', 'SalesmanController@deleteInvoice');
$router->get('/salesman/create_invoice', 'SalesmanController@createInvoice');
$router->post('/salesman/create_invoice', 'SalesmanController@createInvoice');
$router->get('/salesman/create_return_invoice', 'SalesmanController@createReturnInvoice');
$router->post('/salesman/create_return_invoice', 'SalesmanController@createReturnInvoice');
$router->get('/salesman/inventory', 'SalesmanController@inventory');
$router->get('/salesman/profile', 'SalesmanController@profile');
$router->post('/salesman/profile', 'SalesmanController@profile');
