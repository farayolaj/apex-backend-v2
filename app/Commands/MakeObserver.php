<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Support\Entity\GeneratorSupport as Gen;

class MakeObserver extends BaseCommand
{
    protected $group       = 'Support';
    protected $name        = 'app:make:observer';
    protected $description = 'Scaffold an Observer class under app/Hooks/Observers';
    protected $usage       = 'app:make:observer <Entity> [--force] [--dry-run]';
    protected $arguments   = [
        'Entity' => 'Entity name (Studly or any case), e.g., Courses',
    ];
    protected $options     = [
        'force'   => 'Overwrite if file exists',
        'dry-run' => 'Show output path (no writes)',
    ];

    public function run(array $params)
    {
        $entity = $params[0] ?? null;
        if (!$entity) {
            CLI::error('Please provide an entity: php spark app:make:observer Courses');
            return;
        }

        $studly = Gen::studly($entity);
        $path   = APPPATH . "Hooks/Observers/{$studly}.php";
        $force  = CLI::getOption('force') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        $stub = <<<PHP
<?php
namespace App\\Hooks\\Observers;

use App\Hooks\Contracts\Observer; 

/**
 * Observer hooks for {$studly}.
 * - \$data is passed by reference on mutating hooks (beforeCreating/handleUploads/beforeUpdating).
 * - \$extra is passed by value; contains context like \$extra['auth'] (user) etc.
 * - Keep logic fast and deterministic; avoid heavy I/O where possible.
 */
final class {$studly} implements Observer
{
    // ---- INSERT FLOW ----
    public function beforeCreating(array &\$data, array \$extra): void {}
    public function afterCreated(int \$id, array &\$data, array \$extra): void {}
    
    public function handleUploads(array &\$data, array \$files, array \$extra): void {}
    public function cleanupUploads(array \$data, array \$extra): void {}

    // ---- UPDATE FLOW ----
    public function beforeUpdating(int \$id, array &\$data, array \$extra): void {}
    public function afterUpdated(int \$id, array &\$data, array \$extra): void {}

    // ---- DELETE FLOW ----
    public function beforeDeleting(int \$id, array \$extra): void {}
    public function afterDeleted(int \$id, array \$extra): void {}
}
PHP;

        Gen::safeWrite($path, $stub, $force, $dryRun);
    }
}
