<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-05-25
 * Time: 12:42 PM
 */

namespace Angujo\Elocrud\Laravel;


use Illuminate\Console\Command;

class ConsoleCommands extends Command
{
    protected $signature   = 'elocrud:models 
                            {--c|connection=db : The connection to use}
                            {--d|database : The database and its schemas to run}
                            {--e|exclude=? : Exclude tables}
                            {tables? : The list of tables to work on}';
    protected $description = 'Parse database schema tables into models';

    public function __construct(Factory $factory) { parent::__construct(); }
}