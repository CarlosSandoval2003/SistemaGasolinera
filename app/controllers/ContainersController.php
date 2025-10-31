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
            $isAjax = (
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            );
            if ($isAjax) { http_response_code(403); echo 'Forbidden'; exit; }
            header("Location: " . BASE_URL . "/home/index");
            exit;
        }
    }

    public function index() {
        $this->guardMaintenance();
        $q      = isset($_GET['q']) ? trim($_GET['q']) : '';
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;

        $containers = $this->container->listFiltered($q, $status);
        $this->render('containers/index', [
            'containers'=>$containers,
            'q'=>$q,
            'status'=>$status,
            'page'=>'containers',
            'title'=>'Contenedores'
        ]);
    }

    public function manage($id = null) {
        $this->guardMaintenance();
        if (!$id && isset($_GET['id'])) $id = (int)$_GET['id'];
        $item  = $id ? $this->container->find($id) : null;
        $types = $this->container->petrolTypesActive();
        $this->render('containers/manage', ['item'=>$item,'types'=>$types], null, false);
    }

    public function save() {
        $this->guardMaintenance();
        header('Content-Type: application/json');

        $name   = trim($_POST['name'] ?? '');
        $typeId = (int)($_POST['petrol_type_id'] ?? 0);
        $capL   = (float)($_POST['capacity_liters'] ?? 0);
        $minL   = (float)($_POST['min_level_liters'] ?? 0);
        $status = (int)($_POST['status'] ?? 1);
        $isDef  = !empty($_POST['is_default']) ? 1 : 0;

        // Validaciones básicas
        if ($name === '') { echo json_encode(['status'=>'error','msg'=>'El nombre es requerido.']); return; }
        if (mb_strlen($name) > 120) { echo json_encode(['status'=>'error','msg'=>'Nombre demasiado largo (máx 120).']); return; }
        if ($typeId <= 0) { echo json_encode(['status'=>'error','msg'=>'Selecciona el tipo de combustible.']); return; }
        if ($capL <= 0) { echo json_encode(['status'=>'error','msg'=>'La capacidad debe ser mayor a 0.']); return; }
        if ($minL < 0)  { echo json_encode(['status'=>'error','msg'=>'El mínimo no puede ser negativo.']); return; }
        if ($minL > $capL) { echo json_encode(['status'=>'error','msg'=>'El mínimo no puede ser mayor que la capacidad.']); return; }
        if (!in_array($status, [0,1], true)) { echo json_encode(['status'=>'error','msg'=>'Estado inválido.']); return; }

        $data = [
            'name'              => $name,
            'petrol_type_id'    => $typeId,
            'capacity_liters'   => $capL,
            'min_level_liters'  => $minL,
            'is_default'        => $isDef,
            'status'            => $status,
        ];

        try {
            $ok = !empty($_POST['id'])
                ? $this->container->update((int)$_POST['id'], $data)
                : $this->container->create($data);

            echo json_encode(['status'=>$ok?'success':'error','msg'=>$ok?'Contenedor guardado.':'No se pudo guardar.']);
        } catch(\Throwable $e) {
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }
    }

    public function delete() {
        $this->guardMaintenance();
        header('Content-Type: application/json');
        $ok = $this->container->delete((int)($_POST['id'] ?? 0));
        echo json_encode(['status'=>$ok?'success':'error',
                          'msg'=>$ok?'Contenedor eliminado.':'No se pudo eliminar.']);
    }

    public function movements($id) {
        $this->guardMaintenance();
        $from = $_GET['date_from'] ?? null;
        $to   = $_GET['date_to']   ?? null;
        $item = $this->container->find((int)$id);
        $rows = $this->container->movements((int)$id, $from, $to);
        $this->render('containers/movements', ['item'=>$item,'rows'=>$rows], null, false);
    }

    public function transfer() {
        $this->guardMaintenance();
        if ($_SERVER['REQUEST_METHOD']==='GET') {
            $types = $this->container->petrolTypesActive();
            $all   = $this->container->all();
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

        // Validaciones rápidas del controller
        if ($from <= 0 || $to <= 0 || $type <= 0) {
            echo json_encode(['status'=>'error','msg'=>'Selecciona combustible y contenedores.']); return;
        }
        if ($from === $to) {
            echo json_encode(['status'=>'error','msg'=>'No puedes transferir al mismo contenedor.']); return;
        }
        if ($lit <= 0) {
            echo json_encode(['status'=>'error','msg'=>'Los litros deben ser > 0.']); return;
        }

        try {
            $res = $this->inventory->transfer($from,$to,$type,$lit,$note,$uid);
            // $res puede ser bool o array(['ok'=>true,'warning'=>'...'])
            if (is_array($res) && !empty($res['ok'])) {
                echo json_encode(['status'=>'success','msg'=> !empty($res['warning']) ? $res['warning'] : 'Transferencia realizada']);
            } elseif ($res === true) {
                echo json_encode(['status'=>'success','msg'=>'Transferencia realizada']);
            } else {
                echo json_encode(['status'=>'error','msg'=>'Error en transferencia']);
            }
        } catch(\Throwable $e) {
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }
    }
}
