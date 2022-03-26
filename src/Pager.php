<?php

namespace Pebble;

use Pebble\URL;

class Pager
{

    /**
     * Current page in the set. 0 is the first page.
     */
    public int $page;
    public int $limit;
    public int $offset;
    public ?int $next;
    public ?int $prev;
    public int $num_pages;

    public string $query_part;

    public function __construct(int $total, int $limit, string $query_part = 'page')
    {

        $this->query_part = $query_part;
        $data = $this->getData($total, $limit);

        $this->page = $data['page'];
        $this->limit = $limit;
        $this->offset = $data['offset'];
        $this->next = $data['next'];
        $this->prev = $data['prev'];
        $this->num_pages = $data['num_pages'];
    }

    public function getData(int $total, int $limit): array
    {

        $data = [];
        if ($total === 0) {
            $data['num_pages'] = 0;
        } else {
            $data['num_pages'] = ceil($total / $limit);
        }

        $from = (int)URL::getQueryPart($this->query_part) ?? 0;
        $data['page'] = $from;

        $offset = $from * $limit;
        $data['offset'] = $offset;

        $more = false;
        if ($offset + $limit < $total) {
            $more = true;
        }

        $data['next'] = null;
        if ($more) {
            $data['next'] = $data['page'] + 1;
        }

        $data['prev'] = null;
        if ($from > 0) {
            $data['prev'] = $from - 1;
        }

        return $data;
    }
}
