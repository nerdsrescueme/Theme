<?php

namespace Theme\Tasks;

class Theme extends \Geek\Design\Task
{
    public function run()
    {
        $this->geek->write($this->help());
    }

    /**
     * List all themes
     *
     * @todo Read theme files and display accordingly
     */
    public function all()
    {
        $folder  = join(DS, ['public', 'themes', $folder]);

        $this->geek->write('');
        $this->geek->write("Listing themes within $folder");
        $this->geek->write('');

        // If the folder doesn't exist... we failed.
        if (!is_dir($folder)) {
            $this->geek->write('  The theme folder does not exist');
            $this->geek->fail();
            return;
        }

        // Read the directory and display themes
        $handle = opendir($folder);

        while (($file = readdir($handle)) !== false) {
            if (strpos($file, '.theme') !== false) {
                $this->geek->write("  $file");
            }
        }

        $this->geek->write('');
        $this->geek->write('');
        $this->geek->succeed();
    }

    /**
     * Install a theme
     *
     * This task installs a theme into the public/themes directory. The theme is
     * cloned from a git repository into the themes directory... This enables us
     * to allow themes to be shared, updated and essentially acted on in any way
     * that git allows you to.
     *
     * # Usage
     *
     *   > geek application.theme.install https://github.com/user/repo.git {THEME_NAME}
     *
     * @return void
     */
    public function install()
    {
        list($task, $repo, $folder) = $this->geek->args();

        $parts = explode($repo);
        $name  = str_replace('.git', '', end($parts));

        $folder  = join(DS, ['public', 'themes', $folder]);
        $command = "git clone $repo $folder.theme";
        $return  = [];

        $this->geek->write('');
        $this->geek->write("Importing '$name' theme into $folder");

        exec($command, $return);

        foreach ($return as $r) {
            $this->geek->write($r);
        }

        $this->geek->write("Finished importing '$name' theme");
        $this->geek->write('');
    }

    /**
     * Uninstall a theme
     *
     * This task uninstalls a theme.
     *
     * # Usage
     *
     *   > geek application.theme.uninstall {THEME_NAME}
     *
     * @return void
     */
    public function uninstall()
    {
        list($task, $theme) = $this->geek->args();

        $folder  = join(DS, [\Nerd\DOCROOT, 'themes', $theme]);
        $command = "rm -rf $folder.theme";
        $return  = [];

        $this->geek->write('');
        $this->geek->write("Removing $theme theme");

        exec($command, $return);

        foreach ($return as $r) {
            $this->geek->write($r);
        }

        $this->geek->write("Finished removing '$name' theme");
        $this->geek->write('');
    }

    /**
     * Update a theme
     *
     * # Usage
     *
     *   > geek application.theme.update {THEME_NAME}
     *
     * @return void
     */
    public function update()
    {
        list($task, $theme) = $this->geek->args();

        $folder  = join(DS, [\Nerd\DOCROOT, 'themes', $theme]);
        $command = "cd $folder.theme & git pull";
        $return  = [];

        $this->geek->write('');
        $this->geek->write("Updating $theme theme");

        exec($command, $return);

        foreach ($return as $r) {
            $this->geek->write($r);
        }

        $this->geek->write("Finished updating $theme theme");
        $this->geek->write('');
    }

    /**
     * HELP
     *
     * @return string
     */
    public function help()
    {
        return <<<HELP

Usage:
  geek nerd.theme[.task] [args] [flags]

Tasks:
  all                    -- List all installed themes
  install {REPO} {THEME} -- Install a theme from a github repo
  remove  {THEME}        -- Uninstall a theme
  update  {THEME}        -- Update a theme from its source repo

Runtime options:
  None at this time

Description:
  The Theme suite of tasks can be used to perform actions
  pertaining to Themes within NerdCMS. With this suite, you can
  install, remove or update a theme very easily.

Documentation:
  http://nerdphp.com/docs/nerd/tasks/theme

HELP;
    }
}
