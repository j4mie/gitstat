<?php

/**
 * Class to represent a Git repository
 *
 * @author Jamie Matthews 2010-10-03
 */
class RepoChecker {

    protected $_flags;
    protected $_git_command = 'git';

    /**
     * Construct a new RepoChecker for the given repository
     */
    public function __construct($repo_dir) {
        $repo_dir = realpath($repo_dir);
        $this->_flags = "--git-dir={$repo_dir}/.git --work-tree={$repo_dir}";
    }

    /**
     * Is the working tree for this repository dirty?
     */
    public function is_dirty() {
        $command = "{$this->_git_command} {$this->_flags} status --porcelain";
        $output = trim(shell_exec($command));
        return $output !== '';
    }

    /**
     * Return the URL for the specified remote
     */
    public function get_remote_url($remote_name) {
        $command = "{$this->_git_command} {$this->_flags} config --get remote.{$remote_name}.url";
        $output = trim(shell_exec($command));
        return $output;
    }

    /**
     * Is this repository out of sync with the specified remote?
     */
    public function is_out_of_sync_with($remote_name, $remote_ref='HEAD') {
        $remote_sha1 = $this->_get_remote_sha1($remote_name, $remote_ref);
        $local_sha1 = $this->_get_local_sha1();
        return $remote_sha1 !== $local_sha1;
    }

    /**
     * Get the SHA1 of the given remote and ref (ref defaults to HEAD)
     */
    protected function _get_remote_sha1($remote_name, $remote_ref='HEAD') {
        $command = "{$this->_git_command} {$this->_flags} ls-remote {$remote_name} {$remote_ref}";

        // Will be in the format "<sha1>        HEAD"
        $output = trim(shell_exec($command));
        $remote_sha1 = trim(str_replace($remote_ref, '', $output));
        return $remote_sha1;
    }

    /**
     * Get the SHA1 of the HEAD of the given local branch (default MASTER)
     */
    protected function _get_local_sha1($branch='MASTER') {
        $command = "{$this->_git_command} {$this->_flags} rev-list --max-count=1 {$branch} HEAD";
        $output = trim(shell_exec($command));
        return $output;
    }

}

// Parse the configuration INI file
$config = parse_ini_file('config.ini', true);

// Retrieve general configuration data
$general_config = $config['General'];
unset($config['General']);

// Config now only contains repository data
$repos = $config;

// Set timezone
date_default_timezone_set('Europe/London');

// Set up the output data array
$output_data = array(
    'last_updated' => date('H:i, l jS F Y'),
    'repositories' => array(),
);

// Add the details for each repository to the output data array
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
    $output_data['repositories'][] = $repo_data;
}

// Dump the repository data as JSON into the file set in the config
file_put_contents($general_config['output_file'], json_encode($output_data), LOCK_EX);

