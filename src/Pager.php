<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\URL;

class Pager
{
    public int $page;
    public int $limit;
    public int $offset;
    public ?int $next;
    public ?int $prev;
    public bool $has_next;
    public bool $has_prev;
    public bool $current;
    public int $num_pages;

    private string $query_part;

    /**
     * Get a pager that can be used for pagination and setting offset and limit in DB queries
     *
     * @param int $total total items in the set
     * @param int $limit results per page
     * @param string $query_part e.g. /index?page=1 where 'page' acts like the query part
     */
    public function __construct(int $total, int $limit, string $query_part = 'page')
    {
        $this->query_part = $query_part;

        $data = $this->getData($total, $limit);

        $this->page = $data['page'];
        $this->limit = $limit;
        $this->offset = $data['offset'];
        $this->next = $data['next'];
        $this->has_next = $data['has_next'];
        $this->prev = $data['prev'];
        $this->has_prev = $data['has_prev'];
        $this->num_pages = $data['num_pages'];
    }


    private function getFrom(): int
    {
        if (!URL::getQueryPart($this->query_part)) {
            $from = 1;
        } else {
            $from = (int)URL::getQueryPart($this->query_part);
            // If query part e.g. has been a string
            if (!$from) {
                $from = 1;
            }
            
        }

        return $from;
    }

    private function getOffset(int $from, int $limit): int
    {
        $offset = ($from - 1) * $limit;
        return $offset;
    }

    /**
     * @return array<mixed>
     */
    public function getData(int $total, int $limit): array
    {
        $data = [];
        if ($total === 0) {
            $data['num_pages'] = 0;
        } else {
            $data['num_pages'] = (int)ceil($total / $limit);
        }

        $from = $this->getFrom();
        $data['page'] = $from;

        $offset = $this->getOffset($from, $limit);
        $data['offset'] = $offset;

        $more = false;
        if ($offset + $limit < $total) {
            $more = true;
        }

        $data['next'] = null;
        $data['has_next'] = false;
        if ($more) {
            $data['next'] = $data['page'] + 1;
            $data['has_next'] = true;
        }

        $data['prev'] = null;
        $data['has_prev'] = false;
        if ($from > 1) {
            $data['prev'] = $from - 1;
            $data['has_prev'] = true;
        }

        return $data;
    }
}
