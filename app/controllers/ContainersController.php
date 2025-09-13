<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Container;
use App\Models\Inventory;

class ContainersController extends Controller {
    private $container;
    private $inventory;

    public function __construct() {
        $this->container = new Container();
        $this->inventory = new Inventory();
    }

    /** Admin(1), Mantenimiento(2), Abastecimiento(5) */
    private function guardMaintenance() {
        if (session_status()===PHP_SESSION_NONE) session_start();
        $allowed = [1,2,5];
        $type = (int)($_SESSION['type'] ?? 0);

        if (!isset($_SESSION['user_id']) || !in_array($type, $allowed, true)) {
            // Si es AJAX, responde 403 para que no “inyecte” la home en el modal
            $isAjax = (
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            );
            if ($isAjax) {
                http_response_code(403);
                echo 'Forbidden';
                exit;
            }
            header("Location: index.php?url=home/index");
            exit;
        }
    }

    public function index() {
        $this->guardMaintenance();
        $containers = $this->container->all();
        $this->render('containers/index', [
            'containers'=>$containers,
            'page'=>'maintenance',
            'title'=>'Contenedores'
        ]);
    }

    public function manage($id = null) {
        $this->guardMaintenance();
        if (!$id && isset($_GET['id'])) $id = (int)$_GET['id'];
        $item = $id ? $this->container->find($id) : null;
        $types = $this->container->petrolTypesActive();
        // Modal => sin layout
        $this->render('containers/manage', ['item'=>$item,'types'=>$types], null, false);
    }

    public function save() {
        $this->guardMaintenance();
        header('Content-Type: application/json');
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'petrol_type_id' => (int)($_POST['petrol_type_id'] ?? 0),
            'capacity_liters' => (float)($_POST['capacity_liters'] ?? 0),
            'min_level_liters' => (float)($_POST['min_level_liters'] ?? 0),
            'is_default' => !empty($_POST['is_default']) ? 1 : 0,
            'status' => (int)($_POST['status'] ?? 1),
        ];
        try {
            $ok = !empty($_POST['id'])
                ? $this->container->update((int)$_POST['id'], $data)
                : $this->container->create($data);
            echo json_encode(['status'=>$ok?'success':'error','msg'=>$ok?'Guardado':'No se pudo guardar']);
        } catch(\Throwable $e) {
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }
    }

    public function delete() {
        $this->guardMaintenance();
        header('Content-Type: application/json');
        $ok = $this->container->delete((int)($_POST['id'] ?? 0));
        echo json_encode(['status'=>$ok?'success':'error']);
    }

    public function movements($id) {
        $this->guardMaintenance();
        $from = $_GET['date_from'] ?? null;
        $to   = $_GET['date_to'] ?? null;
        $item = $this->container->find((int)$id);
        $rows = $this->container->movements((int)$id, $from, $to);
        // Modal => sin layout
        $this->render('containers/movements', ['item'=>$item,'rows'=>$rows], null, false);
    }

    public function transfer() {
        $this->guardMaintenance();
        if ($_SERVER['REQUEST_METHOD']==='GET') {
            $types = $this->container->petrolTypesActive();
            $all = $this->container->all();
            // Modal => sin layout
            $this->render('containers/transfer', ['types'=>$types,'containers'=>$all], null, false);
            return;
        }
        header('Content-Type: application/json');
        $from  = (int)($_POST['from_container_id'] ?? 0);
        $to    = (int)($_POST['to_container_id'] ?? 0);
        $type  = (int)($_POST['petrol_type_id'] ?? 0);
        $lit   = (float)($_POST['qty_liters'] ?? 0);
        $note  = trim($_POST['note'] ?? '');
        $uid   = $_SESSION['user_id'] ?? null;
        try {
            $ok = $this->inventory->transfer($from,$to,$type,$lit,$note,$uid);
            echo json_encode(['status'=>$ok?'success':'error','msg'=>$ok?'Transferencia realizada':'Error en transferencia']);
        } catch(\Throwable $e) {
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }
    }
}
