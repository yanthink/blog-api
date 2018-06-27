<?php
/**
 * Created by PhpStorm.
 * User: Einsition
 * Date: 2018/6/2
 * Time: 下午3:24
 */

/**
 * 查询sql日志
 *
 * @param bool|true $all
 * @return array|mixed
 */
if (!function_exists('q')) {
    function q($all = true)
    {
        $queries = DB::getQueryLog();

        if ($all == false) {
            $lastQuery = end($queries);

            return $lastQuery;
        }

        return $queries;
    }
}

/**
 * 获取当前登录用户信息
 * @param null $column
 * @return \App\Models\User|bool|null
 */
if (!function_exists('user')) {
    function user($column = null)
    {
        if (Auth::guest()) {
            return false;
        }

        return $column ? Auth::user()->$column : Auth::user();
    }
}
/**
 * 获取group分页
 *
 * @param Illuminate\Database\Eloquent\Builder | Illuminate\Database\Eloquent\Relations\Relation $builder
 * @param int $perPage
 * @param array $columns
 * @param string $pageName
 * @param int $page
 * @return Illuminate\Pagination\LengthAwarePaginator
 */
if (!function_exists('get_group_pages')) {
    function get_group_pages($builder, $perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $builder->getModel()->getPerPage();

        $bindings = $builder->getQuery()->getBindings();

        $builderPage = clone $builder;

        $builderPage->getQuery()->orders = null;//去掉无意义的排序

        $total = DB::connection($builder->getModel()->getConnectionName())
            ->select('SELECT count(1) AS num FROM (' . $builderPage->toSql() . ') AS t', $bindings)[0]->num;

        $results = $total ? $builder->forPage($page, $perPage)->get($columns) : [];

        return new Illuminate\Pagination\LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}