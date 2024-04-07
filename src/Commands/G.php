<?php

namespace Gmxiaoli\Curd\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;


class G extends Command {
    protected $signature = 'gmxiaoli:g
    {--del_module= : delete module name}
    {--module= : create module name}
    {--only= : only create}
    {--force : Overwrite any existing files}';

    protected $description = 'laravel-curd create command';

    private $connection;

    private $duan;

    private $tableName;

    private $className;

    private $basePath;

    const DS = DIRECTORY_SEPARATOR;

    private $fileDescription = '';

    public function __construct() {
        parent::__construct();
        $this->basePath = base_path();
    }

    public function handle() {
        if ($this->option('del_module')) {
            $this->warn("暂不可用删除功能，请手动删除文件");
            return;
            $this->delModule($this->option('del_module'));
            return;
        }

        $duan = $this->ask('请输入端(Manager/Store/App):', 'Manager');
        if (empty($duan)) {
            $duan = 'Manager';
        }
        if (!in_array($duan, ['Manager', 'Store', 'App'])) {
            $this->warn("输入有误");
            exit;
        }

        $this->duan = $duan;

        $this->info('您将为'.$duan.'端创建文件');

        $className = $this->option('module');
        if (empty($className)) {
            $className = $this->ask('请输入要生成的类名:', '');
        }

        if (empty($className)) {
            $this->warn("要生成的类名不能为空");
            exit;
        }
        $this->className = ucfirst($className);

        $this->question('要生成的类名是:' . $this->className);

        $this->connection = "mysql";

        $tableName = $this->ask('请输入类对应的表名(可以为空):', '');

        if (empty($tableName)) {
            $tableName = $className;
        }

        $this->tableName = $tableName = Str::snake($tableName);

        $this->info('Model关联的表是:' . $tableName);

        $only = $this->option('only');

        if (empty($only)) {
            $this->createController();
            $this->createLogic();
            $this->createService();
            $this->createModel();
        } else {
            switch ($only) {
                case 'controller':
                    $this->createController();
                    break;
                case 'model':
                    $this->createModel();
                    break;
                case 'logic':
                    $this->createLogic();
                    break;
                case 'service':
                    $this->createService();
                    break;
                default :
                    $this->alert('命令输入错误');
            }
            return;
        }
    }

    private function createController() {

        $controllerStub = <<<'TOT'
<?php

namespace --namespace--;

use Illuminate\Http\Request;
use AllowDynamicProperties;
use App\Exceptions\BusinessException;
use App\Utils\PageUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Logics\--controllerName--Logic;

#[AllowDynamicProperties] class --controllerName--Controller extends --CommonController--{
    public function __construct() {
        $this->--lowercasecontrollerName--Logic = app(--controllerName--Logic::class);
    }

    public function create(Request $request): JsonResponse {
        $request->validate(["--lowercasecontrollerName--_create"]);
        $fields = ['', ''];
        foreach ($fields as $field) {
            $where[$field] = $request->input($field);
        }
        $this->--lowercasecontrollerName--Logic->create($where);
        return $this->success();
    }

    public function delete(Request $request): JsonResponse {
        $request->validate(["--lowercasecontrollerName--_delete"]);
        $id = $request->input('id');
        $this->--lowercasecontrollerName--Logic->delete($id);
        return $this->success();
    }

    public function list(Request $request): JsonResponse {
        $request->validate(["--lowercasecontrollerName--_list"]);
        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $fields    = ['start_time', 'end_time'];
        foreach ($fields as $field) {
            $filters[$field] = $request->input($field);
        }
        $res  = $this->--lowercasecontrollerName--Logic->list($filters, $page, $page_size);
        $data = PageUtil::generate($page, $page_size, $res->total(), --controllerName--ListResource::collection($res->items()));
        return $this->success($data);
    }
}

TOT;
        if ($this->duan == "Store") {
            $controllerPath   = base_path('app/Http/Controllers/Admin/Store');
            $commonController = "StoreCommonController";
        }
        if ($this->duan == "Manager") {
            $controllerPath   = base_path('app/Http/Controllers/Admin/Manager');
            $commonController = "ManagerCommonController";
        }
        if ($this->duan == "App") {
            $controllerPath   = base_path('app/Http/Controllers/Api');
            $commonController = "ApiCommonController";
        }
        $controllerPath = base_path('app') . str_replace(base_path('app'), '', $controllerPath);

        $currentNameSpace = $this->calculationNameSpace($controllerPath);

        $content = str_replace([
            '--controllerName--',
            '--lowercasecontrollerName--',
            '--namespace--',
            '--CommonController--',
        ],
            [$this->className, lcfirst($this->className), $currentNameSpace, $commonController], $controllerStub);

        $controllerFile = $controllerPath . self::DS . $this->className . 'Controller.php';
        $createFlag     = true;

        if (file_exists($controllerFile) && !$this->option('force')) {
            $createFlag = $this->confirm($this->className . 'Controller.php' . ' 文件已存在，是否替换');
        }

        if (!$createFlag) {
            return false;
        }
        $this->createDir($controllerFile);
        $fileSize = file_put_contents($controllerFile, $content);

        $this->info($controllerFile . ' 文件创建成功' . $fileSize);
    }


    private function createLogic() {
        $logicStub = <<<'TOT'
<?php
namespace App\Logics;

use AllowDynamicProperties;
use App\Services\--logicName--Service;

#[AllowDynamicProperties] class --logicName--Logic {
    public function __construct() {
        $this->--lowercaseLogicName--Service = app(--logicName--Service::class);
    }

    public function list($filters, $page, $page_size){
        return $this->--lowercaseLogicName--Service->list($filters, $page, $page_size);
    }
}

