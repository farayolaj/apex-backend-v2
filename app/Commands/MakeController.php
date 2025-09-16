<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Support\Entity\GeneratorSupport as Gen;

class MakeController extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:make:controller';
    protected $description = 'Scaffold a Controller extending BaseController (default) with optional REST/import methods';
    protected $usage       = 'app:make:controller <Name> [--in=Api/V1] [--entity=Courses] [--slug=courses] [--preset=rest|rest+import] [--methods=index,show,create,update,delete,sample,import,ping] [--extends=BaseController|ResourceController] [--force] [--dry-run]';
    protected $arguments   = [
        'Name' => 'Controller name (Pascal or any). "Controller" suffix auto-added if missing, e.g., course_mapping => CourseMappingController',
    ];
    protected $options     = [
        'in'      => 'Subdirectory under app/Controllers, e.g., Api/V1',
        'entity'  => 'Entity class basename under App\Entities, e.g., Courses. Default: derived from controller name (without "Controller")',
        'slug'    => 'Slug passed to EntityListTrait methods, e.g., courses (default: auto from entity; set explicitly if plural)',
        'preset'  => 'rest (default) | rest+import',
        'methods' => 'Comma list to override preset: index,show,create,update,delete,sample,import,ping',
        'extends' => 'BaseController (default) | ResourceController',
        'force'   => 'Overwrite if file exists',
        'dry-run' => 'Show output path and summary (no writes)',
    ];

    public function run(array $params)
    {
        $rawName = $params[0] ?? null;
        if (!$rawName) {
            CLI::error('Please provide a name: php spark app:make:controller course_mapping');
            return;
        }

        // Normalize controller class
        $base = $this->stripControllerSuffix($rawName);
        $basePascal = Gen::pascal($base);
        $className  = $basePascal . 'Controller';

        // Target subpath & namespace
        $inOpt    = Gen::option('in') ?? '';
        $subPath  = $this->normalizePath($inOpt); // "Api/V1" or ""
        $nsSuffix = $this->nsFromPath($subPath);  // "Api\\V1" or ""
        $namespace = 'App\\Controllers' . ($nsSuffix ? '\\' . $nsSuffix : '');

        // Entity class basename
        $entityOpt = Gen::option('entity');
        $entityBase = $entityOpt ? Gen::studly($entityOpt) : $basePascal;

        // Slug (for EntityListTrait calls)
        $slugOpt = Gen::option('slug');
        $slug = $slugOpt ?: $this->defaultSlugFromEntity($entityBase); // suggest overriding explicitly for plural

        // Methods
        $methods = $this->resolveMethods();

        // Extends
        $extendsOpt = Gen::option('extends') ?: 'BaseController';
        $extendsOpt = strtolower($extendsOpt);
        $extendsBase = $extendsOpt === 'resourcecontroller' ? 'ResourceController' : 'BaseController';

        // Flags
        $force  = CLI::getOption('force') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        // Destination
        $dir  = APPPATH . 'Controllers' . ($subPath ? '/' . $subPath : '');
        $path = $dir . '/' . $className . '.php';

        // Build stub
        $stub = $this->buildStub($namespace, $className, $entityBase, $slug, $methods, $extendsBase);

        // Dry-run summary
        if ($dryRun) {
            CLI::write("Would write: {$path}");
            CLI::write("  Namespace : {$namespace}");
            CLI::write("  Class     : {$className}");
            CLI::write("  Entity    : \\App\\Entities\\{$entityBase}");
            CLI::write("  Slug      : {$slug}");
            CLI::write("  Extends   : {$extendsBase}");
            CLI::write("  Methods   : " . implode(', ', $methods));
            CLI::newLine();
            CLI::write($stub);
            return;
        }

        // Write
        Gen::safeWrite($path, $stub, $force, false);
    }

    // ---------------- helpers ----------------

    private function resolveMethods(): array
    {
        $methodsOpt = Gen::option('methods');
        $preset     = strtolower(Gen::option('preset') ?? 'rest');

        $valid = ['index','show','create','update','delete','sample','import','ping'];
        if ($methodsOpt) {
            $list = array_values(array_filter(array_map('trim', explode(',', strtolower($methodsOpt)))));
            $list = array_values(array_intersect($list, $valid));
            return $list ?: ['index','show','create','update','delete']; // fallback
        }

        if ($preset === 'rest+import') {
            return ['index','show','create','update','delete','sample'];
        }
        // default: rest
        return ['index','show','create','update','delete'];
    }

    private function stripControllerSuffix(string $name): string
    {
        $name = trim($name);
        if ($name === '') return '';
        if (preg_match('/Controller$/i', $name)) {
            return preg_replace('/Controller$/i', '', $name) ?? $name;
        }
        return $name;
    }

    private function normalizePath(string $in): string
    {
        $in = trim($in);
        if ($in === '') return '';
        $in = str_replace('\\', '/', $in);
        return trim($in, '/');
    }

    private function nsFromPath(string $path): string
    {
        if ($path === '') return '';
        $parts = array_map(static fn($p) => Gen::pascal($p), explode('/', $path));
        return implode('\\', $parts);
    }

    private function defaultSlugFromEntity(string $entityBase): string
    {
        // Kebab then underscore: "CourseMapping" => "course_mapping"
        return str_replace('-', '_', Gen::kebab($entityBase));
    }

    private function buildStub(string $namespace, string $class, string $entityBase, string $slug, array $methods, string $extendsBase): string
    {
        $useEntityList = (in_array('index', $methods, true) || in_array('show', $methods, true));
        $useExport     = in_array('sample', $methods, true);

        // use statements
        $uses = [
            'use App\\Libraries\\ApiResponse;',
        ];
        if ($extendsBase === 'BaseController') {
            $uses[] = 'use App\\Controllers\\BaseController;';
        } else {
            $uses[] = 'use CodeIgniter\\RESTful\\ResourceController as CIResourceController;';
        }
        if ($useEntityList) $uses[] = 'use App\\Traits\\Crud\\EntityListTrait;';
        if ($useExport)     $uses[] = 'use App\\Traits\\ExportTrait;';

        $usesCode = implode("\n", $uses);

        // trait uses
        $traitLines = [];
        if ($useEntityList) $traitLines[] = '    use EntityListTrait;';
        if ($useExport)     $traitLines[] = '    use ExportTrait;';
        $traitsCode = $traitLines ? "\n" . implode("\n", $traitLines) . "\n" : "\n";

        // method stubs
        $parts = [];
        foreach ($methods as $m) {
            $parts[] = $this->methodStub($m, $entityBase, $slug);
        }
        $methodsCode = implode("\n\n", $parts);

        $extends = $extendsBase === 'BaseController' ? 'BaseController' : 'CIResourceController';

        return <<<PHP
<?php
namespace {$namespace};

{$usesCode}

/**
 * Generated by app:make:controller
 * - Extends {$extendsBase}
 * - Entity: \\App\\Entities\\{$entityBase}
 * - Slug: {$slug}
 */
class {$class} extends {$extends}
{{$traitsCode}{$methodsCode}
}
PHP;
    }

    private function methodStub(string $name, string $entityBase, string $slug): string
    {
        $entityFQN = "\\App\\Entities\\{$entityBase}";
        $lowerLabel = str_replace('_', ' ', strtolower($entityBase)); // generic label; override in messages if needed
        $ucFirstLabel = ucfirst($lowerLabel);
        switch ($name) {
            case 'index':
                return <<<PHP
    public function index()
    {
        \$payload = \$this->listApiEntity('{$slug}');
        return ApiResponse::success(data: \$payload);
    }
PHP;

            case 'show':
                return <<<PHP
    public function show(int \$id)
    {
        \$payload = \$this->showListEntity('{$slug}', \$id);
        return ApiResponse::success(data: \$payload);
    }
PHP;

            case 'create':
                return <<<PHP
    /**
     * @throws \\Throwable
     */
    public function create()
    {
        \$entity  = new {$entityFQN}();
        \$payload = requestPayload();

        \$row = \$entity->insertSingle(
            \$payload ?? [],
            \$this->request->getFiles() ?? []
        );

        if (!\$row) {
            return ApiResponse::error("Unable to create {$lowerLabel}");
        }
        
        \$payload['id'] = \$row;
        return ApiResponse::success('{$ucFirstLabel} inserted successfully', \$payload);
    }
PHP;

            case 'update':
                return <<<PHP
    /**
     * @throws \\Throwable
     */
    public function update(int \$id)
    {
        \$entity  = new {$entityFQN}();
        \$payload = \$this->request->getJSON(true);

        \$row = \$entity->updateSingle(
            \$id,
            \$payload ?? []
        );

        if (!\$row) {
            return ApiResponse::error("Unable to update {$lowerLabel}");
        }

        return ApiResponse::success('{$ucFirstLabel} updated successfully', \$payload);
    }
PHP;

            case 'delete':
                return <<<PHP
    /**
     * @throws \\Throwable
     */
    public function delete(int \$id)
    {
        \$entity = new {$entityFQN}();

        \$row = \$entity->deleteSingle(\$id);
        if (!\$row) {
            return ApiResponse::error("Unable to delete {$lowerLabel}");
        }

        return ApiResponse::success('{$ucFirstLabel} deleted successfully');
    }
PHP;

            case 'sample':
                return <<<PHP
    public function sample()
    {
        // Template class: \\App\\Hooks\\Templates\\{$entityBase}
        // TODO: wire to your download helper (PhpSpreadsheet or existing pipeline)
        // Example:
        // return \$this->downloadSampleFromTemplate(\\App\\Hooks\\Templates\\{$entityBase}::class);
        return ApiResponse::success('Sample endpoint stubbed. Wire your export helper here.');
    }
PHP;

            case 'import':
                return <<<PHP
    public function import()
    {
        // TODO: validate uploaded file and pass to your import service.
        // Keeping stub minimal since repositories are not wired here by design.
        return ApiResponse::success('Import endpoint stubbed. Wire your import service here.');
    }
PHP;

            case 'ping':
                return <<<PHP
    public function ping()
    {
        return ApiResponse::success(data: ['status' => 'ok', 'ts' => time()]);
    }
PHP;

            default:
                return "// TODO: method '{$name}' not recognized by generator";
        }
    }
}
