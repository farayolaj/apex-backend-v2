<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Support\Entity\GeneratorSupport as Gen;

class MakeRules extends BaseCommand
{
    protected $group       = 'Support';
    protected $name        = 'app:make:rules';
    protected $description = 'Scaffold Validation Rules classes under app/Validation/Entities/{Entity}';
    protected $usage       = 'app:make:rules <Entity> [--actions=create,update,delete] [--force] [--dry-run]';
    protected $arguments   = [
        'Entity' => 'Entity name (Studly or any case), e.g., Courses',
    ];
    protected $options     = [
        'actions' => 'Comma-separated: create,update,delete (default: all)',
        'force'   => 'Overwrite if file exists',
        'dry-run' => 'Show output path(s) (no writes)',
    ];

    public function run(array $params)
    {
        $entity = $params[0] ?? null;
        if (!$entity) {
            CLI::error('Please provide an entity: php spark app:make:rules Courses');
            return;
        }

        $studly  = Gen::studly($entity);
        $force   = CLI::getOption('force') !== null;
        $dryRun  = CLI::getOption('dry-run') !== null;
        $actionsOpt = Gen::option('actions');

        $actions = ['create','update','delete'];
        if ($actionsOpt) {
            $actions = array_values(array_filter(array_map('trim', explode(',', strtolower($actionsOpt)))));
            $actions = array_intersect($actions, ['create','update','delete']);
            if (!$actions) {
                CLI::error('No valid --actions provided (use create,update,delete)');
                return;
            }
        }

        foreach ($actions as $action) {
            $class = ucfirst($action) . 'Rules';
            $ns    = "App\\Validation\\Entities\\{$studly}";
            $dir   = APPPATH . "Validation/Entities/{$studly}";
            $path  = "{$dir}/{$class}.php";

            $stub = <<<PHP
<?php
namespace {$ns};

use App\Validation\Support\Contracts\RulesProvider;
/**
 * Validation rules for {$studly} ({$action}).
 * Methods are static to work with your ValidationAuto runner.
 * Keep authorize fast (no heavy I/O); use precheck for DB lookups.
 */
final class {$class} implements RulesProvider
{
    /** Gate the action using roles/permissions/tenant context from \$ctx. */
    public static function authorize(array \$data, array \$ctx): bool
    {
        return permissionAuthorize(\$ctx['__authorize__'] ?? 'course_create');
    }

    /** Message returned if authorize() returns false. */
    public static function denyMessage(): string
    {
        return 'You are not allowed to perform this action.';
    }

    /**
     * Optional: perform lightweight DB checks; throw your ApiValidationException::field(...)
     * to produce friendly per-field messages.
     */
    public static function precheck(array \$data): void
    {
        // e.g., ensure foreign keys exist, or ownership checks
        // throw new \\App\\Exceptions\\ValidationFailedException('Reason');
    }

    /** CodeIgniter rules array. Keep it minimal and explicit. */
    public static function rules(): array
    {
        return [
            // 'code'  => 'required|max_length[50]',
        ];
    }

    /** Optional custom messages per rule. */
    public static function messages(): array
    {
        return [
            // 'code.required' => 'Code is required.',
        ];
    }
}
PHP;

            Gen::safeWrite($path, $stub, $force, $dryRun);
        }
    }
}
