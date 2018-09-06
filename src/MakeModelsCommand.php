<?php
namespace Wythe\Generator;
use Illuminate\Console\Command;
class MakeModelsCommand extends Command{
   /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wythe:file {action} {folder=0} {fileext=0} {filename=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'handle file tool';

    protected $fileIndex = 1;
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
        $action = $this->argument('action');
        if(is_callable([$this,$action])){
            call_user_func_array([$this,$action],[]);
        }else{
            $this->error('The action' .$action .' option does not exist');
        }
    }



    /*修改文件名*/
    public function rename(){
        /*必须传入文件夹才能修改*/
        if(!is_dir($this->argument('folder'))){
            $this->error('This path is not a folder');
            return;
        }
        $this->coreRename($this->argument('folder'));
    }

    protected function coreRename($folder){
        if(is_dir($folder)){
            $dir = opendir($folder);
            while(($file  = readdir($dir)) !== false){
                if($file != '.' && $file != '..'){
                    $this->coreRename($file);
                }
            }
            closedir($dir);
        }elseif(is_file($this->argument('folder').DIRECTORY_SEPARATOR.$folder)){
            $this->fileRename($this->argument('folder').DIRECTORY_SEPARATOR.$folder);
        }else{
            $this->comment($folder.' is ignored'); 
        }
    }

    protected function fileRename($oldfile){
        $path = pathinfo($oldfile,PATHINFO_DIRNAME);
        $oldFilename = pathinfo($oldfile,PATHINFO_FILENAME);
        $oldFileext = pathinfo($oldfile,PATHINFO_EXTENSION);
        $filename = '';
        /*是否修改文件名*/
        if($this->argument('filename')){
            /*暂时没有想到解决传入一个有序的数的答案*/
           // if(is_callable($this->argument('filenameOrder'))){

           // }else{
                $filename .= $this->argument('filename') . $this->createIndex(); 
           // }               
        }else{
            $filename .= $oldFilename;
        }
        /*是否修改文件后缀*/
        if($this->argument('fileext')){
            $filename .= '.'.$this->argument('fileext');
        }else{
            $filename .= '.'.$oldFileext;
        }
        rename($oldfile,$path.DIRECTORY_SEPARATOR.$filename);
        $this->info($oldfile . '===>' . $path.DIRECTORY_SEPARATOR.$filename);
    }

    protected function createIndex(){
        $index = sprintf("%05d", $this->fileIndex);
        $this->fileIndex += 1;
        return $index;
    }	
}