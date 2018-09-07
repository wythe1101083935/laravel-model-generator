<?php
/**
 +----------------------------------------------------------
 * 根据已经存在的表创建模型
 +----------------------------------------------------------
 * TIME:2018-09-06 15:32:26
 +----------------------------------------------------------
 * author:wythe
 +----------------------------------------------------------
 */
namespace Wythe\LaravelCommand;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class MakeModels extends Command{
   /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:models 
    {--table= : Appoint the tables} 
    {--path= : Where the models maked} 
    {--database= : The tables from which database }
    {--ut|timestamps : Use the timestamps}
    {--created_at=created_at : Set create time field}
    {--updated_at=updated_at : Set update time field}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make models';

    /*要创建的表*/
    protected $tables = [];

    protected $prefix;

    /*存储的模型路径*/
    protected $path = '';

    /*命名空间*/
    protected $namespace = '';

    /*指定的数据库*/
    protected $database = [
        'laravel_name'=>'', //laravel框架中设置的数据库name
        'dbname'=>'', //连接后的数据库名
    ];
    
    /*保存数据库连接*/
    protected $db;//数据库执行句柄

    /*模型替换常量*/
    protected $modelProperty = 
        ['{{namespace}}','{{created_at}}','{{updated_at}}','{{tableName}}','{{timestamps}}','{{dateFormat}}','{{connection}}','{{primaryKey}}','{{incrementing}}','{{perPage}}','{{modelName}}'];

    /*当前创建的表名*/
    protected $currTableName;

    /*当前表信息*/
    protected $currTableInfo;

    /*当前创建的模型名称*/
    protected $currModelName;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*连接数据库*/
        $this->dbConnection();
        /*获取表名*/
        $this->getTables();
        /*设置模型文件目录*/
        $this->setDir();
        /*创建模型*/
        $this->makeModel();
    }

    /*连接数据库*/
    protected function dbConnection(){
        $this->database['laravel_name'] = $this->option('database') ? : config('database.default');
        $this->db = DB::connection($this->database['laravel_name']);
        $this->database['dbname'] = config('database.connections.'.$this->database['laravel_name'].'.database');
    }

    /**
     * 获取表格
     *
     * @return mixed
     */
    protected function getTables(){
        if(!$this->option('table')){
            $this->tables = $this->db
            ->table('information_schema.tables')
            ->where('table_type','=','BASE TABLE')
            ->where('table_schema','=',$this->database['dbname'])
            ->pluck('table_name');
        }
    }

    /*设置目录*/
    protected function setDir(){
        if(!$this->option('path')){
            $this->path = str_replace('/',DIRECTORY_SEPARATOR,'app/Models');
            $this->namespace = 'App\\Models';
        }else{
            $this->path = str_replace('/',DIRECTORY_SEPARATOR,$this->option('path'));
            $this->namespace = ucfirst(trim(str_replace(DIRECTORY_SEPARATOR,'\\',trim($this->path,DIRECTORY_SEPARATOR.'.'))));
        }
        if(!is_dir($this->path)){
            mkdir($this->path);
        }
    }


    /*创建模型*/
    protected function makeModel(){
        $class = file_get_contents(__DIR__.str_replace('/',DIRECTORY_SEPARATOR,'/stubs/model.stub'));
        foreach ($this->tables as $value) {
            /*设置当前创建的模型表名*/
            $this->currTableName = $value;
            /*获取表信息*/
            $this->getTableInfo();
            /*获取模型内容*/
            $newClass = str_replace($this->modelProperty,$this->getProperty(),$class);
            /*创建模型文件*/
            file_put_contents($this->path.DIRECTORY_SEPARATOR.$this->currModelName.'.php',$newClass);

            $this->info('table ['.$value.'] ====> model ['.$this->currModelName.']');
        }
    }

    /*获取属性*/
    protected function getProperty(){
        $property = [];
        foreach ($this->modelProperty as $method) {
            $method = trim($method,'{}');
            $property[] = call_user_func_array([$this,$method],[]);
        }
        return $property;
    }
    /*获取表信息*/
    protected function getTableInfo(){
        $this->currTableInfo['primaryKey'] = $this->db->table('information_schema.COLUMNS')
        ->where('TABLE_SCHEMA','=',$this->database['dbname'])
        ->where('TABLE_NAME','=',$this->currTableName)
        ->where('COLUMN_KEY','=','PRI')
        ->first();        
    }
    /*prefix*/
    protected function setPrefix($tableName){
        $allName = explode('_',$tableName);   
        $this->prefix =  $allName[0];             
    }

    /*namespace*/
    protected function namespace(){
       return $this->namespace;
    }
    /*created_at*/
    protected function created_at(){
        return $this->option('created_at');
    }
    /*updated_at*/
    protected function updated_at(){
        return $this->option('updated_at');
    }
    /*tableName*/
    protected function tableName(){
        return $this->currTableName;
    }
    /*timestamps*/
    protected function timestamps(){
        return $this->option('timestamps') ? 'true' : 'false';
    }
    /*dateFormat*/
    protected function dateFormat(){
        return 'U';
    }
    /*connection*/
    protected function connection(){
        return $this->database['laravel_name'];
    }
    /*primaryKey*/
    protected function primaryKey(){
        return $this->currTableInfo['primaryKey'] ? $this->currTableInfo['primaryKey']->COLUMN_NAME: '';
    }
    /*incrementing*/
    protected function incrementing(){
        return ($this->currTableInfo['primaryKey'] ? $this->currTableInfo['primaryKey']->EXTRA : '') == 'auto_increment' ? 'true' : 'false';
    }
    /*perPage*/
    protected function perPage(){
        return 15;
    }

    /*modelName*/
    protected function modelName(){
        $allName = explode('_',$this->currTableName);
        array_shift($allName);
        $modelName = '';
        foreach ($allName as $val) {
            $modelName .= ucfirst($val);       
        }
        $this->currModelName = $modelName;
        return $modelName;          
    }
}