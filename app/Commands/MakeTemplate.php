<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Support\Entity\GeneratorSupport as Gen;
use CodeIgniter\HTTP\CLIRequest;
use Config\App;

class MakeTemplate extends BaseCommand
{
    protected $group       = 'Support';
    protected $name        = 'app:make:template';
    protected $description = 'Scaffold a CSV Upload Template class under app/Hooks/Templates';
    protected $usage = 'app:make:template <Entity> [--columns=a,b,c] [--sample="key=val,..."] [--classic-studly] [--force] [--dry-run]
        php spark app:make:template Courses \
            --columns=course_code,course_title,course_description,course_guide_url,course_type,department_code \
            --sample=\'course_code=BUS101,course_title="Business Intelligence"\'
    ';
    protected $arguments   = [
        'Entity' => 'Entity name (Studly or any case), e.g., Courses',
    ];
    protected $options     = [
        'columns' => 'Comma-separated header names (e.g., course_code,course_title,department_code)',
        'sample'  => 'JSON or key=value CSV for a single sample row',
        'force'   => 'Overwrite if file exists',
        'dry-run' => 'Show output path (no writes)',
    ];

    public function run(array $params)
    {
        $entity = $params[0] ?? null;
        if (!$entity) {
            CLI::error('Please provide an entity: php spark app:make:template Courses');
            return;
        }
        $request = new CLIRequest(new App());

        $studly   = Gen::studly($entity);
        $kebab    = str_replace('-', '_', Gen::kebab($studly));
        $force    = CLI::getOption('force') !== null;
        $dryRun   = CLI::getOption('dry-run') !== null;
        $columns  = Gen::parseColumnsOption(Gen::option('columns'));
        $sample   = Gen::parseSampleOption(Gen::option('sample'));

        $ns    = "App\\Hooks\\Templates";
        $path  = APPPATH . "Hooks/Templates/{$studly}.php";

        // Ensure sample aligns with columns (if provided)
        if ($columns && $sample) {
            // Keep only known keys; add missing as empty
            $sample = array_intersect_key($sample, array_flip($columns)) + array_fill_keys($columns, '');
        }

        // Build columns array string
        $columnsCode = $columns
            ? implode(",\n            ", array_map(fn($c) => "'{$c}'", $columns))
            : "// 'course_code', 'course_title', 'course_description', 'course_guide_url', 'course_type', 'department_code'";

        // Build sample row string
        if ($sample) {
            $pairs = [];
            foreach ($sample as $k => $v) {
                $v = (string) $v;
                // escape single quotes
                $v = str_replace("'", "\\'", $v);
                $pairs[] = "'{$k}' => '{$v}'";
            }
            $sampleCode = implode(",\n                ", $pairs);
        } elseif ($columns) {
            $pairs = array_map(fn($k) => "'{$k}' => ''", $columns);
            $sampleCode = implode(",\n                    ", $pairs);
        } else {
            $sampleCode = "// 'course_code' => 'BUS101',\n                    // 'course_title' => 'Business Intelligence',\n                    // 'course_description' => 'Business Intelligence',\n                    // 'course_guide_url' => 'https://example.com/path/to/file',\n                    // 'course_type' => 'cbt or written',\n                    // 'department_code' => 'ECO'";
        }

        $stub = <<<PHP
<?php
namespace {$ns};

use App\Hooks\Contracts\Template;

/**
 * CSV template for {$studly} uploads.
 * - columns(): header names (order shown will be exported in HTML/CSV)
 * - sampleRows(): at least one sample row aligned to columns()
 * - filenamePrefix(): used by your download helper
 */
final class {$studly} implements Template
{
    /** @return string[] */
    public static function columns(): array
    {
        return [
            {$columnsCode}
        ];
    }

    /** @return array<int,array<string,string>> */
    public static function sampleRows(): array
    {
        return [
            [
                {$sampleCode}
            ],
        ];
    }

    public static function filenamePrefix(): string
    {
        return "{$kebab}_upload_template";
    }
}
PHP;

        Gen::safeWrite($path, $stub, $force, $dryRun);
    }
}
