<?php


declare(strict_types=1);

namespace MsPro\Generator\Traits;

trait MapperGeneratorTraits
{
    /**
     * 获取搜索代码
     * 追加In.ADD.JENA.20230411
     * @param $column
     * @return string
     */
    protected function getSearchCode($column): string
    {
        return match ($column['query_type']) {
            'neq' => $this->getSearchPHPString($column['column_name'], '!=', $column['column_comment']),
            'gt' => $this->getSearchPHPString($column['column_name'], '<', $column['column_comment']),
            'gte' => $this->getSearchPHPString($column['column_name'], '<=', $column['column_comment']),
            'lt' => $this->getSearchPHPString($column['column_name'], '>', $column['column_comment']),
            'lte' => $this->getSearchPHPString($column['column_name'], '>=', $column['column_comment']),
            'like' => $this->getSearchPHPString($column['column_name'], 'like', $column['column_comment']),
            'between' => $this->getSearchPHPString($column['column_name'], 'between', $column['column_comment']),
            'in' => $this->getSearchPHPString($column['column_name'], 'in', $column['column_comment']),
            'relation' => $this->getSearchPHPString($column['column_name'], 'relation', $column['column_comment']),
            default => $this->getSearchPHPString($column['column_name'], '=', $column['column_comment']),
        };
    }

    /**
     * @param $name
     * @param $mark
     * @param $comment
     * @return string
     */
    protected function getSearchPHPString($name, $mark, $comment): string
    {
        if ($mark == 'like') {
            return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && \$params['{$name}'] !== '') {
            \$query->where('{$name}', 'like', '%'.\$params['{$name}'].'%');
        }

php;

        }

        if ($mark == 'between') {
            return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && is_array(\$params['{$name}']) && count(\$params['{$name}']) == 2) {
            \$query->whereBetween(
                '{$name}',
                [ \$params['{$name}'][0], \$params['{$name}'][1] ]
            );
        }

php;
        }

        if ($mark == 'in') {
            return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && \$params['{$name}'] !== '') {
            \$query->whereIn('{$name}', \$params['{$name}']);
        }

php;

        }

        if ($mark == 'relation') {
            return <<<php

        // 指定关系数据.ADD.JENA.20230605
        // 例如：withRelation=user:id,name#country:id,name
        if (isset(\$params['{$name}']) && \$params['{$name}'] !== '') {
            \$query->with(explode('#', \$params['{$name}']));
        }

php;

        }

        return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && \$params['{$name}'] !== '') {
            \$query->where('{$name}', '{$mark}', \$params['{$name}']);
        }

php;
    } // 该方法结束位置
}