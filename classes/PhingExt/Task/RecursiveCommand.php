<?php
require_once 'phing/Task.php';

class PhingExt_Task_RecursiveCommand extends \Task
{
    /**
     * @var string
     */
    protected $_sourceDir;

    /**
     * @var string
     */
    protected $_resultDir;

    /**
     * @var string
     */
    protected $_filter;

    /**
     * @var string
     */
    protected $_newExt;

    /**
     * @var string
     */
    protected $_command;

    /**
     *  Called by the project to let the task do it's work. This method may be
     *  called more than once, if the task is invoked more than once. For
     *  example, if target1 and target2 both depend on target3, then running
     *  <em>phing target1 target2</em> will run all tasks in target3 twice.
     *
     *  Should throw a BuildException if someting goes wrong with the build
     *
     *  This is here. Must be overloaded by real tasks.
     */
    public function main()
    {
        if (empty ($this->_sourceDir)){
            throw new BuildException("Attribute 'sourceDir' is empty", $this->getLocation());
        }
        if (empty ($this->_resultDir)){
            throw new BuildException("Attribute 'resultDir' is empty", $this->getLocation());
        }
        if (empty ($this->_command)){
            throw new BuildException("Attribute 'command' is empty", $this->getLocation());
        }
        $this->_startRecursiveCommand('', '');
    }


    protected function _startRecursiveCommand($baseDir, $resultDir)
    {
        if (false !== ($dh = opendir($this->_sourceDir.'/'.$baseDir))){
            $dirs = array ();
            while (false !== ($file = readdir($dh))){
                $fullName = sprintf("%s/%s", $baseDir, $file);
                $resultName = sprintf("%s/%s", $resultDir, $file);
                if (is_dir($this->_sourceDir.'/'.$fullName)){
                    if (substr($file, 0, 1) != '.'){
                        $dirs[] = $file;
                    }
                } else {
                    if ($this->_filter && !preg_match('!'.$this->_filter.'!', $file)){
                        continue;
                    }
                    $command = $this->_command;
                    $sourceFile = $this->_sourceDir.'/'.$fullName;
                    $resultFile = $this->_resultDir.'/'.$resultName;
                    if ($this->_newExt){
                        $pathInfo = pathinfo($resultFile);
                        $pathInfo['extension'] = $this->_newExt;
                        $resultFile = sprintf("%s/%s.%s",$pathInfo['dirname'], $pathInfo['filename'], $pathInfo['extension']);
                    }
                    $command = str_replace('%1', escapeshellarg($sourceFile), $command);
                    $command = str_replace('%2', escapeshellarg($resultFile), $command);
                    $this->log("Executing command: ".$command);
                    passthru($command);
                }
            }
            closedir($dh);
            foreach ($dirs as $dir){
                $this->_startRecursiveCommand($baseDir.'/'.$dir, $resultDir.'/'.$dir);
            }
        }
    }

    /**
     * @param string $resultDir
     * @return self
     */
    public function setResultDir($resultDir)
    {
        $this->_resultDir = $resultDir;
        return $this;
    }

    /**
     * @param string $sourceDir
     * @return self
     */
    public function setSourceDir($sourceDir)
    {
        $this->_sourceDir = $sourceDir;
        return $this;
    }

    /**
     * @param string $command
     * @return self
     */
    public function setCommand($command)
    {
        $this->_command = $command;
        return $this;
    }

    /**
     * @param string $filter
     * @return self
     */
    public function setFilter($filter)
    {
        $this->_filter = $filter;
        return $this;
    }

    /**
     * @param string $newExt
     * @return self
     */
    public function setNewExt($newExt)
    {
        $this->_newExt = $newExt;
        return $this;
    }

}