<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dashboard;

class HomeController extends Controller {
    public function index() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: /psms/public/");
        exit;
    }

    $model = new Dashboard();
    $data = [
        'petrol_count' => $model->countPetrolTypes(),
        'customer_count' => $model->countCustomers(),
        'balance' => $model->getBalance(),
        'today_sales' => $model->getTodaySales(),
        'page' => 'home' 
    ];

    $this->render('home/index', $data, 'Inicio | Petrol Station');
}

}