TOT;
        $logicPath = base_path('app/Logics');
        $logicPath = base_path('app') . str_replace(base_path('app'), '', $logicPath);

        $currentNameSpace = $this->calculationNameSpace($logicPath);

        $content = str_replace([
            '--logicName--',
            '--lowercaseLogicName--',
        ],
            [$this->className, lcfirst($this->className)], $logicStub);

        $createFile = $logicPath . self::DS . $this->className . 'Logic.php';
        $createFlag = true;

        if (file_exists($createFile) && !$this->option('force')) {
            $createFlag = $this->confirm($this->className . 'Logic.php' . ' 文件已存在，是否替换');
        }

        if (!$createFlag) {
            return false;
        }
        $this->createDir($createFile);
        $fileSize = file_put_contents($createFile, $content);

        $this->info($createFile . ' 文件创建成功' . $fileSize);
    }


    private function createService() {
        $serviceStub = <<<'TOT'
<?php
namespace App\Services;

use App\Constants\Constant;
use App\Models\--serviceName--Model;

class --serviceName--Service {
    public function list(array $filters, int $page, int $page_size){
        return --serviceName--Model::whereIsDel(Constant::UNDELETED)
            ->filter($filters)->paginate($page_size, '*', 'page', $page);
    }
}

TOT;
        $servicePath = base_path('app/Services');
        $servicePath = base_path('app') . str_replace(base_path('app'), '', $servicePath);

        $content = str_replace([
            '--serviceName--',
            '--lowercaseServiceName--',
        ],
            [$this->className, lcfirst($this->className)], $serviceStub);

        $createFile = $servicePath . self::DS . $this->className . 'Service.php';
        $createFlag = true;

        if (file_exists($createFile) && !$this->option('force')) {
            $createFlag = $this->confirm($this->className . 'Service.php' . ' 文件已存在，是否替换');
        }

        if (!$createFlag) {
            return false;
        }
        $this->createDir($createFile);
        $fileSize = file_put_contents($createFile, $content);

        $this->info($createFile . ' 文件创建成功' . $fileSize);
    }


    private function createModel() {
        $modelSub  = <<<'TOT'
<?php

namespace --namespace--;

use App\Models\Traits\DateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Eloquent;

/**
 * Class --modelName--Model
 * @date --datetime--
 */
class --modelName--Model extends BaseModel
{
    use HasFactory, DateTimeFormatter;

    protected $table='--tableName--';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public static function scopeFilter($query, $filters = []) {
        return $query;
    }

}

TOT;
        $modelPath = base_path('app/Models');
        $modelPath = base_path('app') . str_replace(base_path('app'), '', $modelPath);

        $currentNameSpace = $this->calculationNameSpace($modelPath);

        $content = str_replace([
            '--modelName--',
            '--datetime--',
            '--namespace--',
            '--tableName--',
        ],
            [
                $this->className,
                date("Y-m-d H:i:s"),
                $currentNameSpace,
                $this->tableName,
            ], $modelSub);

        $createFile = $modelPath . self::DS . $this->className . 'Model.php';
        $createFlag = true;

        if (file_exists($createFile) && !$this->option('force')) {
            $createFlag = $this->confirm($this->className . 'Model.php' . ' 文件已存在，是否替换');
        }

        if (!$createFlag) {
            return false;
        }

        $this->createDir($createFile);
        $fileSize = file_put_contents($createFile, $content);

        $this->info($createFile . ' 文件创建成功' . $fileSize);
        $this->warn('如需生成model注释请执行');
        $this->warn( 'php artisan ide-helper:models "App\\Models\\'.$this->className . 'Model"');
    }


    private function createDir($createFile) {
        $path = dirname($createFile);

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function calculationNameSpace($path) {
        //计算根目录 app path
        $basePath = base_path('app');
        $tempPath = str_replace(['/', '\\'], '\\', str_replace($basePath, '', $path));
        return "App" . "\\" . trim($tempPath, '\\');
    }

    private function delModule($module) {

        $path = config('songyz_scaffold.controller_path');
        $path = base_path('app') . str_replace(base_path('app'), '', $path);
        $file = $path . self::DS . ucfirst($module) . 'Controller.php';
        file_exists($file) && unlink($file) && $this->info($file . ' 删除成功');

        $path = config('songyz_scaffold.service_path');
        $path = base_path('app') . str_replace(base_path('app'), '', $path);
        $file = $path . self::DS . ucfirst($module) . 'Service.php';
        file_exists($file) && unlink($file) && $this->info($file . ' 删除成功');

        $path = config('songyz_scaffold.manager_path');
        $path = base_path('app') . str_replace(base_path('app'), '', $path);
        $file = $path . self::DS . ucfirst($module) . 'Manager.php';
        file_exists($file) && unlink($file) && $this->info($file . ' 删除成功');

        $path = config('songyz_scaffold.model_path');
        $path = base_path('app') . str_replace(base_path('app'), '', $path);
        $file = $path . self::DS . ucfirst($module) . 'Model.php';
        file_exists($file) && unlink($file) && $this->info($file . ' 删除成功');

        $this->info($this->option('del_module') . '模块删除成功');
    }

}
