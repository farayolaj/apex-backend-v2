<?php

namespace App\Models;

use App\Exceptions\ValidationFailedException;
use App\Hooks\Resolver\ObserverResolver;
use App\Support\Entity\SubsetSupport;
use App\Validation\Support\Resolver\ValidationResolver;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
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

    /** If null -> defaults to $this->table */
    protected ?string $hooksEntity      = null;

    /** External hooks toggle + order */
    protected bool $useExternalHooks = true;

    /** If true: external runs first, else repo runs first */
    protected bool $externalObserversFirst = false;

    /** Resolved once per repo instance */
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
        'beforeCreate'=>false,
        'afterCreate'=>false,
        'beforeUpdate'=>false,
        'afterUpdate'=>false,
        'handle'=>false,
        'cleanup'=>false
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
            return SubsetSupport::partitionByModelLabel($this->entityClass, $input, ['id']);
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
     * @param array $input
     * @param array $files
     * @param array $options {array: ['dbTransaction']}
     * @return int|null
     * @throws Throwable
     */
    public function insertSingle(array $input, array $files = [], array $options = []): ?int
    {
        $useTx = !array_key_exists('dbTransaction', $options) || (bool)$options['dbTransaction'];
        // Partition input -> persistable + extra (EXTRA IS KEPT)
        [$data, $extra] = $this->buildDataPayloads($input);
        $this->injectDataToExtra($extra);

        // Quick presence check on persistable
        if (!empty($this->required)) $this->ensureRequired($data);

        // Auto-validation against FULL input (persist + extra)
        $entityForValidation = $this->validationEntity ?: $this->getTableName();
        ValidationResolver::run($entityForValidation, 'create', array_merge($data, $extra));

        // Hooks + timestamps (hooks can use $extra to derive persist fields)
        $this->runBeforeCreating($data, $extra);
        $this->applyTimestamps($data, true);
        if (!empty($files)) $this->runHandleUploads($data, $files, $extra);

        try {
            if ($useTx) $this->db->transBegin();

            if (!$this->builder()->insert($data)) {
                $error = $this->db->error();
                throw new DatabaseException($error['message'] ?? 'Insert failed');
            }

            $id = (int)$this->db->insertID();
            if ($id <= 0) throw new DatabaseException('Could not obtain insert ID');

            $this->runAfterCreated($id, $data, $extra);

            if ($useTx) $this->db->transCommit();

            // refresh caches
            $this->invalidateAll();
            $this->invalidateById($id);

            return $id;
        } catch (\Throwable $e) {
            if ($useTx && $this->db->transStatus() !== false) $this->db->transRollback();
            $this->runCleanupUploads($data, $extra);
            throw $e;
        }
    }

    /**
     * Update a single row.
     * - $id: primary key value
     * - $input: raw payload (label-driven subset will be persisted)
     * - $files: for uploads (optional)
     * - $options:
     *     'dbTransaction'=> bool (default true)
     *     'where'        => array additional where pairs (optimistic guard)
     *     'include'      => array passed to findById() if you use updateAndShow()
     *
     * Throws your global exceptions (Forbidden/ValidationFailed/Database) — let them bubble.
     * @throws Throwable
     */
    public function updateSingle(int $id, array $input, array $files = [], array $options = []): bool
    {
        // Build payloads & context
        [$data, $extra] = $this->buildDataPayloads($input);
        $this->injectDataToExtra($extra);

        // Validation (authorize + precheck + rules) — pass id for rules
        $entity = $this->validationEntity ?: $this->getTableName();
        $ctx    = $options['context'] ?? [];
        $ctx['id'] = $id; // handy if your authorize() needs it
        ValidationResolver::run($entity, 'update', array_merge($data, $extra, ['id'=>$id]), $ctx);

        // Hooks & timestamps
        $this->runBeforeUpdating($id, $data, $extra);
        $this->applyTimestamps($data, false);
        if (!empty($files)) $this->runHandleUploads($data, $files, $extra);

        // Persist
        $tx = !array_key_exists('dbTransaction', $options) || (bool)$options['dbTransaction'];
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

            $this->runAfterUpdated($id, $data, $extra);

            // Cache invalidation
            $this->invalidateById($id);
            $this->invalidateAll();

            return true;
        } catch (\Throwable $e) {
            if ($tx && $this->db->transStatus() !== false) $this->db->transRollback();
            $this->runCleanupUploads($data, $extra);
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
}
