<?php

namespace App\Traits\Crud;

use App\Support\Entity\SubsetSupport;
use App\Validation\Resolver\ValidationResolver;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Throwable;

trait EntityCreatorTrait
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

    // ---------------- Hooks (now receive $extra too) ----------------
    protected function beforeCreating(array &$data, array $extra): void {}
    protected function handleUploads(array &$data, array $files, array $extra): void {}
    protected function cleanupUploadsOnFailure(array $data, array $extra = []): void {}
    protected function afterCreated(int $id, array &$data, array $extra): void {}

    // ----------------------------------------------------------------
    protected function builder(): BaseBuilder
    {
        return $this->db->table($this->getTableName());
    }

    protected function ensureRequired(array $data): void
    {
        foreach ($this->required as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }

    protected function applyTimestamps(array &$data, bool $isInsert = true): void
    {
        if (!$this->useTimestamps) return;
        $now = date('Y-m-d H:i:s');
        if ($isInsert && $this->createdField && !isset($data[$this->createdField])) $data[$this->createdField] = $now;
        if ($this->updatedField && !isset($data[$this->updatedField])) $data[$this->updatedField] = $now;
    }

    /**
     * Build the working payloads:
     * - $persist: only keys in $labelArray (light cast)
     * - $extra:   all other input keys (NOT thrown away)
     * If label-driven is off/missing, $persist = $input (filtered by $fillable if provided), $extra = [].
     *
     * @return array{0: array, 1: array} [$persist, $extra]
     */
    protected function buildInsertPayloads(array $input): array
    {
        if ($this->useLabelDrivenInsert && $this->entityClass) {
            return SubsetSupport::partitionByModelLabel($this->entityClass, $input, ['id']);
        }

        // fallback to fillable
        $persist = empty($this->fillable) ? $input : array_intersect_key($input, array_flip($this->fillable));
        $extra   = array_diff_key($input, $persist);
        return [$persist, $extra];
    }

    /**
     * @throws Throwable
     */
    public function create(array $input, array $files = [], array $options = []): ?int
    {
        $useTx   = !array_key_exists('dbTransaction', $options) || (bool)$options['dbTransaction'];

        // Partition input -> persistable + extra (EXTRA IS KEPT)
        [$data, $extra] = $this->buildInsertPayloads($input);

        // Quick presence check on persistable
        if (!empty($this->required)) $this->ensureRequired($data);

        // Auto-validation against FULL input (persist + extra)
        $entityForValidation = $this->validationEntity ?: $this->getTableName();
        ValidationResolver::run($entityForValidation, 'create', array_merge($data, $extra));

        // Hooks + timestamps (hooks can use $extra to derive persist fields)
        $this->beforeCreating($data, $extra);
        $this->applyTimestamps($data, true);
        if (!empty($files)) $this->handleUploads($data, $files, $extra);

        try {
            if ($useTx) $this->db->transBegin();

            if (!$this->builder()->insert($data)) {
                $error = $this->db->error();
                throw new DatabaseException($error['message'] ?? 'Insert failed');
            }

            $id = (int)$this->db->insertID();
            if ($id <= 0) throw new DatabaseException('Could not obtain insert ID');

            $this->afterCreated($id, $data, $extra);

            if ($useTx) $this->db->transCommit();

            // refresh caches
            $this->invalidateAll();
            $this->invalidateById($id);

            return $id;
        } catch (Throwable $e) {
            if ($useTx && $this->db->transStatus() !== false) $this->db->transRollback();
            $this->cleanupUploadsOnFailure($data, $extra);
            throw $e;
        }
    }
}