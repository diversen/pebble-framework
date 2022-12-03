<?php

declare(strict_types=1);

namespace Pebble\Pagination;

use InvalidArgumentException;
use Exception;
use Pebble\URL;
use JasonGrimes\Paginator;

/**
 * Class that helps render pagination with order_by, from, and saves the order_by and from in session.
 */
class PaginationUtils
{
    /**
     * Default order by
     * @var array<mixed>
     */
    private array $order_by_default = [];

    /**
     * Default order by set in constructor
     * @var array<mixed>
     */
    private array $order_by_default_init = [];

    /**
     * Change  'ORDER BY' order dynamically
     * e.g. `['title' => 'ASC', 'updated' => 'DESC']` to `['updated' => 'ASC', 'title' => 'ASC']`
     * If false then only the DIRECTION part of the ORDER BY will change
     */
    private bool $should_change_field_order = true;

    /**
     * @param array<mixed> $order_by_default e.g. `['title' => 'ASC', 'updated' => 'DESC']`.
     * @param string $session_key  store the ordering in SESSION
     */
    public function __construct(array $order_by_default, $session_key = null)
    {
        $this->order_by_default_init = $order_by_default;
        $this->order_by_default = $order_by_default;
        if ($session_key) {
            $this->order_by_default = $_SESSION[$session_key] ?? $order_by_default;
            if (!$this->validateFields($this->order_by_default)) {
                $this->order_by_default = $this->order_by_default_init;
            }
        }
    }

    public function setShouldChangeFieldOrder(bool $val): void
    {
        $this->should_change_field_order = $val;
    }

    /**
     * Validate a field. Checks if it is set `$order_by_default` fields
     */
    private function validateField(string $order_by): void
    {
        $fields = array_keys($this->order_by_default_init);
        if (!in_array($order_by, $fields)) {
            throw new InvalidArgumentException("$order_by is not a allowed order by field");
        }
    }

    /**
     * Checks diretion, it can only be 'ASC' or 'DESC'
     */
    private function validateDirection(string $direction): void
    {
        $direction = mb_strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException("$direction is not an allowed order by direction");
        }
    }

    /**
     * @param array<mixed> $order_by
     * @return array<mixed>
     */
    private function getNewOrderBy(array $order_by): array
    {
        // Check if ORDER BY should be altered
        $order_by_field = $_GET['alter'] ?? null;
        if (!$order_by_field) {
            return $order_by;
        }

        $this->validateField($order_by_field);

        // Change direction of field
        if ($order_by[$order_by_field] === 'ASC') {
            $direction = 'DESC';
        } else {
            $direction = 'ASC';
        }

        if (!$this->should_change_field_order) {
            $order_by[$order_by_field] = $direction;
            return $order_by;
        }

        $new_order_by = [];

        // Set altered field as first ORDER BY
        $new_order_by[$order_by_field] = $direction;
        unset($order_by[$order_by_field]);

        // Add the rest of the fields from current ORDER BY
        foreach ($order_by as $field => $direction) {
            $new_order_by[$field] = $direction;
        }

        return $new_order_by;
    }

    /**
     * @param array<mixed> $order_by
     * @return bool
     */
    private function validateFields(array $order_by): bool
    {
        try {
            foreach ($order_by as $field => $direction) {
                $this->validateField($field);
                $this->validateDirection($direction);
            }
        } catch (Exception $e) {
            return false;
        }

        $order_by_keys = array_keys($order_by);
        $needed_keys = array_keys($this->order_by_default_init);

        sort($order_by_keys);
        sort($needed_keys);

        if ($order_by_keys !== $needed_keys) {
            return false;
        }

        return true;
    }

    /**
     * Get ORDER BY from GET or if not set, use default (SESSION or constructor)
     * @return array<mixed> $order_by
     */
    public function getOrderByFromRequest(string $session_key): array
    {
        $order_by = $this->getOrderByFromQuery();

        if (!isset($_GET['order_by'])) {
            // Prefer session but else get default order by
            $order_by = $_SESSION[$session_key] ?? $order_by;
            if (!$this->validateFields($order_by)) {
                $order_by = $this->order_by_default_init;
            }
        } else {
            // New sorting. Save to session
            if ($this->validateFields($order_by)) {
                $_SESSION[$session_key] = $order_by;
            }
        }

        return $order_by;
    }

    /**
     * Get the ORDER BY parameters from the URL or order by from settings OR
     * get the default ORDER BY
     *
     * @return array<mixed> $order_by , e.g. `['title' => 'ASC', 'updated' => 'DESC']`
     */
    public function getOrderByFromQuery(): array
    {
        $order_by = $_GET['order_by'] ?? null;
        if (!$order_by) {
            return $this->order_by_default;
        }

        // Validate
        foreach ($order_by as $field => $direction) {
            $this->validateField($field);
            $this->validateDirection($direction);
        }

        return $this->getNewOrderBy($order_by);
    }


    /**
     * Build a query URL pattern can be used with JasonGrimes/Paginator
     */
    public function getPaginationURLPattern(string $url): string
    {
        $query['order_by'] = $this->getOrderByFromQuery();
        $query_str = http_build_query($query);
        $url_pattern = $url . '?' . $query_str . '&' . 'page=(:num)';
        return $url_pattern;
    }


    /**
     * Get a URL where a new ORDER BY is indicated using `$_GET['alter'] = 'field'`
     * @param string $field
     */
    public function getAlterOrderUrl(string $field): string
    {
        $query['order_by'] = $this->getOrderByFromQuery();
        $query['page'] = URL::getQueryPart('page') ?? 1;
        $query['page'] = (int) $query['page'];

        $route = strtok($_SERVER["REQUEST_URI"], '?');
        return  $route . '?' . http_build_query($query) . "&alter=$field";
    }

    /**
     * Get a arrow showing current direction of a field
     */
    public function getCurrentDirectionArrow(string $field): string
    {
        $order_by = $this->getOrderByFromQuery();
        $direction = $order_by[$field] ?? null;

        if ($direction == 'ASC') {
            return "↑";
        } else {
            return "↓";
        }
    }

    /**
     * Get JasonGrimes/Paginator and with order by saved to session
     * @return Paginator
     * @param array<mixed> $default_order
     */
    public static function getPaginator(
        int $total_items,
        int $items_per_page,
        int $current_page,
        string $url,
        array $default_order = [],
        int $max_pages = 10,
        string $session_key = null,
    ) {
        $pagination_utils = new PaginationUtils($default_order, $session_key);
        $url_pattern = $pagination_utils->getPaginationURLPattern($url);

        $paginator = new Paginator($total_items, $items_per_page, $current_page, $url_pattern);
        $paginator->setMaxPagesToShow($max_pages);

        return $paginator;
    }
}
