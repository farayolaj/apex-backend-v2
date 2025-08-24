<?php

namespace App\Models;

use App\Enums\WebinarStatusEnum;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationFailedException;
use App\Hooks\Resolver\ObserverResolver;
use App\Support\Entity\BatchErrorLogger;
use App\Support\Entity\SubsetSupport;
use App\Validation\Support\Resolver\ValidationResolver;
use App\Support\Csv\CsvReader;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class Crud extends BaseCrud
{
    protected string $primaryKey = 'id';

    /**
     * For auto-discovered validation: App\Validation\<Entity>\CreateRules
     */
    protected ?string $validationEntity = null;

    /**
     * Label-driven insert using model static arrays
     */
    protected bool $useLabelDrivenInsert = true;
    protected ?string $entityClass = null;

    /**
     * Fallback allowlist (used if label-driven disabled/missing)
     */
    protected array $fillable = [];

    /** Quick presence check (optional) */
    protected array $required = [];

    /** Timestamps */
    protected bool $useTimestamps  = true;
    protected string $createdField = 'date_created';
    protected string $updatedField = 'date_modified';

    // Optional soft delete support
    protected bool   $useSoftDeletes  = false;
    protected string $deletedField    = 'deleted_at';
    protected ?string $deletedByField = null; // e.g. 'deleted_by' if you want

    /**
     * If null -> defaults to $this->table
     */
    protected ?string $hooksEntity      = null;
    /**
     * External hooks toggle + order
     */
    protected bool $useExternalHooks = true;

    /**
     * If true: external runs first, else repo runs first
     */
    protected bool $externalObserversFirst = false;
    /**
     * Resolved once per repo instance
     */
    private ?object $observer = null;
    /** @var array{
     *     beforeCreate:bool,
     *     afterCreate:bool,
     *     beforeUpdate:bool,
     *     afterUpdate:bool,
     *     handle:bool,
     *     cleanup:bool
     * }
     */
    private array $obsFlags = [
        'beforeCreate'=>false, 'afterCreate'=>false,
        'beforeUpdate'=>false, 'afterUpdate'=>false,
        'beforeDelete'=>false, 'afterDelete'=>false,
        'handle'=>false, 'cleanup'=>false
    ];
    private bool $hooksReady = false;

    public function __construct($array = [])
    {
        // CAUTION: initializing the hooks here somewhat changes the getTableName() behavior
        parent::__construct($array);
    }

    private function ensureObserver(): void
    {
        if ($this->hooksReady) return;

        if ($this->useExternalHooks) {
            $entity = $this->hooksEntity ?: $this->getTableName();
            [$inst, $flags] = ObserverResolver::resolve($entity);
            $this->observer = $inst;
            $this->obsFlags = $flags;
        }
        $this->hooksReady = true;
    }

    // ---------------- Hooks (receive $extra too and can be overridden by subclass) ----------------
    protected function beforeCreating(array &$data, array $extra): void {}
    protected function afterCreated(int $id, array &$data, array $extra): void {}

    protected function beforeUpdating(int $id, array &$data, array $extra): void {}
    protected function afterUpdated(int $id, array &$data, array $extra): void {}

    protected function beforeDeleting(int $id, array $extra): void {}
    protected function afterDeleted(int $id, array $extra): void {}

    protected function handleUploads(array &$data, array $files, array $extra): void {}
    protected function cleanupUploadsOnFailure(array $data, array $extra = []): void {}


    // ----------------------------------------------------------------
    protected function builder(): BaseBuilder
    {
        return $this->db->table($this->getTableName());
    }

    protected function ensureRequired(array $data): void
    {
        foreach ($this->required as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                throw new ValidationFailedException("Missing required field: {$field}");
            }
        }
    }

    protected function applyTimestamps(array &$data, bool $isInsert = true): void
    {
        if (!$this->useTimestamps) return;
        $now = date('Y-m-d H:i:s');
        if ($isInsert) {
            if ($this->createdField && !isset($data[$this->createdField])) $data[$this->createdField] = $now;
            if ($this->updatedField && !isset($data[$this->updatedField])) $data[$this->updatedField] = $now;
        } else {
            if ($this->updatedField) $data[$this->updatedField] = $now;
        }
    }

    /**
     * Build the working payloads:
     * - $persist: only keys in $labelArray (light cast)
     * - $extra:   all other input keys (NOT thrown away)
     * If label-driven is off/missing, $persist = $input (filtered by $fillable if provided), $extra = [].
     *
     * @return array{0: array, 1: array} [$persist, $extra]
     */
    protected function buildDataPayloads(array $input): array
    {
        if ($this->useLabelDrivenInsert && $this->entityClass) {
            return SubsetSupport::partitionByModelLabel($this->entityClass, $input);
        }

        // fallback to fillable
        $persist = empty($this->fillable) ? $input : array_intersect_key($input, array_flip($this->fillable));
        $extra   = array_diff_key($input, $persist);
        return [$persist, $extra];
    }

    // --------- HOOK DISPATCH CREATE (ORDERED) ----------
    private function runBeforeCreating(array &$data, array $extra): void
    {
        $this->ensureObserver();
        if ($this->externalObserversFirst) {
            if ($this->observer && $this->obsFlags['beforeCreate']) {
                $this->observer->beforeCreating($data, $extra);
            }
            $this->beforeCreating($data, $extra);
        } else {
            $this->beforeCreating($data, $extra);
            if ($this->observer && $this->obsFlags['beforeCreate']) {
                $this->observer->beforeCreating($data, $extra);
            }
        }
    }
    private function runAfterCreated(int $id, array &$data, array $extra): void
    {
        $this->ensureObserver();
        if ($this->externalObserversFirst) {
            if ($this->observer && $this->obsFlags['afterCreate']) {
                $this->observer->afterCreated($id, $data, $extra);
            }
            $this->afterCreated($id, $data, $extra);
        } else {
            $this->afterCreated($id, $data, $extra);
            if ($this->observer && $this->obsFlags['afterCreate']) {
                $this->observer->afterCreated($id, $data, $extra);
            }
        }
    }

    // --------- HOOK DISPATCH UPDATE (ORDERED) ----------
    private function runBeforeUpdating(int $id, array &$data, array $extra): void
    {
        $this->ensureObserver();
        if ($this->externalObserversFirst) {
            if ($this->observer && $this->obsFlags['beforeUpdate']) {
                $this->observer->beforeUpdating($id, $data, $extra);
            }
            $this->beforeUpdating($id, $data, $extra);
        } else {
            $this->beforeUpdating($id, $data, $extra);
            if ($this->observer && $this->obsFlags['beforeUpdate']) {
                $this->observer->beforeUpdating($id, $data, $extra);
            }
        }
    }
    private function runAfterUpdated(int $id, array &$data, array $extra): void
    {
        $this->ensureObserver();
        if ($this->externalObserversFirst) {
            if ($this->observer && $this->obsFlags['afterUpdate']) {
                $this->observer->afterUpdated($id, $data, $extra);
            }
            $this->afterUpdated($id, $data, $extra);
        } else {
            $this->afterUpdated($id, $data, $extra);
            if ($this->observer && $this->obsFlags['afterUpdate']) {
                $this->observer->afterUpdated($id, $data, $extra);
            }
        }
    }

    // --------- HOOK DISPATCH DELETE (ORDERED) ----------
    private function runBeforeDeleting(int $id, array $extra): void
    {
        $this->ensureObserver();
        if ($this->externalObserversFirst) {
            if ($this->observer && $this->obsFlags['beforeDelete']) {
                $this->observer->beforeDeleting($id, $extra);
            }
            $this->beforeDeleting($id, $extra);
        } else {
            $this->beforeDeleting($id, $extra);
            if ($this->observer && $this->obsFlags['beforeDelete']) {
                $this->observer->beforeDeleting($id, $extra);
            }
        }
    }
    private function runAfterDeleted(int $id, array $extra): void
    {
        $this->ensureObserver();
        if ($this->externalObserversFirst) {
            if ($this->observer && $this->obsFlags['afterDelete']) {
                $this->observer->afterDeleted($id, $extra);
            }
            $this->afterDeleted($id, $extra);
        } else {
            $this->afterDeleted($id, $extra);
            if ($this->observer && $this->obsFlags['afterDelete']) {
                $this->observer->afterDeleted($id, $extra);
            }
        }
    }

    private function runHandleUploads(array &$data, array $files, array $extra): void
    {
        $this->ensureObserver();
        if ($this->externalObserversFirst) {
            if ($this->observer && $this->obsFlags['handle']) {
                $this->observer->handleUploads($data, $files, $extra);
            }
            $this->handleUploads($data, $files, $extra);
        } else {
            $this->handleUploads($data, $files, $extra);
            if ($this->observer && $this->obsFlags['handle']) {
                $this->observer->handleUploads($data, $files, $extra);
            }
        }
    }
    private function runCleanupUploads(array $data, array $extra): void
    {
        $this->ensureObserver();
        if ($this->externalObserversFirst) {
            if ($this->observer && $this->obsFlags['cleanup']) {
                $this->observer->cleanupUploads($data, $extra);
            }
            $this->cleanupUploadsOnFailure($data, $extra);
        } else {
            $this->cleanupUploadsOnFailure($data, $extra);
            if ($this->observer && $this->obsFlags['cleanup']) {
                $this->observer->cleanupUploads($data, $extra);
            }
        }
    }

    protected function injectDataToExtra(array &$extra): void
    {
        // Explicit data injection
        $auth = WebSessionManager::currentAPIUser();
        if($auth){
            $extra['current_user'] = $auth;
        }
    }

    /**
     * This automated insert:
     * - Partitions input into persistable + extra (extra is kept)
     * - Ensures required fields (if any) OR - Validates full input (persist + extra) via ValidationResolver
     * - Runs beforeCreate hooks & Runs afterCreate hooks
     * - Applies timestamps (if enabled)
     * - Handles uploads (if any)
     * - Persists to DB (in transaction if enabled)
     * - Invalidates caches
     * - Returns new ID
     * @param array $input
     * @param array $files
     * @param array $options {array: ['dbTransaction']}
     * @return int|null
     * @throws Throwable
     */
    public function insertSingle(array $input, array $files = [], array $options = []): ?int
    {
        $useTx = !array_key_exists('dbTransaction', $options) || $options['dbTransaction'];
        $ctx = $options['context'] ?? [];
        // Partition input -> persistable + extra (EXTRA IS KEPT)
        [$data, $extra] = $this->buildDataPayloads($input);
        $this->injectDataToExtra($extra);

        // Quick presence check on persistable
        if (!empty($this->required)) $this->ensureRequired($data);

        // Auto-validation against FULL input (persist + extra)
        $entityForValidation = $this->validationEntity ?: $this->getTableName();
        ValidationResolver::run($entityForValidation, 'create', array_merge($data, $extra), $ctx);

        // Hooks + timestamps (hooks can use $extra to derive persist fields)
        if (($options['runHooks'] ?? true) === true) $this->runBeforeCreating($data, $extra);
        $this->applyTimestamps($data);
        if (($options['runHooks'] ?? true) === true && !empty($files)) $this->runHandleUploads($data, $files, $extra);

        try {
            if ($useTx) $this->db->transBegin();

            if (!$this->builder()->insert($data)) {
                $error = $this->db->error();
                throw new DatabaseException($error['message'] ?? 'Insert failed');
            }

            $id = (int)$this->db->insertID();
            if ($id <= 0) throw new DatabaseException('Could not obtain insert ID');

            if (($options['runHooks'] ?? true) === true && ($options['runAfterHook'] ?? true) === true) $this->runAfterCreated($id, $data, $extra);

            if ($useTx) $this->db->transCommit();

            // refresh caches
            $this->invalidateAll();
            $this->invalidateById($id);

            return $id;
        } catch (Throwable $e) {
            if ($useTx && $this->db->transStatus() !== false) $this->db->transRollback();
            if (($options['runHooks'] ?? true) === true) $this->runCleanupUploads($data, $extra);
            throw $e;
        }
    }

    /**
     * Update a single row automatically based on defined entity class:
     * - $options:
     *     'dbTransaction'=> bool (default true)
     *     'where'  => array additional where pairs (optimistic guard ('where' => ['tenant_id' => 5]))
     *     'include' => array passed to findById() if you use updateAndShow()
     *
     * Throws your global exceptions (Forbidden/ValidationFailed/Database) — let them bubble.
     * @throws Throwable
     */
    public function updateSingle(int $id, array $input, array $files = [], array $options = []): bool
    {
        // Build payloads and context
        [$data, $extra] = $this->buildDataPayloads($input);
        $this->injectDataToExtra($extra);

        // Validation (authorize + precheck and rules) — pass id for rules
        $entity = $this->validationEntity ?: $this->getTableName();
        $ctx    = $options['context'] ?? [];
        $ctx['id'] = $id; // handy if your authorize() needs it
        ValidationResolver::run($entity, 'update', array_merge($data, $extra, ['id'=>$id]), $ctx);

        // Hooks & timestamps
        if (($options['runHooks'] ?? true) === true) $this->runBeforeUpdating($id, $data, $extra);
        $this->applyTimestamps($data, false);
        if (($options['runHooks'] ?? true) === true && !empty($files)) $this->runHandleUploads($data, $files, $extra);

        // Persist
        $tx = !array_key_exists('dbTransaction', $options) || $options['dbTransaction'];
        if ($tx) $this->db->transBegin();

        try {
            $b = $this->builder();
            $b->where($this->primaryKey, $id);
            if (!empty($options['where']) && is_array($options['where'])) {
                foreach ($options['where'] as $k => $v) {
                    $b->where($k, $v);
                }
            }

            if (!$b->update($data)) {
                $err = $this->db->error();
                throw new DatabaseException($err['message'] ?? 'Update failed');
            }

            // Commit before afterUpdate side effects
            if ($tx) $this->db->transCommit();

            if (($options['runHooks'] ?? true) === true && ($options['runAfterHook'] ?? true) === true) $this->runAfterUpdated($id, $data, $extra);

            // Cache invalidation
            $this->invalidateById($id);
            $this->invalidateAll();

            return true;
        } catch (Throwable $e) {
            if ($tx && $this->db->transStatus() !== false) $this->db->transRollback();
            if (($options['runHooks'] ?? true) === true) $this->runCleanupUploads($data, $extra);
            throw $e;
        }
    }

    /**
     * Update then return the fresh row (if you prefer the row back).
     * @throws Throwable
     */
    public function updateAndShow(
        int $id,
        array $input,
        array $files = [],
        array $options = []
    ): array {
        $ok = $this->updateSingle($id, $input, $files, $options);
        if (!$ok) {
            throw new DatabaseException('Update indicated failure');
        }
        $include = $options['include'] ?? [];
        $select  = $options['select']  ?? null;
        $escape  = (bool)($options['escape'] ?? false);
        $cache   = $options['cache']   ?? [];
        return $this->detail($id, $include, $select, $escape, $cache);
    }

    /**
     * Delete a single row by id.
     * Options:
     *  - 'dbTransaction' => bool (default true)
     *  - 'where' => array extra guards (e.g. 'where' => ['tenant_id' => 5])
     * @throws Throwable
     */
    public function deleteSingle(int $id, array $options = []): bool
    {
        $extra = [];
        $this->injectDataToExtra($extra);

        // Validation (authorize/precheck/rules)
        $entity = $this->validationEntity ?: $this->getTableName();
        $ctx    = $options['context'] ?? [];
        $ctx['id'] = $id;
        ValidationResolver::run($entity, 'delete', ['id' => $id, '__entity__' => $entity] + $extra, $ctx);

        // Hooks
        $this->runBeforeDeleting($id, $extra);

        $tx = !array_key_exists('dbTransaction', $options) || $options['dbTransaction'];
        if ($tx) $this->db->transBegin();

        try {
            $b = $this->builder()->where($this->primaryKey, $id);
            if (!empty($options['where']) && is_array($options['where'])) {
                foreach ($options['where'] as $k => $v) $b->where($k, $v);
            }

            $soft = array_key_exists('soft', $options) ? (bool)$options['soft'] : $this->useSoftDeletes;
            if ($soft) {
                $payload = [];
                $now = date('Y-m-d H:i:s');
                if ($this->deletedField) $payload[$this->deletedField] = $now;
                if ($this->deletedByField && isset($extra['current_user'])) {
                    $uid = is_object($extra['current_user']) ? ($extra['current_user']->id ?? null) : ($extra['current_user']['id'] ?? null);
                    if ($uid !== null) $payload[$this->deletedByField] = (int)$uid;
                }
                if (!$b->update($payload)) {
                    $err = $this->db->error();
                    throw new DatabaseException($err['message'] ?? 'Soft delete failed');
                }
            } else {
                if (!$b->delete()) {
                    $err = $this->db->error();
                    throw new DatabaseException($err['message'] ?? 'Delete failed');
                }
            }

            if ($tx) $this->db->transCommit();

            $this->runAfterDeleted($id, $extra);

            // Cache bust
            $this->invalidateById($id);
            $this->invalidateAll();

            return true;
        } catch (Throwable $e) {
            if ($tx && $this->db->transStatus() !== false) $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Batch import with modes: insert | update | upsert
     *
     * Options (all optional; defaults keep behavior simple):
     *  - mode: 'insert' | 'update' | 'upsert'       (default 'insert')
     *  - delimiter: string                           (default ',')
     *  - maxRows: int|null                           (limit rows, default null = all)
     *  - lowercaseHeaders: bool                      (default true)
     *  - headerMap: array<string,string>             (map CSV headers once: ['course_code'=>'code'])
     *  - validateColumns: string[]                   (required CSV headers; order ignored)
     *  - staticColumns: array                        (defaults merged into each row if missing for data injection)
     *  - preprocessRow: callable(array): array       (normalize/derive; may throw your ApiValidationException)
     *  - finder: callable(array): ?int               (custom match resolver; return id or null)
     *  - matchBy: string[]                           (AND-equality fields in row for matching)
     *  - where: array                                (extra guards during match/update, e.g. ['tenant_id'=>5])
     *  - updateFields: string[]                      (restrict which fields can be updated)
     *  - dbTransaction: bool                         (per-row when allOrNothing=false; default true)
     *  - allOrNothing: bool                          (wrap entire file in one tx; default false)
     *  - stopOnFirstError: bool                      (default false)
     *  - runHooks: bool                              (default true; master switch for repo/observer hooks)
     *  - runAfterHook: bool                          (default true; skip heavy after hooks if false)
     *  - batchSize: int                              (checkpoint every N rows; default 0 = off)
     *  - onBatch: callable(int $batchNo, array &$summary): void   (progress callback)
     *  - onError: callable(array $err): void         (stream every error out; err = ['row'=>n,'messages'=>...])
     *  - collectIds: bool                            (default true; store ids/updated_ids arrays)
     *  - maxErrorSamples: int                        (default 1000; cap in-memory stored errors)
     *
     * @return array{
     *   total:int, inserted:int, updated:int, failed:int,
     *   ids:int[], updated_ids:int[], errors:array<int,array{row:int,messages:mixed}>
     * }
     */
    public function bulkUpload(UploadedFile|string $file, array $options = []): array
    {
        // Core options
        $mode             = strtolower((string)($options['mode'] ?? 'insert'));
        $delimiter        = $options['delimiter']        ?? ',';
        $maxRows          = $options['maxRows']          ?? null;
        $lowercaseHeaders = $options['lowercaseHeaders'] ?? true;
        $headerMap        = $options['headerMap']        ?? null; // this is required

        $validateCols     = $options['validateColumns']  ?? null;
        $static           = $options['staticColumns']    ?? [];
        $preprocess       = $options['preprocessRow']    ?? null;

        $finder           = $options['finder']   ?? null;
        $matchBy          = $options['matchBy']  ?? null;
        $whereGuards      = $options['where']    ?? [];

        $updateFields     = $options['updateFields']     ?? null;
        $authorize        = $options['__authorize__'] ?? null;

        // Transactions & control
        $allOrNothing     = $options['allOrNothing']     ?? false;
        $stopOnFirstError = $options['stopOnFirstError'] ?? false;
        $perRowTx         = $allOrNothing ? false : ($options['dbTransaction'] ?? true);

        // Hooks
        $runHooks         = $options['runHooks']     ?? false;
        $runAfterHook     = $options['runAfterHook'] ?? false;

        // Perf knobs
        $batchSize        = (int)($options['batchSize'] ?? 0);
        $onBatch          = $options['onBatch'] ?? null;
        $onError          = $options['onError'] ?? null;
        $collectIds       = (bool)($options['collectIds'] ?? false);
        $maxErrorSamples  = (int)($options['maxErrorSamples'] ?? 100);

        $errorLogPath    = $options['errorLogPath']    ?? null;
        $errorFlushEvery = (int)($options['errorFlushEvery'] ?? 200);
        $errLogger = $errorLogPath ? new BatchErrorLogger($errorLogPath, $errorFlushEvery, $this->processLogHeader()) : null;

        $summary = [
            'total'       => 0,
            'inserted'    => 0,
            'updated'     => 0,
            'failed'      => 0,
            'ids'         => [],
            'updated_ids' => [],
            'errors'      => [],
        ];

        if ($allOrNothing) $this->db->transBegin();

        $processedSinceBatch = 0;
        $batchNo = 0;
        try {
            foreach (CsvReader::readAssoc($file, $headerMap, $delimiter, $maxRows, $lowercaseHeaders) as [$rowNo, $row]) {

                // Validate required headers once using first data row keys (after headerMap/lowercase)
                if ($summary['total'] === 0 && is_array($validateCols) && $validateCols) {
                    $firstKeys = array_keys($row);
                    $required = array_map(function ($k) use ($lowercaseHeaders, $headerMap) {
                        $k = (string)$k;
                        if ($lowercaseHeaders) $k = strtolower($k);
                        return $headerMap[$k] ?? $k;
                    }, $validateCols);
                    $missing = array_values(array_diff($required, $firstKeys));
                    if ($missing) throw new InvalidArgumentException('CSV is missing required columns: '.implode(', ', $missing));
                }
                $summary['total']++;

                // Defaults (do not overwrite explicitly provided values)
                foreach ($static as $k => $v) {
                    if (!array_key_exists($k, $row)) $row[$k] = $v;
                }

                // Preprocess (normalize, derive, FK, type-cast); may throw ValidationFailedException
                if (is_callable($preprocess)) $row = (array)$preprocess($row);

                try {
                    // Per-row options handed to insert/update
                    $rowOptions = [
                        'context' => [
                            '__authorize__' => $authorize,
                        ],
                        'dbTransaction' => $perRowTx,
                        'runHooks'      => $runHooks,
                        'runAfterHook'  => $runAfterHook,
                    ];

                    if ($mode === 'insert') {
                        $newId = $this->insertSingle($row, [], $rowOptions);
                        if (!$newId) throw new DatabaseException('Insert failed at row '.$rowNo);

                        $summary['inserted']++;
                        if ($collectIds) $summary['ids'][] = $newId;

                    } elseif ($mode === 'update') {
                        $id = $this->findExistingIdForImport($row, $finder, $matchBy, $whereGuards);
                        if (!$id) throw new RuntimeException('Match not found for update at row '.$rowNo);

                        $payload = $this->restrictUpdateFields($row, $updateFields);
                        $ok = $this->updateSingle($id, $payload, [], $rowOptions);
                        if (!$ok) throw new DatabaseException('Update failed at row '.$rowNo);

                        $summary['updated']++;
                        if ($collectIds) $summary['updated_ids'][] = $id;

                    } elseif ($mode === 'upsert') {
                        $id = $this->findExistingIdForImport($row, $finder, $matchBy, $whereGuards);
                        if ($id) {
                            $payload = $this->restrictUpdateFields($row, $updateFields);
                            $ok = $this->updateSingle($id, $payload, [], $rowOptions);
                            if (!$ok) throw new DatabaseException('Update failed at row '.$rowNo);

                            $summary['updated']++;
                            if ($collectIds) $summary['updated_ids'][] = $id;
                        } else {
                            $newId = $this->insertSingle($row, [], $rowOptions);
                            if (!$newId) throw new DatabaseException('Insert failed at row '.$rowNo);

                            $summary['inserted']++;
                            if ($collectIds) $summary['ids'][] = $newId;
                        }
                    } else {
                        throw new InvalidArgumentException("Unknown import mode '{$mode}'");
                    }

                } catch (Throwable $e) {
                    $summary['failed']++;
                    $err = ['row' => $rowNo, 'messages' => $this->toErrorBag($e)];

                    if ($errLogger) $errLogger->add($err['row'], $err['messages']);
                    if (is_callable($onError)) $onError($err);
                    if (count($summary['errors']) < $maxErrorSamples) $summary['errors'][] = $err;

                    if($e instanceof ForbiddenException){
                        if ($allOrNothing || $stopOnFirstError) throw $e;
                        break;
                    }
                    if ($allOrNothing || $stopOnFirstError) throw $e;
                }

                // Batch checkpoint (progress + GC)
                if ($batchSize > 0 && (++$processedSinceBatch % $batchSize) === 0) {
                    $batchNo++;
                    if (is_callable($onBatch)) $onBatch($batchNo, $summary);
                    $processedSinceBatch = 0;
                    if (function_exists('gc_collect_cycles')) gc_collect_cycles();
                }
            }

            if ($allOrNothing) {
                if ($summary['failed'] > 0) $this->db->transRollback();
                else $this->db->transCommit();
            }

            if ($errLogger) $errLogger->close();
            return $summary;

        } catch (Throwable $e) {
            if ($allOrNothing && $this->db->transStatus() !== false) $this->db->transRollback();
            $err = ['row' => 0, 'messages' => $this->toErrorBag($e)];
            if (is_callable($onError)) $onError($err);
            if (count($summary['errors']) < $maxErrorSamples) $summary['errors'][] = $err;
            if ($errLogger) $errLogger->close();
            return $summary;
        }
    }

    /** Error shape helper (matches your global handler messages) */
    private function toErrorBag(Throwable $e): array|string
    {
        // If you already have ErrorBag::fromThrowable(), call that here instead.
        // Return a string or array; importer passes it through verbatim.
        return $e->getMessage() ?: 'Import error';
    }

    /**
     * Resolve existing id for update/upsert.
     * Priority: custom $finder(row) → AND-equality on $matchBy (+$whereGuards).
     */
    private function findExistingIdForImport(array $row, $finder, ?array $matchBy, array $whereGuards): ?int
    {
        if (is_callable($finder)) {
            $id = (int)$finder($row);
            return $id > 0 ? $id : null;
        }

        if (is_array($matchBy) && $matchBy) {
            $b = $this->builder()->select($this->primaryKey)->limit(1);
            foreach ($matchBy as $field) {
                if (!array_key_exists($field, $row)) return null;

                $b->where($field, $row[$field]);
            }
            foreach ($whereGuards as $k => $v) $b->where($k, $v);

            $hit = $b->get()->getRowArray();
            if ($hit && isset($hit[$this->primaryKey])) return (int)$hit[$this->primaryKey];
        }

        return null;
    }

    /**
     * Optionally restrict the update payload to an allowlist of fields.
     * The Primary key is always excluded.
     */
    private function restrictUpdateFields(array $row, ?array $fields): array
    {
        if (!$fields) return $row;
        $allow = array_flip($fields);
        unset($allow[$this->primaryKey]);
        return array_intersect_key($row, $allow);
    }

    private function processLogHeader(){
        $user      = WebSessionManager::currentAPIUser() ?? null;
        $username  = $user->user_login;
        $userInfo  = [
            'title'     => $user->title,
            'firstname' => $user->firstname,
            'lastname'  => $user->lastname,
        ];

        $fullname = ucwords(strtolower(trim("{$userInfo['title']} {$userInfo['firstname']} {$userInfo['lastname']}")));

        $agent = request()->getUserAgent();
        $progressLog = [];
        $progressLog[] = "Process started " . date('l F d, Y h:i:s') . PHP_EOL;
        $progressLog[] = "Uploaded by: {$fullname}" . PHP_EOL;
        $progressLog[] = "Username: {$username}" . PHP_EOL;
        $progressLog[] = "User Agent: " . $agent->getAgentString() . PHP_EOL;
        $progressLog[] = "Browser: " . $agent->getBrowser() . " Version: " . $agent->getVersion() . PHP_EOL;
        $progressLog[] = "IP Address: " . request()->getIPAddress() . PHP_EOL;
        $progressLog[] = "Platform: " . $agent->getPlatform() . PHP_EOL;
        $progressLog[] = "Hostname: " . gethostname() . PHP_EOL;
        $progressLog[] = str_repeat('_', 75) . PHP_EOL . PHP_EOL . PHP_EOL;

        return $progressLog;
    }


}
