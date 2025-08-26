# CodeIgniter 4 Application Starter

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> - The end of life date for PHP 8.1 will be December 31, 2025.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library


Here’s a clean, GitHub-friendly **README** you can drop into your repo. It explains **how to use** the listing pattern (DTO → Filters → Repository → Controller), with scenarios, examples, cautions, and checklists—without dumping the implementation code.

---

# CI4 API Listing Pattern — Usage Guide

A small, consistent way to build **fast, safe, and testable** list endpoints in CodeIgniter 4.

**What you get out of the box**

* **Defined routes only** (no legacy auto-routing).
* **One predictable response shape**: `{ data, meta }`.
* **Search / Filter / Sort / Count / Pagination** handled once and reused everywhere.
* **Return all rows by default** — pagination applies only when requested.
* Optional **raw select**, **subqueries**, and **raw SQL** while keeping the same pipeline.

This guide shows **how to use** the pattern. It assumes the project already contains:

* `ApiListParams` (request DTO)
* `DictFilters` (applies your internal filter dict to the query builder)
* `BaseListRepository` (shared list pipeline)
* Resource repositories (e.g., `CourseRepository`, `CourseManagerRepository`)
* Optional helpers: `listFromSubquery`, `listFromSQL`

---

## Table of Contents

* [Mental Model](#mental-model)
* [Response Shape](#response-shape)
* [Common Parameters](#common-parameters)
* [The 5 Things You Do Per Endpoint](#the-5-things-you-do-per-endpoint)
* [Scenarios](#scenarios)

    * [1) Simple list (no paging by default)](#1-simple-list-no-paging-by-default)
    * [2) Filters (safe, internal dict)](#2-filters-safe-internal-dict)
    * [3) Free-text search](#3-free-text-search)
    * [4) Sorting (whitelist or default)](#4-sorting-whitelist-or-default)
    * [5) Pagination (only when caller asks)](#5-pagination-only-when-caller-asks)
    * [6) Raw select (computed aliases, groupings)](#6-raw-select-computed-aliases-groupings)
    * [7) Subquery (aggregations/window functions)](#7-subquery-aggregationswindow-functions)
    * [8) Raw SQL (UNION/results from multiple sources)](#8-raw-sql-unionresults-from-multiple-sources)
* [Cautions & Best Practices](#cautions--best-practices)
* [Performance Notes](#performance-notes)
* [Testing Cheatsheet](#testing-cheatsheet)
* [FAQ](#faq)

---

## Mental Model

You’ll touch **only** these places for a given endpoint:

1. **Controller**

    * Parse query params into `ApiListParams`
    * Ask your repository to build safe filters
    * Call `repo->list(...)` (or `listFromSubquery` / `listFromSQL`)
    * Return the result

2. **Repository (per resource)**

    * Declare what’s **searchable**
    * Declare what’s **sortable**
    * (Optionally) choose a **default order**
    * Map public inputs to your **internal filter dict**

> **Internal filter dict**: an array like `['a.code' => 'eco', 'department_id' => 7]`. Keys are **server-controlled** column names (optionally qualified with an alias). Values can be scalars (for `=`), arrays (for `IN (...)`), or `null` (for `IS NULL`).

---

## Response Shape

Every list endpoint returns:

```json
{
  "data": [ /* rows */ ],
  "meta": {
    "page": null,           // null when not paging
    "per_page": null,       // null when not paging
    "total": 123,
    "pages": 1              // 1 when not paging
  }
}
```

---

## Common Parameters

| Param         | Type          | Meaning                                        |
| ------------- | ------------- | ---------------------------------------------- |
| `q`           | string        | Free-text search across whitelisted columns    |
| `sort`        | string        | One of the repo’s whitelisted sort keys        |
| `dir`         | `asc \| desc` | Sort direction                                 |
| `per_page`    | int           | Page size (enables pagination)                 |
| `page`        | int           | Page number (enables pagination)               |
| `start`/`len` | int           | Legacy offset/length (also enables pagination) |
| Filters       | any           | Public params the repo maps to internal dict   |

> **Default behavior:** If **neither** `per_page`/`page` **nor** `start`/`len` is supplied, the endpoint returns **all rows** (sorted).

---

## The 5 Things You Do Per Endpoint

1. **Choose searchable columns** (for `q`).
2. **Choose sortable keys** → actual columns/aliases.
3. **(Optional) default order** (applied if no valid sort is given).
4. **Map public params → internal filter dict** (server-side).
5. **Decide the select**: default columns, or pass a **raw select** when needed.

---

## Scenarios

### 1) Simple list (no paging by default)

**Controller usage (example):**

```php
$params = ApiListParams::fromArray($this->request->getGet(), [
  'maxPerPage' => 100,
  'sort'       => 'code'
]);
$params->filters = $repo->buildFiltersFromInput($this->request->getGet());

// default select (from the repo) and return
return $this->respond($repo->list(null, $params));
```

**Call examples**

* `GET /api/v1/courses` → returns **all** courses (no paging)
* `GET /api/v1/courses?q=eco` → search by “eco”

---

### 2) Filters (safe, internal dict)

**What you do**

* In your repo, read public inputs and create a dict like:

    * Scalar: `['a.active' => 1]` → `WHERE a.active = ?`
    * Array:  `['a.type' => ['core','elective']]` → `WHERE a.type IN (?,?)`
    * Null:   `['a.deleted_at' => null]` → `WHERE a.deleted_at IS NULL`

**Call example**

```
GET /api/v1/courses?department_id=7&type[]=core&type[]=elective
```

Your repo maps to:

```php
['a.department_id' => 7, 'a.type' => ['core','elective']]
```

---

### 3) Free-text search

**What you do**

* In the repo, define `$searchable = ['a.title','a.code']`.
* Client calls: `GET /api/v1/courses?q=eco`.

> Tip: Prefer prefix matches in your implementation when possible for index usage.

---

### 4) Sorting (whitelist or default)

**What you do**

* In the repo, define `$sortable = ['code' => 'a.code', 'id' => 'a.id']`.
* Client calls: `GET /api/v1/courses?sort=code&dir=asc`.
* If `sort` isn’t recognized, the repo’s **default order** runs (e.g., `a.id ASC` or your custom multi-column).

> **Never** pass user input straight into `ORDER BY`. Only use whitelisted keys (or a **server-built** raw order string if you must replicate legacy behavior).

---

### 5) Pagination (only when caller asks)

* **No paging params** → return **all rows** (sorted).
* **Standard**: `?per_page=25&page=2`
* **Legacy**: `?start=25&len=25` (converted internally to page/per\_page)
* `meta.per_page` and `meta.page` are `null` when not paged.

---

### 6) Raw select (computed aliases, groupings)

**When to use:** you need a computed column or alias usable for sorting.

**How to call:**

```php
$rawSelect = 'a.id, a.code, COUNT(m.id) AS materials_count';
$result = $repo->list($rawSelect, $params, /* escape = */ false);
```

**Then sort by the alias (whitelisted):**

```
GET /api/v1/courses?sort=materials&dir=desc
```

> Your repo’s `$sortable` would map `'materials' => 'materials_count'`.

**Caution:** `escape = false` is OK here because **you** constructed the select (never user input).

---

### 7) Subquery (aggregations/window functions)

**When to use:** you need to **shape** a dataset first (GROUP BY, window functions), then still want **search / filters / sort / count / pagination**.

**How to call:**

```php
$result = $repo->listFromSubquery(
  function ($db) {
    // Build and return a CI4 Builder with your joins/groupings
    return $db->table('course_manager a')
      ->select('a.course_manager_id AS manager_id, COUNT(*) AS courses_count', false)
      ->join('courses b', 'b.id = a.course_id')
      ->groupBy('manager_id');
  },
  $params,
  /* searchable */ ['manager_id'],
  /* sortable */   ['courses' => 'x.courses_count', 'id' => 'x.manager_id'],
  /* alias */      'x',
  /* select */     'x.*'
);
```

**Rules of thumb**

* **Don’t** put `ORDER BY` / `LIMIT` **inside** the subquery; let the outer pipeline handle that.
* Filters you pass in `params->filters` should refer to **subquery columns** (e.g., `['x.manager_id' => 42]`).

---

### 8) Raw SQL (UNION/results from multiple sources)

**When to use:** combining different tables into one normalized stream via `UNION` or a complex hand-written query.

**How to call:**

```php
$rawSQL = "
  SELECT a.id, 'course' AS kind, b.title AS title, a.date_created AS created_at
  FROM course_manager a JOIN courses b ON b.id = a.course_id
  UNION ALL
  SELECT w.id, 'webinar' AS kind, w.title AS title, w.created_at
  FROM webinars w
";

$result = $repo->listFromSQL(
  $rawSQL,
  $params,
  /* searchable */ ['title','kind'],
  /* sortable  */  ['created_at' => 'x.created_at', 'title' => 'x.title'],
  /* alias    */    'x',
  /* select   */    'x.*'
);
```

You still get the exact same **search / filters / sort / count / pagination** behavior.

---

## Cautions & Best Practices

* **Filters are server-defined.** Never let clients submit column names. Convert public params to an **internal dict** on the server.
* **Sorting is whitelisted.** Map friendly keys (e.g., `sort=code`) to real columns/aliases in the repo.
* **Raw order?** Only if **you** build it server-side for legacy reasons (e.g., `'a.created_at DESC, course_manager ASC'`).
* **Count first.** The pipeline always clones the query to run `COUNT(*)` **before** `ORDER BY`/`LIMIT`. Don’t use `SQL_CALC_FOUND_ROWS`.
* **Subqueries:** Avoid inner `ORDER BY`/`LIMIT`. Let the outer query control them.
* **Raw select:** Set `$escape = false` **only** when the select string is server-built.

---

## Performance Notes

* **Indexes** matter: align filters and sorts with indexed columns. Prefer prefix search where possible.
* **LIKE '%term%'** can’t use standard indexes; consider `'term%'` if feasible.
* **Cache** the throttler (Redis/Memcached) in production.
* **N+1**: do joins in `baseBuilder()` when you need related fields in list views.

---

## Testing Cheatsheet

Feature tests (end-to-end) are the fastest signal:

* **All rows by default:**
  `GET /api/v1/courses` → `meta.page = null`, `meta.per_page = null`
* **Paging applies only when requested:**
  `GET /api/v1/courses?per_page=10&page=1` → `meta.per_page = 10`
* **Search works:**
  `GET /api/v1/courses?q=eco` → titles/codes match
* **Sorting adheres to whitelist:**
  `GET /api/v1/courses?sort=code&dir=asc`
* **Filters map correctly:**
  `GET /api/v1/courses?department_id=7&type[]=core`
* **Subquery / raw SQL endpoints:**
  verify count, sort, and paging still behave identically

Also handy:

* `php spark routes` — confirm exposed endpoints
* `php spark filter:check get /api/v1/courses` — confirm CORS/throttle

---

## FAQ

**Q: Can I return fewer columns for performance?**
Yes. Pass a custom select string when calling `repo->list($select, $params, false)`.

**Q: How do I add a new filter?**
In the repo’s “build filters” method, map the public param to the internal dict (scalar → `=`, array → `IN`, `null` → `IS NULL`).

**Q: How do I sort by a computed alias?**
Include the alias in your select and whitelist it in `$sortable` (e.g., `'materials' => 'materials_count'`).

**Q: When should I choose subquery vs raw SQL?**

* **Subquery** when you can build it with the query builder (joins/aggregations/window funcs).
* **Raw SQL** when you need `UNION` or the builder becomes awkward. You still get the pipeline on top.

---

> **Keep it boring.** The power of this pattern is that every endpoint looks and behaves the same. You only decide: search fields, sort keys, default order, and how public inputs map to your internal filters. Everything else is handled once.


---

## Examples

### A) Simple Resource (`CourseRepository` + `Courses` controller)

```php
// app/Repositories/CourseRepository.php
<?php
namespace App\Repositories;

final class CourseRepository extends BaseListRepository
{
    protected string $table = 'courses';
    protected array  $searchable = ['a.title','a.code'];
    protected array  $sortable   = ['code'=>'a.code','title'=>'a.title','id'=>'a.id'];

    public function buildFiltersFromInput(array $input): array
    {
        $filters = [];
        if (isset($input['active']))        $filters['a.active'] = (int) $input['active'];
        if (!empty($input['department_id']))$filters['a.department_id'] = (int) $input['department_id'];
        if (isset($input['type']))          $filters['a.type'] = is_array($input['type']) ? $input['type'] : [$input['type']];
        return $filters;
    }
}
```

```php
// app/Controllers/Api/V1/Courses.php
<?php
namespace App\Controllers\Api\V1;

use App\Repositories\CourseRepository;use App\Support\DTO\ApiListParams;use CodeIgniter\RESTful\ResourceController;

final class Courses extends ResourceController
{
    public function index()
    {
        $repo   = new CourseRepository();
        $params = ApiListParams::fromArray($this->request->getGet(), [
            'maxPerPage' => 100,
            'sort'       => 'code',
        ]);

        $params->filters = $repo->buildFiltersFromInput($this->request->getGet());

        $result = $repo->list(
            ['a.id','a.title','a.code','a.active','a.department_id','a.created_at'],
            $params
        );

        return $this->respond($result);
    }
}
```

---

### B) Complex Join & Computed Alias (`CourseManagerRepository`)

Replicates: joins, computed `course_manager`, dept filter (`api_department`), default multi-column order.

```php
// app/Repositories/CourseManagerRepository.php
<?php
namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;

final class CourseManagerRepository extends BaseListRepository
{
    protected string $table = 'course_manager';

    protected array $searchable = ['b.code','b.title','e.lastname','e.firstname','e.title'];
    protected array $sortable   = [
        'date_created'   => 'a.date_created',
        'course_code'    => 'b.code',
        'course_title'   => 'b.title',
        'course_manager' => 'course_manager',
        'id'             => 'a.id',
    ];

    protected function baseBuilder(): BaseBuilder
    {
        return $this->db->table('course_manager a')
            ->join('courses b',  'b.id = a.course_id')
            ->join('sessions c', 'c.id = a.session_id')
            ->join('users_new d','d.id = a.course_manager_id AND d.user_type = "staff"', 'left')
            ->join('staffs e',   'e.id = d.user_table_id', 'left')
            ->join('department f','f.id = b.department_id', 'left');
    }

    protected function defaultSelect()
    {
        return '
            a.id,
            c.date AS session,
            a.course_lecturer_id,
            b.code  AS course_code,
            b.title AS course_title,
            CASE
                WHEN a.course_manager_id IS NULL THEN "N/A"
                ELSE CONCAT(COALESCE(e.title, ""), " ", e.lastname, " ", e.firstname)
            END AS course_manager,
            a.course_manager_id,
            a.course_e_tutor_id,
            a.course_question_tutor_id
        ';
    }

    protected function applyDefaultOrder(BaseBuilder $b): void
    {
        $b->orderBy('a.date_created', 'DESC')
          ->orderBy('course_manager', 'ASC', false); // alias
    }

    public function buildFiltersFromInput(array $input): array
    {
        $filters = [];
        if (isset($input['api_department']) && $input['api_department'] !== '') {
            $filters['f.id'] = (int) $input['api_department']; // WHERE f.id = ?
        }
        if (isset($input['filters']) && is_array($input['filters'])) {
            foreach ($input['filters'] as $col => $val) $filters[$col] = $val;
        }
        return $filters;
    }
}
```

```php
// app/Controllers/Api/V1/CourseManager.php
<?php
namespace App\Controllers\Api\V1;

use App\Repositories\CourseManagerRepository;use App\Support\DTO\ApiListParams;use CodeIgniter\RESTful\ResourceController;

final class CourseManager extends ResourceController
{
    public function index()
    {
        $repo   = new CourseManagerRepository();
        $params = ApiListParams::fromArray($this->request->getGet(), [
            'maxPerPage' => 200,
            'sort'       => 'date_created',
            'dir'        => 'desc',
        ]);

        // Optionally replicate a server-built ORDER BY:
        // $params->rawOrder = 'a.date_created DESC, course_manager ASC';

        $params->filters = $repo->buildFiltersFromInput($this->request->getGet());

        $result = $repo->list(null, $params, false); // default raw select; aliases included
        return $this->respond($result);
    }
}
```

---

### C) Aggregated Analytics (`listFromSubquery`)

Compute once with a subquery (GROUP BY / window funcs), then reuse pipeline:

```php
// app/Repositories/CourseAnalyticsRepository.php
<?php
namespace App\Repositories;

use App\Support\DTO\ApiListParams;use CodeIgniter\Database\BaseBuilder;

final class CourseAnalyticsRepository extends BaseListRepository
{
    public function managerSummary(ApiListParams $p, array $input): array
    {
        $p->filters = $this->mapFilters($input);

        $searchable = ['manager_name','department_name'];
        $sortable   = [
            'courses'      => 'x.courses_count',
            'last_session' => 'x.last_session',
            'manager_name' => 'x.manager_name',
            'id'           => 'x.manager_id',
        ];

        return $this->listFromSubquery(
            function ($db): BaseBuilder {
                return $db->table('course_manager a')
                    ->select("
                        a.course_manager_id AS manager_id,
                        CONCAT(COALESCE(e.title,''),' ', e.lastname, ' ', e.firstname) AS manager_name,
                        f.name AS department_name,
                        COUNT(a.id) AS courses_count,
                        MAX(c.date) AS last_session
                    ", false)
                    ->join('courses b',  'b.id = a.course_id')
                    ->join('sessions c', 'c.id = a.session_id')
                    ->join('users_new d','d.id = a.course_manager_id AND d.user_type = \"staff\"', 'left')
                    ->join('staffs e',   'e.id = d.user_table_id', 'left')
                    ->join('department f','f.id = b.department_id', 'left')
                    ->groupBy('manager_id, manager_name, department_name');
            },
            $p,
            $searchable,
            $sortable,
            'x',
            'x.*'
        );
    }

    private function mapFilters(array $in): array
    {
        $filters = [];
        if (!empty($in['manager_id']))    $filters['manager_id'] = (int) $in['manager_id'];
        if (!empty($in['department_name']))$filters['department_name'] = (string) $in['department_name'];
        return $filters;
    }
}
```

Controller:

```php
// app/Controllers/Api/V1/CourseAnalytics.php
<?php
namespace App\Controllers\Api\V1;

use App\Repositories\CourseAnalyticsRepository;use App\Support\DTO\ApiListParams;use CodeIgniter\RESTful\ResourceController;

final class CourseAnalytics extends ResourceController
{
    public function managerSummary()
    {
        $repo   = new CourseAnalyticsRepository();
        $params = ApiListParams::fromArray($this->request->getGet(), ['maxPerPage' => 200]);
        $result = $repo->managerSummary($params, $this->request->getGet());
        return $this->respond($result);
    }
}
```

---

### D) UNION / Raw SQL (`listFromSQL`)

Combine different sources into one normalized stream, but still get search/sort/paging:

```php
// app/Repositories/SearchRepository.php
<?php
namespace App\Repositories;

use App\Support\DTO\ApiListParams;

final class SearchRepository extends BaseListRepository
{
    public function globalSearch(ApiListParams $p, array $input): array
    {
        $p->filters = $this->mapFilters($input);

        $raw = "
            SELECT a.id, 'course' AS kind, b.title AS title, a.date_created AS created_at
            FROM course_manager a
            JOIN courses b ON b.id = a.course_id

            UNION ALL

            SELECT w.id, 'webinar' AS kind, w.title AS title, w.created_at
            FROM webinars w
        ";

        $searchable = ['title','kind'];
        $sortable   = [
            'created_at' => 'x.created_at',
            'title'      => 'x.title',
            'kind'       => 'x.kind',
            'id'         => 'x.id',
        ];

        return $this->listFromSQL($raw, $p, $searchable, $sortable, 'x', 'x.*');
    }

    private function mapFilters(array $in): array
    {
        $filters = [];
        if (!empty($in['kinds'])) $filters['kind'] = (array) $in['kinds']; // IN (...)
        return $filters;
    }
}
```

---

Minimal feature test:

```php
// tests/Feature/CoursesListTest.php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class CoursesListTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testListAllByDefault(): void
    {
        $res = $this->get('api/v1/courses');
        $res->assertStatus(200);
        $json = json_decode($res->getJSON(), true);
        $this->assertArrayHasKey('data', $json);
        $this->assertNull($json['meta']['per_page']); // no paging
    }

    public function testPaginationWhenRequested(): void
    {
        $res = $this->get('api/v1/courses?per_page=10&page=1');
        $res->assertStatus(200);
        $json = json_decode($res->getJSON(), true);
        $this->assertSame(10, $json['meta']['per_page']);
    }
}
```

---

## Example with Fetch
# `findById` — Quick Usage Guide

---

## Endpoint shape

```
GET /api/v1/<resource>/:id
```

Returns a single JSON object or **404** if not found.

---

## Minimal controller usage

```php
// app/Controllers/Api/V1/Staffs.php (excerpt)
public function show(int $id)
    {
        if ($id <= 0) return $this->failValidationErrors(['id'=>'Invalid id']);

        $include = array_filter(explode(',', (string)$this->request->getGet('include')));
        $view    = (string)$this->request->getGet('view'); // '', 'lean', 'profile'
        $select  = null; $escape = false; $selectTag = null;

        if ($view === 'lean') {
            $select    = 'a.id, a.firstname, a.lastname, a.title';
            $selectTag = 'lean';
        } elseif ($view === 'profile') {
            $select    = 'a.id, a.firstname, a.lastname, a.title, a.avatar, a.department_id';
            $selectTag = 'profile';
        } else {
            // default 'a.*' → tag 'default' (optional)
            $selectTag = 'default';
        }

        $noCache = (int)$this->request->getGet('no_cache') === 1;
        $ttl     = is_numeric($this->request->getGet('ttl')) ? max(0,(int)$this->request->getGet('ttl')) : null;

        $cacheOptions = array_filter([
            'enabled'    => !$noCache,
            'ttl'        => $ttl,
            'namespace'  => 'staffs',
            'extra'      => '',         // e.g. tenant/locale
            'select_tag' => $selectTag, // <- avoids hashing
        ], static fn($v) => $v !== null);

        $repo = new StaffRepository();
        $row  = $repo->findById($id, $include, $select, $escape, $cacheOptions);

        return $row ? $this->respond($row) : $this->failNotFound('Staff not found');
    }
```

---

## What the repository guarantees

* **Default select**: `'a.*'` if you pass `null`.
* **Select overrides**: pass string/array; repo **always ensures `a.id`** is included.
* **Includes**: implemented **in each child repo** and **append** their own selects (won’t break your override).
* **Caching**: on by default; keyed by namespace, id, includes, select signature, and `extra`.

---

## Common recipes

### 1) Default (no includes, default select)

```
GET /api/v1/staffs/42
```

Controller: `findById($id);`

### 2) With includes

```
GET /api/v1/staffs/42?include=user,role
```

Controller: `findById($id, ['user','role']);`

### 3) Compact view (override select)

```php
$select = 'a.id, a.firstname, a.lastname, a.title';
$row = $repo->findById($id, [], $select, false);
```

### 4) Computed field (raw expression)

```php
$select = 'a.id, CONCAT(a.lastname, ", ", a.firstname) AS full_name';
$row = $repo->findById($id, [], $select, false); // escape=false for raw
```

### 5) Cache controls (per-call)

```php
// disable cache
$row = $repo->findById($id, [], null, false, ['enabled' => false]);

// custom TTL + tenant-scoped key
$row = $repo->findById($id, [], null, false, ['ttl' => 1800, 'extra' => 'tenant=school-42']);
```

### 6) Invalidate after writes

```php
$repo->invalidateById($id);  // after update/delete of that record
$repo->invalidateAll();      // after bulk changes
```

---

## Notes

* Keep **includes** logic inside the **child repository** (e.g., joins for `user`, `role`, `department`).
* `postProcessOne()` should handle missing fields gracefully if you ship lean selects.
* Use a robust cache driver (Redis/Memcached) in production.

--

## Example with Insertion
# `insert` — Quick Usage Guide
```php
// Auto-discovered validator: App\Validation\Courses\CreateRules
protected ?string $validationEntity = 'courses';

// Label-driven insert from static dictionaries
protected bool $useLabelDrivenInsert = true;
protected ?string $entityClass        = \App\Entities\Course::class;

// Timestamps using 'date_created'
protected bool   $useTimestamps = true;
protected string $createdField  = 'date_created';
protected string $updatedField  = 'updated_at'; // '' if unused

/**
 * Use EXTRA inputs that are NOT in $labelArray.
 * Example: accept 'department_code' and map it to 'department_id' before insert.
 * Also accept a custom 'audit_note' used only in afterInsert (not persisted).
 */
protected function beforeInsert(array &$data, array $extra): void
{
    // Normalize code (persistable)
    if (isset($data['code'])) {
        $data['code'] = strtoupper(trim((string)$data['code']));
    }

    // Derive department_id from department_code (EXTRA)
    if (!isset($data['department_id']) && !empty($extra['department_code'])) {
        $dept = $this->db->table('department')->select('id')
            ->where('code', (string)$extra['department_code'])
            ->get()->getRowArray();

        if ($dept && isset($dept['id'])) {
            $data['department_id'] = (int)$dept['id'];
        }
    }

    // Default active = 1 if not provided
    if (!isset($data['active'])) {
        $data['active'] = 1;
    }
}

/**
 * Handle file uploads from EXTRA (e.g., 'course_guide' file) and persist path in $data.
 */
protected function handleUploads(array &$data, array $files, array $extra): void
{
    if (!isset($files['course_guide'])) return;
    $file = $files['course_guide'];
    if (!$file->isValid()) return;

    $dir = WRITEPATH . 'uploads/course_guides';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($data['code'] ?? 'course'));
    $ext  = $file->getExtension();
    $name = $safe . '_' . time() . '.' . $ext;

    $file->move($dir, $name, true);
    $data['course_guide_url'] = 'uploads/course_guides/' . $name;
    $data['__uploaded_course_guide'] = $data['course_guide_url']; // for cleanup on failure
}

/**
 * Use EXTRA 'audit_note' for audit log (not persisted to courses table).
 */
protected function afterInsert(int $id, array &$data, array $extra): void
{
    $this->db->table('audit_log')->insert([
        'entity'     => 'course',
        'entity_id'  => $id,
        'action'     => 'create',
        'details'    => json_encode([
            'code'       => $data['code'] ?? null,
            'audit_note' => $extra['audit_note'] ?? null,
        ], JSON_UNESCAPED_SLASHES),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

protected function cleanupUploadsOnFailure(array $data, array $extra = []): void
{
    if (!empty($data['__uploaded_course_guide'])) {
        @unlink(WRITEPATH . $data['__uploaded_course_guide']);
    }
}

```

# Create Flow (CI4) — Usage Guide

---

## What you get

* **One call**: `$repo->create($payload, $files, $options)`
* **Automatic**: authorization (optional), prechecks (DB), CI4 rules, transactions, uploads, observers, cache busting, and consistent JSON errors (via global handler).
* **Convention** over config: predictable file/dir names.

---

## Directory & Naming

```
app/
  Validation/
    Courses/
      CreateRules.php                 # Create validator for 'courses' entity
  Hooks/
    Observers/
      Courses.php                     # Observer for 'courses' entity (optional)
```

**Conventions**

* Entity name = DB table (e.g., `courses`).
* `CreateRules` lives at `App\Validation\<StudlyEntity>\CreateRules`.
* Observer class lives at `App\Hooks\Observers\<StudlyEntity>`.

---

## Quick Start (5 steps)

1. **Repository**
   In your child repo, set the table and (optionally) the observer entity:

   ```php
   final class CourseRepository extends BaseCrudRepository
   {
       protected string  $table       = 'courses';
       protected ?string $hooksEntity = 'courses'; // optional, defaults to $table
   }
   ```

2. **Validation** (authorize → precheck → rules/messages)
   Create `app/Validation/Courses/CreateRules.php`:

   ```php
   final class CreateRules implements RulesProvider
   {
       public static function authorize(array $data, array $ctx): bool { /* return true/false */ }
       public static function denyMessage(): string { return '...' ; }
       public static function precheck(array $data): void { /* throw ApiValidationException::field(...) */ }
       public static function rules(): array { return [/* CI4 rules */]; }
       public static function messages(): array { return [/* CI4 messages */]; }
   }
   ```

3. **Observer (hooks)** — optional
   Create `app/Hooks/Observers/Courses.php` with any of:

   ```php
   final class Courses
   {
       public function beforeInsert(array &$data, array &$extra): void {}
       public function handleUploads(array &$data, array $files, array &$extra): void {}
       public function afterInsert(int $id, array &$data, array &$extra): void {}
       public function cleanupUploads(array $data, array &$extra): void {}
   }
   ```

4. **Global JSON errors**
   Ensure your global handler is set (so controllers don’t need try/catch):

   ```php
   // app/Config/Exceptions.php
   public ?string $handler = \App\Exceptions\JsonExceptionHandler::class;
   ```

5. **Controller usage**

   ```php
   $repo = new CourseRepository();

   $payload = $this->request->getJSON(true) ?: $this->request->getPost();
   $files   = $this->request->getFiles() ?? [];

   $row = $repo->create($payload, $files, [
       'transaction' => true,                         // default true
   ]);

   return $this->respondCreated($row, site_url('api/v1/courses/'.$row['id']));
   ```

---

## The Create Flow (what happens)

1. **Payload split**

    * `data`: keys allowed by your model’s `static $labelArray` (persisted).
    * `extra`: all other keys (for hooks/logic; not auto-persisted).

2**Validation, in order**

    * `authorize($data, $ctx)` → if false → **403** (no DB work).
    * `precheck($data)` → can query DB and throw **ApiValidationException** with custom messages.
    * CI4 `rules()/messages()` → standard validation → **422** with field bag.

3**Observers (hooks)**

    * `beforeInsert(&$data, $extra)` → normalize/derive fields; anything you add stays for later hooks.
    * `handleUploads(&$data, $files, $extra)` → move files, set persisted paths.
    * Transaction insert.
    * `afterInsert($id, &$data, $extra)` → side effects (audit, events…).
    * On failure → `cleanupUploads($data, $extra)` → delete moved files, etc.

4**Cache**

    * List/entity caches are invalidated automatically after commit.

---

## Validation: how to use it

* Put everything for an action in **one class**: `CreateRules`.
* **Authorize** quickly:

  ```php
  public static function authorize(array $data, array $ctx): bool
  {
      $roles = array_map('strtolower', (array)($ctx['roles'] ?? []));
      return in_array('admin', $roles, true);
  }
  public static function denyMessage(): string { return 'Not allowed.'; }
  ```
* **Precheck** for richer errors:

  ```php
  public static function precheck(array $data): void
  {
      if (!empty($data['code'])) {
          $row = db_connect()->table('courses')->select('id,title')
                ->where('code', (string)$data['code'])->get()->getRowArray();
          if ($row) {
              throw \App\Exceptions\ApiValidationException::field(
                  'code', "Course '{$data['code']}' exists (ID: {$row['id']})."
              );
          }
      }
  }
  ```
* **Rules/messages** remain standard CI4 arrays.

**Error shape** (via global handler)

```json
// 422 (validation)
{ "status": 422, "error": 422, "messages": { "code": "Course 'ECO101' exists (ID: 7)." } }

// 403 (authorize)
{ "status": 403, "error": 403, "messages": "Not allowed." }
```

---

## Observers: how to use them

Create one class per entity (optional but recommended):

`app/Hooks/Observers/Courses.php`

```php
final class Courses
{
  public function beforeInsert(array &$data, array &$extra): void
  {
    $user = $extra['auth'] ?? null;
    if ($user) $data['created_by'] = $user->id ?? ($user['id'] ?? null);

    // normalize or derive fields
    if (isset($data['code'])) $data['code'] = strtoupper($data['code']);
    if (!isset($data['active'])) $data['active'] = 1;

    // add transient data for later hooks
    $extra['trace_id'] = bin2hex(random_bytes(6));
  }

  public function handleUploads(array &$data, array $files, array &$extra): void
  {
    if (!isset($files['course_guide'])) return;
    $f = $files['course_guide']; if (!$f->isValid()) return;

    $dir = WRITEPATH.'uploads/course_guides'; if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $name = strtolower($data['code'] ?? 'course') . '_' . time() . '.' . $f->getExtension();
    $f->move($dir, $name, true);

    $data['course_guide_url']        = 'uploads/course_guides/'.$name;
    $data['__uploaded_course_guide'] = $data['course_guide_url']; // used by cleanupUploads
  }

  public function afterInsert(int $id, array &$data, array &$extra): void
  {
    db_connect()->table('audit_log')->insert([
      'entity' => 'course', 'entity_id' => $id, 'action' => 'create',
      'details' => json_encode(['trace_id'=>$extra['trace_id'] ?? null], JSON_UNESCAPED_SLASHES),
      'created_at' => date('Y-m-d H:i:s'),
    ]);
  }

  public function cleanupUploads(array $data, array &$extra): void
  {
    if (!empty($data['__uploaded_course_guide'])) {
      @unlink(WRITEPATH . $data['__uploaded_course_guide']);
    }
  }
}
```
---

## Controller: passing auth & includes

  ```php
  $row = $repo->create($payload, $files, [
      'include' => [], // if your repo supports includes on show
  ]);
  ```
* `include`, `select`, `transaction` are optional.

**422 Validation**

```json
{ "status": 422, "error": 422, "messages": { "code": "Course 'ECO101' exists (ID: 7)." } }
```

**403 Forbidden**

```json
{ "status": 403, "error": 403, "messages": "You do not have permission to create courses." }
```

---

## Options you can tweak
* `select` (string|array): override fields for the return fetch.
* `transaction` (bool): default `true`.
* In your repo:

    * `protected bool $externalFirst = false;` (run observer hooks before repo hooks if `true`).
    * `protected bool $useExternalHooks = true;` (disable observers if `false`).
    * `protected ?string $hooksEntity = 'courses';` (usually equals `$table`).

---

## Common pitfalls

* **Not setting the global exception handler** → you’ll see HTML errors. Fix `app/Config/Exceptions.php`.
* **Mismatched entity names** → Observer class name must match the entity/table (`Courses` for `courses`).

---

# CSV Import (Batch / Update / Upsert)

Lightweight, fast CSV imports that reuse your existing repository pipeline (`insertSingle` / `updateSingle`) with **CI4 validation**, **observers/hooks**, and **clear error reporting**. Tuned for **large files**.


## TL;DR (Quick Start)

```php
$result = $repo->importCsv($file, [
  'mode'            => 'upsert',                 // insert | update | upsert
  'delimiter'       => ',',                      // ',', ';', or "\t"
  'headerMap'       => ['course_code' => 'code', 'course_title' => 'title'],
  'validateColumns' => ['course_code','course_title'],
  'staticColumns'   => ['active' => 1],          // defaults if missing in CSV
  'preprocessRow'   => fn(array $r) => [         // normalize/derive before validate/insert/update
      'code'  => strtoupper(trim($r['code'] ?? '')),
      'title' => trim($r['title'] ?? ''),
  ],
  'finder'          => fn(array $r) => $map[$r['code'] ?? ''] ?? null,  // return existing id or null
  'batchSize'       => 1000,                     // progress checkpoint
  'runAfterHook'    => false,                    // skip heavy after-hooks for speed
]);

```

----------

## What the importer does

-   Streams the CSV (no giant arrays in memory).

-   Remaps headers **once** (`headerMap`), lowercases them (`lowercaseHeaders=true`).

-   Optionally checks the CSV has the columns you expect (`validateColumns`, order not important).

-   Per row: `preprocessRow` → CI4 validation (`authorize` → `precheck` → `rules`) → observers/hooks → insert/update.

-   Flexible matching for update/upsert via `finder` **or** `matchBy` (AND-equality) + optional `where` guards.

-   Clear summary with counts and sampled errors; supports **file-based batched error logging** for huge runs.


----------


| Option             | Type                             | Default  | What it does                                                                    |
| ------------------ | -------------------------------- | -------- | ------------------------------------------------------------------------------- |
| `mode`             | `insert` \| `update` \| `upsert` | `insert` | Import behavior.                                                                |
| `delimiter`        | string                           | `,`      | CSV separator (`,` `;` or `"\t"`).                                              |
| `lowercaseHeaders` | bool                             | `true`   | Lowercase header names before use.                                              |
| `maxRows`          | int\|null                        | `null`   | Process only first N data rows (header not counted).                            |
| `headerMap`        | array                            | `null`   | One-time mapping `['csv_name'=>'model_field']`.                                 |
| `validateColumns`  | string\[]                        | `null`   | Required CSV headers (order ignored); checks once before rows.                  |
| `staticColumns`    | array                            | `[]`     | Defaults merged into each row **if missing**.                                   |
| `preprocessRow`    | `callable(array):array`          | `null`   | Normalize/derive per row; may throw your validation exception to fail that row. |
| `finder`           | `callable(array):?int`           | `null`   | Custom “find existing id” (returns id or `null`).                               |
| `matchBy`          | string\[]                        | `null`   | AND-equality on fields present in row to find existing record.                  |
| `where`            | array                            | `[]`     | Extra guards during matching (e.g., `['tenant_id'=>5]`).                        |
| `updateFields`     | string\[]                        | `null`   | Only these fields are updated (whitelist).                                      |
| `auth`             | mixed                            | `null`   | Passed to hooks/validation via `$extra['auth']` / `$ctx`.                       |
| `dbTransaction`    | bool                             | `true`   | Per-row transaction (when `allOrNothing=false`).                                |
| `allOrNothing`     | bool                             | `false`  | One big transaction for the whole file; any error rolls back all.               |
| `stopOnFirstError` | bool                             | `false`  | Stop immediately on first row error (keeps prior successes).                    |
| `runHooks`         | bool                             | `true`   | Master switch for repository/observer hooks.                                    |
| `runAfterHook`     | bool                             | `true`   | Skip only the after-hook (keep before/upload hooks).                            |
| `batchSize`        | int                              | `0`      | Checkpoint every N rows (call `onBatch`, run GC).                               |
| `onBatch`          | `callable(int,array&):void`      | `null`   | Progress callback at each checkpoint.                                           |
| `onError`          | `callable(array):void`           | `null`   | Stream each error (row + messages) out-of-band.                                 |
| `collectIds`       | bool                             | `true`   | Store `ids`/`updated_ids` arrays; set `false` to save memory.                   |
| `maxErrorSamples`  | int                              | `1000`   | Cap how many errors are kept in-memory in `summary.errors`.                     |
| `errorLogPath`     | string\|null                     | `null`   | If set, write errors to this file in batches.                                   |
| `errorFlushEvery`  | int                              | `200`    | Batch size for file writes (with `errorLogPath`).                               |
| `errorLogHeader`   | string\|array\|null              | `null`   | Meta header written once at top of the error log file.                          |

> **Validation & Authorization:** Your existing rules classes still run (`authorize($data,$ctx)`/`precheck($data)`/`rules()`). Pass context via `options['context']` if you use it; `__authrorize__` is forwarded for hooks.

## Recipes

### 1) Minimal insert

```php
$repo->importCsv($file, [
  'mode'            => 'insert',
  'delimiter'       => ',',
  'validateColumns' => ['course_code','course_title'],
  'headerMap'       => ['course_code'=>'code','course_title'=>'title'],
  'preprocessRow'   => fn($r) => ['code'=>strtoupper(trim($r['code'] ?? '')), 'title'=>trim($r['title'] ?? '')],
  'auth'            => $user,
]);

```

### 2) Update only (match on fields; restrict what can change)

```php
$repo->importCsv($file, [
  'mode'            => 'update',
  'headerMap'       => ['course_code'=>'code'],
  'validateColumns' => ['course_code'],
  'staticColumns'   => ['tenant_id'=>5],
  'matchBy'         => ['code','tenant_id'],            // must be in each row
  'updateFields'    => ['title','description','type'],  // only these are updated
  'auth'            => $user,
]);

```

### 3) Upsert with preloaded dictionary (fast for big files)

```php
// Build in-memory map once: "CODE|DEPT_ID" => id
$rows = db_connect()->table('courses')
    ->select('id, UPPER(code) as code, department_id')->get()->getResultArray();
$map = [];
foreach ($rows as $r) $map[$r['code'].'|'.$r['department_id']] = (int)$r['id'];

$repo->importCsv($file, [
  'mode'             => 'upsert',
  'headerMap'        => ['course_code'=>'code','department_code'=>'department_code','course_title'=>'title'],
  'validateColumns'  => ['course_code','department_code','course_title'],
  'staticColumns'    => ['active'=>1],
  'preprocessRow'    => function(array $r): array {
      static $dept = []; $db = db_connect();
      $r['code'] = strtoupper(trim($r['code'] ?? ''));
      if (!empty($r['department_code'])) {
          $c = strtoupper(trim($r['department_code']));
          if (!isset($dept[$c])) $dept[$c] = (int)$db->table('department')->select('id')->where('code',$c)->get()->getRow('id');
          if ($dept[$c] <= 0) throw \App\Exceptions\ApiValidationException::field('department_code', "Unknown department '{$c}'.");
          $r['department_id'] = $dept[$c];
      }
      unset($r['department_code']);
      return $r;
  },
  'finder'           => fn(array $r) => ($map[strtoupper($r['code'] ?? '').'|'.(int)($r['department_id'] ?? 0)] ?? null),
  'batchSize'        => 1000,
  'runAfterHook'     => false,
  'collectIds'       => false,
  'maxErrorSamples'  => 200,
  'auth'             => $user,
]);

```

### 4) Fail-fast / transactional modes

```php
// Stop on first error (keep earlier successes)
$repo->importCsv($file, ['stopOnFirstError' => true]);

// All or nothing (one big transaction; any error rolls back all)
$repo->importCsv($file, ['allOrNothing' => true]);

```

### 5) Large file + batched error logging to disk

```php
$logPath = WRITEPATH.'imports/errors/'.date('Ymd_His').'_courses.err.log';

// Optional: header at top of the file
$agent = $this->request->getUserAgent();
$header = [
  "Process started ".date('l F d, Y h:i:s').PHP_EOL,
  "Uploaded by: {$fullname}".PHP_EOL,
  "Username: {$username}".PHP_EOL,
  "User Agent: ".$agent->getAgentString().PHP_EOL,
  "Browser: ".$agent->getBrowser()." Version: ".$agent->getVersion().PHP_EOL,
  "IP Address: ".$this->request->getIPAddress().PHP_EOL,
  "Platform: ".$agent->getPlatform().PHP_EOL,
  "Hostname: ".gethostname().PHP_EOL,
  str_repeat('_', 75).PHP_EOL.PHP_EOL.PHP_EOL,
];

$repo->importCsv($file, [
  // …normal options…
  'batchSize'        => 2000,
  'collectIds'       => false,
  'maxErrorSamples'  => 100,          // keep API light; full log goes to file
  'errorLogPath'     => $logPath,
  'errorFlushEvery'  => 500,          // flush in batches
  'errorLogHeader'   => $header,      // meta info once at top
]);

```

----------

## Understanding the key helpers

-   **`headerMap`**: Make CSV columns look like your model fields once (cheaper than remapping every row).

-   **`validateColumns`**: Presence-only check (no order); fails before any rows are processed.

-   **`staticColumns`**: Defaults for missing fields per row (won’t overwrite CSV values).

-   **`preprocessRow`**: Normalize, type-cast, derive FKs; throw your validation exception to fail a single row.

-   **`finder`**: Custom resolver that returns an **ID** or `null` (use preloaded dictionaries for speed).

-   **Hooks**: `runHooks=false` skips all hooks; `runAfterHook=false` skips only the after-hook (keep validation & before/upload hooks).


----------

## Error semantics

-   With `allOrNothing=false` (default):

    -   Errors are per-row; we **continue** unless `stopOnFirstError=true`.

    -   Counts still reflect total/inserted/updated/failed.

-   With `allOrNothing=true`:

    -   First error aborts and **rolls back everything**.

-   `errors` array in the summary is **capped** by `maxErrorSamples`.  
    For every error, use `errorLogPath` (batched file) or `onError` (stream) to capture **all** rows without memory bloat.


----------

## Performance tips

-   Preload dictionaries for matching (O(1) `finder`) instead of per-row DB queries.

-   Use `headerMap` so you don’t remap keys per row.

-   Use `batchSize` (e.g., 1000–2000) + `onBatch` to monitor progress and keep memory flat.

-   Set `collectIds=false` and a reasonable `maxErrorSamples` (e.g., 100–200).

-   Skip `runAfterHook` in bulk runs if it does heavy work (audit/events).

-   Prefer per-row transactions for resilience; use `allOrNothing` only when truly required.


----------

# Generators (Spark CLI)

## Observer

**Syntax**

```bash
php spark app:make:observer <Entity> [--classic-studly] [--force] [--dry-run]

```

**Examples**

```bash
php spark app:make:observer Courses
php spark app:make:observer course_mapping
php spark app:make:observer course_mapping --classic-studly
php spark app:make:observer Courses --force
php spark app:make:observer Courses --dry-run

```

**Options**

```
--classic-studly   Use CourseMapping instead of Course_mapping
--force            Overwrite if file exists
--dry-run          Show output path; do not write

```

## Rules

**Syntax**

```bash
php spark app:make:rules <Entity> [--actions=create,update,delete] [--classic-studly] [--force] [--dry-run]

```

**Examples**

```bash
php spark app:make:rules Courses
php spark app:make:rules Courses --actions=create,update
php spark app:make:rules course_mapping --classic-studly
php spark app:make:rules Courses --force
php spark app:make:rules Courses --dry-run

```

**Options**

```
--actions          Comma list: create,update,delete (default: all)
--classic-studly   Use CourseMapping instead of Course_mapping
--force            Overwrite if file exists
--dry-run          Show output paths; do not write

```

## Template

**Syntax**

```bash
php spark app:make:template <Entity> [--columns=a,b,c] [--sample=JSON|key=value,...] [--classic-studly] [--force] [--dry-run]

```

**Examples**

```bash
php spark app:make:template Courses
php spark app:make:template Courses --columns course_code,course_title,department_code
php spark app:make:template Courses --sample '{"course_code":"BUS101","course_title":"Business Intelligence"}'
php spark app:make:template Courses --sample course_code=BUS101,course_title='Business Intelligence',department_code=ECO
php spark app:make:template course_mapping --classic-studly
php spark app:make:template Courses --force
php spark app:make:template Courses --dry-run

```

**Options**

```
--columns          Comma list of headers (supports "--columns a,b" or "--columns=a,b")
--sample           JSON or key=value CSV (supports "--sample v" or "--sample=v")
--classic-studly   Use CourseMapping instead of Course_mapping
--force            Overwrite if file exists
--dry-run          Show output path; do not write

```

## Controller

**Syntax**

```bash
php spark app:make:controller <Name> [--in=Api/V1] [--entity=Courses] [--slug=courses] \
  [--preset=rest|rest+import] [--methods=index,show,store,update,delete,sample,import,ping] \
  [--extends=BaseController|ResourceController] [--force] [--dry-run]

```

**Examples**

```bash
php spark app:make:controller course_mapping
php spark app:make:controller course_mapping --in=Api/V1
php spark app:make:controller CourseMappingController --in=Api/V1
php spark app:make:controller course_mapping --entity=Courses --slug=courses
php spark app:make:controller course_mapping --preset=rest+import --slug=courses
php spark app:make:controller course_mapping --methods index,show,ping --slug=courses
php spark app:make:controller course_mapping --extends=ResourceController --slug=courses
php spark app:make:controller course_mapping --force
php spark app:make:controller course_mapping --dry-run

```

**Options**

```
--in               Subdirectory under app/Controllers (e.g., Api/V1)
--entity           Entity basename under App\Entities (default: derived from name)
--slug             Slug for EntityListTrait (default: auto; set explicitly for plural)
--preset           rest (default) | rest+import
--methods          Overrides preset; comma list of method stubs
--extends          BaseController (default) | ResourceController
--force            Overwrite if file exists
--dry-run          Show output path + stub; do not write

```

**Notes**

```
- Value options accept both "--opt value" and "--opt=value".
- Controller class naming: PascalCase + "Controller" (e.g., CourseMappingController).

```