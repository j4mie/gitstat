<?php

class RepoChecker {

    protected $_flags;
    protected $_git_command = 'git';

    public function __construct($repo_dir) {
        $repo_dir = realpath($repo_dir);
        $this->_flags = "--git-dir={$repo_dir}/.git --work-tree={$repo_dir}";
    }

    public function is_dirty() {
        $command = "{$this->_git_command} {$this->_flags} status --porcelain";
        $output = trim(shell_exec($command));
        return $output !== '';
    }

    public function get_remote_url($remote_name) {
        $command = "{$this->_git_command} {$this->_flags} config --get remote.{$remote_name}.url";
        $output = trim(shell_exec($command));
        return $output;
    }

    public function is_out_of_sync_with($remote_name, $remote_ref='HEAD') {
        $remote_sha1 = $this->_get_remote_sha1($remote_name, $remote_ref);
        $local_sha1 = $this->_get_local_sha1();
        return $remote_sha1 !== $local_sha1;
    }

    protected function _get_remote_sha1($remote_name, $remote_ref='HEAD') {
        $command = "{$this->_git_command} {$this->_flags} ls-remote {$remote_name} {$remote_ref}";

        // Will be in the format "<sha1>        HEAD"
        $output = trim(shell_exec($command));
        $remote_sha1 = trim(str_replace($remote_ref, '', $output));
        return $remote_sha1;
    }

    protected function _get_local_sha1($branch='MASTER') {
        $command = "{$this->_git_command} {$this->_flags} rev-list --max-count=1 {$branch} HEAD";
        $output = trim(shell_exec($command));
        return $output;
    }

}

$config = parse_ini_file('config.ini', true);

$general_config = $config['General'];
unset($config['General']);

// Config now only contains repository data
$repos = $config;

$output_data = array();

foreach ($repos as $name => $repo) {
    $checker = new RepoChecker($repo['path']);
    $repo_data = array(
        'name' => $name,
        'path' => $repo['path'],
        'remote_name' => $repo['remote'],
        'remote_url' => $checker->get_remote_url($repo['remote']),
        'dirty' => $checker->is_dirty(),
        'out_of_sync' => $checker->is_out_of_sync_with($repo['remote']),
    );
    $output_data[] = $repo_data;
}

file_put_contents($general_config['output_file'], json_encode($output_data), LOCK_EX);

