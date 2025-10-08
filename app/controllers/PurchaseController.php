<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\PetrolType;
use App\Models\Container;

class PurchaseController extends Controller
{
    private Purchase $model;

    public function __construct(){
        $this->model = new Purchase();
    }

    /** LISTA: Órdenes de compra (con filtros y validación de rango) */
    public function index(){
        if (session_status() === PHP_SESSION_NONE) session_start();
        $df  = $_GET['date_from'] ?? null;
        $dt  = $_GET['date_to'] ?? null;
        $sid = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : null;

        $supModel = new Supplier();
        $error = null;
        if ($df && $dt && $df > $dt) $error = 'Rango de fechas inválido.';

        $rows = $error ? [] : $this->model->listOrders($df, $dt, $sid);

        $this->render('purchases/index', [
            'purchases' => $rows,
            'suppliers' => $supModel->all(),
            'filters'   => ['date_from'=>$df, 'date_to'=>$dt, 'supplier_id'=>$sid],
            'page'      => 'purchases',
            'title'     => 'Órdenes de compra',
            'error'     => $error
        ]);
    }

    /** FORM: Crear OC (sin contenedor) */
    public function manage(){
        // create-only en esta fase
        $supModel  = new Supplier();
        $typeModel = new PetrolType();

        $this->render('purchases/manage', [
            'suppliers'  => $supModel->all(),
            'types'      => $typeModel->all()
        ], null, false);
    }

    /** VER: (HISTÓRICO) Compra inmediata previa */
    public function viewPurchase($id){
        $id = (int)$id;
        $h  = $this->model->getHeader($id);
        $it = $this->model->getItems($id);
        $this->render('purchases/view', ['h'=>$h,'items'=>$it], null, false);
    }

    /** GUARDAR: OC (no mueve stock) */
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
                'notes'        => $_POST['notes'] ?? null,
            ];
            if(!$header['supplier_id']){
                echo json_encode(['status'=>'error','msg'=>'Proveedor requerido']); return;
            }

            // arrays paralelos (sin contenedor)
            $pt = $_POST['petrol_type_id'] ?? [];
            $qt = $_POST['qty_liters'] ?? [];
            $uc = $_POST['unit_cost'] ?? [];

            $items = [];
            for($i=0;$i<count($pt);$i++){
                if(!$pt[$i] || ($qt[$i] ?? 0) <= 0) continue;
                $items[] = [
                    'petrol_type_id' => (int)$pt[$i],
                    'qty_liters'     => (float)$qt[$i],
                    'unit_cost'      => (float)($uc[$i] ?? 0),
                ];
            }
            if(!$items){
                echo json_encode(['status'=>'error','msg'=>'Agrega al menos 1 ítem']); return;
            }

            $oid = $this->model->saveOrder($header, $items, (int)$_SESSION['user_id']);
            echo json_encode([
                'status'=>'success',
                'msg'=>'Orden registrada',
                'redirect'=>"index.php?url=purchase/viewOrder/$oid"
            ]);
        }catch(\Throwable $e){
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }
    }

    /** VER: OC (cabecera + ítems) */
    public function viewOrder($id){
        $id = (int)$id;
        $h  = $this->model->getOrderHeader($id);
        $it = $this->model->getOrderItems($id);
        $this->render('purchases/view_order', ['h'=>$h,'items'=>$it], null, false);
    }

    /** FORM: Confirmar recibido (elegir contenedores) */
    public function reciboCreate($ordenId){
        $ordenId = (int)$ordenId;
        $oc  = $this->model->getOrderHeader($ordenId);
        $its = $this->model->getOrderItems($ordenId);

        $contModel = new Container();
        $all = $contModel->all();
        $byType = [];
        foreach($all as $c){
            if(($c['status'] ?? 0) != 1) continue;
            $byType[$c['petrol_type_id']][] = $c;
        }

        $this->render('purchases/receive', [
            'oc'=>[
              'id'=>$oc['id'],
              'codigo'=>$oc['codigo'],
              'proveedor'=>$oc['supplier_name'] ?? '',
              'fecha'=>$oc['fecha']
            ],
            'items'=>$its,
            'contenedoresPorTipo'=>$byType
        ], null, false);
    }

    /** POST: Confirmar recibido (mueve stock + kardex) */
    public function reciboStore($ordenId){
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if(!isset($_SESSION['user_id'])){
            echo json_encode(['status'=>'error','msg'=>'No autenticado']); return;
        }
        try{
            $data = [
                'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
                'documento_proveedor' => $_POST['documento_proveedor'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null,
            ];
            $items = [];
            $n = count($_POST['orden_item_id'] ?? []);
            for($i=0;$i<$n;$i++){
                $items[] = [
                    'orden_item_id' => (int)$_POST['orden_item_id'][$i],
                    'contenedor_id' => (int)$_POST['contenedor_id'][$i],
                    'petrol_type_id'=> (int)$_POST['petrol_type_id'][$i],
                    'rec_litros'    => (float)$_POST['recibido_l'][$i],
                    'costo_u'       => (float)$_POST['costo_u'][$i],
                ];
            }
            $reciboId = $this->model->createReceipt((int)$ordenId, $data, $items, (int)$_SESSION['user_id']);
            echo json_encode(['status'=>'success','msg'=>'Recibido confirmado','recibo_id'=>$reciboId]);
        }catch(\Throwable $e){
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }
    }

    public function printOrder($id){
    $id = (int)$id;
    $h  = $this->model->getOrderHeader($id);
    $it = $this->model->getOrderItems($id);

    // Render sin layout para que salga documento limpio
    $this->render('purchases/print_order', ['h'=>$h,'items'=>$it], null, false);
}
}
