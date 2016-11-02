<?php

/**
 * An cli parser class
 * example:
 * --$cli = new CLI();
 * --$v = $cli->getCommandLineValues();
 * --var_dump($v);
 * @author yaoxiaofeng
 */
class CLI
{
    /**
     * An array of all values specified on the command line.
     *
     * @var array
     */
    protected $values = array();

    /**
     * An array of the current command line arguments we are processing.
     *
     * @var array
     */
    private $_cliArgs = array();

    public function getCommandLineValues()
    {
        /*if (empty($this->values) === false) {
            return $this->values;
        }*/

        $args = $_SERVER['argv'];
        array_shift($args);

        $this->setCommandLineValues($args);
        return $this->values;
    }

    /**
     * Set the command line values.
     *
     * @param array $args An array of command line arguments to process.
     *
     * @return void
     */
    public function setCommandLineValues($args)
    {
        if (empty($this->values) === true) {
            $this->values = $this->getDefaults();
        }

        $this->_cliArgs = $args;
        $numArgs        = count($args);
        // print_r($args);exit;
        for ($i = 0; $i < $numArgs; $i++) {
            $arg = $this->_cliArgs[$i];
            if ($arg === '') {
                continue;
            }
            if ($arg{0} === '-') {
                if ($arg === '-' || $arg === '--') {
                    // Empty argument, ignore it.
                    continue;
                }
                if ($arg{1} === '-') {
                    $this->processLongArgument(substr($arg, 2), $i);
                } else {
                    $switches = str_split($arg);
                    foreach ($switches as $switch) {
                        if ($switch === '-') {
                            continue;
                        }
                        $this->processShortArgument($switch, $i);
                    }
                }
            } else {
                $this->processUnknownArgument($arg, $i);
            }//end if
        }//end for

    }

    public function getDefaults()
    {
        // The default values for config settings.
        $defaults['env']            = 'production';
        $defaults['protocol']       = '';
        $defaults['logDir']         = '/tmp';
        $defaults['reactor']        = 8;
        $defaults['work']           = 8;
        $defaults['task']           = 8;
        $defaults['maxCon']         = 1024;
        $defaults['execScript']     = false;
        $defaults['debug']          = false;

        return $defaults;
    }

    /**
     * Processes a short (-e) command line argument.
     *
     * @param string $arg The command line argument.
     * @param int    $pos The position of the argument on the command line.
     *
     * @return void
     */
    public function processShortArgument($arg, $pos)
    {
        switch ($arg) {
        case 'h' :
        case '?' :
            $this->printUsage();
            exit(0);
            break;
        case 'd' :
            $this->values['debug'] = true;
            break;
        case 'e' :
            $this->values['execScript'] = true;
            break;
        default:
            $this->processUnknownArgument('-'.$arg, $pos);
        }//end switch

    }//end processShortArgument()


    /**
     * Processes a long (--example) command line argument.
     *
     * @param string $arg The command line argument.
     * @param int    $pos The position of the argument on the command line.
     *
     * @return void
     */
    public function processLongArgument($arg, $pos)
    {
        
        switch ($arg) {
        case 'help':
            $this->printUsage();
            exit(0);
        case 'version':
            echo 'yoho-im version 1.0.0'.PHP_EOL;
            echo 'by yaoxiaofeng (http://git.yoho.cn/web/yoho-im)'.PHP_EOL;
            exit(0);
        default:
            if (substr($arg, 0, 4) === 'env=') {
                $this->values['env'] = substr($arg, 4);
            }else if(substr($arg, 0, 9) === 'protocol='){
                $this->values['protocol'] = substr($arg, 9);
            }else if(substr($arg, 0, 8) === 'log-dir='){
                $this->values['logDir'] = substr($arg, 8);
            }else if(substr($arg, 0, 12) === 'reactor-num='){
                $this->values['reactor'] = (int)substr($arg, 12);
            }else if(substr($arg, 0, 11) === 'worker-num='){
                $this->values['work'] = (int)substr($arg, 11);
            }else if(substr($arg, 0, 11) === 'tasker-num='){
                $this->values['task'] = (int)substr($arg, 11);
            }else if(substr($arg, 0, 8) === 'max-con='){
                $this->values['maxCon'] = (int)substr($arg, 8);
            }else{
                $this->processUnknownArgument('--'.$arg, $pos);
            }
            break;
        }//end switch

    }

    /**
     * Processes an unknown command line argument.
     *
     * Assumes all unknown arguments are files and folders to check.
     *
     * @param string $arg The command line argument.
     * @param int    $pos The position of the argument on the command line.
     *
     * @return void
     */
    public function processUnknownArgument($arg, $pos)
    {
        // We don't know about any additional switches; just files.
        if ($arg{0} === '-') {
            
            echo 'ERROR: option "'.$arg.'" not known.'.PHP_EOL.PHP_EOL;
            $this->printUsage();
            exit(2);
        }
    }

    /**
     * Prints out the usage information for PHPCS.
     *
     * @return void
     */
    public function printUsage()
    {
        echo 'Usage: server [--env=<environment>] [--protocol=<protocol>] [--log-dir=<logDirectory>]'.PHP_EOL;
        echo '    [--reactor-num=<reactorNum>] [--worker-num=<workerNum>] '.PHP_EOL;
        echo '    [--tasker-num=<taskerNum>] [--max-con=<maxCon>]'.PHP_EOL;
        echo '    -d             open up debug'.PHP_EOL;
        echo '    -e             execute scripts,these scripts which working for notify online';
        echo '                   numbers or notify messages based on swoole_process'.PHP_EOL;
        echo '    <environment>  environments(production|developer|testing)'.PHP_EOL;
        echo '    <protocol>     protocol (tcp|websocket|proxy)'.PHP_EOL;
        echo '    <logDirectory> server logs directory'.PHP_EOL;
        echo '    <reactorNum>   master reactor number'.PHP_EOL;
        echo '    <workerNum>    worker number'.PHP_EOL;
        echo '    <taskerNum>    tasker number'.PHP_EOL;
        echo '    <maxCon>       max connections'.PHP_EOL;
    }
}

    