<?php

namespace Gmxiaoli\Curd\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
            $this->createRequest();
            $this->createLogic();
            $this->createService();
            $this->createModel();
        } else {
            switch ($only) {
                case 'controller':
                    $this->createController();
                    break;
                case 'request':
                    $this->createRequest();
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
use App\Http\Requests\--filePath--\--controllerName--Request;

use App\Http\Requests\PublicRequest;
use AllowDynamicProperties;
use App\Exceptions\BusinessException;
use App\Utils\PageUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Logics\--controllerName--Logic;

#[AllowDynamicProperties] class --controllerName--Controller extends --CommonController--{
    public function __construct() {
        $this->--lowercasecontrollerName--Logic = app(--controllerName--Logic::class);
    }

    /**
     * 创建
     * @datetime --datetime--
     */
    public function create(--controllerName--Request $request): JsonResponse {
        $request->validate(["--lowercasecontrollerName--_create"]);
        $fields = ['', ''];
        foreach ($fields as $field) {
            $where[$field] = $request->input($field);
        }
        $this->--lowercasecontrollerName--Logic->create($where);
        return $this->success();
    }


    /**
     * 开关
     * @datetime --datetime--
     */
    public function switch(--controllerName--Request $request): JsonResponse {
        $request->validate(["--lowercasecontrollerName--_switch"]);
        $id = $request->input('id');
        $status = $request->input('status');
        $this->--lowercasecontrollerName--Logic->switch($id,$status);
        return $this->success();
    }

    /**
     * 删除
     * @datetime --datetime--
     */
    public function delete(--controllerName--Request $request): JsonResponse {
        $request->validate(["--lowercasecontrollerName--_delete"]);
        $id = $request->input('id');
        $this->--lowercasecontrollerName--Logic->delete($id);
        return $this->success();
    }

     /**
     * 修改
     * @datetime --datetime--
     */
    public function edit(--controllerName--Request $request): JsonResponse {
        $request->validate(['--lowercasecontrollerName--_edit']);
        $fields = ['', ''];
        foreach ($fields as $field) {
            $data[$field] = $request->input($field);
        }
        $id = $request->input('id');
        $this->--lowercasecontrollerName--Logic->edit($id, $data);
        return $this->success();
    }

    /**
     * 列表
     * @datetime --datetime--
     */
    public function list(--controllerName--Request $request): JsonResponse {
        $request->validate(["--lowercasecontrollerName--_list"]);
        $page = $request->input('page');
        $page_size = $request->input('page_size');
        $fields = ['start_time', 'end_time'];
        foreach ($fields as $field) {
            $filters[$field] = $request->input($field);
        }
        $res  = $this->--lowercasecontrollerName--Logic->list($filters, $page, $page_size);
        $data = PageUtil::generate($page, $page_size, $res->total(), --controllerName--ListResource::collection($res->items()));
        return $this->success($data);
    }

    /**
     * 详情
     * @datetime --datetime--
     */
    public function detail(--controllerName--Request $request): JsonResponse {
        $request->validate(['--lowercasecontrollerName--_detail']);
        $id = $request->input('id');
        $res = $this->--lowercasecontrollerName--Logic->detail($id);
        return $this->success($res);
    }
}

TOT;
        if ($this->duan == "Store") {
            $filePath   = 'Admin\Store';
            $controllerPath   = base_path('app/Http/Controllers/Admin/Store');
            $commonController = "StoreCommonController";
        }
        if ($this->duan == "Manager") {
            $filePath   = 'Admin\Manager';
            $controllerPath   = base_path('app/Http/Controllers/Admin/Manager');
            $commonController = "ManagerCommonController";
        }
        if ($this->duan == "App") {
            $filePath   ='Api';
            $controllerPath   = base_path('app/Http/Controllers/Api');
            $commonController = "ApiCommonController";
        }
        $controllerPath = base_path('app') . str_replace(base_path('app'), '', $controllerPath);

        $currentNameSpace = $this->calculationNameSpace($controllerPath);

        $content = str_replace([
            '--filePath--',
            '--controllerName--',
            '--lowercasecontrollerName--',
            '--namespace--',
            '--CommonController--',
            '--datetime--'
        ],
            [$filePath,$this->className, lcfirst($this->className), $currentNameSpace, $commonController,date("Y-m-d H:i:s")], $controllerStub);

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

    private function createRequest() {

        $requestStub = <<<'TOT'
<?php

namespace --namespace--;
use App\Http\Requests\ScenesBaseRequest;

class --requestName--Request extends ScenesBaseRequest{
//规则参考：用后删除～
//    'required' => '验证的字段必须存在于输入数据中，但不可以为空',
//                  //以下情况视为空：1.该值为null,2.空字符串,3.空数组或空的可数对象,4.没有路径的上传文件
//    'accepted' => '必须为yes,on,1,true',
//    'active_url' => '是否是一个合法的url,基于PHP的checkdnsrr函数，因此也可以用来验证邮箱地址是否存在',
//    'after:date' => '验证字段必须是给定日期后的值，比如required|date|after:tomorrow,通过PHP函数strtotime来验证',
//    'after_or_equal:date' => '大于等于',
//    'alpha' => '验证字段必须全是字母',
//    'alpha_dash' => '验证字段可能具有字母、数字、破折号、下划线',
//    'alpha_num' => '验证字段必须全是字母和数字',
//    'array' => '数组',
//    'before:date' => '小于',
//    'before_or_equal:date' => '小于等于',
//    'between:min,max' => '给定大小在min,max之间,字符串，数字，数组或者文件大小都用size函数评估',
//    'boolean' => '必须为能转化为布尔值的参数，比如：true,false,1,0,"1","0"',
//    'confirmed' => '字段必须与foo_confirmation字段值一致，比如，要验证的是password,输入中必须存在匹配的password_confirmation字段',
//    'date' => '通过strtotime校验的有效日期',
//    'date_equals:date' => '等于',
//    'date_format:format' => 'date和date_format不应该同时使用，按指定时间格式传值',
//    'different:field' => '验证的字段值必须与字段field的值相同',
//    'digits:value' => '必须是数字，并且有确切的值',
//    'digits_between:min,max' => '字段长度必须在min,max之间',
//    'dimensions' => '验证的文件是图片并且图片比例必须符合规则,比如dimensions:min_width=100,min_height=200,可用
//                    的规则有min_width,max_width,min_height,max_height,width,height,ratio',
//    'distinct' => '无重复值',
//    'email' => '符合e-mail地址格式',
//    'exists:table,column' => '必须存在于指定的数据库表中',
//    'file' => '成功上传的文件',
//    'filled' => '验证的字段存在时不能为空',
//    'image' => '验证的文件必须是图像，jpeg,png,bmp,gif,svg',
//    'in:foo,bar,...' => '验证的字段必须包含在给定的值列表中',
//    'in_array:anotherfield' => '验证的字段必须存在于另一个字段的值中',
//    'integer' => '整数',
//    'ip' => 'ip地址',
//    'ipv4' => 'ipv4地址',
//    'ipv6' => 'ipv6地址',
//    'json' => 'json字符串',
//    'max:value' => '大于',
//    'mimetypes:text/plain,...' => '验证的文件必须与给定的MIME类型匹配',
//    'mimes:foo,bar,...' => '验证的文件必须具有列出的其中一个扩展名对应的MIME类型',
//    'min:value' => '小于',
//    'nullable' => '可为null,可以包含空值的字符串和整数',
//    'not_in:foo,bar...' => '不包含',
//    'numeric' => '必须为数字',
//    'present' => '验证的字段必须存在于输入数据中，但可以为空',
//    'regex:pattern' => '验证的字段必须与给定正则表达式匹配',
//
//    'required_if:anotherfield,value,...' => '如果指定的anotherfield等于value时，被验证的字段必须存在且不为空',
//    'required_unless:anotherfield,value,...' => '如果指定的anotherfield等于value时，被验证的字段不必存在',
//    'required_with:foo,bar,...' => '只要指定的其它字段中有任意一个字段存在，被验证的字段就必须存在且不为空',
//    'required_with_all:foo,bar,...' => '当指定的其它字段必须全部存在时，被验证的字段才必须存在且不为空',
//    'required_without_all:foo,bar,...' => '当指定的其它字段必须全部不存在时，被验证的字段必须存在且不为空',
//    'required_without:foo,bar,...' => '当指定的其它字段有一个字段不存在，被验证的字段就必须存在且不为空',
//    'same:field' => '给定字段必须与验证字段匹配',
//    'size:value' => '验证字段必须具有与给定值匹配的大小，对字符串，value对应字符数；对数字，对应给定的
//                    整数值；对数组，对应count值；对文件，是文件大小（kb）',
//    'timezone' => '验证字段是有效的时区标识符，根据PHP函数timezone_identifiers_list判断',
//    'unique:table,column,except,idColumn' => '验证字段必须是数据库中唯一的',
//    'url' => '有效的url',
        public function rules(): array {
        return --rules--;
    }

    public function messages(): array {
        return [
            '*.required' => '缺少必要参数',
        ];
    }

    public $scenes = [
        '--lowercaseRequestName--_create' => ['', ''],
        '--lowercaseRequestName--_delete' => ['', ''],
        '--lowercaseRequestName--_switch' => ['', ''],
        '--lowercaseRequestName--_edit' => ['', ''],
        '--lowercaseRequestName--_list' => ['', ''],
        '--lowercaseRequestName--_detail' => ['', ''],
    ];
}

TOT;
        if ($this->duan == "Store") {
            $requestPath   = base_path('app/Http/Requests/Admin/Store');
        }
        if ($this->duan == "Manager") {
            $requestPath   = base_path('app/Http/Requests/Admin/Manager');
        }
        if ($this->duan == "App") {
            $requestPath   = base_path('app/Http/Requests/Api');
        }
        $requestPath = base_path('app') . str_replace(base_path('app'), '', $requestPath);

        $currentNameSpace = $this->calculationNameSpace($requestPath);

        $content = str_replace([
            '--requestName--',
            '--lowercaseRequestName--',
            '--namespace--',
            '--datetime--',
            '--rules--'
        ],
            [$this->className, lcfirst($this->className), $currentNameSpace,date("Y-m-d H:i:s"),var_export($this->generateRules('qsl_'.$this->tableName), true)], $requestStub);

        $requestFile = $requestPath . self::DS . $this->className . 'Request.php';
        $createFlag     = true;

        if (file_exists($requestFile) && !$this->option('force')) {
            $createFlag = $this->confirm($this->className . 'Request.php' . ' 文件已存在，是否替换');
        }

        if (!$createFlag) {
            return false;
        }
        $this->createDir($requestFile);
        $fileSize = file_put_contents($requestFile, $content);

        $this->info($requestFile . ' 文件创建成功' . $fileSize);
    }


    private function createLogic() {
        $logicStub = <<<'TOT'
<?php
namespace App\Logics;

use AllowDynamicProperties;
use App\Services\--logicName--Service;
use App\Exceptions\BusinessException;
use DB;
use Exception;

#[AllowDynamicProperties] class --logicName--Logic {
    public function __construct() {
        $this->--lowercaseLogicName--Service = app(--logicName--Service::class);
    }
     /**
     * 创建
     * @datetime --datetime--
     */
    public function create($data): bool {
        DB::beginTransaction();
        try {
            $this->--lowercaseLogicName--Service->create($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw new BusinessException($e);
        }
        return true;
    }

     /**
     * 开关
     * @datetime --datetime--
     */
     public function switch($id,$status): bool {
        try {
            $this->--lowercaseLogicName--Service->switch($id,$status);
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
            throw new BusinessException($e);
        }
        return true;
    }

    /**
     * 删除
     * @datetime --datetime--
     */
     public function delete($id): bool {
        try {
            $this->--lowercaseLogicName--Service->delete($id);
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
            throw new BusinessException($e);
        }
        return true;
    }



     /**
     * 修改
     * @datetime --datetime--
     */
     public function edit($id, $data): bool {
        $res = $this->--lowercaseLogicName--Service->edit($id, $data);
        if (!$res) {
            throw new BusinessException("修改失败");
        }
        return true;
    }

    /**
     * 列表
     * @datetime --datetime--
     */
    public function list($filters, $page, $page_size){
        return $this->--lowercaseLogicName--Service->list($filters, $page, $page_size);
    }

     /**
     * 详情
     * @datetime --datetime--
     */
    public function detail($id){
        return $this->--lowercaseLogicName--Service->detail($id);
    }
}

TOT;
        $logicPath = base_path('app/Logics');
        $logicPath = base_path('app') . str_replace(base_path('app'), '', $logicPath);

        $currentNameSpace = $this->calculationNameSpace($logicPath);

        $content = str_replace([
            '--logicName--',
            '--lowercaseLogicName--',
            '--datetime--'
        ],
            [$this->className, lcfirst($this->className),date("Y-m-d H:i:s")], $logicStub);

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

     /**
     * 创建
     * @datetime --datetime--
     */
    public function create(array $where): mixed {
        return --serviceName--Model::create($where)->id;
    }
     /**
     * 开关
     * @datetime --datetime--
     */
    public function switch(int $id,int $status): bool {
        $--lowercaseServiceName-- = --serviceName--Model::findOrFail($id);
        $--lowercaseServiceName--->status = $status;
        return $--lowercaseServiceName--->save();
    }
    /**
     * 删除
     * @datetime --datetime--
     */
    public function delete(int $id): bool {
        $--lowercaseServiceName-- = --serviceName--Model::findOrFail($id);
        $--lowercaseServiceName--->is_del = Constant::DELETED;
        return $--lowercaseServiceName--->save();
    }

     /**
     * 修改
     * @datetime --datetime--
     */
    public function edit($id,$data): bool {
        $--lowercaseServiceName-- = --serviceName--Model::findOrFail($id);
        return $--lowercaseServiceName--->fill($data)->save();
    }
    /**
     * 列表
     * @datetime --datetime--
     */
    public function list(array $filters, int $page, int $page_size){
        return --serviceName--Model::whereIsDel(Constant::UNDELETED)
            ->filter($filters)->paginate($page_size, '*', 'page', $page);
    }
     /**
     * 详情
     * @datetime --datetime--
     */
    public function detail(int $id){
        return --serviceName--Model::whereIsDel(Constant::UNDELETED)
            ->findOrFail($id);
    }
}

TOT;
        $servicePath = base_path('app/Services');
        $servicePath = base_path('app') . str_replace(base_path('app'), '', $servicePath);

        $content = str_replace([
            '--serviceName--',
            '--lowercaseServiceName--',
            '--datetime--'
        ],
            [$this->className, lcfirst($this->className),date("Y-m-d H:i:s")], $serviceStub);

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


    public function generateRules($tableName)
    {
        $columns = DB::select("SHOW COLUMNS FROM $tableName");
        $rules = [];
        foreach ($columns as $column) {
            switch ($column->Type) {
                case 'int':
                    $rule = 'integer';
                    break;
                case 'varchar':
                    $rule = 'string';
                    preg_match('/varchar\((\d+)\)/', $column->Type, $matches);
                    if (!empty($matches[1])) {
                        $rule .= '|max:' . $matches[1];
                    }
                    break;
                default:
                    $rule = 'nullable';
            }
            $isRequired = $column->Null === 'NO';
            if ($isRequired) {
                $rule = 'required|' . $rule;
            } else {
                $rule = 'nullable|' . $rule;
            }
            $rules[$column->Field] = $rule;
        }

        return $rules;
    }

}
