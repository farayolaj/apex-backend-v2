<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TransformModels extends BaseCommand
{
    protected $group       = 'Support';
    protected $name        = 'transform:model';
    protected $description = 'Transforms old CodeIgniter 3 model files into CodeIgniter 4 compatible model files.';
    protected $usage       = "transform:model <input_path> [output_path] \n
    php spark transform:model app/Models/Employee.php transformed/Models/Employee.php \n
    php spark transform:model app/Models transformed/Models \n
    php spark transform:model app/Models \n
    php spark transform:model /var/www/project/app/Models /var/www/project/transformed/Models
    ";
    protected $arguments   = [
        'input_path'  => 'The path to the input file or directory.',
        'output_path' => 'The path to save the transformed file or directory (optional).',
    ];

    protected array $examples = [
        'Transform a single file' => 'php spark transform:model /path/to/Employee_old.php /path/to/Employee_new.php',
        'Transform a directory'   => 'php spark transform:model /path/to/input_directory /path/to/output_directory',
        'Transform and save in the same location' => 'php spark transform:model /path/to/input_directory',
    ];

    private string $namespace = 'App\\Entities';

    /**
     * Models that should be treated as libraries even if loaded via $this->load->model()
     * Add/remove names here as needed without changing the transformation logic
     */
    private array $forceAsLibrary = [
        'remita',
    ];

    public function run(array $params)
    {
        // Validate input
        if (empty($params[0])) {
            CLI::error('You must provide the input path (file or directory).');
            return;
        }

        // Resolve the input path to an absolute path
        $inputPath = $this->resolvePath($params[0]);
        $outputPath = isset($params[1]) ? $this->resolvePath($params[1]) : $inputPath;

        // Check if the input path is a file or directory
        if (is_file($inputPath)) {
            $this->transformFile($inputPath, $outputPath);
        } elseif (is_dir($inputPath)) {
            $this->transformDirectory($inputPath, $outputPath);
        } else {
            CLI::error("The input path '{$inputPath}' does not exist or is not a valid file/directory.");
            return;
        }

        CLI::write('Transformation complete!', 'green');
    }

    protected function resolvePath(string $path): string
    {
        if (strpos($path, '/') === 0 || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return rtrim($path, '/');
        }

        return rtrim(ROOTPATH . $path, '/');
    }

    protected function transformFile(string $inputFile, string $outputPath): void
    {
        $filename = basename($inputFile);
        $content = file_get_contents($inputFile);
        $className = $this->extractClassName($content);
        if (!$className) {
            CLI::error("Could not determine the class name from the file '{$filename}'. Skipping.", 'red');
        }

        CLI::write("Transforming file: {$filename}", 'blue');
        $transformedContent = $this->transformContent($content, $className);

        if (is_dir($outputPath)) {
            $outputFile = rtrim($outputPath, '/') . '/' . $filename;
        } else {
            $outputFile = $outputPath;
        }

        // Write the transformed content to the output file
        file_put_contents($outputFile, $transformedContent);
        CLI::write("Transformed file saved to: {$outputFile}", 'blue');
        CLI::write("\n");
    }

    protected function transformDirectory(string $inputDir, string $outputDir): void
    {
        // Create the output directory if it doesn't exist
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $files = glob($inputDir . '/*.php');

        if (empty($files)) {
            CLI::error("No PHP files found in the input directory '{$inputDir}'.");
            return;
        }

        // Process each file
        foreach ($files as $file) {
            $filename = basename($file);
            $outputFile = $outputDir . '/' . $filename;
            $this->transformFile($file, $outputFile);
        }
    }

    protected function extractClassName(string $content): ?string
    {
        // Regex to match class definition (with or without extends)
        $pattern = '/class\s+(\w+)(?:\s+extends\s+\w+)?\s*\{/';

        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function changeRequireOncetoUseStatement($content){
        // Transform specific require_once statements into use statements
        $replacements = [
            // Constants
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/Admission\.php['\"];\s*/"
            => "use App\\Enums\\AdmissionEnum as Admission;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/AuthType\.php['\"];\s*/"
            => "use App\\Enums\\AuthEnum as AuthType;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/BookstoreStatus\.php['\"];\s*/"
            => "use App\\Enums\\BookstoreStatusEnum as BookstoreStatus;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/ClaimType\.php['\"];\s*/"
            => "use App\\Enums\\ClaimEnum as ClaimType;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/CommonSlug\.php['\"];\s*/"
            => "use App\\Enums\\CommonEnum as CommonSlug;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/CourseManagerStatus\.php['\"];\s*/"
            => "use App\\Enums\\CourseManagerStatusEnum as CourseManagerStatus;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/DocumentSlug\.php['\"];\s*/"
            => "use App\\Enums\\DocumentEnum as DocumentSlug;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/FeeDescriptionCode\.php['\"];\s*/"
            => "use App\\Enums\\FeeDescriptionCodeEnum as FeeDescriptionCode;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/OutflowStatus\.php['\"];\s*/"
            => "use App\\Enums\\OutflowStatusEnum as OutflowStatus;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/PaymentFeeDescription\.php['\"];\s*/"
            => "use App\\Enums\\PaymentFeeDescriptionEnum as PaymentFeeDescription;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/PaymentPercentage\.php['\"];\s*/"
            => "use App\\Libraries\\PaymentPercentage;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/RemitaResponse\.php['\"];\s*/"
            => "use App\\Libraries\\RemitaResponse;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/ReportSlug\.php['\"];\s*/"
            => "use App\\Enums\\ReportEnum as ReportSlug;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/RequestStatus\.php['\"];\s*/"
            => "use App\\Enums\\RequestStatusEnum as RequestStatus;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/RequestTypeSlug\.php['\"];\s*/"
            => "use App\\Enums\\RequestTypeEnum as RequestTypeSlug;\n",

            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/RouteURI\.php['\"];\s*/"
            => "use App\\Libraries\\RouteURI;\n",

            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/ServiceType\.php['\"];\s*/"
            => "use App\\Enums\\ServiceTypeEnum as ServiceType;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/SettingSlug\.php['\"];\s*/"
            => "use App\\Enums\\SettingSlugEnum as SettingSlug;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/StageIndex\.php['\"];\s*/"
            => "use App\\Enums\\StageIndexEnum as StageIndex;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/StudentStatus\.php['\"];\s*/"
            => "use App\\Enums\\StudentStatusEnum as StudentStatus;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/TransactionCode\.php['\"];\s*/"
            => "use App\\Enums\\TransactionCodeEnum as TransactionCode;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]constants\/UserOutflowType\.php['\"];\s*/"
            => "use App\\Enums\\UserOutflowTypeEnum as UserOutflowType;\n",

            // Traits
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/AccountTrait\.php['\"];\s*/"
            => "use App\\Traits\\AccountTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/AdminModelTrait\.php['\"];\s*/"
            => "use App\\Traits\\AdminModelTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/ApiModelTrait\.php['\"];\s*/"
            => "use App\\Traits\\ApiModelTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/AuthTrait\.php['\"];\s*/"
            => "use App\\Traits\\AuthTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/CommonStatsTrait\.php['\"];\s*/"
            => "use App\\Traits\\CommonStatsTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/CommonTrait\.php['\"];\s*/"
            => "use App\\Traits\\CommonTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/CrudTrait\.php['\"];\s*/"
            => "use App\\Traits\\CrudTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/EntityListTrait\.php['\"];\s*/"
            => "use App\\Traits\\EntityListTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/ExportTrait\.php['\"];\s*/"
            => "use App\\Traits\\ExportTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/ResultManagerTrait\.php['\"];\s*/"
            => "use App\\Traits\\ResultManagerTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/StudentTrait\.php['\"];\s*/"
            => "use App\\Traits\\StudentTrait;\n",
            "/require_once\s*APPPATH\s*\.\s*['\"]traits\/UploadTrait\.php['\"];\s*/"
            => "use App\\Traits\\UploadTrait;\n",
        ];
        foreach ($replacements as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        return $content;
    }

    protected function transformConstantsToEnumValues($content): array|string|null
    {
        $enumReplacements = [
            'CommonSlug::',
            'PaymentFeeDescription::',
            'FeeDescriptionCode::',
            'Admission::',
            'AuthType::',
            'ClaimType::',
            'DocumentSlug::',
            'OutflowStatus::',
            'ReportSlug::',
            'RequestStatus::',
            'RequestTypeSlug::',
            'StageIndex::',
            'UserOutflowType::',
        ];

        foreach ($enumReplacements as $enum) {
            // Regex to match constants like CommonSlug::APPLICANT and replace them with CommonSlug::APPLICANT->value
            $content = preg_replace_callback(
                '/\b(' . preg_quote($enum, '/') . '\w+)\b/',
                function ($matches) {
                    $constant = $matches[1];
                    return $constant . '->value';
                },
                $content
            );
        }

        return $content;
    }

    private function removeOpeningAndAddNamespace($content): array|string|null
    {
        // Remove the BASEPATH defined check
        $basepathPattern = '/if\s*\(!\s*defined\(\'BASEPATH\'\)\)\s*\{\s*exit\(\'No direct script access allowed\'\);\s*\}/';
        $content = preg_replace($basepathPattern, '', $content);

        // Remove defined check
        $basepathPattern = '/defined\([\'\"]BASEPATH[\'\"]\)\s+OR\s+exit\([\'\"](No direct script access allowed|[^\'\"]+)[\'\"]?\);/';
        $content = preg_replace($basepathPattern, '', $content);

        // Remove PHP opening tag and replace with a clean one
        $content = preg_replace('/^<\?php.*?(\r\n|\r|\n)/s', "<?php\n", $content);

        // Remove all require/include statements
        $pattern = '/<\?php\s+require_once\s+\'application\/models\/Crud\.php\';/';
        $content = preg_replace($pattern, '<?php', $content);

        $content = preg_replace("/<\?php\s*require_once\s*\(\s*['\"]application\/models\/Crud\.php['\"]\s*\);\s*/", "<?php\n", $content);

        $namespaceExists = strpos($content, 'namespace App\Entities;') !== false;
        $useCrudExists = strpos($content, 'use App\Models\Crud;') !== false;

        // Add the namespace and use statement if they don't exist
        if (!$namespaceExists || !$useCrudExists) {
            // Prepare the new header
            $newHeader = "<?php\n";
            if (!$namespaceExists) {
                $newHeader .= "namespace App\\Entities;\n\n";
            }
            if (!$useCrudExists) {
                $newHeader .= "use App\\Models\\Crud;\n\n";
            }

            // Replace the opening PHP tag with the new header
            $content = preg_replace(
                '/<\?php\s+/',
                $newHeader,
                $content
            );
        }

        // Transform specific require_once statements into use statements
        $content = $this->changeRequireOncetoUseStatement($content);

        // Remove PHP closing tags if they exist
        $content = preg_replace('/\?>\s*$/', '', $content);

        return $content;
    }

    private function transformResultMethod($content) {
        // Transform result_array() to getResultArray()
        return str_replace('result_array()', 'getResultArray()', $content);
    }

    private function fixIdCaseSensitivity($content) {
        // Fix ID to id in array access (CI4 uses lowercase variable names)
        $content = str_replace(
            'if (!isset($this->array[\'ID\'])) {',
            'if (!isset($this->array[\'id\'])) {',
            $content
        );

        $content = str_replace(
            '$id = $this->array[\'ID\'];',
            '$id = $this->array[\'id\'];',
            $content
        );

        return $content;
    }

    private function transformReturnObject($content): array|string|null
    {
        // Handle include_once with single object return
        $content = preg_replace_callback(
            '/include_once\(\'(\w+)\.php\'\);\s*\$resultObject\s*=\s*new\s+(\w+)\(\$result\[0\]\);\s*return\s+\$resultObject;/',
            function ($matches) {
                $includedClass = $matches[1]; // e.g., Faculty
                return "return new \\App\\Entities\\$includedClass(\$result[0]);";
            },
            $content
        );

        $content = preg_replace_callback(
            '/include_once\s*\'(\w+)\.php\';\s*\$resultObject\s*=\s*new\s+(\w+)\(\$result\[0\]\);\s*return\s+\$resultObject;/',
            function ($matches) {
                $includedClass = $matches[1]; // e.g., Faculty
                return "return new \\App\\Entities\\$includedClass(\$result[0]);";
            },
            $content
        );

        // Handle include_once with array of objects return
        $content = preg_replace_callback(
            '/include_once\(\'(\w+)\.php\'\);\s*\$resultObjects\s*=\s*array\(\);\s*foreach\s*\(\$result\s*as\s*\$value\)\s*\{\s*\$resultObjects\[\]\s*=\s*new\s+(\w+)\(\$value\);\s*\}/',
            function ($matches) {
                $includedClass = $matches[1]; // e.g., Matric_number_generated
                return "\$resultObjects = [];\n\t\tforeach (\$result as \$value) {\n    \t\t\$resultObjects[] = new \\App\\Entities\\$includedClass(\$value);\n\t\t}";
            },
            $content
        );

        // Handle include_once with array of objects return
        $content = preg_replace_callback(
            '/include_once\(\'(\w+)\.php\'\);\s*\$resultobjects\s*=\s*array\(\);\s*foreach\s*\(\$result\s*as\s*\$value\)\s*\{\s*\$resultObjects\[\]\s*=\s*new\s+(\w+)\(\$value\);\s*\}/',
            function ($matches) {
                $includedClass = $matches[1]; // e.g., Matric_number_generated
                return "\$resultObjects = [];\n\t\tforeach (\$result as \$value) {\n    \t\t\$resultObjects[] = new \\App\\Entities\\$includedClass(\$value);\n\t\t}";
            },
            $content
        );

        // Handle include_once with single object return (alternative pattern)
        $content = preg_replace_callback(
            '/include_once\(\'(\w+)\.php\'\);\s*return\s+new\s+(\w+)\(\$result\[0\]\);/',
            function ($matches) {
                $includedClass = $matches[1]; // e.g., Faculty
                return "return new \\App\\Entities\\$includedClass(\$result[0]);";
            },
            $content
        );

        return $content;
    }

    private function transformHelperFunction($content) {
        // Transform permissionAccess
        $content = preg_replace(
            '/permissionAccess\(\s*\$this\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\);/',
            'permissionAccess($1, $2);',
            $content
        );

        // Transform decryptData
        $content = preg_replace(
            '/decryptData\(\s*\$this\s*,\s*([^,]+)\s*\);/',
            'decryptData($1);',
            $content
        );

        // Transform logAction
        $content = preg_replace(
            '/logAction\(\s*\$this\s*,\s*([^,]+)\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\);/',
            'logAction($1, $2, $3);',
            $content
        );

        // Transform $this->webSessionManager->currentAPIUser()
        $content = preg_replace(
            '/\$this->webSessionManager->currentAPIUser\(\);/',
            'WebSessionManager::currentAPIUser();',
            $content
        );

        return $content;
    }

    protected function transformLoadClass($content) {
        $loadClassExists = str_contains($content, 'loadClass(');
        if($loadClassExists){
            $content = preg_replace(
                '/loadClass\(\s*\$this->load\s*,\s*([^)]+)\s*\);/',
                'EntityLoader::loadClass($this, $1);',
                $content
            );

            $useEntityLoader = 'use App\Libraries\EntityLoader;';
            if (!str_contains($content, $useEntityLoader)) {
                // Check if use App\Models\Crud; exists
                $useCrudPos = strpos($content, 'use App\Models\Crud;');

                if ($useCrudPos !== false) {
                    // Insert after use App\Models\Crud;
                    $insertPos = strpos($content, ';', $useCrudPos) + 1;
                    $content = substr_replace($content, "\n" . $useEntityLoader, $insertPos, 0);
                } else {
                    // If use App\Models\Crud; doesn't exist, insert after the namespace or opening PHP tag
                    $namespacePos = strpos($content, 'namespace ');
                    $firstUsePos = strpos($content, 'use ');

                    if ($namespacePos !== false) {
                        // Insert after the namespace declaration
                        $insertPos = strpos($content, ';', $namespacePos) + 1;
                    } elseif ($firstUsePos !== false) {
                        // Insert before the first use statement
                        $insertPos = $firstUsePos;
                    } else {
                        // Insert after the opening PHP tag
                        $insertPos = strpos($content, '<?php') + 5;
                    }

                    $content = substr_replace($content, "\n" . $useEntityLoader . "\n", $insertPos, 0);
                }
            }
        }
        return $content;
    }

    private function transformModelBuilder($content){
        // Transform get_where into CodeIgniter 4's query builder format
        $content = preg_replace_callback(
            '/\$query\s*=\s*\$this->db->get_where\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*array\(([^)]+)\)\s*\);/',
            function ($matches) {
                $tableName = $matches[1]; // e.g., 'faculty'
                $conditions = $matches[2]; // e.g., "'id' => $id, 'active' => 1"

                // Convert the conditions array into individual where clauses
                $conditions = preg_replace('/\'(\w+)\'\s*=>\s*([^,]+)/', '->where(\'$1\', $2)', $conditions);

                // Remove commas and newlines between where clauses
                $conditions = preg_replace('/\)\s*,\s*->where\(/', ')->where(', $conditions);

                // Build the new query using CodeIgniter 4's query builder
                $newQuery = <<<PHP
\$query = \$this->db->table('$tableName')
                  $conditions
                  ->get();
PHP;

                return $newQuery;
            },
            $content
        );

        // Replace num_rows() with getNumRows()
        $content = str_replace('num_rows()', 'getNumRows()', $content);

        // Replace row() with getRow()
        $content = str_replace('row()', 'getRow()', $content);

        // Transform escape_string to escape
        $content = preg_replace_callback(
            '/\$this->db->conn_id->escape_string\(([^)]+)\);/',
            function ($matches) {
                $variable = $matches[1]; // e.g., $start or $len
                return "\$this->db->escapeString($variable);";
            },
            $content
        );

        // Replace result_array() with getResultArray()
        $content = str_replace('result_array()', 'getResultArray()', $content);
        return $content;
    }

    private function transformInputAndUri(string $content): string
    {
        // Short-circuit if there's nothing to do (cheap guard for speed & idempotence)
        if (strpos($content, '$this->input->') === false && strpos($content, '$this->uri->') === false) {
            return $content;
        }

        // GET
        $content = preg_replace(
            '/\$this->input->get\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*true\s*\)/i',
            "request()->getGet('$1')",
            $content
        );
        $content = preg_replace('/\$this->input->get\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*false\s*\)/i', "request()->getGet('$1')", $content);
        $content = preg_replace('/\$this->input->get\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i', "request()->getGet('$1')", $content);
        $content = preg_replace('/\$this->input->get\(\s*\)/i', 'request()->getGet()', $content);

        // POST
        $content = preg_replace(
            '/\$this->input->post\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*true\s*\)/i',
            "request()->getPost('$1')",
            $content
        );
        $content = preg_replace('/\$this->input->post\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*false\s*\)/i', "request()->getPost('$1')", $content);
        $content = preg_replace('/\$this->input->post\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i', "request()->getPost('$1')", $content);
        $content = preg_replace('/\$this->input->post\(\s*\)/i', 'request()->getPost()', $content);

        // GET/POST either
        $content = preg_replace('/\$this->input->get_post\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*(?:true|false))?\s*\)/i', "request()->getVar('$1')", $content);
        $content = preg_replace('/\$this->input->get_post\(\s*\)/i', 'request()->getVar()', $content);

        // SERVER
        $content = preg_replace('/\$this->input->server\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i', "request()->getServer('$1')", $content);

        // Misc input helpers
        $content = preg_replace('/\$this->input->ip_address\(\s*\)/i', 'request()->getIPAddress()', $content);
        $content = preg_replace('/\$this->input->method\(\s*\)/i', 'request()->getMethod()', $content);
        $content = preg_replace('/\$this->input->is_ajax_request\(\s*\)/i', 'request()->isAJAX()', $content);
        $content = preg_replace('/\$this->input->get_request_header\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*(?:true|false))?\s*\)/i', "request()->getHeaderLine('$1')", $content);
        // User agent becomes an object in CI4; get a comparable string:
        $content = preg_replace('/\$this->input->user_agent\(\s*\)/i', 'request()->getUserAgent()->getAgentString()', $content);

        // Cookies (basic read)
        $content = preg_replace('/\$this->input->cookie\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i', "request()->getCookie('$1')", $content);

        // segment(n[, default])
        $content = preg_replace_callback(
            '/\$this->uri->segment\(\s*(\d+)\s*(?:,\s*([^)]+)\s*)?\)/i',
            function ($m) {
                $n = $m[1];
                // Optional default: use null-coalesce if present and not obviously null
                if (isset($m[2]) && trim($m[2]) !== '') {
                    $def = trim($m[2]);
                    return "(service('uri')->getSegment($n) ?? $def)";
                }
                return "service('uri')->getSegment($n)";
            },
            $content
        );

        $content = preg_replace('/\$this->uri->uri_string\(\s*\)/i', "service('uri')->getPath()", $content);
        $content = preg_replace('/\$this->uri->total_segments\(\s*\)/i', "count(service('uri')->getSegments())", $content);
        $content = preg_replace('/\$this->uri->segment_array\(\s*\)/i', "service('uri')->getSegments()", $content);

        return $content;
    }

    /**
     * Transform CI3 loader calls (model/library) to CI4 Factories **inside methods only**.
     * - Skips __construct
     * - Replaces `$this->load->model('foo', 'bar')` with `$bar = Factories::models('Foo');`
     * - Replaces `$this->bar->...` with `$bar->...` (method-scoped)
     * - Handles `load->library()` similarly via Factories::libraries()
     * - Lets you force specific names (e.g. 'remita') to be treated as libraries
     */
    private function transformLoaderToFactoriesInMethods(string $content): string
    {
        // Split content into methods for processing
        $methods = $this->extractMethods($content);

        foreach ($methods as $methodName => $methodContent) {
            // Skip constructors - we don't want to transform initialization code
            if (strtolower($methodName) === '__construct') {
                continue;
            }

            // Transform the method content
            $transformedMethodContent = $this->transformModelLoadingInMethod($methodContent);

            // Replace the method in the original content
            $content = str_replace($methodContent, $transformedMethodContent, $content);
        }

        return $content;
    }

    /**
     * Extract methods from the class content
     */
    private function extractMethods(string $content): array
    {
        $methods = [];

        // Pattern to match method definitions
        $pattern = '/(?:public|private|protected)\s+function\s+(\w+)\s*\([^)]*\)\s*\{/';

        if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                $methodName = $matches[1][$index][0];
                $startPos = $match[1];

                // Find the end of the method by counting braces
                $endPos = $this->findMethodEnd($content, $startPos);

                if ($endPos !== false) {
                    $methodContent = substr($content, $startPos, $endPos - $startPos + 1);
                    $methods[$methodName] = $methodContent;
                }
            }
        }

        return $methods;
    }

    /**
     * Find the end position of a method by counting braces
     */
    private function findMethodEnd(string $content, int $startPos): int|false
    {
        $braceCount = 0;
        $inString = false;
        $stringChar = '';
        $escaped = false;

        for ($i = $startPos; $i < strlen($content); $i++) {
            $char = $content[$i];

            // Handle string literals to avoid counting braces inside strings
            if (!$escaped && ($char === '"' || $char === "'")) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                    $stringChar = '';
                }
            }

            if (!$inString) {
                if ($char === '{') {
                    $braceCount++;
                } elseif ($char === '}') {
                    $braceCount--;
                    if ($braceCount === 0) {
                        return $i;
                    }
                }
            }

            $escaped = ($char === '\\' && !$escaped);
        }

        return false;
    }

    /**
     * Transform model loading patterns within a single method
     */
    private function transformModelLoadingInMethod(string $methodContent): string
    {
        $loadedItems = [];

        // Transform $this->load->model() calls
        $methodContent = preg_replace_callback(
            '/\$this->load->model\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*[\'"]([^\'"]+)[\'"]\s*)?\);/',
            function ($matches) use (&$loadedItems) {
                $itemName = $matches[1];
                $alias = isset($matches[2]) ? $matches[2] : basename($itemName);

                // Check if this should be treated as a library instead of model
                $factoryType = in_array(strtolower($alias), $this->forceAsLibrary) ? 'libraries' : 'models';

                // Store the mapping
                $loadedItems[$alias] = ['name' => $itemName, 'type' => $factoryType];

                // Convert to CI4 Factories pattern
                $capitalizedAlias = ucfirst($alias);
                return "\${$alias} = \\CodeIgniter\\Config\\Factories::{$factoryType}('{$capitalizedAlias}');";
            },
            $methodContent
        );

        // Transform $this->load->library() calls
        $methodContent = preg_replace_callback(
            '/\$this->load->library\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*[^,)]+(?:,\s*[\'"]([^\'"]+)[\'"]\s*)?)?\);/',
            function ($matches) use (&$loadedItems) {
                $itemName = $matches[1];
                $alias = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : basename($itemName);

                // Libraries always go to Factories::libraries()
                $loadedItems[$alias] = ['name' => $itemName, 'type' => 'libraries'];

                // Convert to CI4 Factories pattern
                $capitalizedAlias = ucfirst($alias);
                return "\${$alias} = \\CodeIgniter\\Config\\Factories::libraries('{$capitalizedAlias}');";
            },
            $methodContent
        );

        // Replace all $this->itemname-> calls with $itemname->
        foreach ($loadedItems as $alias => $item) {
            $methodContent = preg_replace(
                '/\$this->' . preg_quote($alias, '/') . '->/',
                "\${$alias}->",
                $methodContent
            );
        }

        return $methodContent;
    }

    protected function transformContent(string $content, string $className): string
    {
        // Add namespace and use statement
        $content = $this->removeOpeningAndAddNamespace($content);

        // Update the ID to id (case sensitivity)
        $content = $this->fixIdCaseSensitivity($content);

        // Update the result_array() to getResultArray()
        $content = $this->transformResultMethod($content);

        // Update the include_once to use namespace and return object
        $content = $this->transformReturnObject($content);

        $content = $this->transformModelBuilder($content);

        $content = $this->transformHelperFunction($content);

        $content = $this->transformConstantsToEnumValues($content);

        $content = $this->transformLoadClass($content);

        $content = $this->transformInputAndUri($content);

        $content = $this->transformLoaderToFactoriesInMethods($content);

        return $content;
    }

}