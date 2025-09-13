<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\PetrolType;
use App\Models\Container; // asumiendo que ya lo tienes

class PurchaseController extends Controller
{
    private Purchase $model;
    public function __construct(){ $this->model = new Purchase(); }

    public function index(){
        if (session_status() === PHP_SESSION_NONE) session_start();
        $df = $_GET['date_from'] ?? null;
        $dt = $_GET['date_to'] ?? null;
        $sid = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : null;

        $supModel = new Supplier();
        $rows = $this->model->list($df, $dt, $sid);

        $this->render('purchases/index', [
            'purchases' => $rows,
            'suppliers' => $supModel->all(),
            'filters'   => ['date_from'=>$df, 'date_to'=>$dt, 'supplier_id'=>$sid],
            'page'      => 'purchases',
            'title'     => 'Purchases'
        ]);
    }

    public function manage(){
        // create-only en MVP (si quieres editar, agregamos luego)
        $supModel = new Supplier();
        $typeModel = new PetrolType();
        $contModel = new Container();

        $this->render('purchases/manage', [
            'suppliers'  => $supModel->all(),
            'types'      => $typeModel->all(),         // asumiendo ->all()
            'containers' => $contModel->all(),         // asumiendo ->all()
        ], null, false);
    }

    public function viewPurchase($id){
        $id = (int)$id;
        $h  = $this->model->getHeader($id);
        $it = $this->model->getItems($id);
        $this->render('purchases/view', ['h'=>$h,'items'=>$it], null, false);
    }

    public function save(){
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])){
            echo json_encode(['status'=>'error','msg'=>'No autenticado']); return;
        }
        try{
            $header = [
                'supplier_id'  => (int)($_POST['supplier_id'] ?? 0),
                'date'         => $_POST['date'] ?? date('Y-m-d'),
                'payment_type' => (int)($_POST['payment_type'] ?? 1),
                'notes'        => $_POST['notes'] ?? null,
            ];
            if(!$header['supplier_id']){ echo json_encode(['status'=>'error','msg'=>'Proveedor requerido']); return; }

            // arrays paralelos
            $pt = $_POST['petrol_type_id'] ?? [];
            $co = $_POST['container_id'] ?? [];
            $qt = $_POST['qty_liters'] ?? [];
            $uc = $_POST['unit_cost'] ?? [];

            $items = [];
            for($i=0;$i<count($pt);$i++){
                if(!$pt[$i] || !$co[$i] || $qt[$i] <= 0) continue;
                $items[] = [
                    'petrol_type_id' => (int)$pt[$i],
                    'container_id'   => (int)$co[$i],
                    'qty_liters'     => (float)$qt[$i],
                    'unit_cost'      => (float)($uc[$i] ?? 0),
                ];
            }
            if(!$items){ echo json_encode(['status'=>'error','msg'=>'Agrega al menos 1 ítem']); return; }

            $pid = $this->model->save($header, $items, (int)$_SESSION['user_id']);
            echo json_encode(['status'=>'success','msg'=>'Compra registrada','redirect'=>"index.php?url=purchase/viewPurchase/$pid"]);
        }catch(\Throwable $e){
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }
    }
}
