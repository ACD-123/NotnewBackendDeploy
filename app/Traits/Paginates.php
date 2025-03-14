<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/2021
 * Time: 3:40 AM
 */

namespace App\Traits;

trait Paginates
{
    protected $pageSize = 100;
    protected $pageSize_homepage = 10;
    protected $pageSize_reports = 100;

    public function getPageSize(): int
    {
        return request()->get('results', $this->pageSize);
    }

    public function getPageSize_homepage(): int
    {
        return request()->get('results', $this->pageSize_homepage);
    }

    public function getPageSize_reports(): int
    {
        return request()->get('results', $this->pageSize_reports);
    }
}
