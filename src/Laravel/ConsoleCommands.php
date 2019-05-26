<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-05-25
 * Time: 12:42 PM
 */

namespace Angujo\Elocrud\Laravel;


use Angujo\Elocrud\Models\Model;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConsoleCommands extends Command
{
    protected $signature   = 'elocrud:models 
                            {--c|connection=mysql : The connection to use}
                            {--d|database= : The database and its schemas to run}
                            {--e|exclude=* : Excluded tables}
                            {--f|force : Force overwriting models}
                            {tables?* : The list of tables to work on}';
    protected $description = 'Parse database schema tables into models';
    private   $elocrud;

    public function __construct(Factory $factory)
    {
        parent::__construct();
        $this->elocrud = $factory;
    }

    public function handle()
    {
        try {
            $this->elocrud->setConnection(DB::connection($this->option('connection')), config('database.connections.'.$this->option('connection').'.driver'));
            if ($db = $this->option('database')) {
                $this->elocrud->switchDB($db);
            }
            if ($ex = $this->option('exclude')) {
                $this->elocrud->exclude(is_array($ex) ? $ex : [$ex]);
            }
            if ($onl = $this->argument('tables')) {
                $this->elocrud->only(is_array($onl) ? $onl : [$onl]);
            }
            if ($this->option('force') && $this->confirm('Are you sure you wish to recreate the Models? (Any customized change will be lost)')) {
                $this->elocrud->overwrite($this->option('force'));
            }
            $this->elocrud->generate(function (Model $model) {
                $this->info($model->namespace.'\\'.$model->className.' generated successfully at '.date('H:i:s'));
            });
        } catch (\Throwable $exception) {
            $this->error($exception->getCode().'::'.$exception->getMessage());
            $this->error($exception->getTraceAsString());
        }
    }
}